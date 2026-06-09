<?php
require_once __DIR__ . '/auth.php';
requireAdminLogin();

$pageTitle = 'Courses';
$message = '';
$courseId = null;
$categories = ['Coding', 'Math', 'Science', 'Languages', 'Arts'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $courseId = isset($_POST['course_id']) ? (int) $_POST['course_id'] : null;
    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = in_array($_POST['status'] ?? 'active', ['active', 'inactive'], true) ? $_POST['status'] : 'active';
    $imagePath = trim($_POST['current_image'] ?? '');

    if (!empty($_FILES['course_image']['name'])) {
        $uploadDir = __DIR__ . '/../uploads/images/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $fileName = basename($_FILES['course_image']['name']);
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($extension, $allowed, true) && $_FILES['course_image']['error'] === UPLOAD_ERR_OK) {
            $safeName = 'course-' . time() . '.' . $extension;
            $targetPath = $uploadDir . $safeName;
            if (move_uploaded_file($_FILES['course_image']['tmp_name'], $targetPath)) {
                $imagePath = 'uploads/images/' . $safeName;
            }
        }
    }

    if ($courseId) {
        $stmt = $pdo->prepare('UPDATE courses SET title = :title, description = :description, category = :category, image = :image, status = :status WHERE id = :id');
        $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':category' => $category,
            ':image' => $imagePath,
            ':status' => $status,
            ':id' => $courseId,
        ]);
        $message = 'Course updated successfully.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO courses (title, description, category, image, status) VALUES (:title, :description, :category, :image, :status)');
        $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':category' => $category,
            ':image' => $imagePath,
            ':status' => $status,
        ]);
        $message = 'Course added successfully.';
    }
    header('Location: courses.php?success=1');
    exit;
}

if (!empty($_GET['delete'])) {
    $deleteId = (int) $_GET['delete'];
    $pdo->prepare('DELETE FROM courses WHERE id = :id')->execute([':id' => $deleteId]);
    header('Location: courses.php?deleted=1');
    exit;
}

if (!empty($_GET['edit'])) {
    $courseId = (int) $_GET['edit'];
    $course = $pdo->prepare('SELECT * FROM courses WHERE id = :id LIMIT 1');
    $course->execute([':id' => $courseId]);
    $course = $course->fetch(PDO::FETCH_ASSOC);
} else {
    $course = null;
}

$courses = $pdo->query('SELECT * FROM courses ORDER BY created_at DESC')->fetchAll(PDO::FETCH_ASSOC);
include __DIR__ . '/admin-header.php';
?>
<?php if (!empty($_GET['success'])): ?>
    <div class="alert alert-success">Course saved successfully.</div>
<?php endif; ?>
<?php if (!empty($_GET['deleted'])): ?>
    <div class="alert alert-success">Course deleted successfully.</div>
<?php endif; ?>
<div class="row g-4">
    <div class="col-lg-5">
        <div class="card admin-card p-4">
            <h5><?= $course ? 'Edit Course' : 'Add Course' ?></h5>
            <form method="post" enctype="multipart/form-data" class="mt-4">
                <input type="hidden" name="course_id" value="<?= htmlspecialchars($course['id'] ?? '') ?>">
                <input type="hidden" name="current_image" value="<?= htmlspecialchars($course['image'] ?? '') ?>">
                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($course['title'] ?? '') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat) ?>" <?= ($course['category'] ?? '') === $cat ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="4" class="form-control" required><?= htmlspecialchars($course['description'] ?? '') ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Image</label>
                    <input type="file" name="course_image" class="form-control">
                    <?php if (!empty($course['image'])): ?>
                        <img src="../<?= htmlspecialchars($course['image']) ?>" alt="Course image" class="img-fluid rounded-3 mt-3" style="max-height: 160px;">
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" <?= ($course['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= ($course['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Save Course</button>
            </form>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card admin-card p-4">
            <h5>Courses</h5>
            <div class="table-responsive mt-4">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($courses as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['title']) ?></td>
                                <td><?= htmlspecialchars($row['category']) ?></td>
                                <td><?= htmlspecialchars($row['status']) ?></td>
                                <td class="text-end">
                                    <a href="courses.php?edit=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <a href="courses.php?delete=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this course?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($courses)): ?>
                            <tr><td colspan="4" class="text-center text-muted">No courses found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/admin-footer.php'; ?>
