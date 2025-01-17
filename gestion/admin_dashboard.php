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

// Obtener el conteo de las entidades
$parentCount = $pdo->query("SELECT COUNT(*) FROM parents")->fetchColumn();
$childCount = $pdo->query("SELECT COUNT(*) FROM children")->fetchColumn();
$schoolCount = $pdo->query("SELECT COUNT(*) FROM schools")->fetchColumn();
$gradeCount = $pdo->query("SELECT COUNT(*) FROM grades")->fetchColumn();
$activityCount = $pdo->query("SELECT COUNT(*) FROM activities")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
</head>
<body>
<div class="container py-5">
    <h1 class="mb-4">Admin Dashboard</h1>
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Parents</h5>
                    <p class="card-text"><?= $parentCount ?> parents</p>
                    <a href="admin_parents.php" class="btn btn-primary">Manage Parents</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Children</h5>
                    <p class="card-text"><?= $childCount ?> children</p>
                    <a href="admin_children.php" class="btn btn-primary">Manage Children</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Schools</h5>
                    <p class="card-text"><?= $schoolCount ?> schools</p>
                    <a href="admin_schools.php" class="btn btn-primary">Manage Schools</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mt-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Grades</h5>
                    <p class="card-text"><?= $gradeCount ?> grades</p>
                    <a href="admin_grades.php" class="btn btn-primary">Manage Grades</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mt-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Activities</h5>
                    <p class="card-text"><?= $activityCount ?> activities</p>
                    <a href="admin_activities.php" class="btn btn-primary">Manage Activities</a>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>