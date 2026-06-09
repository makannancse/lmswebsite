<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/site.php';
requireAdminLogin();

$pageTitle = 'Sample Videos';
$editId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'sort') {
    $stmt = $pdo->prepare('UPDATE sample_videos SET sort_order = :sort_order WHERE id = :id');
    foreach (($_POST['order'] ?? []) as $index => $id) {
        $stmt->execute([':sort_order' => $index + 1, ':id' => (int) $id]);
    }
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare('DELETE FROM sample_videos WHERE id = :id');
    $stmt->execute([':id' => (int) $_GET['delete']]);
    header('Location: sample_videos.php?deleted=1');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') !== 'sort') {
    $thumbnail = cmsUploadFile($_FILES['thumbnail'] ?? [], 'thumbnails', ['jpg', 'jpeg', 'png', 'webp'], 'thumb');
    $videoFile = cmsUploadFile($_FILES['video_file'] ?? [], 'videos', ['mp4', 'webm', 'ogg'], 'video');

    $payload = [
        ':title' => trim($_POST['title'] ?? ''),
        ':description' => trim($_POST['description'] ?? ''),
        ':video_url' => trim($_POST['video_url'] ?? ''),
        ':sort_order' => (int) ($_POST['sort_order'] ?? 0),
        ':status' => $_POST['status'] ?? 'inactive',
    ];

    if (!empty($_POST['id'])) {
        $currentStmt = $pdo->prepare('SELECT thumbnail, video_file FROM sample_videos WHERE id = :id');
        $currentStmt->execute([':id' => (int) $_POST['id']]);
        $current = $currentStmt->fetch(PDO::FETCH_ASSOC) ?: ['thumbnail' => '', 'video_file' => ''];
        $payload[':thumbnail'] = $thumbnail ?: $current['thumbnail'];
        $payload[':video_file'] = $videoFile ?: $current['video_file'];
        $payload[':id'] = (int) $_POST['id'];
        $stmt = $pdo->prepare('
            UPDATE sample_videos
            SET title = :title, description = :description, thumbnail = :thumbnail, video_file = :video_file,
                video_url = :video_url, sort_order = :sort_order, status = :status
            WHERE id = :id
        ');
        $stmt->execute($payload);
    } else {
        $payload[':thumbnail'] = $thumbnail ?: '';
        $payload[':video_file'] = $videoFile ?: '';
        $stmt = $pdo->prepare('
            INSERT INTO sample_videos (title, description, thumbnail, video_file, video_url, sort_order, status)
            VALUES (:title, :description, :thumbnail, :video_file, :video_url, :sort_order, :status)
        ');
        $stmt->execute($payload);
    }

    header('Location: sample_videos.php?saved=1');
    exit;
}

$editVideo = null;
if ($editId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM sample_videos WHERE id = :id');
    $stmt->execute([':id' => $editId]);
    $editVideo = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

$videos = getSampleVideos('all');

include __DIR__ . '/admin-header.php';
?>
<?php if (!empty($_GET['saved'])): ?><div class="alert alert-success">Video saved successfully.</div><?php endif; ?>
<?php if (!empty($_GET['deleted'])): ?><div class="alert alert-success">Video deleted successfully.</div><?php endif; ?>
<div class="row g-4">
    <div class="col-xl-5">
        <div class="card admin-card p-4">
            <h5 class="mb-4"><?= $editVideo ? 'Edit Video' : 'Add Video' ?></h5>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= htmlspecialchars((string) ($editVideo['id'] ?? '')) ?>">
                <div class="mb-3">
                    <label class="form-label">Video Title</label>
                    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($editVideo['title'] ?? '') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="3" class="form-control"><?= htmlspecialchars($editVideo['description'] ?? '') ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Thumbnail</label>
                    <input type="file" name="thumbnail" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">MP4 / Web Video File</label>
                    <input type="file" name="video_file" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">External Video URL</label>
                    <input type="text" name="video_url" class="form-control" value="<?= htmlspecialchars($editVideo['video_url'] ?? '') ?>">
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="<?= htmlspecialchars((string) ($editVideo['sort_order'] ?? count($videos) + 1)) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active" <?= ($editVideo['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= ($editVideo['status'] ?? 'active') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Save Video</button>
                </div>
            </form>
        </div>
    </div>
    <div class="col-xl-7">
        <div class="card admin-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-1">Video Order</h5>
                    <p class="text-muted mb-0">Upload files or use URLs, then drag to reorder.</p>
                </div>
            </div>
            <ul class="list-group list-group-flush video-sortable">
                <?php foreach ($videos as $video): ?>
                    <li class="list-group-item d-flex align-items-center justify-content-between gap-3" data-id="<?= (int) $video['id'] ?>">
                        <div class="d-flex align-items-center gap-3">
                            <span class="drag-handle btn btn-light btn-sm"><i class="bi bi-list"></i></span>
                            <img src="<?= htmlspecialchars($video['thumbnail'] ? '../' . $video['thumbnail'] : 'https://images.unsplash.com/photo-1481627834876-b7833e8f5570?auto=format&fit=crop&w=200&q=80') ?>" alt="" width="72" height="48" class="rounded-3 object-fit-cover">
                            <div>
                                <strong><?= htmlspecialchars($video['title']) ?></strong>
                                <div class="text-muted small"><?= htmlspecialchars($video['status']) ?> | <?= $video['video_file'] ? 'Uploaded file' : 'External URL' ?></div>
                            </div>
                        </div>
                        <div class="text-end">
                            <a href="sample_videos.php?edit=<?= (int) $video['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                            <a href="sample_videos.php?delete=<?= (int) $video['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this video?');">Delete</a>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
const videoList = document.querySelector('.video-sortable');
if (videoList) {
    Sortable.create(videoList, {
        animation: 180,
        handle: '.drag-handle',
        onEnd: function () {
            const data = new FormData();
            data.append('action', 'sort');
            Array.from(videoList.querySelectorAll('li')).forEach(function (item) {
                data.append('order[]', item.dataset.id);
            });
            fetch('sample_videos.php', {
                method: 'POST',
                body: data
            });
        }
    });
}
</script>
<?php include __DIR__ . '/admin-footer.php'; ?>
