<!DOCTYPE html> 
<html>
<head>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Same layout as generate_similarity_matrix.php */
        
        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(160deg, #eaf0f6 0%, #f7f9fc 100%);
            font-family: Arial, sans-serif;
            font-size: 18px;
            text-align: center;
        }
        .main-container {
            max-width: 800px;
            margin: 40px auto;
            background: #fff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        h2 {
            color: #1E3D7B;
            font-size: 36px;
            font-weight: 800;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
            text-align: left; /* Align labels to the left */
        }
        label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
        }
        textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            resize: none; /* Disable resizing */
        }
        button {
            background-color: #1E3D7B;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            padding: 10px 20px;
            transition: background-color 0.3s, transform 0.3s;
            margin: 0 5px; /* Add some spacing between buttons */
        }
        button:hover {
            background-color: #2c4e9e;
            transform: translateY(-2px);
        }
        .back-button {
            display: inline-block;
            margin-top: 20px;
            background-color: #1E3D7B;
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            font-size: 16px;
            padding: 10px 20px;
            transition: background-color 0.3s, transform 0.3s;
        }
        .back-button:hover {
            background-color: #2c4e9e;
            transform: translateY(-2px);
        }
    </style>
    
    <meta charset="UTF-8">
    <title>Pattern Scan</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script>
        function loadExampleData() {
            document.getElementById('fasta_sequence').value = `<?php 
                $example_file = "/home/s2682415/public_html/Website/sample/example.fasta";
                if (file_exists($example_file)) {
                    echo addslashes(file_get_contents($example_file));
                } else {
                    echo "Example data file not found!";
                }
            ?>`;
        }
    </script>
</head>
<body>
  <div class="main-container">
    <h2>Pattern Scan</h2>
    <form action="run_pattern_scan.php" method="post">
        <div class="form-group">
            <label for="fasta_sequence">Paste FASTA Sequence:</label>
            <textarea id="fasta_sequence" name="fasta_sequence" rows="10" required></textarea>
        </div>
        <button type="submit">Run Analysis</button>
        <button type="button" onclick="loadExampleData()">Use Example Data</button>
    </form>
    <a class="back-button" href="index2.php">Back to Homepage</a>
</body>
</html>
