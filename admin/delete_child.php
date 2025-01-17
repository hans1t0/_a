<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403); // Forbidden
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $child_id = $_POST['id'] ?? null;

    if (!$child_id) {
        echo json_encode(['status' => 'error', 'message' => 'Child ID is required.']);
        exit;
    }

    try {
        $pdo = new PDO('mysql:host=localhost;dbname=test;charset=utf8mb4', 'root', 'hans', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        $stmt = $pdo->prepare("DELETE FROM children WHERE id = ?");
        $stmt->execute([$child_id]);

        echo json_encode(['status' => 'success', 'message' => 'Child deleted successfully.']);
    } catch (PDOException $e) {
        http_response_code(500); // Internal Server Error
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
