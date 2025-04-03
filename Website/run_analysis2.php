<?php
// 引入数据库配置
include(__DIR__ . '/config/config.php');
// 加载专用于分析流程的配置文件 config2.php
include(__DIR__ . '/config2.php');
// 新增Session启动
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 过滤和转义用户输入
    //$protein_family = trim($_POST['protein_family']);
    //$taxonomy = trim($_POST['taxonomy']);
    $email = trim($_POST['email']);
    $protein_family = preg_replace('/[^A-Za-z0-9_-]/', '', trim($_POST['protein_family']));
    $taxonomy = preg_replace('/[^A-Za-z0-9_-]/', '', trim($_POST['taxonomy']));

    // 不在这里替换空格，Python 脚本内部会处理
    $pf_arg = escapeshellarg($protein_family);
    $tax_arg = escapeshellarg($taxonomy);
    $email_arg = escapeshellarg($email);

    // 构造命令，追加 2>&1 捕获标准错误
    $command = "$PYTHON_EXE $PYTHON_SCRIPT $pf_arg $tax_arg $email_arg 2>&1";

    // 输出调试信息（便于排查问题）
    echo "<html><head><title>Protein Analysis Results</title></head><body>";
    echo "<h2>Protein Analysis Results</h2>";
    echo "<p><strong>Debug Info:</strong></p>";
    echo "<p>Python executable: " . htmlspecialchars($PYTHON_EXE) . "</p>";
    echo "<p>Python script: " . htmlspecialchars($PYTHON_SCRIPT) . "</p>";
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
    $family_clean = preg_replace("/[^A-Za-z0-9_\\-]/", "_", $protein_family);
    $taxonomy_clean = preg_replace("/[^A-Za-z0-9_\\-]/", "_", $taxonomy);

    // 后端文件系统路径
    $fasta_file_abs = "$data_dir_abs/{$family_clean}_{$taxonomy_clean}.fasta";
    $patterns_file_abs = "$data_dir_abs/{$family_clean}_{$taxonomy_clean}_patterns.txt";
    $plotcon_file_abs = "$data_dir_abs/{$family_clean}_{$taxonomy_clean}_plotcon.png";
    $aligned_file_abs = "$data_dir_abs/{$family_clean}_{$taxonomy_clean}_aligned.aln";
    $similarity_matrix_abs = "$data_dir_abs/{$family_clean}_{$taxonomy_clean}_similarity.mat";
    $heatmap_file_abs = "$data_dir_abs/{$family_clean}_{$taxonomy_clean}_similarity_heatmap.png";

    // 用于生成网页链接的相对路径（相对于网站根目录 /home/s2682415/public_html/Website/）
    $fasta_file_url = "data/{$family_clean}_{$taxonomy_clean}.fasta";
    $patterns_file_url = "data/{$family_clean}_{$taxonomy_clean}_patterns.txt";
    $plotcon_file_url = "data/{$family_clean}_{$taxonomy_clean}_plotcon.png";
    $aligned_file_url = "data/{$family_clean}_{$taxonomy_clean}_aligned.aln";
    $similarity_matrix_url = "data/{$family_clean}_{$taxonomy_clean}_similarity.mat";
    $heatmap_file_url = "data/{$family_clean}_{$taxonomy_clean}_similarity_heatmap.png";

    if ($return_var === 0) {
        echo "<h3>Output Files:</h3>";
        echo "<ul>";
        echo file_exists($fasta_file_abs) ? "<li><a href='$fasta_file_url' target='_blank'>FASTA Sequences</a></li>" : "<li>FASTA Sequences file not found.</li>";
        echo file_exists($patterns_file_abs) ? "<li><a href='$patterns_file_url' target='_blank'>Motif Scan Results</a></li>" : "<li>Motif Scan Results file not found.</li>";
        echo file_exists($plotcon_file_abs) ? "<li><a href='$plotcon_file_url' target='_blank'>Conservation Analysis Plot</a></li>" : "<li>Conservation Analysis Plot not found.</li>";
        echo file_exists($aligned_file_abs) ? "<li><a href='$aligned_file_url' target='_blank'>Alignment Results</a></li>" : "<li>Alignment Results file not found.</li>";
        echo file_exists($similarity_matrix_abs) ? "<li><a href='$similarity_matrix_url' target='_blank'>Similarity Matrix</a></li>" : "<li>Similarity Matrix file not found.</li>";
        echo file_exists($heatmap_file_abs) ? "<li><a href='$heatmap_file_url' target='_blank'>Similarity Heatmap</a></li>" : "<li>Similarity Heatmap file not found.</li>";
        echo "</ul>";

        // 将分析记录保存到数据库中
        $analysis_log = implode("\n", $output);
        try {
            $sql = "INSERT INTO analysis_results (protein_family, taxonomy, email, fasta_file, patterns_file, plotcon_file, aligned_file, similarity_matrix, heatmap_file, analysis_log, created_at, user_id, session_id)
                    VALUES (:protein_family, :taxonomy, :email, :fasta_file, :patterns_file, :plotcon_file, :aligned_file, :similarity_matrix, :heatmap_file, :analysis_log, NOW(), :user_id, :session_id)";
            $stmt = $pdo->prepare($sql);
            
            // 如果用户登录，则设置 user_id，否则设置为 NULL
            $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
            
            // 获取当前会话ID
            $session_id = session_id();
            
            $stmt->execute([
                ':protein_family' => $protein_family,
                ':taxonomy' => $taxonomy,
                ':email' => $email,
                ':fasta_file' => $fasta_file_url,
                ':patterns_file' => $patterns_file_url,
                ':plotcon_file' => $plotcon_file_url,
                ':aligned_file' => $aligned_file_url,
                ':similarity_matrix' => $similarity_matrix_url,
                ':heatmap_file' => $heatmap_file_url,
                ':analysis_log' => $analysis_log,
                ':user_id' => $user_id,
                ':session_id' => $session_id
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