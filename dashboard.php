<?php
session_start();

// Verificar si el usuario es un padre
if (!isset($_SESSION['parent_id'])) {
    header("Location: login.php");
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

// Obtener la información del padre
$stmt = $pdo->prepare("SELECT * FROM parents WHERE id = ?");
$stmt->execute([$_SESSION['parent_id']]);
$parent = $stmt->fetch();

// Obtener la lista de niños del padre
$stmt = $pdo->prepare("SELECT children.*, grades.name AS grade_name, schools.name AS school_name FROM children JOIN grades ON children.grade_id = grades.id JOIN schools ON children.school_id = schools.id WHERE parent_id = ?");
$stmt->execute([$_SESSION['parent_id']]);
$children = $stmt->fetchAll();

// Obtener la lista de todas las actividades
$stmt = $pdo->query("SELECT * FROM activities");
$all_activities = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<div class="container py-5">
    <h1 class="mb-4">Welcome, <?= htmlspecialchars($parent['name']) ?> <?= htmlspecialchars($parent['lastname']) ?></h1>
    <a href="logout.php" class="btn btn-danger mb-4"><i class="fas fa-sign-out-alt"></i> Logout</a>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?= $_SESSION['message_type'] ?> alert-dismissible fade show" role="alert">
            <?= $_SESSION['message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
    <?php endif; ?>

    <h2>Children</h2>
    <div class="row">
        <?php foreach ($children as $child): ?>
            <div class="col-md-6">
                <div class="card card-child">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($child['name']) ?> <?= htmlspecialchars($child['lastname']) ?></h5>
                        <p class="card-text"><strong>Date of Birth:</strong> <?= htmlspecialchars($child['dob']) ?></p>
                        <p class="card-text"><strong>Grade:</strong> <?= htmlspecialchars($child['grade_name']) ?></p>
                        <p class="card-text"><strong>School:</strong> <?= htmlspecialchars($child['school_name']) ?></p>
                        <p class="card-text"><strong>Desired Activities:</strong></p>
                        <ul>
                            <?php
                            $stmt = $pdo->prepare("SELECT activities.id, activities.name, children_activities.id AS subscription_id FROM activities JOIN children_activities ON activities.id = children_activities.activity_id WHERE children_activities.child_id = ?");
                            $stmt->execute([$child['id']]);
                            $child_activities = $stmt->fetchAll();
                            $subscribed_activity_ids = array_column($child_activities, 'id');
                            foreach ($child_activities as $child_activity):
                            ?>
                                <li>
                                    <?= htmlspecialchars($child_activity['name']) ?>
                                    <form method="POST" action="unsubscribe_activity.php" class="d-inline">
                                        <input type="hidden" name="subscription_id" value="<?= $child_activity['subscription_id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-times"></i> Unsubscribe</button>
                                    </form>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <h5 class="mt-4">Add New Activity</h5>
                        <div class="row">
                            <?php
                            $stmt = $pdo->prepare("SELECT activities.id, activities.name, activities.description FROM activities
                                                   JOIN school_course_activities ON activities.id = school_course_activities.activity_id
                                                   WHERE school_course_activities.school_id = ? AND school_course_activities.min_grade_id <= ? AND school_course_activities.max_grade_id >= ?");
                            $stmt->execute([$child['school_id'], $child['grade_id'], $child['grade_id']]);
                            $available_activities = $stmt->fetchAll();
                            foreach ($available_activities as $activity):
                                if (!in_array($activity['id'], $subscribed_activity_ids)):
                            ?>
                                    <div class="col-md-6">
                                        <div class="card card-activity">
                                            <div class="card-body">
                                                <h5 class="card-title"><?= htmlspecialchars($activity['name']) ?></h5>
                                                <p class="card-text"><?= htmlspecialchars($activity['description']) ?></p>
                                                <form method="POST" action="subscribe_activity.php">
                                                    <input type="hidden" name="child_id" value="<?= $child['id'] ?>">
                                                    <input type="hidden" name="activity_id" value="<?= $activity['id'] ?>">
                                                    <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Subscribe</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
</body>
</html>