<?php
include_once "config/config.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $protein_family = escapeshellarg($_POST["protein_family"]);
    $taxonomy = escapeshellarg($_POST["taxonomy"]);
    $email = escapeshellarg($_POST["email"]);

    // 生成分析命令
    $command = PYTHON_PATH . " " . SCRIPT_PATH . " $protein_family $taxonomy $email";

    // 执行命令并捕获输出
    exec($command . " 2>&1", $output, $return_code);

    if ($return_code !== 0) {
        echo "<h3>Analysis failed:</h3>";
        echo "<pre>" . implode("\n", $output) . "</pre>";
        exit;
    }

    // 根据输入参数构建文件路径
    #$fasta_file = DATA_PATH . "$protein_family" . "_" . "$taxonomy.fasta";
    #$plotcon_file = RESULTS_PATH . "$protein_family" . "_" . "$taxonomy" . "_plotcon.png";
    #$pattern_file = RESULTS_PATH . "$protein_family" . "_" . "$taxonomy" . "_patterns.txt";
    
    #$fasta_file = "/~s2682415/public_html/Website/data/" . basename($protein_family . "_" . $taxonomy . ".fasta");
    #$plotcon_file = "/~s2682415/public_html/Website/results/" . basename($protein_family . "_" . $taxonomy . "_plotcon.png");
    #$pattern_file = "/~s2682415/public_html/Website/results/" . basename($protein_family . "_" . $taxonomy . "_patterns.txt");
    $fasta_file = "/localdisk/home/s2682415/public_html/Website/data/" . $protein_family . "_" . $taxonomy . ".fasta";
    $plotcon_file = "/localdisk/home/s2682415/public_html/Website/results/" . $protein_family . "_" . $taxonomy . "_plotcon.png";
    $pattern_file = "/localdisk/home/s2682415/public_html/Website/results/" . $protein_family . "_" . $taxonomy . "_patterns.txt";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Analysis Results</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <h2>Analysis Results for <?= htmlspecialchars($_POST['protein_family']) ?> (<?= htmlspecialchars($_POST['taxonomy']) ?>)</h2>
    <h3>FASTA File:</h3>
    <?php
    // 检查FASTA文件是否存在
    if (file_exists($fasta_file)) {
        // 如果文件存在，提供下载链接
        echo '<a href="/~s2682415/public_html/Website/data/' . basename($fasta_file) . '" download>Download FASTA</a>';
    } else {
        // 如果文件不存在，显示错误信息
        echo '<p>FASTA file not found.</p>';
    }
    ?>

    <h3>Conservation Plot:</h3>
    <?php
    // 检查Conservation Plot文件是否存在
    if (file_exists($plotcon_file)) {
        // 如果文件存在，显示图片
        echo '<img src="/~s2682415/public_html/Website/results/' . basename($plotcon_file) . '" alt="Conservation Plot">';
    } else {
        // 如果文件不存在，显示错误信息
        echo '<p>Plot not found.</p>';
    }
    ?>

    <h3>Motif Scan Report:</h3>
    <?php
    // 检查Motif Scan Report文件是否存在
    if (file_exists($pattern_file)) {
        // 如果文件存在，提供下载链接
        echo '<a href="/~s2682415/public_html/Website/results/' . basename($pattern_file) . '" download>Download Report</a>';
    } else {
        // 如果文件不存在，显示错误信息
        echo '<p>Report not found.</p>';
    }
    ?>
</body>
</html>