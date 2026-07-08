<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/site.php';
requireAdminLogin();

$pageTitle = 'Menus';
$editId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'sort') {
    $stmt = $pdo->prepare('UPDATE menus SET sort_order = :sort_order WHERE id = :id');
    foreach (($_POST['order'] ?? []) as $index => $id) {
        $stmt->execute([':sort_order' => $index + 1, ':id' => (int) $id]);
    }
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare('DELETE FROM menus WHERE id = :id');
    $stmt->execute([':id' => (int) $_GET['delete']]);
    header('Location: menus.php?deleted=1');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') !== 'sort') {
    $payload = [
        ':menu_name' => trim($_POST['menu_name'] ?? ''),
        ':menu_link' => trim($_POST['menu_link'] ?? ''),
        ':sort_order' => (int) ($_POST['sort_order'] ?? 0),
        ':status' => $_POST['status'] ?? 'inactive',
    ];

    if (!empty($_POST['id'])) {
        $payload[':id'] = (int) $_POST['id'];
        $stmt = $pdo->prepare('UPDATE menus SET menu_name = :menu_name, menu_link = :menu_link, sort_order = :sort_order, status = :status WHERE id = :id');
        $stmt->execute($payload);
    } else {
        $stmt = $pdo->prepare('INSERT INTO menus (menu_name, menu_link, sort_order, status) VALUES (:menu_name, :menu_link, :sort_order, :status)');
        $stmt->execute($payload);
    }

    header('Location: menus.php?saved=1');
    exit;
}

$editMenu = null;
if ($editId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM menus WHERE id = :id');
    $stmt->execute([':id' => $editId]);
    $editMenu = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

$menus = getMenus('all');

include __DIR__ . '/admin-header.php';
?>
<?php if (!empty($_GET['saved'])): ?><div class="alert alert-success">Menu saved successfully.</div><?php endif; ?>
<?php if (!empty($_GET['deleted'])): ?><div class="alert alert-success">Menu deleted successfully.</div><?php endif; ?>
<div class="row g-4">
    <div class="col-xl-4">
        <div class="card admin-card p-4">
            <h5 class="mb-4"><?= $editMenu ? 'Edit Menu Item' : 'Add Menu Item' ?></h5>
            <form method="post">
                <input type="hidden" name="id" value="<?= htmlspecialchars((string) ($editMenu['id'] ?? '')) ?>">
                <div class="mb-3">
                    <label class="form-label">Menu Name</label>
                    <input type="text" name="menu_name" class="form-control" value="<?= htmlspecialchars($editMenu['menu_name'] ?? '') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Menu Link</label>
                    <input type="text" name="menu_link" class="form-control" placeholder="about.php" value="<?= htmlspecialchars($editMenu['menu_link'] ?? '') ?>" required>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="<?= htmlspecialchars((string) ($editMenu['sort_order'] ?? count($menus) + 1)) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active" <?= ($editMenu['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= ($editMenu['status'] ?? 'active') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Save Menu Item</button>
                </div>
            </form>
        </div>
    </div>
    <div class="col-xl-8">
        <div class="card admin-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-1">Menu Order</h5>
                    <p class="text-muted mb-0">Drag and drop to reorder the navigation.</p>
                </div>
            </div>
            <ul class="list-group list-group-flush menu-sortable">
                <?php foreach ($menus as $menu): ?>
                    <li class="list-group-item d-flex align-items-center justify-content-between gap-3" data-id="<?= (int) $menu['id'] ?>">
                        <div class="d-flex align-items-center gap-3">
                            <span class="drag-handle btn btn-light btn-sm"><i class="bi bi-list"></i></span>
                            <div>
                                <strong><?= htmlspecialchars($menu['menu_name']) ?></strong>
                                <div class="text-muted small"><?= htmlspecialchars($menu['menu_link']) ?> | <?= htmlspecialchars($menu['status']) ?></div>
                            </div>
                        </div>
                        <div class="text-end">
                            <a href="menus.php?edit=<?= (int) $menu['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                            <a href="menus.php?delete=<?= (int) $menu['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this menu item?');">Delete</a>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
const menuList = document.querySelector('.menu-sortable');
if (menuList) {
    Sortable.create(menuList, {
        animation: 180,
        handle: '.drag-handle',
        onEnd: function () {
            const data = new FormData();
            data.append('action', 'sort');
            Array.from(menuList.querySelectorAll('li')).forEach(function (item) {
                data.append('order[]', item.dataset.id);
            });
            fetch('menus.php', {
                method: 'POST',
                body: data
            });
        }
    });
}
</script>
<?php include __DIR__ . '/admin-footer.php'; ?>
