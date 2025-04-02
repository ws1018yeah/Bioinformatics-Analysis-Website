<?php
// view_results.php
// 引入数据库连接配置
include(__DIR__ . '/config/config.php');

// 查询所有分析记录，按时间降序排序
$sql = "SELECT * FROM analysis_results ORDER BY created_at DESC";
$stmt = $pdo->query($sql);
$results = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>查看之前生成的数据</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        table { border-collapse: collapse; width: 100%; }
        table, th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
        th { background-color: #f4f4f4; }
    </style>
</head>
<body>
    <h2>之前生成的分析记录</h2>
    <?php if ($results): ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Protein Family</th>
                <th>Taxonomy</th>
                <th>Email</th>
                <th>FASTA文件</th>
                <th>Motif扫描结果</th>
                <th>保守性图像</th>
                <th>Alignment结果</th>
                <th>相似性矩阵</th>
                <th>相似性热图</th>
                <th>日志</th>
                <th>创建时间</th>
            </tr>
            <?php foreach ($results as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['protein_family']); ?></td>
                    <td><?php echo htmlspecialchars($row['taxonomy']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td>
                        <?php
                        if (file_exists($row['fasta_file'])) {
                            echo "<a href='" . htmlspecialchars($row['fasta_file']) . "' target='_blank'>查看</a>";
                        } else {
                            echo "未找到";
                        }
                        ?>
                    </td>
                    <td>
                        <?php
                        if (file_exists($row['patterns_file'])) {
                            echo "<a href='" . htmlspecialchars($row['patterns_file']) . "' target='_blank'>查看</a>";
                        } else {
                            echo "未找到";
                        }
                        ?>
                    </td>
                    <td>
                        <?php
                        if (file_exists($row['plotcon_file'])) {
                            echo "<a href='" . htmlspecialchars($row['plotcon_file']) . "' target='_blank'>查看</a>";
                        } else {
                            echo "未找到";
                        }
                        ?>
                    </td>
                    <td>
                        <?php
                        $alignedPath = $basePath . '/' . $row['aligned_file'];
                        if (file_exists($alignedPath)) {
                            echo "<a href='" . htmlspecialchars($row['aligned_file']) . "' target='_blank'>查看</a>";
                        } else {
                            echo "未找到";
                        }
                        ?>
                    </td>
                    <td>
                        <?php
                        $simMatrixPath = $basePath . '/' . $row['similarity_matrix'];
                        if (file_exists($simMatrixPath)) {
                            echo "<a href='" . htmlspecialchars($row['similarity_matrix']) . "' target='_blank'>查看</a>";
                        } else {
                            echo "未找到";
                        }
                        ?>
                    </td>
                    <td>
                        <?php
                        $heatmapPath = $basePath . '/' . $row['heatmap_file'];
                        if (file_exists($heatmapPath)) {
                            echo "<a href='" . htmlspecialchars($row['heatmap_file']) . "' target='_blank'>查看</a>";
                        } else {
                            echo "未找到";
                        }
                        ?>
                    </td>
                    <td><pre><?php echo htmlspecialchars($row['analysis_log']); ?></pre></td>
                    <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>当前还没有分析记录。</p>
    <?php endif; ?>
    <p><a href="index2.php">返回主页</a></p>
</body>
</html>