<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

// Obtener la lista de colegios
$stmt = $pdo->query("SELECT id, name FROM schools");
$schools = $stmt->fetchAll();

// Obtener la lista de grados
$stmt = $pdo->query("SELECT id, name FROM grades");
$grades = $stmt->fetchAll();

// Procesar el formulario de adición
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $description = filter_var($_POST['description'], FILTER_SANITIZE_STRING);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $min_grade = filter_var($_POST['min_grade'], FILTER_SANITIZE_NUMBER_INT);
    $max_grade = filter_var($_POST['max_grade'], FILTER_SANITIZE_NUMBER_INT);
    $school_id = filter_var($_POST['school_id'], FILTER_SANITIZE_NUMBER_INT);

    $stmt = $pdo->prepare("INSERT INTO activities (name, description, start_date, end_date, min_grade, max_grade) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$name, $description, $start_date, $end_date, $min_grade, $max_grade]);

    $activity_id = $pdo->lastInsertId();

    $stmt = $pdo->prepare("INSERT INTO school_course_activities (school_id, min_grade_id, max_grade_id, activity_id) VALUES (?, ?, ?, ?)");
    $stmt->execute([$school_id, $min_grade, $max_grade, $activity_id]);

    $_SESSION['message'] = "Activity added successfully.";
    header("Location: admin_activities.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Activity</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<div class="container py-5">
    <h1 class="mb-4">Add Activity</h1>
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($_SESSION['message']) ?>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>
    <form method="POST" action="admin_add_activity.php">
        <div class="mb-3">
            <label for="school_id" class="form-label">School</label>
            <select class="form-control" id="school_id" name="school_id" required>
                <option value="">Select School</option>
                <?php foreach ($schools as $school): ?>
                    <option value="<?= $school['id'] ?>"><?= htmlspecialchars($school['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="name" class="form-label">Activity Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" required></textarea>
        </div>
        <div class="mb-3">
            <label for="start_date" class="form-label">Start Date</label>
            <input type="date" class="form-control" id="start_date" name="start_date" required>
        </div>
        <div class="mb-3">
            <label for="end_date" class="form-label">End Date</label>
            <input type="date" class="form-control" id="end_date" name="end_date" required>
        </div>
        <div class="mb-3">
            <label for="min_grade" class="form-label">Minimum Grade</label>
            <select class="form-control" id="min_grade" name="min_grade" required>
                <option value="">Select Minimum Grade</option>
                <?php foreach ($grades as $grade): ?>
                    <option value="<?= $grade['id'] ?>"><?= htmlspecialchars($grade['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="max_grade" class="form-label">Maximum Grade</label>
            <select class="form-control" id="max_grade" name="max_grade" required>
                <option value="">Select Maximum Grade</option>
                <?php foreach ($grades as $grade): ?>
                    <option value="<?= $grade['id'] ?>"><?= htmlspecialchars($grade['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Add Activity</button>
        <a href="admin_activities.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>