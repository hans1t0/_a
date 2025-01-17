<?php
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

if (isset($_GET['school_id']) && isset($_GET['grade_id'])) {
    $schoolId = $_GET['school_id'];
    $gradeId = $_GET['grade_id'];
    $stmt = $pdo->prepare("SELECT activities.id, activities.name FROM activities
                           JOIN school_course_activities ON activities.id = school_course_activities.activity_id
                           WHERE school_course_activities.school_id = ? AND school_course_activities.min_grade_id <= ? AND school_course_activities.max_grade_id >= ?");
    $stmt->execute([$schoolId, $gradeId, $gradeId]);
    $activities = $stmt->fetchAll();

    foreach ($activities as $activity) {
        echo '<option value="' . htmlspecialchars($activity['id']) . '">' . htmlspecialchars($activity['name']) . '</option>';
    }
}
?>