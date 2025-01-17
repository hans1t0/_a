<?php
session_start();

if (!isset($_SESSION['parent_id'])) {
    header("Location: login.php");
    exit;
}

// Conexión a la base de datos
$dsn = 'mysql:host=localhost;dbname=family_db;charset=utf8mb4';
$username = 'root';
$password = 'hans';
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $child_id = $_POST['child_id'];
    $activity_id = $_POST['activity_id'];
    $parent_id = $_SESSION['parent_id'];

    // Verificar que el niño pertenece al padre
    $stmt = $pdo->prepare("SELECT * FROM children WHERE id = ? AND parent_id = ?");
    $stmt->execute([$child_id, $parent_id]);
    $child = $stmt->fetch();

    if ($child) {
        $stmt = $pdo->prepare("UPDATE children SET activity_id = ? WHERE id = ?");
        $stmt->execute([$activity_id, $child_id]);
    }

    header("Location: dashboard.php");
    exit;
}
?>