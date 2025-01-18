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

// Obtener la actividad a editar
if (isset($_GET['id'])) {
    $activityId = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM activities WHERE id = ?");
    $stmt->execute([$activityId]);
    $activity = $stmt->fetch();

    if (!$activity) {
        die("Activity not found.");
    }
} else {
    die("Invalid request.");
}

// Obtener la lista de colegios
$stmt = $pdo->query("SELECT id, name FROM schools");
$schools = $stmt->fetchAll();

// Obtener la lista de grados
$stmt = $pdo->query("SELECT id, name FROM grades");
$grades = $stmt->fetchAll();

// Obtener los colegios asociados a la actividad
$stmt = $pdo->prepare("SELECT school_id FROM school_course_activities WHERE activity_id = ?");
$stmt->execute([$activityId]);
$activity_schools = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Procesar el formulario de edición
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $description = filter_var($_POST['description'], FILTER_SANITIZE_STRING);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $min_grade = filter_var($_POST['min_grade'], FILTER_SANITIZE_NUMBER_INT);
    $max_grade = filter_var($_POST['max_grade'], FILTER_SANITIZE_NUMBER_INT);
    $school_ids = $_POST['school_ids'];

    $stmt = $pdo->prepare("UPDATE activities SET name = ?, description = ?, start_date = ?, end_date = ?, min_grade = ?, max_grade = ? WHERE id = ?");
    $stmt->execute([$name, $description, $start_date, $end_date, $min_grade, $max_grade, $activityId]);

    // Eliminar las relaciones actuales de la actividad con los colegios
    $stmt = $pdo->prepare("DELETE FROM school_course_activities WHERE activity_id = ?");
    $stmt->execute([$activityId]);

    // Insertar las nuevas relaciones de la actividad con los colegios
    foreach ($school_ids as $school_id) {
        $school_id = filter_var($school_id, FILTER_SANITIZE_NUMBER_INT);
        $stmt = $pdo->prepare("INSERT INTO school_course_activities (school_id, min_grade_id, max_grade_id, activity_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$school_id, $min_grade, $max_grade, $activityId]);
    }

    $_SESSION['message'] = "Activity updated successfully.";
    header("Location: admin_activities.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Activity</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
</head>
<body>
<div class="container py-5">
    <h1 class="mb-4">Edit Activity</h1>
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($_SESSION['message']) ?>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>
    <form method="POST" action="admin_edit_activity.php?id=<?= htmlspecialchars($activityId) ?>">
        <div class="mb-3">
            <label for="school_ids" class="form-label">Schools</label>
            <select class="form-control" id="school_ids" name="school_ids[]" multiple required>
                <option value="">Select Schools</option>
                <?php foreach ($schools as $school): ?>
                    <option value="<?= $school['id'] ?>" <?= in_array($school['id'], $activity_schools) ? 'selected' : '' ?>><?= htmlspecialchars($school['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="name" class="form-label">Activity Name</label>
            <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($activity['name']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" required><?= htmlspecialchars($activity['description']) ?></textarea>
        </div>
        <div class="mb-3">
            <label for="start_date" class="form-label">Start Date</label>
            <input type="date" class="form-control" id="start_date" name="start_date" value="<?= htmlspecialchars($activity['start_date']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="end_date" class="form-label">End Date</label>
            <input type="date" class="form-control" id="end_date" name="end_date" value="<?= htmlspecialchars($activity['end_date']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="min_grade" class="form-label">Minimum Grade</label>
            <select class="form-control" id="min_grade" name="min_grade" required>
                <option value="">Select Minimum Grade</option>
                <?php foreach ($grades as $grade): ?>
                    <option value="<?= $grade['id'] ?>" <?= $grade['id'] == $activity['min_grade'] ? 'selected' : '' ?>><?= htmlspecialchars($grade['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="max_grade" class="form-label">Maximum Grade</label>
            <select class="form-control" id="max_grade" name="max_grade" required>
                <option value="">Select Maximum Grade</option>
                <?php foreach ($grades as $grade): ?>
                    <option value="<?= $grade['id'] ?>" <?= $grade['id'] == $activity['max_grade'] ? 'selected' : '' ?>><?= htmlspecialchars($grade['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="admin_activities.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>