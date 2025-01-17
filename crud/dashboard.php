<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
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
    http_response_code(500); // Internal Server Error
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Fetch parent data
$parent_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM parents WHERE id = ?");
$stmt->execute([$parent_id]);
$parent = $stmt->fetch();

if (!$parent) {
    http_response_code(404); // Not Found
    echo json_encode(['status' => 'error', 'message' => 'Parent not found.']);
    exit;
}

// Fetch children data with school and hobby names
$stmt = $pdo->prepare("
    SELECT c.*, s.name AS school_name, h.name AS hobby_name
    FROM children c
    LEFT JOIN schools s ON c.school_id = s.id
    LEFT JOIN hobbies h ON c.hobby_id = h.id
    WHERE c.parent_id = ?
");
$stmt->execute([$parent_id]);
$children = $stmt->fetchAll();

// Fetch subscriptions
$stmt = $pdo->prepare("SELECT c.name AS child_name, a.name AS activity_name, s.subscription_date, 
                        s.id AS subscription_id, s.child_id, s.activity_id 
                        FROM subscriptions s
                        JOIN children c ON s.child_id = c.id
                        JOIN sports_activities a ON s.activity_id = a.id
                        WHERE c.parent_id = ?");
$stmt->execute([$parent_id]);
$subscriptions = $stmt->fetchAll();

// Fetch available activities
$stmt = $pdo->query("SELECT * FROM sports_activities");
$activities = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parent Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</head>
<body class="bg-light">
<div class="container py-5">
    <h1 class="mb-4 text-center">Welcome, <?= htmlspecialchars($parent['name']) ?></h1>

    <!-- Notification and Spinner -->
    <div id="notification" class="alert d-none" role="alert"></div>
    <div id="spinner" class="d-none text-center">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

     <div class="mb-4">
        <h3>Parent Information</h3>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?= htmlspecialchars($parent['name']) ?></td>
                    <td><?= htmlspecialchars($parent['email']) ?></td>
                    <td><?= htmlspecialchars($parent['phone']) ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div>
        <h3>Your Children</h3>
        <?php if (!empty($children)): ?>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Age</th>
                        <th>Gender</th>
                        <th>School</th>
                        <th>Hobbies</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($children as $child): ?>
                    <tr>
                        <td><?= htmlspecialchars($child['name']) ?></td>
                        <td><?= htmlspecialchars($child['age']) ?></td>
                        <td><?= htmlspecialchars($child['gender']) ?></td>
                        <td><?= htmlspecialchars($child['school_name']) ?></td>
                        <td><?= htmlspecialchars($child['hobby_name']) ?></td>
                        <td><?= htmlspecialchars($child['notes']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>You have not added any children yet.</p>
        <?php endif; ?>
    </div>

    <h2 class="mt-5">Your Subscriptions</h2>
    <table id="subscriptionsTable" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Child</th>
                <th>Activity</th>
                <th>Subscription Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($subscriptions as $subscription): ?>
            <tr>
                <td><?= htmlspecialchars($subscription['child_name']) ?></td>
                <td><?= htmlspecialchars($subscription['activity_name']) ?></td>
                <td><?= htmlspecialchars($subscription['subscription_date']) ?></td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm" 
                            onclick="unsubscribe(<?= $subscription['child_id'] ?>, <?= $subscription['activity_id'] ?>)">
                        Unsubscribe
                    </button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <h2 class="mt-5">Subscribe to Sports Activities</h2>
    <div class="row row-cols-1 row-cols-md-3 g-4">
        <?php foreach ($activities as $activity): ?>
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($activity['name']) ?></h5>
                        <p class="card-text"><?= htmlspecialchars($activity['description']) ?></p>
                        <p class="card-text">
                            <strong>Age Group:</strong> <?= htmlspecialchars($activity['age_group']) ?><br>
                            <strong>De:</strong> <?= htmlspecialchars($activity['start_date']) ?><br>
                            <strong>A:</strong> <?= htmlspecialchars($activity['end_date']) ?>
                        </p>
                        <div class="mb-3">
                            <label for="child_id_<?= $activity['id'] ?>" class="form-label">Select Child</label>
                            <select id="child_id_<?= $activity['id'] ?>" class="form-select" required>
                                <option value="">-- Select Child --</option>
                                <?php foreach ($children as $child): ?>
                                    <option value="<?= $child['id'] ?>"><?= htmlspecialchars($child['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="button" class="btn btn-primary w-100" 
                                onclick="subscribe($('#child_id_<?= $activity['id'] ?>').val(), <?= $activity['id'] ?>)">
                            Subscribe
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
function showSpinner() {
    $('#spinner').removeClass('d-none');
}

function hideSpinner() {
    $('#spinner').addClass('d-none');
}

function showNotification(message, type) {
    $('#notification')
        .removeClass('d-none alert-success alert-danger')
        .addClass(`alert-${type}`)
        .text(message);
    setTimeout(() => $('#notification').addClass('d-none'), 5000); // Hide after 5 seconds
}

function subscribe(childId, activityId) {
    if (!childId) {
        showNotification('Please select a child before subscribing.', 'danger');
        return;
    }

    showSpinner();

    $.ajax({
        url: 'subscribe.php',
        type: 'POST',
        data: { child_id: childId, activity_id: activityId },
        dataType: 'json',
        success: function(response) {
            hideSpinner();
            if (response.status === 'success') {
                showNotification(response.message, 'success');
                setTimeout(() => location.reload(), 1000); // Reload after a short delay
            } else {
                showNotification(response.message, 'danger');
            }
        },
        error: function() {
            hideSpinner();
            showNotification('An error occurred while subscribing. Please try again.', 'danger');
        }
    });
}

function unsubscribe(childId, activityId) {
    showSpinner();

    $.ajax({
        url: 'unsubscribe.php',
        type: 'POST',
        data: { child_id: childId, activity_id: activityId },
        dataType: 'json',
        success: function(response) {
            hideSpinner();
            if (response.status === 'success') {
                showNotification(response.message, 'success');
                setTimeout(() => location.reload(), 1000); // Reload after a short delay
            } else {
                showNotification(response.message, 'danger');
            }
        },
        error: function() {
            hideSpinner();
            showNotification('An error occurred while unsubscribing. Please try again.', 'danger');
        }
    });
}
</script>
</body>
</html>
