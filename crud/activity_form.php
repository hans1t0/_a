<?php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Database connection
try {
    $pdo = new PDO('mysql:host=localhost;dbname=test;charset=utf8mb4', 'root', 'hans', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Variables
$id = null;
$name = '';
$description = '';
$start_date = '';
$end_date = '';

// Check if editing an activity
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM sports_activities WHERE id = ?");
    $stmt->execute([$id]);
    $activity = $stmt->fetch();
    if ($activity) {
        $name = $activity['name'];
        $description = $activity['description'];
        $start_date = $activity['start_date'];
        $end_date = $activity['end_date'];
    } else {
        die("Activity not found.");
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    if ($id) {
        // Update existing activity
        $stmt = $pdo->prepare("UPDATE sports_activities SET name = ?, description = ?, start_date = ?, end_date = ? WHERE id = ?");
        $stmt->execute([$name, $description, $start_date, $end_date, $id]);
    } else {
        // Add new activity
        $stmt = $pdo->prepare("INSERT INTO sports_activities (name, description, start_date, end_date) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $description, $start_date, $end_date]);
    }
    header("Location: admin_dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $id ? 'Edit Activity' : 'Add Activity' ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <h1 class="mb-4 text-center"><?= $id ? 'Edit Activity' : 'Add Activity' ?></h1>

    <form action="" method="post" class="bg-white p-4 shadow rounded">
        <div class="mb-3">
            <label for="name" class="form-label">Activity Name</label>
            <input type="text" name="name" id="name" class="form-control" value="<?= htmlspecialchars($name) ?>" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea name="description" id="description" class="form-control" rows="4" required><?= htmlspecialchars($description) ?></textarea>
        </div>
        <div class="mb-3">
            <label for="start_date" class="form-label">Start Date</label>
            <input type="date" name="start_date" id="start_date" class="form-control" value="<?= htmlspecialchars($start_date) ?>" required>
        </div>
        <div class="mb-3">
            <label for="end_date" class="form-label">End Date</label>
            <input type="date" name="end_date" id="end_date" class="form-control" value="<?= htmlspecialchars($end_date) ?>" required>
        </div>
        <div class="text-center">
            <button type="submit" class="btn btn-primary"><?= $id ? 'Update Activity' : 'Add Activity' ?></button>
            <a href="admin_dashboard.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
