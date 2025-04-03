<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>保守性分析</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* 简单调整，使过滤选项更美观 */
        fieldset {
            border: 1px solid #ccc;
            padding: 10px;
            margin-top: 15px;
        }
        legend {
            font-weight: bold;
        }
        label {
            display: inline-block;
            width: 180px;
            text-align: right;
            margin-right: 10px;
        }
        input[type="text"],
        input[type="number"] {
            width: 200px;
        }
    </style>
</head>
<body>
    <h2>保守性分析</h2>
    <form action="run_analyze_conservation.php" method="post">
        <label for="fasta_sequence">粘贴FASTA序列：</label><br>
        <textarea name="fasta_sequence" rows="10" cols="50" required></textarea><br>
        
        <fieldset>
            <legend>过滤选项</legend>
            <label for="taxonomy_filter">分类群过滤（关键字）：</label>
            <input type="text" name="taxonomy_filter" placeholder="例如: Mammalia"><br>
            <label for="min_length">最小序列长度：</label>
            <input type="number" name="min_length" placeholder="例如: 50"><br>
            <label for="max_length">最大序列长度：</label>
            <input type="number" name="max_length" placeholder="例如: 1000"><br>
            <label for="max_count">最大序列数量：</label>
            <input type="number" name="max_count" placeholder="例如: 10"><br>
        </fieldset>
        
        <button type="submit">运行分析</button>
    </form>
    <p><a href="index2.php">返回主页</a></p>
</body>
</html>
