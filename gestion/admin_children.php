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

// Obtener la lista de niños
$stmt = $pdo->query("SELECT children.*, parents.name AS parent_name, parents.lastname AS parent_lastname FROM children JOIN parents ON children.parent_id = parents.id");
$children = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Children</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
</head>
<body>
<div class="container py-5">
    <h1 class="mb-4">Manage Children</h1>
    <a href="admin_add_child.php" class="btn btn-success mb-4">Add Child</a>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Last Name</th>
                <th>Date of Birth</th>
                <th>Parent</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($children as $child): ?>
                <tr>
                    <td><?= htmlspecialchars($child['id']) ?></td>
                    <td><?= htmlspecialchars($child['name']) ?></td>
                    <td><?= htmlspecialchars($child['lastname']) ?></td>
                    <td><?= htmlspecialchars($child['dob']) ?></td>
                    <td><?= htmlspecialchars($child['parent_name']) ?> <?= htmlspecialchars($child['parent_lastname']) ?></td>
                    <td>
                        <a href="admin_edit_child.php?id=<?= $child['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                        <a href="admin_delete_child.php?id=<?= $child['id'] ?>" class="btn btn-danger btn-sm">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>