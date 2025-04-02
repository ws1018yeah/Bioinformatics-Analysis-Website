<?php
session_start();
include(__DIR__ . '/config/config.php');

try {
    $results = [];
    
    if (isset($_SESSION['user_id'])) {
        // 如果用户登录，查询 user_id 对应的记录
        $stmt = $pdo->prepare("SELECT * FROM generate_similarity_matrix_results WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$_SESSION['user_id']]);
        $results = $stmt->fetchAll();
    } else {
        // 如果用户未登录，查询 session_id 对应的记录
        $stmt = $pdo->prepare("SELECT * FROM generate_similarity_matrix_results WHERE session_id = ? ORDER BY created_at DESC");
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
    <title>相似性矩阵记录</title>
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
    <h2>相似性矩阵记录</h2>
    
    <div class="div-button-container">
        <a href="view_results2.php">返回所有分析记录</a>
        <a href="index2.php">返回主页</a>
    </div>

    <?php if (empty($results)): ?>
        <p>没有找到相似性矩阵记录<?php if (!isset($_SESSION['user_id'])) echo "，请登录后查看账户记录"; ?></p>
    <?php else: ?>
        <table>
            <tr>
                <th>时间</th>
                <th>FASTA 序列</th>
                <th>相似性矩阵</th>
                <th>相似性热图</th>
                <th>操作</th>
            </tr>
            <?php foreach ($results as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['created_at']) ?></td>
                <td>
                    <?php if (!empty($row['fasta_file'])): ?>
                        <a href="<?= htmlspecialchars($row['fasta_file']) ?>" target="_blank">FASTA 序列</a>
                    <?php else: ?>
                        <span>FASTA 序列未提供</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if (!empty($row['similarity_matrix'])): ?>
                        <a href="<?= htmlspecialchars($row['similarity_matrix']) ?>" target="_blank">相似性矩阵</a>
                    <?php else: ?>
                        <span>相似性矩阵未提供</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if (!empty($row['heatmap_file'])): ?>
                        <a href="<?= htmlspecialchars($row['heatmap_file']) ?>" target="_blank">相似性热图</a>
                    <?php else: ?>
                        <span>相似性热图未提供</span>
                    <?php endif; ?>
                </td>
                <td>
                    <button onclick="confirmDelete('generate_similarity_matrix_results', <?= $row['id'] ?>)">删除</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</body>
</html>