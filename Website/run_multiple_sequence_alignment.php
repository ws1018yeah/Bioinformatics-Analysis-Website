<?php
// Include database configuration
include(__DIR__ . '/config/config.php');
include(__DIR__ . '/config2.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve user-pasted FASTA sequence
    $fasta_sequence = trim($_POST['fasta_sequence']);
    
    // Save FASTA sequence to a temporary file
    $data_dir = __DIR__ . '/data';
    if (!is_dir($data_dir)) {
        mkdir($data_dir, 0755, true);
    }

    // Generate a unique filename
    $fasta_filename = 'alignment_' . session_id() . '_' . time() . '.fasta';
    $fasta_path = $data_dir . '/' . $fasta_filename;
    file_put_contents($fasta_path, $fasta_sequence);

    // Construct command, append 2>&1 to capture standard error
    $command = "$PYTHON_EXE " . __DIR__ . "/scripts/multiple_sequence_alignment.py $fasta_path 2>&1";

    // Execute command and capture output
    exec($command, $output, $return_var);

    echo "<html><head><title>Multiple Sequence Alignment Results</title></head><body>";
    echo "<style>
            body { font-family: Arial, sans-serif; background: #f7f9fc; padding: 20px; }
            h2 { color: #1E3D7B; font-size: 32px; text-align: center; }
            .result { text-align: center; margin-top: 30px; }
            .result p { font-size: 18px; }
            .button { 
                background-color: #1E3D7B; 
                color: white; 
                padding: 10px 20px; 
                font-size: 18px; 
                border: none; 
                border-radius: 6px; 
                cursor: pointer;
                text-decoration: none; 
                margin-top: 20px;
            }
            .button:hover { background-color: #3f5c9d; }
          </style>";
    
    // Define hardcoded directory and construct file paths
    $data_dir_abs = "/home/s2682415/public_html/Website/data";
    $temp_base = basename($fasta_path);
    $root = pathinfo($temp_base, PATHINFO_FILENAME);
    $aligned_file_abs = "$data_dir_abs/{$root}_aligned.aln";
    $aligned_file_url = "data/{$root}_aligned.aln";

    // Ensure fasta_file_url is a valid path
    $fasta_file_url = 'data/' . $fasta_filename;

    if ($return_var === 0) {
        echo "<h2>Multiple Sequence Alignment Results</h2>";  // Keep only this title here
        echo "<h3>Output Files:</h3>";
        echo "<ul>";
        echo file_exists($aligned_file_abs) ? "<li><a href='$aligned_file_url' target='_blank'>Alignment Results</a></li>" : "<li>Alignment Results file not found.</li>";
        echo "</ul>";

        // Save analysis log into the database
        $analysis_log = implode("\n", $output);
        try {
            // Insert into multiple_sequence_alignment_results table
            $sql = "INSERT INTO multiple_sequence_alignment_results (aligned_file, analysis_log, created_at, user_id, session_id, fasta_file)
                    VALUES (:aligned_file, :analysis_log, NOW(), :user_id, :session_id, :fasta_file)";
            $stmt = $pdo->prepare($sql);
            
            // Set user_id if logged in, otherwise set to NULL
            $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
            
            // Get current session ID
            $session_id = session_id();
            
            $stmt->execute([
                ':aligned_file' => $aligned_file_url,
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

    echo "<div class='result'><a href='index2.php' class='button'>Return to Home</a></div>";
    echo "</body></html>";
} else {
    header("Location: index2.php");
    exit();
}
?>
