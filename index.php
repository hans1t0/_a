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

// Obtener la lista de colegios
$stmt = $pdo->query("SELECT id, name FROM schools");
$schools = $stmt->fetchAll();

// Obtener la lista de grados
$stmt = $pdo->query("SELECT id, name FROM grades");
$grades = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Family Form</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<div class="container py-5">
    <h1 class="mb-4">Family Form</h1>
    <form id="family-form" method="POST" action="submit_form.php">
        <div class="section-header">
            <h2>Parent Information</h2>
        </div>
        <div class="row mb-3">
            <div class="col-md-6 col-12">
                <label for="parent_name" class="form-label">Parent Name</label>
                <input type="text" class="form-control" id="parent_name" name="parent[name]" required>
            </div>
            <div class="col-md-6 col-12">
                <label for="parent_lastname" class="form-label">Parent Last Name</label>
                <input type="text" class="form-control" id="parent_lastname" name="parent[lastname]" required>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6 col-12">
                <label for="parent_dni" class="form-label">Parent DNI</label>
                <input type="text" class="form-control" id="parent_dni" name="parent[dni]" required>
            </div>
            <div class="col-md-6 col-12">
                <label for="parent_cc" class="form-label">Parent CC</label>
                <input type="text" class="form-control" id="parent_cc" name="parent[cc]" required>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6 col-12">
                <label for="parent_phone" class="form-label">Parent Phone</label>
                <input type="text" class="form-control" id="parent_phone" name="parent[phone]" required>
            </div>
            <div class="col-md-6 col-12">
                <label for="parent_email" class="form-label">Parent Email</label>
                <input type="email" class="form-control" id="parent_email" name="parent[email]" required>
            </div>
        </div>
        <div class="section-header">
            <h2>Children Information</h2>
        </div>
        <div id="children-container"></div>
        <button type="button" class="btn btn-primary mb-3" id="add-child-btn">Add Child</button>
        <button type="submit" class="btn btn-success">Submit</button>
    </form>
</div>

<!-- Modal para confirmar eliminación -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmDeleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to remove this child?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Remove</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    let childIndex = 0;
    let childToRemove = null;

    $('#add-child-btn').click(function () {
        const childHtml = `
            <div id="child-${childIndex}" class="child-section">
                <div class="d-flex justify-content-between align-items-center">
                    <h3>Child ${childIndex + 1}</h3>
                    <button type="button" class="btn btn-link toggle-btn">Toggle</button>
                </div>
                <div class="child-content">
                    <div class="row mb-3">
                        <div class="col-md-6 col-12">
                            <label for="child_name_${childIndex}" class="form-label">Child Name</label>
                            <input type="text" class="form-control" id="child_name_${childIndex}" name="children[${childIndex}][name]" required>
                        </div>
                        <div class="col-md-6 col-12">
                            <label for="child_lastname_${childIndex}" class="form-label">Child Last Name</label>
                            <input type="text" class="form-control" id="child_lastname_${childIndex}" name="children[${childIndex}][lastname]" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6 col-12">
                            <label for="child_dob_${childIndex}" class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" id="child_dob_${childIndex}" name="children[${childIndex}][dob]" required>
                        </div>
                        <div class="col-md-6 col-12">
                            <label for="child_grade_${childIndex}" class="form-label">Grade</label>
                            <select class="form-control" id="child_grade_${childIndex}" name="children[${childIndex}][grade_id]" required>
                                <option value="">Select Grade</option>
                                <?php foreach ($grades as $grade): ?>
                                    <option value="<?= $grade['id'] ?>"><?= htmlspecialchars($grade['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6 col-12">
                            <label for="child_school_${childIndex}" class="form-label">School</label>
                            <select class="form-control" id="child_school_${childIndex}" name="children[${childIndex}][school_id]" required>
                                <option value="">Select School</option>
                                <?php foreach ($schools as $school): ?>
                                    <option value="<?= $school['id'] ?>"><?= htmlspecialchars($school['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Please select the child's school.</div>
                        </div>
                        <div class="col-md-6 col-12">
                            <label for="child_activities_${childIndex}" class="form-label">Activities</label>
                            <select class="form-control" id="child_activities_${childIndex}" name="children[${childIndex}][activity_id]" required>
                                <option value="">Select Activity</option>
                            </select>
                            <div class="invalid-feedback">Please select an activity.</div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-danger mb-3 remove-child-btn" data-child-index="${childIndex}">Remove Child</button>
                </div>
            </div>
        `;
        $('#children-container').append(childHtml);
        childIndex++;
        updateChildCount();

        // Añadir validación en tiempo real a los nuevos campos
        $(`#child-${childIndex - 1} input, #child-${childIndex - 1} select`).on('input change', function () {
            if (this.checkValidity()) {
                $(this).removeClass('is-invalid').addClass('is-valid');
            } else {
                $(this).removeClass('is-valid').addClass('is-invalid');
            }
        });

        // Añadir evento para actualizar actividades según el colegio y el curso seleccionado
        $(`#child_school_${childIndex - 1}, #child_grade_${childIndex - 1}`).change(function () {
            var schoolId = $(`#child_school_${childIndex - 1}`).val();
            var gradeId = $(`#child_grade_${childIndex - 1}`).val();
            var activitySelect = $(`#child_activities_${childIndex - 1}`);
            if (schoolId && gradeId) {
                $.ajax({
                    url: 'get_activities.php',
                    type: 'GET',
                    data: { school_id: schoolId, grade_id: gradeId },
                    success: function(data) {
                        activitySelect.html(data);
                    }
                });
            } else {
                activitySelect.html('<option value="">Select Activity</option>');
            }
        });
    });

    $(document).on('click', '.remove-child-btn', function () {
        childToRemove = $(this).data('child-index');
        $('#confirmDeleteModal').modal('show');
    });

    $('#confirmDeleteBtn').click(function () {
        $(`#child-${childToRemove}`).remove();
        $('#confirmDeleteModal').modal('hide');
        updateChildCount();
    });

    $(document).on('click', '.toggle-btn', function () {
        $(this).closest('.child-section').toggleClass('collapsed');
    });

    // Validación en tiempo real para los campos existentes
    $('#family-form input, #family-form select').on('input change', function () {
        if (this.checkValidity()) {
            $(this).removeClass('is-invalid').addClass('is-valid');
        } else {
            $(this).removeClass('is-valid').addClass('is-invalid');
        }
    });

    function updateChildCount() {
        const count = $('#children-container .child-section').length;
        $('#child-count').text(`(${count})`);
    }
});
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>