<?php 
// Include database configuration
include(__DIR__ . '/config/config.php');
include(__DIR__ . '/config2.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve the user-pasted FASTA sequence
    $fasta_sequence = trim($_POST['fasta_sequence']);
    
    // Save the FASTA sequence to a temporary file
    $data_dir = __DIR__ . '/data';
    if (!is_dir($data_dir)) {
        mkdir($data_dir, 0755, true);
    }

    // Generate a unique filename
    $fasta_filename = 'similarity_' . session_id() . '_' . time() . '.fasta';
    $fasta_path = $data_dir . '/' . $fasta_filename;
    file_put_contents($fasta_path, $fasta_sequence);

    // Check if the file exists
    if (!file_exists($fasta_path)) {
        echo "<p>FASTA file not found: $fasta_path</p>";
        exit();
    }

    // Set environment variable
    putenv('MPLCONFIGDIR=/tmp/matplotlib');

    // Construct the command, appending 2>&1 to capture standard errors
    $command = "python3 " . __DIR__ . "/scripts/generate_similarity_matrix.py $fasta_path 2>&1";

    // Execute the command and capture output
    exec($command, $output, $return_var);

    echo "<html><head><title>Similarity Matrix and Heatmap Results</title>";
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
    echo "</head><body>";
    echo "<h2>Similarity Matrix and Heatmap Results</h2>";

    // Define hardcoded directory and construct file paths
    $data_dir_abs = "/home/s2682415/public_html/Website/data";
    $temp_base = basename($fasta_path);
    $root = pathinfo($temp_base, PATHINFO_FILENAME);
    $similarity_matrix_abs = "$data_dir_abs/{$root}_similarity.mat";
    $heatmap_file_abs = "$data_dir_abs/{$root}_similarity_heatmap.png";
    $similarity_matrix_url = "data/{$root}_similarity.mat";
    $heatmap_file_url = "data/{$root}_similarity_heatmap.png";

    // Ensure fasta_file_url is a valid path
    $fasta_file_url = 'data/' . $fasta_filename;

    if ($return_var === 0) {
        echo "<h3>Output Files:</h3>";
        echo "<ul>";
        echo file_exists($similarity_matrix_abs) ? "<li><a href='$similarity_matrix_url' target='_blank'>Similarity Matrix</a></li>" : "<li>Similarity Matrix file not found.</li>";
        echo file_exists($heatmap_file_abs) ? "<li><a href='$heatmap_file_url' target='_blank'>Similarity Heatmap</a></li>" : "<li>Similarity Heatmap file not found.</li>";
        echo "</ul>";

        // Save analysis records to the database
        $analysis_log = implode("\n", $output);
        try {
            // Insert into generate_similarity_matrix_results table
            $sql = "INSERT INTO generate_similarity_matrix_results (similarity_matrix, heatmap_file, analysis_log, created_at, user_id, session_id, fasta_file)
                    VALUES (:similarity_matrix, :heatmap_file, :analysis_log, NOW(), :user_id, :session_id, :fasta_file)";
            $stmt = $pdo->prepare($sql);
            
            // Set user_id if the user is logged in, otherwise set it to NULL
            $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
            
            // Get the current session ID
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
        echo "<p>Please check the log above for details.</p>";
    }

    echo "<div class='result'><a href='index2.php' class='button'>Return to Home</a></div>";
    echo "</body></html>";
} else {
    header("Location: index2.php");
    exit();
}
?>
