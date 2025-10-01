<?php
session_start();

$host = 'mysql';  // Service name from docker-compose
$dbname = 'srms_db';
$username = 'srms_user';
$password = 'srms_pass';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
