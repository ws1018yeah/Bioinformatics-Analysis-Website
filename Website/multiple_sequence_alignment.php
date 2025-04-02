<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>多序列比对</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <h2>多序列比对</h2>
    <form action="run_multiple_sequence_alignment.php" method="post">
        <label for="fasta_sequence">粘贴FASTA序列：</label><br>
        <textarea name="fasta_sequence" rows="10" cols="50" required></textarea><br>
        <button type="submit">运行分析</button>
    </form>
    <p><a href="index2.php">返回主页</a></p>
</body>
</html>