<?php
session_start();
include(__DIR__ . '/config/config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: index2.php");
            exit();
        } else {
            throw new Exception("邮箱或密码错误");
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>登录</title>
    <link rel="stylesheet" href="assets/css/style4.css">
</head>
<body>
    <div class="login-container">
        <div class="form-wrapper"> <!-- 新增表单容器 -->
            <h2>用户登录</h2>
            <?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>
            <form method="post">
                <div class="form-group">
                    <label for="email">邮箱：</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">密码：</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit">登录</button>
            </form>
            <p class="register-link">还没有账户？ <a href="register.php">立即注册</a></p>
        </div>
    </div>
</body>
</html>