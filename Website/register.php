<?php 
ob_start(); // New: Enable output buffering
session_start();
include(__DIR__ . '/config/config.php');

$error = null; // Initialize error variable

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        
        // New: Validate non-empty fields
        if (empty($username) || empty($email) || empty($password)) {
            throw new Exception("All fields must be filled");
        }

        // Check email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }

        // Check if email already exists (optimized query)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("This email is already registered");
        }

        // Password strength validation (new)
        if (strlen($password) < 8) {
            throw new Exception("Password must be at least 8 characters long");
        }

        // Create user (use transactions to ensure data consistency)
        $pdo->beginTransaction();
        try {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email, $password_hash]);
            
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['username'] = $username;
            
            $pdo->commit();
            
            // Clean the buffer before redirecting
            ob_end_clean(); // New: Clean output buffer
            header("Location: index2.php");
            exit();
            
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e; // Re-throw exception
        }
        
    } catch (Exception $e) {
        $error = $e->getMessage();
        ob_end_clean(); // New: Clean buffer when an error occurs
    }
}
// Output HTML after PHP code ends
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" href="assets/css/style4.css">
</head>
<body>
    <div class="login-container">
        <div class="form-wrapper">
            <h2>Register New Account</h2>
            <?php if(isset($error)): ?>
                <p class='error'><?php echo htmlspecialchars($error) ?></p>
            <?php endif; ?>
            <form method="post">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required minlength="8">
                </div>
                <button type="submit">Register</button>
            </form>
            <p class="register-link">Already have an account? <a href="login.php">Login now</a></p>
        </div>
    </div>
</body>
</html>
