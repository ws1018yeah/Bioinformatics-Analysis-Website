<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Conservation Analysis</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Overall page background and basic layout */
        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(160deg, #eaf0f6 0%, #f7f9fc 100%);
            font-family: Arial, sans-serif;
            font-size: 18px; /* Enlarged overall font */
            line-height: 1.6;
            text-align: center;
        }
        
        /* Main container: centered, white background, increased size */
        .main-container {
            max-width: 500px; /* Increased to 800px */
            margin: 40px auto; /* Centered horizontally and vertically */
            background: #fff;
            padding: 40px;     /* Increased padding */
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        /* Title style: larger font */
        h2 {
            color: #1E3D7B;
            font-size: 36px; /* Enlarged title font */
            font-weight: 800;
            margin-bottom: 30px;
        }

        /* Form overall */
        form {
            text-align: left;
            margin: 0 auto;
        }

        /* Each input area */
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
        }
        textarea,
        input[type="text"],
        input[type="number"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            resize: none; /* Prevent user from resizing */
            box-sizing: border-box;
        }
        textarea:focus,
        input[type="text"]:focus,
        input[type="number"]:focus {
            border-color: #1E3D7B;
            outline: none;
        }

        /* Button group: centered */
        .button-group {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }
        /* Button style: same size as before */
        button {
            background-color: #1E3D7B;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            padding: 10px 20px;
            transition: background-color 0.3s, transform 0.3s;
        }
        button:hover {
            background-color: #2c4e9e;
            transform: translateY(-2px);
        }

        /* Back to homepage button */
        .back-button {
            display: inline-block;
            margin-top: 20px;
            background-color: #1E3D7B;
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            padding: 10px 20px;
            transition: background-color 0.3s, transform 0.3s;
        }
        .back-button:hover {
            background-color: #2c4e9e;
            transform: translateY(-2px);
        }

        /* Responsive: auto scaling on smaller screens */
        @media (max-width: 600px) {
            .main-container {
                margin: 20px auto;
                padding: 20px;
                width: 90%;
            }
            h2 {
                font-size: 28px;
            }
        }
    </style>
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
        <h2>Conservation Analysis</h2>
        <form id="analysis_form" action="run_analyze_conservation.php" method="post">
            <div class="form-group">
                <label for="fasta_sequence">Paste FASTA sequence:</label>
                <textarea id="fasta_sequence" name="fasta_sequence" rows="10" required></textarea>
            </div>

            <div class="form-group">
                <label for="taxonomy_filter">Taxonomic group filter (keyword):</label>
                <input type="text" name="taxonomy_filter" placeholder="e.g., Mammalia">
            </div>

            <div class="form-group">
                <label for="min_length">Minimum sequence length:</label>
                <input type="number" name="min_length" placeholder="e.g., 50">
            </div>

            <div class="form-group">
                <label for="max_length">Maximum sequence length:</label>
                <input type="number" name="max_length" placeholder="e.g., 1000">
            </div>

            <div class="form-group">
                <label for="max_count">Maximum sequence count:</label>
                <input type="number" name="max_count" placeholder="e.g., 10">
            </div>

            <div class="button-group">
                <button type="submit">Run Analysis</button>
                <button type="button" onclick="loadExampleData()">Use Example Data</button>
            </div>
        </form>

        <a class="back-button" href="index2.php">Back to Homepage</a>
    </div>
</body>
</html>
