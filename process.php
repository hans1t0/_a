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

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate and sanitize parent fields
    if (empty($_POST['father_name']) || empty($_POST['father_email']) || empty($_POST['father_phone'])) {
        die("All parent fields are required.");
    }

    $father_name = htmlspecialchars(trim($_POST['father_name']));
    $father_email = filter_var(trim($_POST['father_email']), FILTER_SANITIZE_EMAIL);
    $father_phone = htmlspecialchars(trim($_POST['father_phone']));

    // Validate children fields
    $children = $_POST['children'] ?? [];
    if (count($children) == 0) {
        die("At least one child must be added.");
    }

    foreach ($children as $key => $child) {
        if (empty($child['name']) || empty($child['age']) || empty($child['gender']) || empty($child['school']) || empty($child['hobbies'])) {
            die("All child fields are required.");
        }

        // Sanitize child data
        $children[$key]['name'] = htmlspecialchars(trim($child['name']));
        $children[$key]['age'] = filter_var($child['age'], FILTER_VALIDATE_INT);
        $children[$key]['gender'] = htmlspecialchars(trim($child['gender']));
        $children[$key]['school'] = filter_var($child['school'], FILTER_VALIDATE_INT);
        $children[$key]['hobbies'] = filter_var($child['hobbies'], FILTER_VALIDATE_INT);
        $children[$key]['notes'] = htmlspecialchars(trim($child['notes']));
    }

    // Generate a random password for the parent
    $randomPassword = generateRandomPassword();
    $hashedPassword = password_hash($randomPassword, PASSWORD_DEFAULT); // Hash the password for security

    // Insert parent data
    $pdo->beginTransaction();

    try {
        // Insert parent data into the database
        $stmt = $pdo->prepare("INSERT INTO parents (name, email, phone, password) VALUES (?, ?, ?, ?)");
        $stmt->execute([$father_name, $father_email, $father_phone, $hashedPassword]);
        $parent_id = $pdo->lastInsertId();

        // Insert children data
        foreach ($children as $child) {
            $stmt = $pdo->prepare("INSERT INTO children (parent_id, name, age, gender, school_id, hobby_id, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $parent_id,
                $child['name'],
                $child['age'],
                $child['gender'],
                $child['school'],
                $child['hobbies'],
                $child['notes']
            ]);
        }

        $pdo->commit();
        
        // Redirect to the index.php with a success message and the generated password
        header("Location: index.php?status=success&password=" . urlencode($randomPassword));
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Error processing the data: " . $e->getMessage());
    }
}

// Function to generate a random password
function generateRandomPassword($length = 12) {
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[random_int(0, strlen($characters) - 1)];
    }
    return $password;
}
?>
