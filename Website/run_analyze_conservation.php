<?php
// 引入数据库配置
include(__DIR__ . '/config/config.php');
include(__DIR__ . '/config2.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 获取用户粘贴的FASTA序列
    $fasta_sequence = trim($_POST['fasta_sequence']);
    
    // 获取过滤选项
    $taxonomy_filter = isset($_POST['taxonomy_filter']) ? trim($_POST['taxonomy_filter']) : '';
    $min_length = isset($_POST['min_length']) ? intval($_POST['min_length']) : 0;
    $max_length = isset($_POST['max_length']) ? intval($_POST['max_length']) : 0;
    $max_count = isset($_POST['max_count']) ? intval($_POST['max_count']) : 0;

    // 保存FASTA序列到 data 目录
    $data_dir = __DIR__ . '/data';
    if (!is_dir($data_dir)) {
        mkdir($data_dir, 0755, true);
    }
    // 生成唯一文件名
    $fasta_filename = 'conservation_' . session_id() . '_' . time() . '.fasta';
    $fasta_path = $data_dir . '/' . $fasta_filename;
    file_put_contents($fasta_path, $fasta_sequence);

    // 构造 Python 命令，传递过滤参数
    $command = "$PYTHON_EXE " . __DIR__ . "/scripts/analyze_conservation.py $fasta_path " .
               escapeshellarg($taxonomy_filter) . " " . $min_length . " " . $max_length . " " . $max_count . " 2>&1";

    // 输出调试信息
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

    if ($return_var === 0) {
        // 根据是否传入过滤选项来构造预期的输出文件名称
        if ($taxonomy_filter || $min_length || $max_length || $max_count) {
            // 如果有过滤，则输出文件名会在原始文件名中增加 _filtered
            $expected_file = str_replace(".fasta", "_filtered_plotcon.png", $fasta_filename);
        } else {
            $expected_file = str_replace(".fasta", "_plotcon.png", $fasta_filename);
        }
        $plotcon_file_abs = $data_dir . '/' . $expected_file;
        
        if (file_exists($plotcon_file_abs)) {
            // 构造输出文件的 URL
            $plotcon_file_url = "https://bioinfmsc8.bio.ed.ac.uk/~s2682415/Website/data/" . $expected_file;
            
            // 将分析记录保存到数据库中
            $analysis_log = implode("\n", $output);
            try {
                $sql = "INSERT INTO analyze_conservation_results (plotcon_file, analysis_log, created_at, user_id, session_id, fasta_file)
                        VALUES (:plotcon_file, :analysis_log, NOW(), :user_id, :session_id, :fasta_file)";
                $stmt = $pdo->prepare($sql);
                
                $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
                $session_id = session_id();
                
                $stmt->execute([
                    ':plotcon_file' => $plotcon_file_url,
                    ':analysis_log' => $analysis_log,
                    ':user_id' => $user_id,
                    ':session_id' => $session_id,
                    ':fasta_file' => 'data/' . $fasta_filename
                ]);
                echo "<p>Data successfully inserted into the database.</p>";
            } catch (PDOException $e) {
                echo "<p>Error inserting data: " . htmlspecialchars($e->getMessage()) . "</p>";
            }

            // 从数据库中读取当前会话最新的分析记录，并展示输出文件链接
            try {
                $sql = "SELECT plotcon_file FROM analyze_conservation_results WHERE session_id = :session_id ORDER BY created_at DESC LIMIT 1";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':session_id' => session_id()]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($result && !empty($result['plotcon_file'])) {
                    echo "<h3>Output Files (from Database):</h3>";
                    echo "<ul>";
                    echo "<li><a href='" . htmlspecialchars($result['plotcon_file']) . "' target='_blank'>Conservation Analysis Plot</a></li>";
                    echo "</ul>";
                } else {
                    echo "<p>No output file found in the database.</p>";
                }
            } catch (PDOException $e) {
                echo "<p>Error reading from database: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        } else {
            echo "<p>Conservation Analysis Plot not found.</p>";
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