<?php
ob_start(); // 新增：启用输出缓冲
session_start();
include(__DIR__ . '/config/config.php');

$error = null; // 初始化错误变量

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        
        // 新增空值验证
        if (empty($username) || empty($email) || empty($password)) {
            throw new Exception("所有字段都必须填写");
        }

        // 检查邮箱格式
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("邮箱格式无效");
        }

        // 检查邮箱是否已存在（优化查询语句）
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("该邮箱已被注册");
        }

        // 密码强度验证（新增）
        if (strlen($password) < 8) {
            throw new Exception("密码至少需要8个字符");
        }

        // 创建用户（使用事务保障数据一致性）
        $pdo->beginTransaction();
        try {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email, $password_hash]);
            
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['username'] = $username;
            
            $pdo->commit();
            
            // 在跳转前清理缓冲区
            ob_end_clean(); // 新增：清空输出缓冲区
            header("Location: index2.php");
            exit();
            
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e; // 重新抛出异常
        }
        
    } catch (Exception $e) {
        $error = $e->getMessage();
        ob_end_clean(); // 新增：发生错误时清理缓冲区
    }
}
// 结束PHP代码块后再输出HTML
?>
<!DOCTYPE html>
<html>
<head>
    <title>注册</title>
    <link rel="stylesheet" href="assets/css/style2.css">
</head>
<body>
    <h2>注册新账户</h2>
    <?php if(isset($error)): ?>
        <p class='error'><?php echo htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="post">
        用户名：<input type="text" name="username" required><br>
        邮箱：<input type="email" name="email" required><br>
        密码：<input type="password" name="password" required minlength="8"><br>
        <button type="submit">注册</button>
    </form>
    <p>已有账户？ <a href="login.php">立即登录</a></p>
</body>
</html>
