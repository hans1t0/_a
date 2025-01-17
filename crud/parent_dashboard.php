<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'parent') {
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

// Fetch sports activities
$activities = $pdo->query("SELECT * FROM sports_activities")->fetchAll();

// Handle subscription
if (isset($_GET['subscribe'])) {
    $activity_id = (int)$_GET['subscribe'];
    $parent_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("SELECT * FROM activity_subscriptions WHERE parent_id = ? AND activity_id = ?");
    $stmt->execute([$parent_id, $activity_id]);

    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO activity_subscriptions (parent_id, activity_id) VALUES (?, ?)");
        $stmt->execute([$parent_id, $activity_id]);
    }

    header("Location: parent_dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parent Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <h1 class="text-center mb-4">Sports Activities</h1>

    <div class="row">
        <?php foreach ($activities as $activity): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($activity['name']) ?></h5>
                        <p class="card-text"><?= htmlspecialchars($activity['description']) ?></p>
                        <p class="card-text">
                            <small class="text-muted">Start: <?= htmlspecialchars($activity['start_date']) ?></small><br>
                            <small class="text-muted">End: <?= htmlspecialchars($activity['end_date']) ?></small>
                        </p>
                        <div class="d-grid">
                            <a href="?subscribe=<?= $activity['id'] ?>" class="btn btn-primary">Subscribe</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
