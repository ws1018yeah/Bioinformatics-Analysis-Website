<?php
session_start();
include(__DIR__ . '/config/config.php');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>蛋白质分析平台 | 爱丁堡大学</title>
    <link rel="stylesheet" href="assets/css/style2.css">
</head>
<body>
    <header>
        <h2>蛋白质序列分析工具</h2>
        <nav>
            <?php if(isset($_SESSION['user_id'])): ?>
                <span style="color:#1E3D7B;margin-right:20px;">欢迎，<?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="view_results2.php">我的记录</a>
                <a href="logout.php">注销</a>
            <?php else: ?>
                <a href="login.php" style="margin-right:15px;">登录</a>
                <a href="register.php">注册</a>
            <?php endif; ?>
            <a href="sample_dataset.php">示例数据集</a>
        </nav>
    </header>

    <div class="main-container">
        <!-- 分析表单 -->
        <form action="run_analysis2.php" method="post">
            <div class="form-group">
                <label for="protein_family">蛋白质家族：</label>
                <input type="text" id="protein_family" name="protein_family" required>
            </div>

            <div class="form-group">
                <label for="taxonomy">生物分类：</label>
                <input type="text" id="taxonomy" name="taxonomy" required>
            </div>

            <div class="form-group">
                <label for="email">邮箱：</label>
                <input type="email" id="email" name="email" required>
            </div>

            <button type="submit"><?php echo isset($_SESSION['user_id']) ? '开始分析' : '开始分析（访客模式）' ?></button>
            <button type="button" id="fill-sample-data" style="margin-left: 10px;">使用示例数据</button>
        </form>

        <!-- 下一步分析 -->
        <div style="margin-top:40px;">
            <h3>下一步分析</h3>
            <p>在获取FASTA序列后，您可以选择以下分析步骤：</p>
            <ul>
                <li><a href="analyze_conservation.php">保守性分析</a></li>
                <li><a href="pattern_scan.php">模式扫描</a></li>
                <li><a href="multiple_sequence_alignment.php">多序列比对</a></li>
                <li><a href="generate_similarity_matrix.php">相似性矩阵和热图</a></li>
            </ul>
        </div>
    </div>

    <!-- 页脚 -->
    <footer>
        <hr>
        <p><a href="copyright.php">版权声明页面</a>
        <a href="https://github.com/ws1018yeah/Bioinformatics-Analysis-Website" target="_blank">GitHub项目仓库</a>
        </p>
    </footer>

    <script>
        document.getElementById("fill-sample-data").addEventListener("click", function() {
            document.getElementById("protein_family").value = "glucose-6-phosphatase";  // 示例蛋白质家族
            document.getElementById("taxonomy").value = "Aves";  // 示例生物分类
            document.getElementById("email").value = "15268163017@163.com";  // 示例邮箱
        });
    </script>
</body>
</html>
