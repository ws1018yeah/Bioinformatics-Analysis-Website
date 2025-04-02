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
    <link rel="stylesheet" href="assets/css/style2.css">
</head>
<body>
    <h2>用户登录</h2>
    <?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>
    <form method="post">
        邮箱：<input type="email" name="email" required><br>
        密码：<input type="password" name="password" required><br>
        <button type="submit">登录</button>
    </form>
    <p>还没有账户？ <a href="register.php">立即注册</a></p>
</body>
</html>
