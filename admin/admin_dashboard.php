<?php
session_start();

// Restrict access to admins
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
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

// Fetch data
$parents = $pdo->query("SELECT * FROM parents")->fetchAll();
$children = $pdo->query("SELECT * FROM children")->fetchAll();
$activities = $pdo->query("SELECT * FROM sports_activities")->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</head>
<body class="bg-light">
<div class="container py-5">
    <h1 class="mb-4 text-center">Admin Dashboard</h1>

    <!-- Manage Parents -->
    <h2>Manage Parents</h2>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($parents as $parent): ?>
            <tr>
                <td><?= htmlspecialchars($parent['id']) ?></td>
                <td><?= htmlspecialchars($parent['name']) ?></td>
                <td><?= htmlspecialchars($parent['email']) ?></td>
                <td><?= htmlspecialchars($parent['role']) ?></td>
                <td>
                    <button class="btn btn-warning btn-sm" onclick="editParent(<?= $parent['id'] ?>)">Edit</button>
                    <button class="btn btn-danger btn-sm" onclick="deleteParent(<?= $parent['id'] ?>)">Delete</button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Manage Children -->
    <h2 class="mt-5">Manage Children</h2>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Parent ID</th>
                <th>Age</th>
                <th>Gender</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($children as $child): ?>
            <tr>
                <td><?= htmlspecialchars($child['id']) ?></td>
                <td><?= htmlspecialchars($child['name']) ?></td>
                <td><?= htmlspecialchars($child['parent_id']) ?></td>
                <td><?= htmlspecialchars($child['age']) ?></td>
                <td><?= htmlspecialchars($child['gender']) ?></td>
                <td>
                    <button class="btn btn-warning btn-sm" onclick="editChild(<?= $child['id'] ?>)">Edit</button>
                    <button class="btn btn-danger btn-sm" onclick="deleteChild(<?= $child['id'] ?>)">Delete</button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Manage Activities -->
    <h2 class="mt-5">Manage Sports Activities</h2>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Age Group</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($activities as $activity): ?>
            <tr>
                <td><?= htmlspecialchars($activity['id']) ?></td>
                <td><?= htmlspecialchars($activity['name']) ?></td>
                <td><?= htmlspecialchars($activity['description']) ?></td>
                <td><?= htmlspecialchars($activity['age_group']) ?></td>
                <td><?= htmlspecialchars($activity['start_date']) ?></td>
                <td><?= htmlspecialchars($activity['end_date']) ?></td>
                <td>
                    <button class="btn btn-warning btn-sm" onclick="editActivity(<?= $activity['id'] ?>)">Edit</button>
                    <button class="btn btn-danger btn-sm" onclick="deleteActivity(<?= $activity['id'] ?>)">Delete</button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div class="mt-4 text-center">
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
</div>

<script>
function editParent(id) {
    alert('Edit Parent: ' + id); // Replace with actual edit logic
}

function deleteParent(id) {
    if (confirm('Are you sure you want to delete this parent?')) {
        $.post('delete_parent.php', { id: id }, function(response) {
            alert(response.message);
            location.reload();
        }, 'json');
    }
}

function editChild(id) {
     if (confirm('Are you sure you want to delete this parent?')) {
        $.post('edit_child.php', { id: id }, function(response) {
            alert(response.message);
            location.reload();
        }, 'json');
    }
}

function deleteChild(id) {
    if (confirm('Are you sure you want to delete this child?')) {
        $.post('delete_child.php', { id: id }, function(response) {
            alert(response.message);
            location.reload();
        }, 'json');
    }
}

function editActivity(id) {
    alert('Edit Activity: ' + id); // Replace with actual edit logic
}

function deleteActivity(id) {
    if (confirm('Are you sure you want to delete this activity?')) {
        $.post('delete_activity.php', { id: id }, function(response) {
            alert(response.message);
            location.reload();
        }, 'json');
    }
}
</script>

</body>
</html>
