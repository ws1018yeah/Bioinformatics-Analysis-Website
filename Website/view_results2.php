<?php
session_start();
include(__DIR__ . '/config/config.php');

try {
    $results = [];
    $table = isset($_GET['table']) ? $_GET['table'] : 'analysis_results';
    
    if (isset($_SESSION['user_id'])) {
        // 如果用户登录，查询 user_id 对应的记录
        $stmt = $pdo->prepare("SELECT * FROM $table WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$_SESSION['user_id']]);
        $results = $stmt->fetchAll();
    } else {
        // 如果用户未登录，查询 session_id 对应的记录
        $stmt = $pdo->prepare("SELECT * FROM $table WHERE session_id = ? ORDER BY created_at DESC");
        $stmt->execute([session_id()]);
        $results = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    die("数据库错误: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>分析记录</title>
    <link rel="stylesheet" href="assets/css/style3.css">
    <script>
        function confirmDelete(table, id) {
            if (confirm("确定要删除这条记录吗？")) {
                // 用户确认删除，提交表单
                var form = document.createElement('form');
                form.action = 'delete_record.php';
                form.method = 'POST';
                form.style.display = 'none';
                
                var tableInput = document.createElement('input');
                tableInput.type = 'hidden';
                tableInput.name = 'table';
                tableInput.value = table;
                
                var idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'id';
                idInput.value = id;
                
                form.appendChild(tableInput);
                form.appendChild(idInput);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</head>
<body>
    <h2>分析历史记录</h2>
    
    <!-- 添加按钮切换不同表 -->
    <div class="div-button-container">
        <a href="view_results2.php?table=analysis_results">所有分析记录</a> | 
        <a href="conservation_results.php">保守性分析记录</a> | 
        <a href="generate_similarity_matrix_results.php">相似性矩阵记录</a> | 
        <a href="multiple_sequence_alignment_results.php">多序列比对记录</a> | 
        <a href="pattern_scan_results.php">模式扫描记录</a>
        <a href="index2.php">返回主页</a>
    </div>

    <?php if (empty($results)): ?>
        <p>没有找到历史记录<?php if (!isset($_SESSION['user_id'])) echo "，请登录后查看账户记录"; ?></p>
    <?php else: ?>
        <table>
            <tr>
                <th>时间</th>
                <th>蛋白质家族</th>
                <th>分类</th>
                <th>操作</th>
                <th>删除</th>
            </tr>
            <?php foreach ($results as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['created_at']) ?></td>
                <td><?= htmlspecialchars($row['protein_family']) ?></td>
                <td><?= htmlspecialchars($row['taxonomy']) ?></td>
                <td>
                    <?php if (!empty($row['fasta_file'])): ?>
                        <a href="<?= htmlspecialchars($row['fasta_file']) ?>">FASTA 序列</a><br>
                    <?php endif; ?>
                    <?php if (!empty($row['patterns_file'])): ?>
                        <a href="<?= htmlspecialchars($row['patterns_file']) ?>">模式扫描结果</a><br>
                    <?php endif; ?>
                    <?php if (!empty($row['plotcon_file'])): ?>
                        <a href="<?= htmlspecialchars($row['plotcon_file']) ?>">保守性分析图</a><br>
                    <?php endif; ?>
                    <?php if (!empty($row['aligned_file'])): ?>
                        <a href="<?= htmlspecialchars($row['aligned_file']) ?>">多序列比对结果</a><br>
                    <?php endif; ?>
                    <?php if (!empty($row['similarity_matrix'])): ?>
                        <a href="<?= htmlspecialchars($row['similarity_matrix']) ?>">相似性矩阵</a><br>
                    <?php endif; ?>
                    <?php if (!empty($row['heatmap_file'])): ?>
                        <a href="<?= htmlspecialchars($row['heatmap_file']) ?>">相似性热图</a>
                    <?php endif; ?>
                </td>
                <td>
                    <button onclick="confirmDelete('<?= $table ?>', <?= $row['id'] ?>)">删除</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</body>
</html>