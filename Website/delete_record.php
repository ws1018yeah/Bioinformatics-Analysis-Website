<?php
session_start();
include(__DIR__ . '/config/config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 获取表名和记录ID
    $table = $_POST['table'];
    $id = $_POST['id'];
    
    try {
        // 如果用户登录，检查记录是否属于该用户
        if (isset($_SESSION['user_id'])) {
            $stmt = $pdo->prepare("SELECT * FROM $table WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $_SESSION['user_id']]);
            $record = $stmt->fetch();
            
            if (!$record) {
                die("无权删除该记录");
            }
        } else {
            // 如果用户未登录，检查记录是否属于该会话
            $stmt = $pdo->prepare("SELECT * FROM $table WHERE id = ? AND session_id = ?");
            $stmt->execute([$id, session_id()]);
            $record = $stmt->fetch();
            
            if (!$record) {
                die("无权删除该记录");
            }
        }
        
        // 删除记录
        $stmt = $pdo->prepare("DELETE FROM $table WHERE id = ?");
        $stmt->execute([$id]);
        
        // 重定向回原页面
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    } catch (PDOException $e) {
        die("数据库错误: " . $e->getMessage());
    }
} else {
    header("Location: view_results2.php");
    exit();
}