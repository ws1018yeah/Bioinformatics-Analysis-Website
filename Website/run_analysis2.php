<?php 
// Include database configuration
include(__DIR__ . '/config/config.php');
// Load additional configuration for the analysis process
include(__DIR__ . '/config2.php');
// Start session
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and escape user input
    $email = trim($_POST['email']);
    $protein_family = preg_replace('/[^A-Za-z0-9_-]/', '', trim($_POST['protein_family']));
    $taxonomy = preg_replace('/[^A-Za-z0-9_-]/', '', trim($_POST['taxonomy']));

    // Do not replace spaces here, Python script will handle it internally
    $pf_arg = escapeshellarg($protein_family);
    $tax_arg = escapeshellarg($taxonomy);
    $email_arg = escapeshellarg($email);

    // Construct command and append 2>&1 to capture standard errors
    $command = "$PYTHON_EXE $PYTHON_SCRIPT $pf_arg $tax_arg $email_arg 2>&1";

    // Execute command and capture output
    exec($command, $output, $return_var);

    echo "<html><head><title>Protein Analysis Results</title>";
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
    echo "<h2>Protein Analysis Results</h2>";  // Add title here

    if ($return_var === 0) {
        // Define absolute directory path and construct file paths
        $data_dir_abs = "/home/s2682415/public_html/Website/data";
        $family_clean = preg_replace("/[^A-Za-z0-9_\-]/", "_", $protein_family);
        $taxonomy_clean = preg_replace("/[^A-Za-z0-9_\-]/", "_", $taxonomy);

        // Backend file system paths
        $fasta_file_abs = "$data_dir_abs/{$family_clean}_{$taxonomy_clean}.fasta";
        $patterns_file_abs = "$data_dir_abs/{$family_clean}_{$taxonomy_clean}_patterns.txt";
        $plotcon_file_abs = "$data_dir_abs/{$family_clean}_{$taxonomy_clean}_plotcon.png";
        $aligned_file_abs = "$data_dir_abs/{$family_clean}_{$taxonomy_clean}_aligned.aln";
        $similarity_matrix_abs = "$data_dir_abs/{$family_clean}_{$taxonomy_clean}_similarity.mat";
        $heatmap_file_abs = "$data_dir_abs/{$family_clean}_{$taxonomy_clean}_similarity_heatmap.png";

        // Relative paths for web links
        $fasta_file_url = "data/{$family_clean}_{$taxonomy_clean}.fasta";
        $patterns_file_url = "data/{$family_clean}_{$taxonomy_clean}_patterns.txt";
        $plotcon_file_url = "data/{$family_clean}_{$taxonomy_clean}_plotcon.png";
        $aligned_file_url = "data/{$family_clean}_{$taxonomy_clean}_aligned.aln";
        $similarity_matrix_url = "data/{$family_clean}_{$taxonomy_clean}_similarity.mat";
        $heatmap_file_url = "data/{$family_clean}_{$taxonomy_clean}_similarity_heatmap.png";

        echo "<h3>Output Files:</h3>";
        echo "<ul>";
        echo file_exists($fasta_file_abs) ? "<li><a href='$fasta_file_url' target='_blank'>FASTA Sequences</a></li>" : "<li>FASTA Sequences file not found.</li>";
        echo file_exists($patterns_file_abs) ? "<li><a href='$patterns_file_url' target='_blank'>Motif Scan Results</a></li>" : "<li>Motif Scan Results file not found.</li>";
        echo file_exists($plotcon_file_abs) ? "<li><a href='$plotcon_file_url' target='_blank'>Conservation Analysis Plot</a></li>" : "<li>Conservation Analysis Plot not found.</li>";
        echo file_exists($aligned_file_abs) ? "<li><a href='$aligned_file_url' target='_blank'>Alignment Results</a></li>" : "<li>Alignment Results file not found.</li>";
        echo file_exists($similarity_matrix_abs) ? "<li><a href='$similarity_matrix_url' target='_blank'>Similarity Matrix</a></li>" : "<li>Similarity Matrix file not found.</li>";
        echo file_exists($heatmap_file_abs) ? "<li><a href='$heatmap_file_url' target='_blank'>Similarity Heatmap</a></li>" : "<li>Similarity Heatmap file not found.</li>";
        echo "</ul>";

        // Save analysis records to the database
        $analysis_log = implode("\n", $output);
        try {
            $sql = "INSERT INTO analysis_results (protein_family, taxonomy, email, fasta_file, patterns_file, plotcon_file, aligned_file, similarity_matrix, heatmap_file, analysis_log, created_at, user_id, session_id)
                    VALUES (:protein_family, :taxonomy, :email, :fasta_file, :patterns_file, :plotcon_file, :aligned_file, :similarity_matrix, :heatmap_file, :analysis_log, NOW(), :user_id, :session_id)";
            $stmt = $pdo->prepare($sql);
            
            // Set user_id if logged in, otherwise set to NULL
            $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
            
            // Get current session ID
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

    // Add the "Return to Home" button
    echo "<div class='result'><a href='index2.php' class='button'>Return to Home</a></div>";
    echo "</body></html>";
} else {
    header("Location: index2.php");
    exit();
}
?>
