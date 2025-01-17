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
    $subscription_id = $_POST['subscription_id'];
    $parent_id = $_SESSION['parent_id'];

    // Verificar que la suscripción pertenece a un niño del padre
    $stmt = $pdo->prepare("SELECT children_activities.id FROM children_activities JOIN children ON children_activities.child_id = children.id WHERE children_activities.id = ? AND children.parent_id = ?");
    $stmt->execute([$subscription_id, $parent_id]);
    $subscription = $stmt->fetch();

    if ($subscription) {
        $stmt = $pdo->prepare("DELETE FROM children_activities WHERE id = ?");
        if ($stmt->execute([$subscription_id])) {
            $_SESSION['message'] = "Successfully unsubscribed from the activity.";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Failed to unsubscribe from the activity.";
            $_SESSION['message_type'] = "danger";
        }
    } else {
        $_SESSION['message'] = "Invalid subscription or parent.";
        $_SESSION['message_type'] = "danger";
    }

    header("Location: dashboard.php");
    exit;
}
?>