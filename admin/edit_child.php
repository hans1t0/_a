<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403); // Forbidden
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $child_id = $_POST['id'] ?? null;
    $name = $_POST['name'] ?? null;
    $age = $_POST['age'] ?? null;
    $gender = $_POST['gender'] ?? null;
    $school_id = $_POST['school_id'] ?? null;
    $hobby_id = $_POST['hobby_id'] ?? null;
    $notes = $_POST['notes'] ?? null;

    if (!$child_id || !$name || !$age || !$gender || !$school_id || !$hobby_id) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit;
    }

    try {
        $pdo = new PDO('mysql:host=localhost;dbname=test;charset=utf8mb4', 'root', 'hans', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        $stmt = $pdo->prepare("
            UPDATE children 
            SET name = ?, age = ?, gender = ?, school_id = ?, hobby_id = ?, notes = ?
            WHERE id = ?
        ");
        $stmt->execute([$name, $age, $gender, $school_id, $hobby_id, $notes, $child_id]);

        echo json_encode(['status' => 'success', 'message' => 'Child updated successfully.']);
    } catch (PDOException $e) {
        http_response_code(500); // Internal Server Error
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
