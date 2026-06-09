<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/site.php';
requireAdminLogin();

$pageTitle = 'Sections';
$pageId = isset($_GET['page_id']) ? (int) $_GET['page_id'] : 0;
$editId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'sort') {
    $order = $_POST['order'] ?? [];
    $stmt = $pdo->prepare('UPDATE page_sections SET sort_order = :sort_order WHERE id = :id');
    foreach ($order as $index => $id) {
        $stmt->execute([
            ':sort_order' => $index + 1,
            ':id' => (int) $id,
        ]);
    }
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;
}

if (isset($_GET['delete'])) {
    $deleteId = (int) $_GET['delete'];
    $stmt = $pdo->prepare('DELETE FROM page_sections WHERE id = :id');
    $stmt->execute([':id' => $deleteId]);
    header('Location: sections.php?page_id=' . $pageId . '&deleted=1');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') !== 'sort') {
    $payload = [
        ':page_id' => (int) ($_POST['page_id'] ?? 0),
        ':section_key' => trim($_POST['section_key'] ?? ''),
        ':section_title' => trim($_POST['section_title'] ?? ''),
        ':section_subtitle' => trim($_POST['section_subtitle'] ?? ''),
        ':section_content' => trim($_POST['section_content'] ?? ''),
        ':section_image' => trim($_POST['section_image'] ?? ''),
        ':section_type' => trim($_POST['section_type'] ?? 'rich_text'),
        ':section_settings' => trim($_POST['section_settings'] ?? ''),
        ':sort_order' => (int) ($_POST['sort_order'] ?? 0),
        ':status' => $_POST['status'] ?? 'inactive',
    ];

    if (!empty($_POST['id'])) {
        $payload[':id'] = (int) $_POST['id'];
        $stmt = $pdo->prepare('
            UPDATE page_sections
            SET page_id = :page_id, section_key = :section_key, section_title = :section_title, section_subtitle = :section_subtitle,
                section_content = :section_content, section_image = :section_image, section_type = :section_type,
                section_settings = :section_settings, sort_order = :sort_order, status = :status
            WHERE id = :id
        ');
        $stmt->execute($payload);
    } else {
        $stmt = $pdo->prepare('
            INSERT INTO page_sections (
                page_id, section_key, section_title, section_subtitle, section_content,
                section_image, section_type, section_settings, sort_order, status
            ) VALUES (
                :page_id, :section_key, :section_title, :section_subtitle, :section_content,
                :section_image, :section_type, :section_settings, :sort_order, :status
            )
        ');
        $stmt->execute($payload);
    }

    header('Location: sections.php?page_id=' . (int) $payload[':page_id'] . '&saved=1');
    exit;
}

$pages = $pdo->query('SELECT id, page_name, page_title FROM pages ORDER BY page_title ASC')->fetchAll(PDO::FETCH_ASSOC);
if ($pageId === 0 && !empty($pages)) {
    $pageId = (int) $pages[0]['id'];
}

$editSection = null;
if ($editId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM page_sections WHERE id = :id');
    $stmt->execute([':id' => $editId]);
    $editSection = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

$sections = [];
if ($pageId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM page_sections WHERE page_id = :page_id ORDER BY sort_order ASC, id ASC');
    $stmt->execute([':page_id' => $pageId]);
    $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

include __DIR__ . '/admin-header.php';
?>
<?php if (!empty($_GET['saved'])): ?><div class="alert alert-success">Section saved successfully.</div><?php endif; ?>
<?php if (!empty($_GET['deleted'])): ?><div class="alert alert-success">Section deleted successfully.</div><?php endif; ?>
<div class="row g-4">
    <div class="col-xl-5">
        <div class="card admin-card p-4">
            <h5 class="mb-4"><?= $editSection ? 'Edit Section' : 'Add Section' ?></h5>
            <form method="post">
                <input type="hidden" name="id" value="<?= htmlspecialchars((string) ($editSection['id'] ?? '')) ?>">
                <div class="mb-3">
                    <label class="form-label">Page</label>
                    <select name="page_id" class="form-select" required>
                        <?php foreach ($pages as $page): ?>
                            <option value="<?= (int) $page['id'] ?>" <?= (int) ($editSection['page_id'] ?? $pageId) === (int) $page['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($page['page_title']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Section Key</label>
                    <input type="text" name="section_key" class="form-control" placeholder="hero" value="<?= htmlspecialchars($editSection['section_key'] ?? '') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Section Type</label>
                    <select name="section_type" class="form-select">
                        <?php foreach (getSectionTypes() as $type => $label): ?>
                            <option value="<?= htmlspecialchars($type) ?>" <?= ($editSection['section_type'] ?? '') === $type ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" name="section_title" class="form-control" value="<?= htmlspecialchars($editSection['section_title'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Subtitle</label>
                    <textarea name="section_subtitle" rows="3" class="form-control"><?= htmlspecialchars($editSection['section_subtitle'] ?? '') ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Content</label>
                    <textarea name="section_content" rows="7" class="form-control"><?= htmlspecialchars($editSection['section_content'] ?? '') ?></textarea>
                    <div class="form-text">Use pipe-separated lines for structured content, for example `Title|Description|bi-icon`.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Image URL</label>
                    <input type="text" name="section_image" class="form-control" value="<?= htmlspecialchars($editSection['section_image'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Section Settings JSON</label>
                    <textarea name="section_settings" rows="4" class="form-control"><?= htmlspecialchars($editSection['section_settings'] ?? '') ?></textarea>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="<?= htmlspecialchars((string) ($editSection['sort_order'] ?? count($sections) + 1)) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active" <?= ($editSection['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= ($editSection['status'] ?? 'active') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Save Section</button>
                </div>
            </form>
        </div>
    </div>
    <div class="col-xl-7">
        <div class="card admin-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3 gap-3">
                <div>
                    <h5 class="mb-1">Section Order</h5>
                    <p class="text-muted mb-0">Drag and drop to reorder active page sections.</p>
                </div>
                <form method="get" class="d-flex gap-2">
                    <select name="page_id" class="form-select" onchange="this.form.submit()">
                        <?php foreach ($pages as $page): ?>
                            <option value="<?= (int) $page['id'] ?>" <?= $pageId === (int) $page['id'] ? 'selected' : '' ?>><?= htmlspecialchars($page['page_title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
            <ul class="list-group list-group-flush section-sortable" data-page-id="<?= $pageId ?>">
                <?php foreach ($sections as $section): ?>
                    <li class="list-group-item d-flex align-items-center justify-content-between gap-3" data-id="<?= (int) $section['id'] ?>">
                        <div class="d-flex align-items-center gap-3">
                            <span class="drag-handle btn btn-light btn-sm"><i class="bi bi-list"></i></span>
                            <div>
                                <strong><?= htmlspecialchars($section['section_title'] ?: $section['section_key']) ?></strong>
                                <div class="text-muted small"><?= htmlspecialchars($section['section_type']) ?> | <?= htmlspecialchars($section['status']) ?></div>
                            </div>
                        </div>
                        <div class="text-end">
                            <a href="sections.php?page_id=<?= $pageId ?>&edit=<?= (int) $section['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                            <a href="sections.php?page_id=<?= $pageId ?>&delete=<?= (int) $section['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this section?');">Delete</a>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.querySelectorAll('.section-sortable').forEach(function (list) {
    Sortable.create(list, {
        animation: 180,
        handle: '.drag-handle',
        onEnd: function () {
            const data = new FormData();
            data.append('action', 'sort');
            Array.from(list.querySelectorAll('li')).forEach(function (item) {
                data.append('order[]', item.dataset.id);
            });
            fetch('sections.php?page_id=<?= $pageId ?>', {
                method: 'POST',
                body: data
            });
        }
    });
});
</script>
<?php include __DIR__ . '/admin-footer.php'; ?>
