<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/site.php';
requireAdminLogin();

$pageTitle = 'Sample Videos';
$editId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
$message = '';
$messageType = 'success';

function lwFetchSampleVideo(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM sample_videos WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    $video = $stmt->fetch(PDO::FETCH_ASSOC);
    return $video ?: null;
}

function lwDeleteUploadedMediaFile(?string $path, array $allowedFolders): void
{
    $relativePath = ltrim(str_replace('\\', '/', trim((string) $path)), '/');
    if ($relativePath === '' || preg_match('/^https?:\/\//i', $relativePath)) {
        return;
    }

    $allowed = false;
    foreach ($allowedFolders as $folder) {
        if (strpos($relativePath, 'uploads/' . trim($folder, '/') . '/') === 0) {
            $allowed = true;
            break;
        }
    }

    if (!$allowed) {
        return;
    }

    $projectRoot = realpath(dirname(__DIR__));
    if ($projectRoot === false) {
        return;
    }

    $absolutePath = realpath($projectRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath));
    if ($absolutePath === false || !is_file($absolutePath)) {
        return;
    }

    $projectRoot = rtrim($projectRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    if (strpos($absolutePath, $projectRoot) === 0) {
        @unlink($absolutePath);
    }
}

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
    $deleteId = (int) $_GET['delete'];
    $deleted = false;
    $video = null;

    try {
        $pdo->beginTransaction();
        $video = lwFetchSampleVideo($pdo, $deleteId);
        if ($video) {
            $stmt = $pdo->prepare('DELETE FROM sample_videos WHERE id = :id');
            $stmt->execute([':id' => $deleteId]);
            $deleted = $stmt->rowCount() === 1;
        }

        if ($deleted) {
            $pdo->commit();
            lwDeleteUploadedMediaFile($video['thumbnail'] ?? '', ['thumbnails']);
            lwDeleteUploadedMediaFile($video['video_file'] ?? '', ['videos']);
        } else {
            $pdo->rollBack();
        }
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        lwReportException($exception, ['area' => 'admin_sample_videos_delete', 'id' => $deleteId]);
    }

    header('Location: sample_videos.php?' . ($deleted ? 'deleted=1' : 'delete_failed=1'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') !== 'sort' && ($oversizedPostMessage = lwGetPostMaxSizeUploadError('admin_sample_videos')) !== null) {
    $message = $oversizedPostMessage;
    $messageType = 'danger';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') !== 'sort') {
    $id = (int) ($_POST['id'] ?? 0);
    $current = $id > 0 ? lwFetchSampleVideo($pdo, $id) : null;
    $title = trim((string) ($_POST['title'] ?? ''));
    $description = trim((string) ($_POST['description'] ?? ''));
    $videoUrl = trim((string) ($_POST['video_url'] ?? ''));
    $sortOrder = max(0, (int) ($_POST['sort_order'] ?? 0));
    $status = in_array($_POST['status'] ?? 'inactive', ['active', 'inactive'], true) ? $_POST['status'] : 'inactive';
    $source = in_array($_POST['source'] ?? 'url', ['url', 'file'], true) ? $_POST['source'] : 'url';

    $thumbnailError = null;
    $videoFileError = null;
    $thumbnail = cmsUploadFile($_FILES['thumbnail'] ?? [], 'thumbnails', ['jpg', 'jpeg', 'png', 'webp'], 'thumb', 5 * 1024 * 1024, $thumbnailError, 'thumbnail');
    $videoFile = cmsUploadFile($_FILES['video_file'] ?? [], 'videos', ['mp4', 'webm', 'ogg'], 'video', 50 * 1024 * 1024, $videoFileError, 'video_file');

    if ($title === '') {
        $message = 'Please enter a video title.';
        $messageType = 'danger';
    } elseif ($id > 0 && !$current) {
        $message = 'Video not found.';
        $messageType = 'danger';
    } elseif ($thumbnailError !== null) {
        $message = $thumbnailError;
        $messageType = 'danger';
    } elseif ($videoFileError !== null) {
        $message = $videoFileError;
        $messageType = 'danger';
    } elseif ($videoUrl !== '' && filter_var($videoUrl, FILTER_VALIDATE_URL) === false) {
        $message = 'Please enter a valid external video URL.';
        $messageType = 'danger';
    } else {
        $thumbnailPath = (string) ($current['thumbnail'] ?? '');
        if (!empty($_POST['remove_thumbnail']) && $thumbnailPath !== '') {
            lwDeleteUploadedMediaFile($thumbnailPath, ['thumbnails']);
            $thumbnailPath = '';
        }
        if ($thumbnail !== null) {
            if ($thumbnailPath !== '' && $thumbnailPath !== $thumbnail) {
                lwDeleteUploadedMediaFile($thumbnailPath, ['thumbnails']);
            }
            $thumbnailPath = $thumbnail;
        }

        $videoFilePath = (string) ($current['video_file'] ?? '');
        if (!empty($_POST['remove_video_file']) && $videoFilePath !== '') {
            lwDeleteUploadedMediaFile($videoFilePath, ['videos']);
            $videoFilePath = '';
        }
        if ($videoFile !== null) {
            if ($videoFilePath !== '' && $videoFilePath !== $videoFile) {
                lwDeleteUploadedMediaFile($videoFilePath, ['videos']);
            }
            $videoFilePath = $videoFile;
            $source = 'file';
        }

        if ($source === 'url') {
            if ($videoUrl === '') {
                $message = 'Please enter an external video URL or switch to uploaded video file.';
                $messageType = 'danger';
            } else {
                if ($videoFilePath !== '') {
                    lwDeleteUploadedMediaFile($videoFilePath, ['videos']);
                }
                $videoFilePath = '';
            }
        } elseif ($source === 'file' && $videoFilePath === '' && $videoUrl === '') {
            $message = 'Please upload a video file or enter an external video URL.';
            $messageType = 'danger';
        }

        if ($messageType !== 'danger') {
            if ($id > 0) {
                $stmt = $pdo->prepare('
                    UPDATE sample_videos
                    SET title = :title, description = :description, thumbnail = :thumbnail, video_file = :video_file,
                        video_url = :video_url, sort_order = :sort_order, status = :status
                    WHERE id = :id
                ');
                $stmt->execute([
                    ':title' => $title,
                    ':description' => $description,
                    ':thumbnail' => $thumbnailPath,
                    ':video_file' => $videoFilePath,
                    ':video_url' => $videoUrl,
                    ':sort_order' => $sortOrder,
                    ':status' => $status,
                    ':id' => $id,
                ]);
                header('Location: sample_videos.php?updated=1');
                exit;
            }

            $stmt = $pdo->prepare('
                INSERT INTO sample_videos (title, description, thumbnail, video_file, video_url, sort_order, status)
                VALUES (:title, :description, :thumbnail, :video_file, :video_url, :sort_order, :status)
            ');
            $stmt->execute([
                ':title' => $title,
                ':description' => $description,
                ':thumbnail' => $thumbnailPath,
                ':video_file' => $videoFilePath,
                ':video_url' => $videoUrl,
                ':sort_order' => $sortOrder,
                ':status' => $status,
            ]);
            header('Location: sample_videos.php?saved=1');
            exit;
        }
    }
}

$editVideo = null;
if ($editId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM sample_videos WHERE id = :id');
    $stmt->execute([':id' => $editId]);
    $editVideo = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

$videos = getSampleVideos('all');
$editSource = !empty($editVideo['video_file']) ? 'file' : 'url';
$selectedSource = in_array($_POST['source'] ?? $editSource, ['url', 'file'], true) ? ($_POST['source'] ?? $editSource) : 'url';

include __DIR__ . '/admin-header.php';
?>
<?php if (!empty($_GET['saved'])): ?><div class="alert alert-success">Video saved successfully.</div><?php endif; ?>
<?php if (!empty($_GET['updated'])): ?><div class="alert alert-success">Video updated successfully.</div><?php endif; ?>
<?php if (!empty($_GET['deleted'])): ?><div class="alert alert-success">Video deleted successfully.</div><?php endif; ?>
<?php if (!empty($_GET['delete_failed'])): ?><div class="alert alert-danger">Video could not be deleted. It may have already been removed.</div><?php endif; ?>
<?php if ($message !== ''): ?><div class="alert alert-<?= htmlspecialchars($messageType) ?>"><?= htmlspecialchars($message) ?></div><?php endif; ?>
<div class="row g-4">
    <div class="col-xl-5">
        <div class="card admin-card p-4">
            <h5 class="mb-4"><?= $editVideo ? 'Edit Video' : 'Add Video' ?></h5>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= htmlspecialchars((string) ($editVideo['id'] ?? '')) ?>">
                <div class="mb-3">
                    <label class="form-label">Video Title</label>
                    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($editVideo['title'] ?? ($_POST['title'] ?? '')) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="3" class="form-control"><?= htmlspecialchars($editVideo['description'] ?? ($_POST['description'] ?? '')) ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Video Source</label>
                    <div class="d-flex flex-wrap gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="source" id="sourceUrl" value="url" <?= $selectedSource === 'url' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="sourceUrl">External URL</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="source" id="sourceFile" value="file" <?= $selectedSource === 'file' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="sourceFile">Uploaded file</label>
                        </div>
                    </div>
                    <div class="form-text">Choose External URL when updating YouTube/Vimeo links. This clears any old uploaded file so the new link displays immediately.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">External Video URL</label>
                    <input type="url" name="video_url" class="form-control" placeholder="https://www.youtube.com/watch?v=..." value="<?= htmlspecialchars($editVideo['video_url'] ?? ($_POST['video_url'] ?? '')) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">MP4 / Web Video File</label>
                    <input type="file" name="video_file" class="form-control" accept=".mp4,.webm,.ogg,video/mp4,video/webm,video/ogg">
                    <?php if (!empty($editVideo['video_file'])): ?>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="remove_video_file" id="removeVideoFile" value="1">
                            <label class="form-check-label" for="removeVideoFile">Remove current uploaded video file</label>
                        </div>
                        <div class="form-text">Current file: <?= htmlspecialchars($editVideo['video_file']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <label class="form-label">Thumbnail</label>
                    <input type="file" name="thumbnail" class="form-control" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                    <?php if (!empty($editVideo['thumbnail'])): ?>
                        <?php $currentThumbnail = preg_match('/^https?:\/\//i', $editVideo['thumbnail']) ? $editVideo['thumbnail'] : '../' . $editVideo['thumbnail']; ?>
                        <div class="mt-3">
                            <img src="<?= htmlspecialchars($currentThumbnail) ?>" alt="Current thumbnail" class="img-fluid rounded-3 object-fit-cover" style="max-height: 140px;">
                        </div>
                        <?php if (!preg_match('/^https?:\/\//i', $editVideo['thumbnail'])): ?>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="remove_thumbnail" id="removeThumbnail" value="1">
                                <label class="form-check-label" for="removeThumbnail">Remove current thumbnail</label>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="<?= htmlspecialchars((string) ($editVideo['sort_order'] ?? ($_POST['sort_order'] ?? count($videos) + 1))) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <?php $selectedStatus = $editVideo['status'] ?? ($_POST['status'] ?? 'active'); ?>
                            <option value="active" <?= $selectedStatus === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= $selectedStatus === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><?= $editVideo ? 'Update Video' : 'Save Video' ?></button>
                    <?php if ($editVideo): ?>
                        <a href="sample_videos.php" class="btn btn-outline-secondary">Cancel Edit</a>
                    <?php endif; ?>
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
                    <?php
			$thumbnailSrc = $video['thumbnail'];
                    ?>
                    <li class="list-group-item d-flex align-items-center justify-content-between gap-3" data-id="<?= (int) $video['id'] ?>">
                        <div class="d-flex align-items-center gap-3">
                            <span class="drag-handle btn btn-light btn-sm"><i class="bi bi-list"></i></span>
                            <img src="<?= htmlspecialchars($thumbnailSrc) ?>" alt="" width="72" height="48" class="rounded-3 object-fit-cover">
                            <div>
                                <strong><?= htmlspecialchars($video['title']) ?></strong>
                                <div class="text-muted small"><?= htmlspecialchars($video['status']) ?> | <?= $video['video_file'] ? 'Uploaded file' : 'External URL' ?></div>
                                <?php if (!empty($video['video_url'])): ?>
                                    <div class="small text-truncate" style="max-width: 420px;"><?= htmlspecialchars($video['video_url']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="text-end">
                            <a href="sample_videos.php?edit=<?= (int) $video['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                            <a href="sample_videos.php?delete=<?= (int) $video['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this video?');">Delete</a>
                        </div>
                    </li>
                <?php endforeach; ?>
                <?php if (empty($videos)): ?>
                    <li class="list-group-item text-center text-muted py-5">No videos found.</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
const videoList = document.querySelector('.video-sortable');
if (videoList && window.Sortable) {
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
