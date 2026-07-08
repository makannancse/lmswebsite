<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/site.php';
requireAdminLogin();

$pageTitle = 'Pages';
$editId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;

if (isset($_GET['delete'])) {
    $deleteId = (int) $_GET['delete'];
    $stmt = $pdo->prepare('DELETE FROM pages WHERE id = :id');
    $stmt->execute([':id' => $deleteId]);
    header('Location: pages.php?deleted=1');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payload = [
        ':page_name' => trim($_POST['page_name'] ?? ''),
        ':page_title' => trim($_POST['page_title'] ?? ''),
        ':meta_title' => trim($_POST['meta_title'] ?? ''),
        ':meta_description' => trim($_POST['meta_description'] ?? ''),
        ':og_image' => trim($_POST['og_image'] ?? ''),
        ':status' => $_POST['status'] ?? 'inactive',
    ];

    if (!empty($_POST['id'])) {
        $payload[':id'] = (int) $_POST['id'];
        $stmt = $pdo->prepare('
            UPDATE pages
            SET page_name = :page_name, page_title = :page_title, meta_title = :meta_title,
                meta_description = :meta_description, og_image = :og_image, status = :status
            WHERE id = :id
        ');
        $stmt->execute($payload);
    } else {
        $stmt = $pdo->prepare('
            INSERT INTO pages (page_name, page_title, meta_title, meta_description, og_image, status)
            VALUES (:page_name, :page_title, :meta_title, :meta_description, :og_image, :status)
        ');
        $stmt->execute($payload);
    }

    header('Location: pages.php?saved=1');
    exit;
}

$editPage = null;
if ($editId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM pages WHERE id = :id');
    $stmt->execute([':id' => $editId]);
    $editPage = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

$pages = $pdo->query('SELECT * FROM pages ORDER BY created_at ASC, id ASC')->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/admin-header.php';
?>
<?php if (!empty($_GET['saved'])): ?><div class="alert alert-success">Page saved successfully.</div><?php endif; ?>
<?php if (!empty($_GET['deleted'])): ?><div class="alert alert-success">Page deleted successfully.</div><?php endif; ?>
<div class="row g-4">
    <div class="col-xl-5">
        <div class="card admin-card p-4">
            <h5 class="mb-4"><?= $editPage ? 'Edit Page' : 'Add Page' ?></h5>
            <form method="post">
                <input type="hidden" name="id" value="<?= htmlspecialchars((string) ($editPage['id'] ?? '')) ?>">
                <div class="mb-3">
                    <label class="form-label">Page Slug</label>
                    <input type="text" name="page_name" class="form-control" placeholder="about" value="<?= htmlspecialchars($editPage['page_name'] ?? '') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Page Title</label>
                    <input type="text" name="page_title" class="form-control" value="<?= htmlspecialchars($editPage['page_title'] ?? '') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Meta Title</label>
                    <input type="text" name="meta_title" class="form-control" value="<?= htmlspecialchars($editPage['meta_title'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Meta Description</label>
                    <textarea name="meta_description" rows="3" class="form-control"><?= htmlspecialchars($editPage['meta_description'] ?? '') ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">OG Image URL</label>
                    <input type="text" name="og_image" class="form-control" value="<?= htmlspecialchars($editPage['og_image'] ?? '') ?>">
                </div>
                <div class="mb-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" <?= ($editPage['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= ($editPage['status'] ?? 'active') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Save Page</button>
            </form>
        </div>
    </div>
    <div class="col-xl-7">
        <div class="card admin-card p-4">
            <h5 class="mb-3">All Pages</h5>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Page</th>
                            <th>Slug</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pages as $page): ?>
                            <tr>
                                <td><?= htmlspecialchars($page['page_title']) ?></td>
                                <td><?= htmlspecialchars($page['page_name']) ?></td>
                                <td><span class="badge <?= $page['status'] === 'active' ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= htmlspecialchars($page['status']) ?></span></td>
                                <td class="text-end">
                                    <a href="pages.php?edit=<?= (int) $page['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <a href="sections.php?page_id=<?= (int) $page['id'] ?>" class="btn btn-sm btn-outline-secondary">Sections</a>
                                    <a href="pages.php?delete=<?= (int) $page['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this page and all its sections?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/admin-footer.php'; ?>
