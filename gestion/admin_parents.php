<?php
session_start();

// Verificar si el usuario es un administrador
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
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

// Obtener la lista de padres
$stmt = $pdo->query("SELECT * FROM parents");
$parents = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Parents</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
</head>
<body>
<div class="container py-5">
    <h1 class="mb-4">Manage Parents</h1>
    <a href="admin_add_parent.php" class="btn btn-success mb-4">Add Parent</a>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Last Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($parents as $parent): ?>
                <tr>
                    <td><?= htmlspecialchars($parent['id']) ?></td>
                    <td><?= htmlspecialchars($parent['name']) ?></td>
                    <td><?= htmlspecialchars($parent['lastname']) ?></td>
                    <td><?= htmlspecialchars($parent['email']) ?></td>
                    <td><?= htmlspecialchars($parent['phone']) ?></td>
                    <td>
                        <a href="admin_edit_parent.php?id=<?= $parent['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                        <a href="admin_delete_parent.php?id=<?= $parent['id'] ?>" class="btn btn-danger btn-sm">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>