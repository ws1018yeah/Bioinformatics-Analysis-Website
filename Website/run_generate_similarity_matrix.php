<?php
// 引入数据库配置
include(__DIR__ . '/config/config.php');
include(__DIR__ . '/config2.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 获取用户粘贴的FASTA序列
    $fasta_sequence = trim($_POST['fasta_sequence']);
    
    // 将FASTA序列保存到临时文件
    $data_dir = __DIR__ . '/data';
    if (!is_dir($data_dir)) {
        mkdir($data_dir, 0755, true);
    }

    // 生成唯一文件名
    $fasta_filename = 'similarity_' . session_id() . '_' . time() . '.fasta';
    $fasta_path = $data_dir . '/' . $fasta_filename;
    file_put_contents($fasta_path, $fasta_sequence);

    // 构造命令，追加 2>&1 捕获标准错误
    $command = "$PYTHON_EXE " . __DIR__ . "/scripts/generate_similarity_matrix.py $fasta_path 2>&1";

    // 输出调试信息（便于排查问题）
    echo "<html><head><title>Similarity Matrix and Heatmap Results</title></head><body>";
    echo "<h2>Similarity Matrix and Heatmap Results</h2>";
    echo "<p><strong>Debug Info:</strong></p>";
    echo "<p>Python executable: " . htmlspecialchars($PYTHON_EXE) . "</p>";
    echo "<p>Python script: " . htmlspecialchars(__DIR__ . "/scripts/generate_similarity_matrix.py") . "</p>";
    echo "<p>Command: " . htmlspecialchars($command) . "</p>";

    // 执行命令并捕获返回信息
    exec($command, $output, $return_var);

    echo "<p>Return Code: " . $return_var . "</p>";
    echo "<h3>Execution Log:</h3>";
    echo "<pre>";
    foreach ($output as $line) {
        echo htmlspecialchars($line) . "\n";
    }
    echo "</pre>";

    // 定义硬编码的目录和构造文件路径
    $data_dir_abs = "/home/s2682415/public_html/Website/data";
    $temp_base = basename($fasta_path);
    $root = pathinfo($temp_base, PATHINFO_FILENAME);
    $similarity_matrix_abs = "$data_dir_abs/{$root}_similarity.mat";
    $heatmap_file_abs = "$data_dir_abs/{$root}_similarity_heatmap.png";
    $similarity_matrix_url = "data/{$root}_similarity.mat";
    $heatmap_file_url = "data/{$root}_similarity_heatmap.png";

    // 确保 fasta_file_url 是一个有效的路径
    $fasta_file_url = 'data/' . $fasta_filename;

    if ($return_var === 0) {
        echo "<h3>Output Files:</h3>";
        echo "<ul>";
        echo file_exists($similarity_matrix_abs) ? "<li><a href='$similarity_matrix_url' target='_blank'>Similarity Matrix</a></li>" : "<li>Similarity Matrix file not found.</li>";
        echo file_exists($heatmap_file_abs) ? "<li><a href='$heatmap_file_url' target='_blank'>Similarity Heatmap</a></li>" : "<li>Similarity Heatmap file not found.</li>";
        echo "</ul>";

        // 将分析记录保存到数据库中
        $analysis_log = implode("\n", $output);
        try {
            // 插入到 generate_similarity_matrix_results 表
            $sql = "INSERT INTO generate_similarity_matrix_results (similarity_matrix, heatmap_file, analysis_log, created_at, user_id, session_id, fasta_file)
                    VALUES (:similarity_matrix, :heatmap_file, :analysis_log, NOW(), :user_id, :session_id, :fasta_file)";
            $stmt = $pdo->prepare($sql);
            
            // 如果用户登录，则设置 user_id，否则设置为 NULL
            $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
            
            // 获取当前会话ID
            $session_id = session_id();
            
            $stmt->execute([
                ':similarity_matrix' => $similarity_matrix_url,
                ':heatmap_file' => $heatmap_file_url,
                ':analysis_log' => $analysis_log,
                ':user_id' => $user_id,
                ':session_id' => $session_id,
                ':fasta_file' => $fasta_file_url
            ]);
            echo "<p>Data successfully inserted into the database.</p>";
        } catch (PDOException $e) {
            echo "<p>Error inserting data: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    } else {
        echo "<h2>Error occurred during analysis</h2>";
        echo "<p>Please check the above log for details.</p>";
    }

    echo "<p><a href='index2.php'>Return to Home</a></p>";
    echo "</body></html>";
} else {
    header("Location: index2.php");
    exit();
}
?>