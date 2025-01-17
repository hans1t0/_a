<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

// Database connection
try {
    $dsn = 'mysql:host=localhost;dbname=test;charset=utf8mb4';
    $username = 'root';
    $password = 'hans';
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Fetch parent ID
$parent_id = $_SESSION['user_id'];

// Fetch updated subscriptions
$stmt = $pdo->prepare("
    SELECT c.name AS child_name, a.name AS activity_name, s.subscription_date
    FROM subscriptions s
    JOIN children c ON s.child_id = c.id
    JOIN sports_activities a ON s.activity_id = a.id
    WHERE c.parent_id = :parent_id
");
$stmt->bindParam(':parent_id', $parent_id, PDO::PARAM_INT);
$stmt->execute();
$subscriptions = $stmt->fetchAll();

// Return subscriptions as JSON
echo json_encode(['status' => 'success', 'subscriptions' => $subscriptions]); 
?>