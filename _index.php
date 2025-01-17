<?php
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

// Fetch schools and hobbies from the database
$schools = $pdo->query("SELECT id, name FROM schools")->fetchAll();
$hobbies = $pdo->query("SELECT id, name FROM hobbies")->fetchAll();

// Capture success status and password from URL
$status = $_GET['status'] ?? null;
$password = $_GET['password'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Family Registration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</head>
<body class="bg-light">
<div class="container py-5">
    <h1 class="mb-4 text-center">Family Registration</h1>

    <!-- Feedback Section -->
    <?php if ($status === 'success'): ?>
        <div class="alert alert-success">
            <strong>Success!</strong> Your registration was successful. Your generated password is: <strong><?= htmlspecialchars($password) ?></strong>
        </div>
    <?php elseif ($status === 'error'): ?>
        <div class="alert alert-danger">
            <strong>Error!</strong> There was an issue processing your registration.
        </div>
    <?php endif; ?>

    <form id="family-form" action="process.php" method="post">

        <!-- Parent Section -->
        <div class="form-section parent-section">
            <h3>Parent's Information</h3>
            <div class="mb-3">
                <label for="father_name" class="form-label">Name</label>
                <input type="text" class="form-control" id="father_name" name="father_name" required>
            </div>
            <div class="mb-3">
                <label for="father_email" class="form-label">Email</label>
                <input type="email" class="form-control" id="father_email" name="father_email" required>
            </div>
            <div class="mb-3">
                <label for="father_phone" class="form-label">Phone</label>
                <input type="tel" class="form-control" id="father_phone" name="father_phone" required>
            </div>
        </div>

        <!-- Children Section -->
        <div class="form-section">
            <h3>Children</h3>
            <div id="children-container"></div>
            <button type="button" id="add-child-btn" class="btn btn-primary">Add Child</button>
        </div>

        <!-- Submit Button -->
        <div class="text-center">
            <button type="submit" class="btn btn-success">Submit</button>
        </div>
    </form>
</div>

<script>
    $(document).ready(function () {
        let childIndex = 0;

        // Add child section
        $('#add-child-btn').click(function () {
            const childHtml = `
                <div class="child-section" id="child-${childIndex}">
                    <span class="remove-child-btn text-danger" data-child-index="${childIndex}">Remove</span>
                    <div class="mb-3">
                        <label for="children[${childIndex}][name]" class="form-label">Child's Name</label>
                        <input type="text" class="form-control" name="children[${childIndex}][name]" required>
                    </div>
                    <div class="mb-3">
                        <label for="children[${childIndex}][age]" class="form-label">Age</label>
                        <input type="number" class="form-control" name="children[${childIndex}][age]" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gender</label>
                        <div>
                            <label class="form-check-label me-3">
                                <input type="radio" class="form-check-input" name="children[${childIndex}][gender]" value="Male" required> Male
                            </label>
                            <label class="form-check-label me-3">
                                <input type="radio" class="form-check-input" name="children[${childIndex}][gender]" value="Female"> Female
                            </label>
                            <label class="form-check-label">
                                <input type="radio" class="form-check-input" name="children[${childIndex}][gender]" value="Other"> Other
                            </label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="children[${childIndex}][school]" class="form-label">School</label>
                        <select class="form-control" name="children[${childIndex}][school]" required>
                            <option value="">Select School</option>
                            <?php foreach ($schools as $school): ?>
                                <option value="<?= $school['id'] ?>"><?= htmlspecialchars($school['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="children[${childIndex}][hobbies]" class="form-label">Hobbies</label>
                        <select class="form-control" name="children[${childIndex}][hobbies]" required>
                            <option value="">Select Hobby</option>
                            <?php foreach ($hobbies as $hobby): ?>
                                <option value="<?= $hobby['id'] ?>"><?= htmlspecialchars($hobby['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="children[${childIndex}][notes]" class="form-label">Notes</label>
                        <textarea class="form-control" name="children[${childIndex}][notes]" rows="2"></textarea>
                    </div>
                </div>
            `;
            $('#children-container').append(childHtml);
            childIndex++;
        });

        // Remove child section
        $(document).on('click', '.remove-child-btn', function () {
            const childIndex = $(this).data('child-index');
            $(`#child-${childIndex}`).remove();
        });

        // Form submit validation to ensure at least one child is added
        $('#family-form').submit(function (e) {
            if ($('#children-container').children().length === 0) {
                e.preventDefault(); // Prevent form submission
                alert("At least one child must be added.");
            }
        });
    });
</script>
</body>
</html>
