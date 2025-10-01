<?php
require_once '../config/database.php';

// Session check
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$message = '';

// Handle CRUD
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim((string)($_POST['name'] ?? ''));
    $roll_number = trim((string)($_POST['roll_number'] ?? ''));
    $email_input = (string)($_POST['email'] ?? '');
    $course = trim((string)($_POST['course'] ?? ''));
    $grades_raw = $_POST['grades'] ?? ''; // Could be string or array

    // Validate email
    $email = filter_var($email_input, FILTER_VALIDATE_EMAIL) ? $email_input : null;

    // Parse grades (handles both string from textarea and array from old input)
    $grades_input = [];
    if (is_array($grades_raw)) {
        // Old form: array of strings like ['Math:90', 'Science:85']
        foreach ($grades_raw as $grade_str) {
            if (preg_match('/^(\w+):(\d+)$/', trim((string)$grade_str), $matches)) {
                $grades_input[$matches[1]] = (int)$matches[2];
            }
        }
    } else {
        // New form: string from textarea, e.g., "Math:90\nScience:85"
        $lines = explode("\n", trim((string)$grades_raw));
        foreach ($lines as $line) {
            if (preg_match('/^(\w+):(\d+)$/', trim($line), $matches)) {
                $grades_input[$matches[1]] = (int)$matches[2];
            }
        }
    }
    $grades = json_encode($grades_input);

    if (isset($_POST['create'])) {
        // Create
        if ($email && $name && $roll_number && $course) {
            try {
                $stmt = $pdo->prepare("INSERT INTO students (name, roll_number, email, course, grades) VALUES (?, ?, ?, ?, ?)");
                if ($stmt->execute([$name, $roll_number, $email, $course, $grades])) {
                    $message = 'Student created successfully!';
                } else {
                    $message = 'Error creating student.';
                }
            } catch (PDOException $e) {
                $message = 'Database error: ' . $e->getMessage();
            }
        } else {
            $message = 'Invalid input data (check email and required fields).';
        }
    } elseif (isset($_POST['update'])) {
        // Update
        $id = (int)($_POST['id'] ?? 0);
        if ($email && $name && $roll_number && $course && $id > 0) {
            try {
                $stmt = $pdo->prepare("UPDATE students SET name=?, roll_number=?, email=?, course=?, grades=? WHERE id=?");
                if ($stmt->execute([$name, $roll_number, $email, $course, $grades, $id])) {
                    $message = 'Student updated successfully!';
                } else {
                    $message = 'Error updating student (no changes or not found).';
                }
            } catch (PDOException $e) {
                $message = 'Database error: ' . $e->getMessage();
            }
        } else {
            $message = 'Invalid input data or ID.';
        }
    } elseif (isset($_POST['delete'])) {
        // Delete
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $stmt = $pdo->prepare("DELETE FROM students WHERE id=?");
                if ($stmt->execute([$id])) {
                    $message = 'Student deleted successfully!';
                }
            } catch (PDOException $e) {
                $message = 'Database error: ' . $e->getMessage();
            }
        }
    }

    // Refresh students list after any operation
    $stmt = $pdo->query("SELECT * FROM students ORDER BY created_at DESC");
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Initial read
    $stmt = $pdo->query("SELECT * FROM students ORDER BY created_at DESC");
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// For display, decode grades
foreach ($students as &$student) {
    $student['grades_display'] = json_decode($student['grades'], true) ?: [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SRMS - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">SRMS Admin</a>
            <a href="logout.php" class="btn btn-outline-light ms-auto">Logout</a>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Student Records</h2>
        <?php if ($message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Create Button -->
        <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createModal">Add Student</button>

        <!-- Students Table -->
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Roll Number</th>
                        <th>Email</th>
                        <th>Course</th>
                        <th>Grades</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($students)): ?>
                        <tr><td colspan="7" class="text-center">No students found. Add one to get started!</td></tr>
                    <?php else: ?>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?php echo $student['id']; ?></td>
                                <td><?php echo htmlspecialchars($student['name']); ?></td>
                                <td><?php echo htmlspecialchars($student['roll_number']); ?></td>
                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                                <td><?php echo htmlspecialchars($student['course']); ?></td>
                                <td>
                                    <?php 
                                    $grades = $student['grades_display'];
                                    if (is_array($grades) && !empty($grades)) {
                                        echo htmlspecialchars(implode(', ', array_map(function($k, $v) { return "$k: $v"; }, array_keys($grades), array_values($grades))));
                                    } else {
                                        echo 'No grades';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-warning" onclick="editStudent(<?php echo $student['id']; ?>)">Edit</button>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this student?');">
                                        <input type="hidden" name="id" value="<?php echo $student['id']; ?>">
                                        <button type="submit" name="delete" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Modal (Uses textarea for grades - string input) -->
    <div class="modal fade" id="createModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="create" value="1">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Roll Number</label>
                            <input type="text" class="form-control" name="roll_number" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Course</label>
                            <input type="text" class="form-control" name="course" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Grades (one per line: Subject:Score)</label>
                            <textarea class="form-control" name="grades" rows="3" placeholder="Math:90&#10;Science:85&#10;English:78"></textarea>
                            <small class="form-text text-muted">Example: Math:90 (scores 0-100). Empty is OK.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Student</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal (Same as Create, but populated by JS) -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editForm" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="update" value="1">
                        <input type="hidden" name="id" id="editId">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" id="editName" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Roll Number</label>
                            <input type="text" class="form-control" name="roll_number" id="editRollNumber" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="editEmail" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Course</label>
                            <input type="text" class="form-control" name="course" id="editCourse" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Grades (one per line: Subject:Score)</label>
                            <textarea class="form-control" name="grades" id="editGrades" rows="3"></textarea>
                            <small class="form-text text-muted">Example: Math:90 (scores 0-100). Empty is OK.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Student</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>
