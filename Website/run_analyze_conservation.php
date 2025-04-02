<?php
// 引入数据库配置
include(__DIR__ . '/config/config.php');
include(__DIR__ . '/config2.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 获取用户粘贴的FASTA序列
    $fasta_sequence = trim($_POST['fasta_sequence']);
    
    // 将FASTA序列保存到临时文件
    //$temp_fasta_file = tempnam(sys_get_temp_dir(), 'fasta_');
    //file_put_contents($temp_fasta_file, $fasta_sequence);
    
    // 构造命令，追加 2>&1 捕获标准错误
    //$command = "$PYTHON_EXE " . __DIR__ . "/scripts/analyze_conservation.py $temp_fasta_file 2>&1";
    // 替换原有tempnam逻辑
    $data_dir = __DIR__ . '/data';
    if (!is_dir($data_dir)) {
    mkdir($data_dir, 0755, true);
    }

    // 生成唯一文件名
    $fasta_filename = 'conservation_' . session_id() . '_' . time() . '.fasta';
    $fasta_path = $data_dir . '/' . $fasta_filename;
    file_put_contents($fasta_path, $fasta_sequence);

    // 修改Python命令路径
    $command = "$PYTHON_EXE " . __DIR__ . "/scripts/analyze_conservation.py $fasta_path 2>&1";

 
    // 输出调试信息（便于排查问题）
    echo "<html><head><title>Conservation Analysis Results</title></head><body>";
    echo "<h2>Conservation Analysis Results</h2>";
    echo "<p><strong>Debug Info:</strong></p>";
    echo "<p>Python executable: " . htmlspecialchars($PYTHON_EXE) . "</p>";
    echo "<p>Python script: " . htmlspecialchars(__DIR__ . "/scripts/analyze_conservation.py") . "</p>";
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

    // 自动查找生成的文件
    $data_dir_abs = "/home/s2682415/public_html/Website/data";
    $plotcon_file_abs = "";
    foreach (glob("$data_dir_abs/*.png") as $file) {
        if (strpos($file, 'plotcon') !== false) {
            $plotcon_file_abs = $file;
            break;
        }
    }

    // 用于生成网页链接的相对路径
    $plotcon_file_url = str_replace($data_dir_abs, "data", $plotcon_file_abs);

    // 确保 fasta_file_url 是一个有效的路径
   // $fasta_file_url = 'data/' . basename($temp_fasta_file);
    $fasta_file_url = 'data/' . $fasta_filename;

    if ($return_var === 0 && !empty($plotcon_file_abs) && file_exists($plotcon_file_abs)) {
        echo "<h3>Output Files:</h3>";
        echo "<ul>";
        echo "<li><a href='$plotcon_file_url' target='_blank'>Conservation Analysis Plot</a></li>";
        echo "</ul>";

        // 将分析记录保存到数据库中
        $analysis_log = implode("\n", $output);
        try {
            // 插入到 analyze_conservation_results 表
            $sql = "INSERT INTO analyze_conservation_results (plotcon_file, analysis_log, created_at, user_id, session_id, fasta_file)
                    VALUES (:plotcon_file, :analysis_log, NOW(), :user_id, :session_id, :fasta_file)";
            $stmt = $pdo->prepare($sql);
            
            // 如果用户登录，则设置 user_id，否则设置为 NULL
            $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
            
            // 获取当前会话ID
            $session_id = session_id();
            
            $stmt->execute([
                ':plotcon_file' => $plotcon_file_url,
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
        echo "<p>Conservation Analysis Plot not found.</p>";
        echo "<p>Please check the above log for details.</p>";
    }

    echo "<p><a href='index2.php'>Return to Home</a></p>";
    echo "</body></html>";
} else {
    header("Location: index2.php");
    exit();
}
?>