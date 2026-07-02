<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/site.php';
requireAdminLogin();

$pageTitle = 'Manage Teachers';
$message = '';
$messageType = 'success';

function lwFetchTeacher(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM teachers WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $teacher = $stmt->fetch(PDO::FETCH_ASSOC);
    return $teacher ?: null;
}

function lwDeleteTeacherPhotoFile(?string $path): void
{
    $relativePath = ltrim(str_replace('\\', '/', trim((string) $path)), '/');
    if ($relativePath === '' || strpos($relativePath, 'uploads/teachers/') !== 0) {
        return;
    }

    $projectRoot = realpath(dirname(__DIR__));
    if ($projectRoot === false) {
        return;
    }

    $teacherDir = realpath($projectRoot . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'teachers');
    $absolutePath = realpath($projectRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath));
    if ($teacherDir === false || $absolutePath === false || !is_file($absolutePath)) {
        return;
    }

    $teacherDir = rtrim($teacherDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    if (strpos($absolutePath, $teacherDir) === 0) {
        @unlink($absolutePath);
    }
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $action = (string) ($_POST['action'] ?? '');

    if (($oversizedPostMessage = lwGetPostMaxSizeUploadError('admin_teachers')) !== null) {
        $message = $oversizedPostMessage;
        $messageType = 'danger';
    } else {
        try {
            if ($action === 'add' || $action === 'edit') {
                $id = (int) ($_POST['id'] ?? 0);
                $name = trim((string) ($_POST['name'] ?? ''));
                $subject = trim((string) ($_POST['subject'] ?? ''));
                $qualifications = trim((string) ($_POST['qualifications'] ?? ''));
                $bio = trim((string) ($_POST['bio'] ?? ''));
                $experienceYears = max(0, (int) ($_POST['experience_years'] ?? 0));
                $studentsCount = max(0, (int) ($_POST['students_count'] ?? 0));
                $experience = $experienceYears > 0 ? $experienceYears . ' years' : '';
                $status = isset($_POST['status']) ? 'active' : 'inactive';
                $currentTeacher = $action === 'edit' ? lwFetchTeacher($pdo, $id) : null;

                if ($name === '' || $subject === '') {
                    $message = 'Please enter the teacher name and subject.';
                    $messageType = 'danger';
                } elseif ($action === 'edit' && !$currentTeacher) {
                    $message = 'Teacher profile not found.';
                    $messageType = 'danger';
                } else {
                    $imagePath = (string) ($currentTeacher['image'] ?? '');
                    $uploadError = null;
                    $uploadedImage = cmsUploadFile(
                        $_FILES['image'] ?? [],
                        'teachers',
                        ['jpg', 'jpeg', 'png', 'webp'],
                        'teacher',
                        5 * 1024 * 1024,
                        $uploadError,
                        'image'
                    );

                    if ($uploadError !== null) {
                        $message = $uploadError;
                        $messageType = 'danger';
                    } else {
                        if ($uploadedImage !== null) {
                            lwOptimizeUploadedImage($uploadedImage, 900, 900, 82);
                            if ($imagePath !== '' && $imagePath !== $uploadedImage) {
                                lwDeleteTeacherPhotoFile($imagePath);
                            }
                            $imagePath = $uploadedImage;
                        } elseif ($action === 'edit' && !empty($_POST['remove_image']) && $imagePath !== '') {
                            lwDeleteTeacherPhotoFile($imagePath);
                            $imagePath = '';
                        }

                        if ($action === 'add') {
                            $stmt = $pdo->prepare('
                                INSERT INTO teachers (name, subject, experience, qualifications, bio, experience_years, students_count, image, status)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                            ');
                            $stmt->execute([
                                $name,
                                $subject,
                                $experience,
                                $qualifications,
                                $bio,
                                $experienceYears,
                                $studentsCount,
                                $imagePath,
                                $status,
                            ]);
                            $message = 'Teacher added successfully.';
                        } else {
                            $stmt = $pdo->prepare('
                                UPDATE teachers
                                SET name = ?, subject = ?, experience = ?, qualifications = ?, bio = ?, experience_years = ?, students_count = ?, image = ?, status = ?
                                WHERE id = ?
                            ');
                            $stmt->execute([
                                $name,
                                $subject,
                                $experience,
                                $qualifications,
                                $bio,
                                $experienceYears,
                                $studentsCount,
                                $imagePath,
                                $status,
                                $id,
                            ]);
                            $message = 'Teacher updated successfully.';
                        }

                        $messageType = 'success';
                    }
                }
            } elseif ($action === 'delete') {
                $id = (int) ($_POST['id'] ?? 0);
                $teacher = lwFetchTeacher($pdo, $id);
                if ($teacher) {
                    lwDeleteTeacherPhotoFile($teacher['image'] ?? '');
                    $stmt = $pdo->prepare('DELETE FROM teachers WHERE id = ?');
                    $stmt->execute([$id]);
                    $message = 'Teacher deleted successfully.';
                    $messageType = 'success';
                } else {
                    $message = 'Teacher profile not found.';
                    $messageType = 'danger';
                }
            }
        } catch (Throwable $exception) {
            lwReportException($exception, ['area' => 'admin_teachers']);
            $message = 'Something went wrong while saving the teacher profile.';
            $messageType = 'danger';
        }
    }
}

$teachers = getTeachers('all');
$teachersById = [];
foreach ($teachers as $teacher) {
    $teachersById[(int) $teacher['id']] = $teacher;
}
$activeTeachers = array_filter($teachers, static fn($teacher) => ($teacher['status'] ?? '') === 'active');
$placeholderImage = getTeacherPlaceholderImage();

include __DIR__ . '/admin-header.php';
?>
<style>
    .teacher-thumb {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #fff;
        box-shadow: 0 8px 18px rgba(19, 44, 77, 0.12);
        background: #eef4ff;
    }

    .teacher-preview-panel {
        border: 1px dashed #c9d6ea;
        border-radius: 18px;
        padding: 1rem;
        background: #f8fbff;
    }

    .teacher-preview-img {
        width: 132px;
        height: 132px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #fff;
        box-shadow: 0 14px 30px rgba(19, 44, 77, 0.14);
        background: #eef4ff;
    }

    .teacher-modal-footer {
        position: sticky;
        bottom: 0;
        z-index: 2;
        background: #fff;
        border-top: 1px solid #e7ebf4;
    }
</style>

<?php if ($message !== ''): ?>
    <div class="alert alert-<?= htmlspecialchars($messageType) ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card admin-card p-4">
            <small class="text-muted fw-semibold">Total Teachers</small>
            <h2 class="text-primary mb-0"><?= count($teachers) ?></h2>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card admin-card p-4">
            <small class="text-muted fw-semibold">Active Profiles</small>
            <h2 class="text-success mb-0"><?= count($activeTeachers) ?></h2>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card admin-card p-4">
            <small class="text-muted fw-semibold">With Photos</small>
            <h2 class="text-primary mb-0"><?= count(array_filter($teachers, static fn($teacher) => trim((string) ($teacher['image'] ?? '')) !== '')) ?></h2>
        </div>
    </div>
</div>

<div class="card admin-card p-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h5 class="mb-1">Teacher Profiles</h5>
            <p class="text-muted mb-0">Upload, preview, update, or remove teacher profile photos.</p>
        </div>
        <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#teacherModal" onclick="resetForm()">
            <i class="bi bi-plus-circle me-1"></i> Add Teacher
        </button>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Name</th>
                    <th>Subject</th>
                    <th>Experience</th>
                    <th>Students</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($teachers as $teacher): ?>
                    <?php
                    $photo = '../' . getTeacherPhotoUrl($teacher);
                    $status = (string) ($teacher['status'] ?? '');
                    ?>
                    <tr>
                        <td>
                            <img src="<?= htmlspecialchars($photo) ?>" alt="<?= htmlspecialchars($teacher['name']) ?>" class="teacher-thumb" loading="lazy" decoding="async">
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($teacher['name']) ?></strong>
                            <?php if (!empty($teacher['qualifications'])): ?>
                                <div class="small text-muted"><?= htmlspecialchars($teacher['qualifications']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($teacher['subject']) ?></td>
                        <td><?= (int) ($teacher['experience_years'] ?? 0) ?> yrs</td>
                        <td><?= (int) ($teacher['students_count'] ?? 0) ?>+</td>
                        <td>
                            <span class="badge bg-<?= $status === 'active' ? 'success' : 'secondary' ?>">
                                <?= $status === 'active' ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary me-1" type="button" onclick="editTeacher(<?= (int) $teacher['id'] ?>)">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" type="button" onclick="deleteTeacher(<?= (int) $teacher['id'] ?>, <?= htmlspecialchars(json_encode((string) $teacher['name'], JSON_HEX_APOS | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8') ?>)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($teachers)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">No teacher profiles found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="teacherModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add Teacher</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" enctype="multipart/form-data" id="teacherForm">
                <div class="modal-body">
                    <input type="hidden" name="action" id="action" value="add">
                    <input type="hidden" name="id" id="teacherId">

                    <div class="row g-4">
                        <div class="col-lg-4">
                            <div class="teacher-preview-panel text-center">
                                <img src="../<?= htmlspecialchars($placeholderImage) ?>" alt="Teacher photo preview" id="imagePreview" class="teacher-preview-img mb-3">
                                <div id="imagePreviewNote" class="small text-muted">Default placeholder</div>
                                <div class="form-check mt-3 text-start d-none" id="removeImageWrap">
                                    <input class="form-check-input" type="checkbox" id="remove_image" name="remove_image" value="1">
                                    <label class="form-check-label" for="remove_image">Remove current photo</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-8">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Name *</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="subject" class="form-label">Subject *</label>
                                    <input type="text" class="form-control" id="subject" name="subject" required>
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
                                <div class="col-md-6 mb-3">
                                    <label for="experience_years" class="form-label">Experience (Years)</label>
                                    <input type="number" class="form-control" id="experience_years" name="experience_years" min="0" value="0">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="students_count" class="form-label">Students Count</label>
                                    <input type="number" class="form-control" id="students_count" name="students_count" min="0" value="0">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="image" class="form-label">Profile Photo</label>
                                <input type="file" class="form-control" id="image" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                                <div class="form-text">JPG, PNG, or WEBP only. Maximum size 5MB. Images are optimized automatically.</div>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="status" name="status" checked>
                                <label class="form-check-label" for="status">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer teacher-modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="teacherSubmitButton">Save Teacher</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-header">
                <h5 class="modal-title">Delete Teacher</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete <strong id="deleteTeacherName"></strong>? This will also remove the uploaded teacher photo.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" class="d-inline">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="deleteTeacherId">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const teachers = <?= json_encode($teachersById, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
const placeholderImage = <?= json_encode('../' . $placeholderImage, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
let activeEditImage = '';

function teacherPhotoSrc(path) {
    if (!path) {
        return placeholderImage;
    }
    if (/^https?:\/\//i.test(path) || path.startsWith('../')) {
        return path;
    }
    return '../' + path;
}

function setPreview(src, note) {
    document.getElementById('imagePreview').src = src;
    document.getElementById('imagePreviewNote').textContent = note;
}

function resetForm() {
    activeEditImage = '';
    document.getElementById('teacherForm').reset();
    document.getElementById('action').value = 'add';
    document.getElementById('teacherId').value = '';
    document.getElementById('modalTitle').textContent = 'Add Teacher';
    document.getElementById('teacherSubmitButton').textContent = 'Save Teacher';
    document.getElementById('experience_years').value = '0';
    document.getElementById('students_count').value = '0';
    document.getElementById('status').checked = true;
    document.getElementById('remove_image').checked = false;
    document.getElementById('removeImageWrap').classList.add('d-none');
    setPreview(placeholderImage, 'Default placeholder');
}

function editTeacher(id) {
    const teacher = teachers[id];
    if (!teacher) {
        return;
    }

    document.getElementById('teacherForm').reset();
    document.getElementById('action').value = 'edit';
    document.getElementById('teacherId').value = teacher.id || '';
    document.getElementById('modalTitle').textContent = 'Edit Teacher';
    document.getElementById('teacherSubmitButton').textContent = 'Save Changes';
    document.getElementById('name').value = teacher.name || '';
    document.getElementById('subject').value = teacher.subject || '';
    document.getElementById('qualifications').value = teacher.qualifications || '';
    document.getElementById('bio').value = teacher.bio || '';
    document.getElementById('experience_years').value = teacher.experience_years || '0';
    document.getElementById('students_count').value = teacher.students_count || '0';
    document.getElementById('status').checked = teacher.status === 'active';
    document.getElementById('image').value = '';
    document.getElementById('remove_image').checked = false;

    activeEditImage = teacher.image || '';
    const hasImage = activeEditImage.trim() !== '';
    document.getElementById('removeImageWrap').classList.toggle('d-none', !hasImage);
    setPreview(teacherPhotoSrc(activeEditImage), hasImage ? 'Current teacher photo' : 'Default placeholder');

    const modal = new bootstrap.Modal(document.getElementById('teacherModal'));
    modal.show();
}

function deleteTeacher(id, name) {
    document.getElementById('deleteTeacherId').value = id;
    document.getElementById('deleteTeacherName').textContent = name;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

document.getElementById('image').addEventListener('change', function () {
    const file = this.files && this.files[0] ? this.files[0] : null;
    if (!file) {
        setPreview(teacherPhotoSrc(activeEditImage), activeEditImage ? 'Current teacher photo' : 'Default placeholder');
        return;
    }

    const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    if (!allowedTypes.includes(file.type)) {
        this.value = '';
        setPreview(teacherPhotoSrc(activeEditImage), activeEditImage ? 'Current teacher photo' : 'Default placeholder');
        alert('Please choose a JPG, PNG, or WEBP image.');
        return;
    }

    if (file.size > 5 * 1024 * 1024) {
        this.value = '';
        setPreview(teacherPhotoSrc(activeEditImage), activeEditImage ? 'Current teacher photo' : 'Default placeholder');
        alert('Please choose an image smaller than 5MB.');
        return;
    }

    document.getElementById('remove_image').checked = false;
    setPreview(URL.createObjectURL(file), 'New photo preview');
});

document.getElementById('remove_image').addEventListener('change', function () {
    if (this.checked) {
        document.getElementById('image').value = '';
        setPreview(placeholderImage, 'Photo will be removed');
    } else {
        setPreview(teacherPhotoSrc(activeEditImage), activeEditImage ? 'Current teacher photo' : 'Default placeholder');
    }
});
</script>
<?php include __DIR__ . '/admin-footer.php'; ?>
