<?php
// Database connection
$conn = new mysqli('localhost', 'root', 'hans', 'test');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch data
$schools = $conn->query("SELECT id, name FROM schools");
$hobbies = $conn->query("SELECT id, name FROM hobbies");

$data = [
    'schools' => [],
    'hobbies' => []
];

while ($row = $schools->fetch_assoc()) {
    $data['schools'][] = $row;
}

while ($row = $hobbies->fetch_assoc()) {
    $data['hobbies'][] = $row;
}

// Return JSON
header('Content-Type: application/json');
echo json_encode($data);

$conn->close();
?>
