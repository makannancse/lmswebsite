<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/site.php';
requireAdminLogin();

$pageTitle = 'Testimonials';
$editId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
$message = '';
$messageType = 'success';

function lwFetchTestimonial(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM testimonials WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    $testimonial = $stmt->fetch(PDO::FETCH_ASSOC);
    return $testimonial ?: null;
}

function lwDeleteTestimonialImage(?string $path): void
{
    $relativePath = ltrim(str_replace('\\', '/', trim((string) $path)), '/');
    if ($relativePath === '' || strpos($relativePath, 'uploads/testimonials/') !== 0) {
        return;
    }

    $projectRoot = realpath(dirname(__DIR__));
    if ($projectRoot === false) {
        return;
    }

    $absolutePath = realpath($projectRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath));
    $testimonialDir = realpath($projectRoot . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'testimonials');
    if ($absolutePath === false || $testimonialDir === false || !is_file($absolutePath)) {
        return;
    }

    $testimonialDir = rtrim($testimonialDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    if (strpos($absolutePath, $testimonialDir) === 0) {
        @unlink($absolutePath);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'sort') {
    $stmt = $pdo->prepare('UPDATE testimonials SET sort_order = :sort_order WHERE id = :id');
    foreach (($_POST['order'] ?? []) as $index => $id) {
        $stmt->execute([':sort_order' => $index + 1, ':id' => (int) $id]);
    }
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;
}

if (isset($_GET['delete'])) {
    $deleteId = (int) $_GET['delete'];
    $testimonial = lwFetchTestimonial($pdo, $deleteId);
    if ($testimonial) {
        $stmt = $pdo->prepare('DELETE FROM testimonials WHERE id = :id');
        $stmt->execute([':id' => $deleteId]);
        lwDeleteTestimonialImage($testimonial['image'] ?? '');
        header('Location: testimonials.php?deleted=1');
        exit;
    }

    header('Location: testimonials.php?delete_failed=1');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') !== 'sort' && ($oversizedPostMessage = lwGetPostMaxSizeUploadError('admin_testimonials')) !== null) {
    $message = $oversizedPostMessage;
    $messageType = 'danger';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') !== 'sort') {
    $id = (int) ($_POST['id'] ?? 0);
    $current = $id > 0 ? lwFetchTestimonial($pdo, $id) : null;
    $parentName = trim((string) ($_POST['parent_name'] ?? ''));
    $studentName = trim((string) ($_POST['student_name'] ?? ''));
    $reviewText = trim((string) ($_POST['review_text'] ?? ''));
    $rating = max(1, min(5, (int) ($_POST['rating'] ?? 5)));
    $sortOrder = max(0, (int) ($_POST['sort_order'] ?? 0));
    $status = in_array($_POST['status'] ?? 'inactive', ['active', 'inactive'], true) ? $_POST['status'] : 'inactive';

    $imageError = null;
    $uploadedImage = cmsUploadFile(
        $_FILES['image'] ?? [],
        'testimonials',
        ['jpg', 'jpeg', 'png', 'webp'],
        'testimonial',
        5 * 1024 * 1024,
        $imageError,
        'image'
    );

    if ($parentName === '' || $reviewText === '') {
        $message = 'Please enter the parent name and testimonial content.';
        $messageType = 'danger';
    } elseif ($id > 0 && !$current) {
        $message = 'Testimonial not found.';
        $messageType = 'danger';
    } elseif ($imageError !== null) {
        $message = $imageError;
        $messageType = 'danger';
    } else {
        $imagePath = (string) ($current['image'] ?? '');
        if (!empty($_POST['remove_image']) && $imagePath !== '') {
            lwDeleteTestimonialImage($imagePath);
            $imagePath = '';
        }
        if ($uploadedImage !== null) {
            lwOptimizeUploadedImage($uploadedImage, 900, 900, 82);
            if ($imagePath !== '' && $imagePath !== $uploadedImage) {
                lwDeleteTestimonialImage($imagePath);
            }
            $imagePath = $uploadedImage;
        }

        if ($id > 0) {
            $stmt = $pdo->prepare('
                UPDATE testimonials
                SET parent_name = :parent_name, student_name = :student_name, review_text = :review_text,
                    rating = :rating, image = :image, sort_order = :sort_order, status = :status
                WHERE id = :id
            ');
            $stmt->execute([
                ':parent_name' => $parentName,
                ':student_name' => $studentName !== '' ? $studentName : null,
                ':review_text' => $reviewText,
                ':rating' => $rating,
                ':image' => $imagePath,
                ':sort_order' => $sortOrder,
                ':status' => $status,
                ':id' => $id,
            ]);
            header('Location: testimonials.php?updated=1');
            exit;
        }

        $stmt = $pdo->prepare('
            INSERT INTO testimonials (parent_name, student_name, review_text, rating, image, sort_order, status)
            VALUES (:parent_name, :student_name, :review_text, :rating, :image, :sort_order, :status)
        ');
        $stmt->execute([
            ':parent_name' => $parentName,
            ':student_name' => $studentName !== '' ? $studentName : null,
            ':review_text' => $reviewText,
            ':rating' => $rating,
            ':image' => $imagePath,
            ':sort_order' => $sortOrder,
            ':status' => $status,
        ]);
        header('Location: testimonials.php?saved=1');
        exit;
    }
}

$editTestimonial = null;
if ($editId > 0) {
    $editTestimonial = lwFetchTestimonial($pdo, $editId);
}

$testimonials = getTestimonials('all');

include __DIR__ . '/admin-header.php';
?>
<style>
    .testimonial-admin-thumb {
        width: 74px;
        height: 74px;
        border-radius: 50%;
        object-fit: cover;
        background: #eef4ff;
        border: 3px solid #fff;
        box-shadow: 0 10px 24px rgba(19, 44, 77, 0.12);
    }
</style>

<?php if (!empty($_GET['saved'])): ?><div class="alert alert-success">Testimonial saved successfully.</div><?php endif; ?>
<?php if (!empty($_GET['updated'])): ?><div class="alert alert-success">Testimonial updated successfully.</div><?php endif; ?>
<?php if (!empty($_GET['deleted'])): ?><div class="alert alert-success">Testimonial deleted successfully.</div><?php endif; ?>
<?php if (!empty($_GET['delete_failed'])): ?><div class="alert alert-danger">Testimonial could not be deleted.</div><?php endif; ?>
<?php if ($message !== ''): ?><div class="alert alert-<?= htmlspecialchars($messageType) ?>"><?= htmlspecialchars($message) ?></div><?php endif; ?>

<div class="row g-4">
    <div class="col-xl-5">
        <div class="card admin-card p-4">
            <h5 class="mb-4"><?= $editTestimonial ? 'Edit Testimonial' : 'Add Testimonial' ?></h5>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= htmlspecialchars((string) ($editTestimonial['id'] ?? '')) ?>">

                <div class="mb-3">
                    <label class="form-label">Parent Name</label>
                    <input type="text" name="parent_name" class="form-control" value="<?= htmlspecialchars($editTestimonial['parent_name'] ?? ($_POST['parent_name'] ?? '')) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Student Name</label>
                    <input type="text" name="student_name" class="form-control" value="<?= htmlspecialchars($editTestimonial['student_name'] ?? ($_POST['student_name'] ?? '')) ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Review Text</label>
                    <textarea name="review_text" rows="5" class="form-control" required><?= htmlspecialchars($editTestimonial['review_text'] ?? ($_POST['review_text'] ?? '')) ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Rating</label>
                    <?php $selectedRating = (int) ($editTestimonial['rating'] ?? ($_POST['rating'] ?? 5)); ?>
                    <select name="rating" class="form-select">
                        <?php for ($rating = 5; $rating >= 1; $rating--): ?>
                            <option value="<?= $rating ?>" <?= $selectedRating === $rating ? 'selected' : '' ?>><?= $rating ?> star<?= $rating === 1 ? '' : 's' ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Parent Image</label>
                    <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                    <?php if (!empty($editTestimonial['image'])): ?>
                        <div class="mt-3 d-flex align-items-center gap-3">
                            <img src="../<?= htmlspecialchars($editTestimonial['image']) ?>" alt="Current parent image" class="testimonial-admin-thumb">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remove_image" id="removeImage" value="1">
                                <label class="form-check-label" for="removeImage">Remove current image</label>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="<?= htmlspecialchars((string) ($editTestimonial['sort_order'] ?? ($_POST['sort_order'] ?? count($testimonials) + 1))) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <?php $selectedStatus = $editTestimonial['status'] ?? ($_POST['status'] ?? 'active'); ?>
                        <select name="status" class="form-select">
                            <option value="active" <?= $selectedStatus === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= $selectedStatus === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><?= $editTestimonial ? 'Update Testimonial' : 'Save Testimonial' ?></button>
                    <?php if ($editTestimonial): ?>
                        <a href="testimonials.php" class="btn btn-outline-secondary">Cancel Edit</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <div class="col-xl-7">
        <div class="card admin-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-1">Testimonial Order</h5>
                    <p class="text-muted mb-0">Drag to reorder the Parents Trust Us carousel.</p>
                </div>
            </div>

            <ul class="list-group list-group-flush testimonial-sortable">
                <?php foreach ($testimonials as $testimonial): ?>
                    <li class="list-group-item d-flex align-items-center justify-content-between gap-3" data-id="<?= (int) $testimonial['id'] ?>">
                        <div class="d-flex align-items-center gap-3">
                            <span class="drag-handle btn btn-light btn-sm"><i class="bi bi-list"></i></span>
                            <img src="../<?= htmlspecialchars(getTestimonialImageUrl($testimonial)) ?>" alt="<?= htmlspecialchars($testimonial['parent_name']) ?>" class="testimonial-admin-thumb">
                            <div>
                                <strong><?= htmlspecialchars($testimonial['parent_name']) ?></strong>
                                <?php if (!empty($testimonial['student_name'])): ?>
                                    <div class="small text-muted">Parent of <?= htmlspecialchars($testimonial['student_name']) ?></div>
                                <?php endif; ?>
                                <div class="small text-muted"><?= htmlspecialchars($testimonial['status']) ?> | <?= (int) $testimonial['rating'] ?> stars</div>
                            </div>
                        </div>
                        <div class="text-end">
                            <a href="testimonials.php?edit=<?= (int) $testimonial['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                            <a href="testimonials.php?delete=<?= (int) $testimonial['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this testimonial?');">Delete</a>
                        </div>
                    </li>
                <?php endforeach; ?>
                <?php if (empty($testimonials)): ?>
                    <li class="list-group-item text-center text-muted py-5">No testimonials found.</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
const testimonialList = document.querySelector('.testimonial-sortable');
if (testimonialList && window.Sortable) {
    Sortable.create(testimonialList, {
        animation: 180,
        handle: '.drag-handle',
        onEnd: function () {
            const data = new FormData();
            data.append('action', 'sort');
            Array.from(testimonialList.querySelectorAll('li')).forEach(function (item) {
                data.append('order[]', item.dataset.id);
            });
            fetch('testimonials.php', {
                method: 'POST',
                body: data
            });
        }
    });
}
</script>
<?php include __DIR__ . '/admin-footer.php'; ?>
