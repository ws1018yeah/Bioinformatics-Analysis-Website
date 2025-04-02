<!DOCTYPE html>
<html>
<head>
    <title>Protein Analysis Tool</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <h2>Protein Analysis Tool</h2>
    <form action="run_analysis2.php" method="post">
        Protein Family: <input type="text" name="protein_family" required><br>
        Taxonomy: <input type="text" name="taxonomy" required><br>
        Email: <input type="email" name="email" required><br>
        <button type="submit">Run Analysis</button>
    </form>
</body>
</html>
