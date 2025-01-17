<?php
session_start();

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
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    $stmt = $pdo->prepare("SELECT * FROM parents WHERE email = ? AND phone = ?");
    $stmt->execute([$email, $phone]);
    $parent = $stmt->fetch();

    if ($parent) {
        $_SESSION['parent_id'] = $parent['id'];
        header("Location: dashboard.php");
        exit;
    } else {
        echo "Invalid email or phone number.";
    }
}
?>