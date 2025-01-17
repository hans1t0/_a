<?php
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

// Datos del nuevo administrador
$newAdminUsername = 'hans';
$newAdminPassword = 'CknX9inHTC++';

// Encriptar la contraseña
$hashedPassword = password_hash($newAdminPassword, PASSWORD_DEFAULT);

// Insertar el nuevo administrador en la base de datos
$stmt = $pdo->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
$stmt->execute([$newAdminUsername, $hashedPassword]);

echo "New admin created successfully.";
?>