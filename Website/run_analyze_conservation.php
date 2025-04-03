<?php  
// Include database configuration
include(__DIR__ . '/config/config.php');
include(__DIR__ . '/config2.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve user-pasted FASTA sequence
    $fasta_sequence = trim($_POST['fasta_sequence']);
    
    // Retrieve filtering options
    $taxonomy_filter = isset($_POST['taxonomy_filter']) ? trim($_POST['taxonomy_filter']) : '';
    $min_length = isset($_POST['min_length']) ? intval($_POST['min_length']) : 0;
    $max_length = isset($_POST['max_length']) ? intval($_POST['max_length']) : 0;
    $max_count = isset($_POST['max_count']) ? intval($_POST['max_count']) : 0;

    // Save FASTA sequence to the data directory
    $data_dir = __DIR__ . '/data';
    if (!is_dir($data_dir)) {
        mkdir($data_dir, 0755, true);
    }
    // Generate a unique filename
    $fasta_filename = 'conservation_' . session_id() . '_' . time() . '.fasta';
    $fasta_path = $data_dir . '/' . $fasta_filename;
    file_put_contents($fasta_path, $fasta_sequence);

    // Construct Python command, passing filter parameters
    $command = "$PYTHON_EXE " . __DIR__ . "/scripts/analyze_conservation.py $fasta_path " . 
               escapeshellarg($taxonomy_filter) . " " . $min_length . " " . $max_length . " " . $max_count . " 2>&1";

    // Execute command and capture output
    exec($command, $output, $return_var);

    echo "<html><head><title>Conservation Analysis Results</title>";
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

    // Add the title for the results page
    echo "<h2>Conservation Analysis Results</h2>";  // Add title here

    if ($return_var === 0) {
        // Construct expected output file name based on filter options
        if ($taxonomy_filter || $min_length || $max_length || $max_count) {
            $expected_file = str_replace(".fasta", "_filtered_plotcon.png", $fasta_filename);
        } else {
            $expected_file = str_replace(".fasta", "_plotcon.png", $fasta_filename);
        }
        $plotcon_file_abs = $data_dir . '/' . $expected_file;
        
        if (file_exists($plotcon_file_abs)) {
            // Construct output file URL
            $plotcon_file_url = "https://bioinfmsc8.bio.ed.ac.uk/~s2682415/Website/data/" . $expected_file;
            
            // Save analysis log to the database
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
                echo "<div class='result'>";
                echo "<p>Data successfully inserted into the database.</p>";
            } catch (PDOException $e) {
                echo "<p>Error inserting data: " . htmlspecialchars($e->getMessage()) . "</p>";
            }

            // Retrieve the latest analysis record from the database and display the output file link
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
            echo "</div>";
        } else {
            echo "<p>Conservation Analysis Plot not found.</p>";
        }
    } else {
        echo "<h2>Error occurred during analysis</h2>";
        echo "<p>Conservation Analysis Plot not found.</p>";
        echo "<p>Please check the log above for details.</p>";
    }

    // Add the "Return to Home" button
    echo "<div class='result'><a href='index2.php' class='button'>Return to Home</a></div>";
    echo "</body></html>";
} else {
    header("Location: index2.php");
    exit();
}
?>
