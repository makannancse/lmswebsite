<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/site.php';
requireAdminLogin();

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'add' || $action === 'edit') {
            $name = trim($_POST['name'] ?? '');
            $subject = trim($_POST['subject'] ?? '');
            $qualifications = trim($_POST['qualifications'] ?? '');
            $bio = trim($_POST['bio'] ?? '');
            $experience_years = (int)($_POST['experience_years'] ?? 0);
            $students_count = (int)($_POST['students_count'] ?? 0);
            $status = isset($_POST['status']) ? 'active' : 'inactive';

            $image = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../uploads/teachers/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
                $uploadFile = $uploadDir . $fileName;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
                    $image = 'uploads/teachers/' . $fileName;
                }
            }

            if ($action === 'add') {
                $stmt = $pdo->prepare('INSERT INTO teachers (name, subject, qualifications, bio, experience_years, students_count, image, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([$name, $subject, $qualifications, $bio, $experience_years, $students_count, $image, $status]);
                $message = 'Teacher added successfully!';
                $messageType = 'success';
            } elseif ($action === 'edit' && isset($_POST['id'])) {
                $id = (int)$_POST['id'];

                if ($image) {
                    $stmt = $pdo->prepare('UPDATE teachers SET name = ?, subject = ?, qualifications = ?, bio = ?, experience_years = ?, students_count = ?, image = ?, status = ? WHERE id = ?');
                    $stmt->execute([$name, $subject, $qualifications, $bio, $experience_years, $students_count, $image, $status, $id]);
                } else {
                    $stmt = $pdo->prepare('UPDATE teachers SET name = ?, subject = ?, qualifications = ?, bio = ?, experience_years = ?, students_count = ?, status = ? WHERE id = ?');
                    $stmt->execute([$name, $subject, $qualifications, $bio, $experience_years, $students_count, $status, $id]);
                }

                $message = 'Teacher updated successfully!';
                $messageType = 'success';
            }
        } elseif ($action === 'delete' && isset($_POST['id'])) {
            $id = (int)$_POST['id'];
            $stmt = $pdo->prepare("DELETE FROM teachers WHERE id = ?");
            $stmt->execute([$id]);
            $message = 'Teacher deleted successfully!';
            $messageType = 'success';
        }
    }
}

$teachers = getTeachers();
$editTeacher = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM teachers WHERE id = ?");
    $stmt->execute([$editId]);
    $editTeacher = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Teachers - LearnWise Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">LearnWise Admin</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="sections.php">Sections</a>
                <a class="nav-link" href="courses.php">Courses</a>
                <a class="nav-link active" href="teachers.php">Teachers</a>
                <a class="nav-link" href="settings.php">Settings</a>
                <a class="nav-link" href="../index.php" target="_blank">View Site</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Manage Teachers</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#teacherModal" onclick="resetForm()">
                        <i class="bi bi-plus-circle"></i> Add Teacher
                    </button>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>Subject</th>
                                        <th>Experience</th>
                                        <th>Students</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($teachers as $teacher): ?>
                                        <tr>
                                            <td>
                                                <?php if ($teacher['image']): ?>
                                                    <img src="../<?= htmlspecialchars($teacher['image']) ?>" alt="<?= htmlspecialchars($teacher['name']) ?>" class="rounded-circle" width="40" height="40">
                                                <?php else: ?>
                                                    <div class="bg-secondary rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                        <i class="bi bi-person text-white"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($teacher['name']) ?></td>
                                            <td><?= htmlspecialchars($teacher['subject']) ?></td>
                                            <td><?= htmlspecialchars($teacher['experience_years']) ?> yrs</td>
                                            <td><?= htmlspecialchars($teacher['students_count']) ?>+</td>
                                            <td>
                                                <span class="badge bg-<?= $teacher['status'] ? 'success' : 'secondary' ?>">
                                                    <?= $teacher['status'] ? 'Active' : 'Inactive' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary me-1" onclick="editTeacher(<?= $teacher['id'] ?>)">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" onclick="deleteTeacher(<?= $teacher['id'] ?>, '<?= htmlspecialchars($teacher['name']) ?>')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Quick Stats</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="border-end">
                                    <h3 class="text-primary"><?= count($teachers) ?></h3>
                                    <small class="text-muted">Total Teachers</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <h3 class="text-success"><?= count(array_filter($teachers, fn($t) => $t['status'])) ?></h3>
                                <small class="text-muted">Active</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Teacher Modal -->
    <div class="modal fade" id="teacherModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Teacher</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" id="action" value="add">
                        <input type="hidden" name="id" id="teacherId">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Name *</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="subject" class="form-label">Subject *</label>
                                    <input type="text" class="form-control" id="subject" name="subject" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="qualifications" class="form-label">Qualifications</label>
                            <input type="text" class="form-control" id="qualifications" name="qualifications" placeholder="e.g. M.Sc. Mathematics, B.Ed.">
                        </div>

                        <div class="mb-3">
                            <label for="bio" class="form-label">Bio</label>
                            <textarea class="form-control" id="bio" name="bio" rows="3"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="experience_years" class="form-label">Experience (Years)</label>
                                    <input type="number" class="form-control" id="experience_years" name="experience_years" min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="students_count" class="form-label">Students Count</label>
                                    <input type="number" class="form-control" id="students_count" name="students_count" min="0">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="image" class="form-label">Profile Image</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <div class="form-text">Leave empty to keep current image (for edits)</div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="status" name="status" checked>
                                <label class="form-check-label" for="status">
                                    Active
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Teacher</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Teacher</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete <strong id="deleteTeacherName"></strong>?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="deleteTeacherId">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function resetForm() {
            document.getElementById('action').value = 'add';
            document.getElementById('teacherId').value = '';
            document.getElementById('modalTitle').textContent = 'Add Teacher';
            document.getElementById('name').value = '';
            document.getElementById('subject').value = '';
            document.getElementById('bio').value = '';
            document.getElementById('experience_years').value = '';
            document.getElementById('students_count').value = '';
            document.getElementById('image').value = '';
            document.getElementById('status').checked = true;
        }

        function editTeacher(id) {
            fetch(`?edit=${id}`)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const teacher = JSON.parse(doc.querySelector('script[data-teacher]')?.textContent || '{}');

                    if (teacher) {
                        document.getElementById('action').value = 'edit';
                        document.getElementById('teacherId').value = teacher.id;
                        document.getElementById('modalTitle').textContent = 'Edit Teacher';
                        document.getElementById('name').value = teacher.name;
                        document.getElementById('subject').value = teacher.subject;
                        document.getElementById('bio').value = teacher.bio;
                        document.getElementById('experience_years').value = teacher.experience_years;
                        document.getElementById('students_count').value = teacher.students_count;
                        document.getElementById('status').checked = teacher.status == 1;

                        const modal = new bootstrap.Modal(document.getElementById('teacherModal'));
                        modal.show();
                    }
                });
        }

        function deleteTeacher(id, name) {
            document.getElementById('deleteTeacherId').value = id;
            document.getElementById('deleteTeacherName').textContent = name;
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        }

        <?php if ($editTeacher): ?>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('action').value = 'edit';
            document.getElementById('teacherId').value = '<?= $editTeacher['id'] ?>';
            document.getElementById('modalTitle').textContent = 'Edit Teacher';
            document.getElementById('name').value = '<?= htmlspecialchars($editTeacher['name']) ?>';
            document.getElementById('subject').value = '<?= htmlspecialchars($editTeacher['subject']) ?>';
            document.getElementById('bio').value = '<?= htmlspecialchars($editTeacher['bio']) ?>';
            document.getElementById('experience_years').value = '<?= $editTeacher['experience_years'] ?>';
            document.getElementById('students_count').value = '<?= $editTeacher['students_count'] ?>';
            document.getElementById('status').checked = <?= $editTeacher['status'] ? 'true' : 'false' ?>;

            const modal = new bootstrap.Modal(document.getElementById('teacherModal'));
            modal.show();
        });
        <?php endif; ?>
    </script>
</body>
</html>