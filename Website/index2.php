<?php 
session_start();
include(__DIR__ . '/config/config.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Protein Analysis Platform | University of Edinburgh</title>
    <link rel="stylesheet" href="assets/css/style2.css">
</head>
<body>
    <header>
        <h2>Protein Sequence Analysis Tool</h2>
        <nav>
            <?php if(isset($_SESSION['user_id'])): ?>
                <span style="color:#1E3D7B;margin-right:20px;">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="view_results2.php">My Records</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php" style="margin-right:15px;">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
        </nav>
    </header>

    <div class="main-container">
        <!-- Analysis Form -->
        <form action="run_analysis2.php" method="post">
            <div class="form-group">
                <label for="protein_family">Protein Family:</label>
                <input type="text" id="protein_family" name="protein_family" required>
            </div>

            <div class="form-group">
                <label for="taxonomy">Taxonomy:</label>
                <input type="text" id="taxonomy" name="taxonomy" required>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>

            <button type="submit"><?php echo isset($_SESSION['user_id']) ? 'Start Analysis' : 'Start Analysis (Guest Mode)' ?></button>
            <button type="button" id="fill-sample-data" style="margin-left: 10px;">Use Sample Data</button>
        </form>

        <!-- Next Steps Analysis -->
        <div style="margin-top:40px;">
            <h3>Next Steps Analysis</h3>
            <p>After obtaining the FASTA sequences, you can choose the following analysis steps:</p>
            <ul>
                <li><a href="analyze_conservation.php">Conservation Analysis</a></li>
                <li><a href="pattern_scan.php">Pattern Scan</a></li>
                <li><a href="multiple_sequence_alignment.php">Multiple Sequence Alignment</a></li>
                <li><a href="generate_similarity_matrix.php">Similarity Matrix and Heatmap</a></li>
            </ul>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <hr>
        <p><a href="copyright.php">Copyright Statement</a>
        <a href="https://github.com/ws1018yeah/Bioinformatics-Analysis-Website" target="_blank">GitHub Project Repository</a>
        </p>
    </footer>

    <script>
        document.getElementById("fill-sample-data").addEventListener("click", function() {
            document.getElementById("protein_family").value = "glucose-6-phosphatase";  // Sample protein family
            document.getElementById("taxonomy").value = "Aves";  // Sample taxonomy
            document.getElementById("email").value = "15268163017@163.com";  // Sample email
        });
    </script>
</body>
</html>
