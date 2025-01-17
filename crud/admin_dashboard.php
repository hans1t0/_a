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

// Fetch all parents and children
function fetchAll($pdo, $query) {
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll();
}

$parents = fetchAll($pdo, "SELECT * FROM parents");
$children = fetchAll($pdo, "
    SELECT children.*, hobbies.name AS hobby_name, schools.name AS school_name 
    FROM children 
    LEFT JOIN hobbies ON children.hobby_id = hobbies.id 
    LEFT JOIN schools ON children.school_id = schools.id
");

// Fetch subscriptions grouped by school and activity
$schoolsSubscriptions = fetchAll($pdo, "
    SELECT s.name AS school_name, a.name AS activity_name, COUNT(sub.id) AS subscription_count
    FROM schools s
    LEFT JOIN children c ON s.id = c.school_id
    LEFT JOIN subscriptions sub ON c.id = sub.child_id
    LEFT JOIN sports_activities a ON sub.activity_id = a.id
    GROUP BY s.id, a.id
");

// Organize subscriptions by school
$schools = [];
foreach ($schoolsSubscriptions as $subscription) {
    $schools[$subscription['school_name']][] = $subscription;
}

// Delete logic
if (isset($_GET['delete']) && isset($_GET['type'])) {
    $id = (int) $_GET['delete'];
    $type = $_GET['type'];

    if ($type === 'parent') {
        $stmt = $pdo->prepare("DELETE FROM parents WHERE id = ?");
        $stmt->execute([$id]);
        $stmt = $pdo->prepare("DELETE FROM children WHERE parent_id = ?");
        $stmt->execute([$id]);
    } elseif ($type === 'child') {
        $stmt = $pdo->prepare("DELETE FROM children WHERE id = ?");
        $stmt->execute([$id]);
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
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .table-responsive {
            margin-top: 20px;
        }
        .table thead {
            background-color: #343a40;
            color: white;
        }
        .btn-custom {
            margin-bottom: 10px;
        }
        .card-icon {
            font-size: 2rem;
            color: #007bff;
        }
        .card-header {
            background-color: #007bff;
            color: white;
        }
        .card-footer {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center">
        <h1>Admin Dashboard</h1>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
    <p>Welcome, <?= htmlspecialchars($_SESSION['user_name']); ?>!</p>

    <div class="my-4">
        <h2>Parents</h2>
        <div class="d-flex justify-content-end">
            <a href="parent_form.php" class="btn btn-success btn-custom">Add Parent</a>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($parents as $parent): ?>
                    <tr>
                        <td><?= htmlspecialchars($parent['id']); ?></td>
                        <td><?= htmlspecialchars($parent['name']); ?></td>
                        <td><?= htmlspecialchars($parent['email']); ?></td>
                        <td><?= htmlspecialchars($parent['phone']); ?></td>
                        <td><?= htmlspecialchars($parent['role']); ?></td>
                        <td>
                            <a href="parent_form.php?id=<?= $parent['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="?delete=<?= $parent['id']; ?>&type=parent" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this parent?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="my-4">
        <h2>Children</h2>
        <div class="d-flex justify-content-end">
            <a href="child_form.php" class="btn btn-success btn-custom">Add Child</a>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Parent ID</th>
                    <th>Name</th>
                    <th>Age</th>
                    <th>Gender</th>
                    <th>School</th>
                    <th>Hobby</th>
                    <th>Notes</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($children as $child): ?>
                    <tr>
                        <td><?= htmlspecialchars($child['id']); ?></td>
                        <td><?= htmlspecialchars($child['parent_id']); ?></td>
                        <td><?= htmlspecialchars($child['name']); ?></td>
                        <td><?= htmlspecialchars($child['age']); ?></td>
                        <td><?= htmlspecialchars($child['gender']); ?></td>
                        <td><?= htmlspecialchars($child['school_name']); ?></td>
                        <td><?= htmlspecialchars($child['hobby_name']); ?></td>
                        <td><?= htmlspecialchars($child['notes']); ?></td>
                        <td>
                            <a href="child_form.php?id=<?= $child['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="?delete=<?= $child['id']; ?>&type=child" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this child?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <h2 class="mt-5">Sports Activities</h2>
    <div class="mb-3 text-end">
        <a href="activity_form.php" class="btn btn-success">Add Activity</a>
    </div>
    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $activities = fetchAll($pdo, "SELECT * FROM sports_activities");
            foreach ($activities as $activity): ?>
                <tr>
                    <td><?= htmlspecialchars($activity['id']); ?></td>
                    <td><?= htmlspecialchars($activity['name']); ?></td>
                    <td><?= htmlspecialchars($activity['description']); ?></td>
                    <td><?= htmlspecialchars($activity['start_date']); ?></td>
                    <td><?= htmlspecialchars($activity['end_date']); ?></td>
                    <td>
                        <a href="activity_form.php?id=<?= $activity['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                        <a href="?delete=<?= $activity['id']; ?>&type=activity" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this activity?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <h2 class="mt-5">Schools and Subscriptions</h2>
    <div class="row row-cols-1 row-cols-md-3 g-4">
        <?php foreach ($schools as $schoolName => $subscriptions): ?>
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <div class="card-header">
                        <h5 class="card-title"><i class="fas fa-school card-icon"></i> <?= htmlspecialchars($schoolName); ?></h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($subscriptions as $subscription): ?>
                            <p class="card-text"><i class="fas fa-futbol card-icon"></i> <strong>Activity:</strong> <?= htmlspecialchars($subscription['activity_name']); ?></p>
                            <p class="card-text"><i class="fas fa-users card-icon"></i> <strong>Number of Subscriptions:</strong> <?= htmlspecialchars($subscription['subscription_count']); ?></p>
                        <?php endforeach; ?>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?= htmlspecialchars($schoolName); ?>" aria-expanded="false" aria-controls="collapse-<?= htmlspecialchars($schoolName); ?>">
                            Toggle Activities
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>