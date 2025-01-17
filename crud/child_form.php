<?php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Database connection
try {
    $dsn = 'mysql:host=localhost;dbname=test;charset=utf8mb4';
    $username = 'root';
    $password = 'hans';
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Fetch parent IDs for the dropdown
$parents = $pdo->query("SELECT id, name FROM parents")->fetchAll();

// Initialize variables
$child_id = null;
$parent_id = '';
$name = '';
$age = '';
$gender = '';
$school_id = '';
$hobby_id = '';
$notes = '';

// Check if editing an existing child
if (isset($_GET['id'])) {
    $child_id = (int) $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM children WHERE id = ?");
    $stmt->execute([$child_id]);
    $child = $stmt->fetch();

    if ($child) {
        $parent_id = $child['parent_id'];
        $name = $child['name'];
        $age = $child['age'];
        $gender = $child['gender'];
        $school_id = $child['school_id'];
        $hobby_id = $child['hobby_id'];
        $notes = $child['notes'];
    } else {
        die("Child not found.");
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $parent_id = (int) $_POST['parent_id'];
    $name = htmlspecialchars(trim($_POST['name']));
    $age = (int) $_POST['age'];
    $gender = htmlspecialchars(trim($_POST['gender']));
    $school_id = (int) $_POST['school_id'];
    $hobby_id = (int) $_POST['hobby_id'];
    $notes = htmlspecialchars(trim($_POST['notes']));

    if ($child_id) {
        // Update existing child
        $stmt = $pdo->prepare("UPDATE children SET parent_id = ?, name = ?, age = ?, gender = ?, school_id = ?, hobby_id = ?, notes = ? WHERE id = ?");
        $stmt->execute([$parent_id, $name, $age, $gender, $school_id, $hobby_id, $notes, $child_id]);
    } else {
        // Insert new child
        $stmt = $pdo->prepare("INSERT INTO children (parent_id, name, age, gender, school_id, hobby_id, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$parent_id, $name, $age, $gender, $school_id, $hobby_id, $notes]);
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
    <title>Child Form</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <h1 class="mb-4"><?= $child_id ? "Edit Child" : "Add Child" ?></h1>
    <form method="POST">
        <div class="mb-3">
            <label for="parent_id" class="form-label">Parent</label>
            <select id="parent_id" name="parent_id" class="form-select" required>
                <?php foreach ($parents as $parent): ?>
                    <option value="<?= $parent['id'] ?>" <?= $parent['id'] == $parent_id ? 'selected' : '' ?>>
                        <?= htmlspecialchars($parent['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($name) ?>" required>
        </div>
        <div class="mb-3">
            <label for="age" class="form-label">Age</label>
            <input type="number" class="form-control" id="age" name="age" value="<?= htmlspecialchars($age) ?>" required>
        </div>
        <div class="mb-3">
            <label for="gender" class="form-label">Gender</label>
            <select id="gender" name="gender" class="form-select" required>
                <option value="male" <?= $gender === 'male' ? 'selected' : '' ?>>Male</option>
                <option value="female" <?= $gender === 'female' ? 'selected' : '' ?>>Female</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="school_id" class="form-label">School</label>
            <input type="number" class="form-control" id="school_id" name="school_id" value="<?= htmlspecialchars($school_id) ?>">
        </div>
        <div class="mb-3">
            <label for="hobby_id" class="form-label">Hobby</label>
            <input type="number" class="form-control" id="hobby_id" name="hobby_id" value="<?= htmlspecialchars($hobby_id) ?>">
        </div>
        <div class="mb-3">
            <label for="notes" class="form-label">Notes</label>
            <textarea class="form-control" id="notes" name="notes"><?= htmlspecialchars($notes) ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary"><?= $child_id ? "Update" : "Add" ?></button>
        <a href="admin_dashboard.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
</body>
</html>
