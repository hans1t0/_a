<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

// Validate POST data
if (empty($_POST['child_id']) || empty($_POST['activity_id'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Invalid request parameters.']);
    exit;
}

$child_id = $_POST['child_id'];
$activity_id = $_POST['activity_id'];

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
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit;
}

// Verify ownership
$stmt = $pdo->prepare("
    SELECT s.id 
    FROM subscriptions s
    JOIN children c ON s.child_id = c.id
    WHERE s.child_id = ? AND s.activity_id = ? AND c.parent_id = ?
");
$stmt->execute([$child_id, $activity_id, $_SESSION['user_id']]);
$subscription = $stmt->fetch();

if (!$subscription) {
    http_response_code(403); // Forbidden
    echo json_encode(['status' => 'error', 'message' => 'Subscription not found or unauthorized access.']);
    exit;
}

// Delete subscription
$stmt = $pdo->prepare("DELETE FROM subscriptions WHERE child_id = ? AND activity_id = ?");
if ($stmt->execute([$child_id, $activity_id])) {
    echo json_encode(['status' => 'success', 'message' => 'Successfully unsubscribed from the activity.']);
} else {
    http_response_code(500); // Internal Server Error
    echo json_encode(['status' => 'error', 'message' => 'Failed to unsubscribe from the activity.']);
}
?>
