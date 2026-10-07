<?php
session_start();

$host = "localhost";
$db   = "mirachat_db";
$user = "root";
$pass = "";

try {
    $pdo = new PDO(
        "mysql:host=$host;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$db`");

    // USERS
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            full_name VARCHAR(100) NOT NULL,
            email VARCHAR(150) DEFAULT NULL,
            password VARCHAR(255) NOT NULL,
            avatar VARCHAR(255) DEFAULT NULL,
            bio VARCHAR(255) DEFAULT '',
            status ENUM('online','offline') DEFAULT 'offline',
            last_seen DATETIME DEFAULT CURRENT_TIMESTAMP,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    // MESSAGES
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS messages (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            sender_id INT UNSIGNED NOT NULL,
            receiver_id INT UNSIGNED NOT NULL,
            message TEXT NOT NULL,
            is_read TINYINT(1) DEFAULT 0,
            edited TINYINT(1) DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX(sender_id),
            INDEX(receiver_id),
            INDEX(created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

function isLoggedIn()
{
    return isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
}

function requireLogin()
{
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }
}

function currentUser()
{
    global $pdo;

    if (!isLoggedIn()) {
        return null;
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);

    return $stmt->fetch();
}

function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function jsonResponse($data)
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
