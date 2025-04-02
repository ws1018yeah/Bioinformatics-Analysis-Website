<?php
// config/config.php
// 数据库连接配置

$host = '127.0.0.1';
$db   = 's2682415_my_first_db';  // 数据库名称，确保与你在 database_setup.sql 中创建的名称一致
$user = 's2682415';
$pass = 'Ws1018@yeah';  // 将此处替换为你的实际数据库密码
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // 发生错误时抛出异常
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // 连接失败时输出错误信息
    die("数据库连接失败: " . $e->getMessage());
}
?>