<?php
require_once __DIR__ . '/auth.php';
requireAdminLogin();
require_once __DIR__ . '/../includes/site.php';

$pageTitle = 'Teaching Standards';
$pageSectionTypes = [
    'hero' => 'Standards Hero',
    'mission' => 'Mission Banner',
    'compliance_intro' => 'Compliance Intro',
    'cta' => 'Bottom CTA',
    'teaching_standards' => 'Homepage Preview',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'reorder') {
    $target = $_POST['target'] ?? '';
    $order = $_POST['order'] ?? [];
    $table = $target === 'compliance' ? 'compliance_rules' : 'standards_sections';
    $stmt = $pdo->prepare("UPDATE {$table} SET sort_order = :sort_order WHERE id = :id");
    foreach ($order as $sort => $id) {
        $stmt->execute([
            ':sort_order' => (int) $sort + 1,
            ':id' => (int) $id,
        ]);
    }
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_page_section') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $pdo->prepare('UPDATE page_sections SET title = :title, subtitle = :subtitle, content = :content, button_text = :button_text, button_link = :button_link, status = :status WHERE id = :id');
        $stmt->execute([
            ':title' => trim($_POST['title'] ?? ''),
            ':subtitle' => trim($_POST['subtitle'] ?? ''),
            ':content' => trim($_POST['content'] ?? ''),
            ':button_text' => trim($_POST['button_text'] ?? ''),
            ':button_link' => trim($_POST['button_link'] ?? ''),
            ':status' => in_array($_POST['status'] ?? '', ['active', 'inactive'], true) ? $_POST['status'] : 'active',
            ':id' => $id,
        ]);
        header('Location: standards.php?updated=1');
        exit;
    }

    if ($action === 'save_standard') {
        $id = (int) ($_POST['id'] ?? 0);
        $payload = [
            ':title' => trim($_POST['title'] ?? ''),
            ':content' => trim($_POST['content'] ?? ''),
            ':icon' => trim($_POST['icon'] ?? ''),
            ':status' => in_array($_POST['status'] ?? '', ['active', 'inactive'], true) ? $_POST['status'] : 'active',
        ];

        if ($id > 0) {
            $stmt = $pdo->prepare('UPDATE standards_sections SET title = :title, content = :content, icon = :icon, status = :status WHERE id = :id');
            $payload[':id'] = $id;
            $stmt->execute($payload);
        } else {
            $sortOrder = (int) $pdo->query('SELECT COALESCE(MAX(sort_order), 0) FROM standards_sections')->fetchColumn() + 1;
            $stmt = $pdo->prepare('INSERT INTO standards_sections (title, content, icon, sort_order, status) VALUES (:title, :content, :icon, :sort_order, :status)');
            $payload[':sort_order'] = $sortOrder;
            $stmt->execute($payload);
        }

        header('Location: standards.php?updated=1');
        exit;
    }

    if ($action === 'save_rule') {
        $id = (int) ($_POST['id'] ?? 0);
        $payload = [
            ':title' => trim($_POST['title'] ?? ''),
            ':content' => trim($_POST['content'] ?? ''),
            ':icon' => trim($_POST['icon'] ?? ''),
            ':penalty' => trim($_POST['penalty'] ?? ''),
            ':status' => in_array($_POST['status'] ?? '', ['active', 'inactive'], true) ? $_POST['status'] : 'active',
        ];

        if ($id > 0) {
            $stmt = $pdo->prepare('UPDATE compliance_rules SET title = :title, content = :content, icon = :icon, penalty = :penalty, status = :status WHERE id = :id');
            $payload[':id'] = $id;
            $stmt->execute($payload);
        } else {
            $sortOrder = (int) $pdo->query('SELECT COALESCE(MAX(sort_order), 0) FROM compliance_rules')->fetchColumn() + 1;
            $stmt = $pdo->prepare('INSERT INTO compliance_rules (title, content, icon, penalty, sort_order, status) VALUES (:title, :content, :icon, :penalty, :sort_order, :status)');
            $payload[':sort_order'] = $sortOrder;
            $stmt->execute($payload);
        }

        header('Location: standards.php?updated=1');
        exit;
    }
}

if (isset($_GET['delete_standard'])) {
    $stmt = $pdo->prepare('DELETE FROM standards_sections WHERE id = :id');
    $stmt->execute([':id' => (int) $_GET['delete_standard']]);
    header('Location: standards.php?deleted=1');
    exit;
}

if (isset($_GET['delete_rule'])) {
    $stmt = $pdo->prepare('DELETE FROM compliance_rules WHERE id = :id');
    $stmt->execute([':id' => (int) $_GET['delete_rule']]);
    header('Location: standards.php?deleted=1');
    exit;
}

$pageSections = $pdo->query("SELECT * FROM page_sections WHERE (page_name = 'standards' OR (page_name = 'home' AND section_type = 'teaching_standards')) ORDER BY page_name ASC, sort_order ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);
$standards = getStandardsSections($pdo, 'all');
$rules = getComplianceRules($pdo, 'all');

include __DIR__ . '/admin-header.php';
?>
<?php if (!empty($_GET['updated'])): ?>
    <div class="alert alert-success">Teaching standards content updated successfully.</div>
<?php endif; ?>
<?php if (!empty($_GET['deleted'])): ?>
    <div class="alert alert-success">Item deleted successfully.</div>
<?php endif; ?>

<div class="card admin-card p-4 mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h5 class="mb-1">Page content</h5>
            <p class="text-muted mb-0">Edit section titles, descriptions, CTA labels, and homepage preview copy.</p>
        </div>
        <a href="../standards.php" target="_blank" class="btn btn-outline-primary">Preview Page</a>
    </div>
    <div class="row g-4">
        <?php foreach ($pageSections as $section): ?>
            <div class="col-12">
                <div class="border rounded-4 p-4 bg-light-subtle">
                    <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                        <div>
                            <h6 class="mb-1"><?= htmlspecialchars($pageSectionTypes[$section['section_type']] ?? $section['section_type']) ?></h6>
                            <small class="text-muted"><?= htmlspecialchars($section['page_name']) ?> page</small>
                        </div>
                        <span class="badge text-bg-secondary text-uppercase"><?= htmlspecialchars($section['status']) ?></span>
                    </div>
                    <form method="post">
                        <input type="hidden" name="action" value="save_page_section">
                        <input type="hidden" name="id" value="<?= (int) $section['id'] ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Title</label>
                                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($section['title']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Subtitle</label>
                                <input type="text" name="subtitle" class="form-control" value="<?= htmlspecialchars($section['subtitle']) ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description / Content</label>
                                <textarea name="content" rows="3" class="form-control"><?= htmlspecialchars($section['content']) ?></textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">CTA Text</label>
                                <input type="text" name="button_text" class="form-control" value="<?= htmlspecialchars($section['button_text']) ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">CTA Link</label>
                                <input type="text" name="button_link" class="form-control" value="<?= htmlspecialchars($section['button_link']) ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="active" <?= $section['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                    <option value="inactive" <?= $section['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                </select>
                            </div>
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-primary">Save Section</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-6">
        <div class="card admin-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <h5 class="mb-1">Standards cards</h5>
                    <p class="text-muted mb-0">Add, edit, disable, delete, and drag to reorder standards.</p>
                </div>
                <span class="badge bg-primary-subtle text-primary"><?= count($standards) ?> items</span>
            </div>

            <form method="post" class="border rounded-4 p-4 bg-light mb-4">
                <input type="hidden" name="action" value="save_standard">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" placeholder="Student Engagement" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Icon</label>
                        <input type="text" name="icon" class="form-control" placeholder="🧠">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="content" rows="3" class="form-control" required></textarea>
                    </div>
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-primary">Add Standard</button>
                    </div>
                </div>
            </form>

            <div class="list-group section-sortable" data-target="standards">
                <?php foreach ($standards as $standard): ?>
                    <div class="list-group-item border rounded-4 mb-3" data-id="<?= (int) $standard['id'] ?>">
                        <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                            <div class="d-flex align-items-center gap-3">
                                <button type="button" class="btn btn-light btn-sm drag-handle"><i class="bi bi-grip-vertical"></i></button>
                                <div class="standards-icon standards-icon-sm"><?= htmlspecialchars($standard['icon'] ?: '✨') ?></div>
                                <div>
                                    <h6 class="mb-1"><?= htmlspecialchars($standard['title']) ?></h6>
                                    <small class="text-muted text-uppercase"><?= htmlspecialchars($standard['status']) ?></small>
                                </div>
                            </div>
                            <a href="standards.php?delete_standard=<?= (int) $standard['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this standard card?');">Delete</a>
                        </div>
                        <form method="post">
                            <input type="hidden" name="action" value="save_standard">
                            <input type="hidden" name="id" value="<?= (int) $standard['id'] ?>">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Title</label>
                                    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($standard['title']) ?>" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Icon</label>
                                    <input type="text" name="icon" class="form-control" value="<?= htmlspecialchars($standard['icon']) ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="active" <?= $standard['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                        <option value="inactive" <?= $standard['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Description</label>
                                    <textarea name="content" rows="3" class="form-control" required><?= htmlspecialchars($standard['content']) ?></textarea>
                                </div>
                                <div class="col-12 text-end">
                                    <button type="submit" class="btn btn-outline-primary">Update Standard</button>
                                </div>
                            </div>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="col-xl-6">
        <div class="card admin-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <h5 class="mb-1">Compliance rules</h5>
                    <p class="text-muted mb-0">Manage class protocols, penalties, visibility, and display order.</p>
                </div>
                <span class="badge bg-warning text-dark"><?= count($rules) ?> items</span>
            </div>

            <form method="post" class="border rounded-4 p-4 bg-light mb-4">
                <input type="hidden" name="action" value="save_rule">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" placeholder="Teacher No-Show" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Icon</label>
                        <input type="text" name="icon" class="form-control" placeholder="👩‍🏫">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Penalty</label>
                        <input type="text" name="penalty" class="form-control" placeholder="₹250 penalty">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Rules</label>
                        <textarea name="content" rows="4" class="form-control" placeholder="One rule per line" required></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-primary">Add Rule</button>
                    </div>
                </div>
            </form>

            <div class="list-group section-sortable" data-target="compliance">
                <?php foreach ($rules as $rule): ?>
                    <div class="list-group-item border rounded-4 mb-3" data-id="<?= (int) $rule['id'] ?>">
                        <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                            <div class="d-flex align-items-center gap-3">
                                <button type="button" class="btn btn-light btn-sm drag-handle"><i class="bi bi-grip-vertical"></i></button>
                                <div class="standards-icon standards-icon-sm"><?= htmlspecialchars($rule['icon'] ?: '📌') ?></div>
                                <div>
                                    <h6 class="mb-1"><?= htmlspecialchars($rule['title']) ?></h6>
                                    <small class="text-muted"><?= htmlspecialchars($rule['penalty'] ?: 'No penalty') ?></small>
                                </div>
                            </div>
                            <a href="standards.php?delete_rule=<?= (int) $rule['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this compliance rule?');">Delete</a>
                        </div>
                        <form method="post">
                            <input type="hidden" name="action" value="save_rule">
                            <input type="hidden" name="id" value="<?= (int) $rule['id'] ?>">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Title</label>
                                    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($rule['title']) ?>" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Icon</label>
                                    <input type="text" name="icon" class="form-control" value="<?= htmlspecialchars($rule['icon']) ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Penalty</label>
                                    <input type="text" name="penalty" class="form-control" value="<?= htmlspecialchars($rule['penalty']) ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Rules</label>
                                    <textarea name="content" rows="4" class="form-control" required><?= htmlspecialchars($rule['content']) ?></textarea>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="active" <?= $rule['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                        <option value="inactive" <?= $rule['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                    </select>
                                </div>
                                <div class="col-12 text-end">
                                    <button type="submit" class="btn btn-outline-primary">Update Rule</button>
                                </div>
                            </div>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    document.querySelectorAll('.section-sortable').forEach((list) => {
        Sortable.create(list, {
            animation: 180,
            handle: '.drag-handle',
            onEnd: function () {
                const order = Array.from(list.querySelectorAll('[data-id]')).map((item) => item.dataset.id);
                const data = new FormData();
                data.append('action', 'reorder');
                data.append('target', list.dataset.target);
                order.forEach((id) => data.append('order[]', id));

                fetch('standards.php', {
                    method: 'POST',
                    body: data
                }).then((response) => response.json()).then((data) => {
                    if (!data.success) {
                        alert('Unable to update order.');
                    }
                }).catch(() => {
                    alert('Unable to update order.');
                });
            }
        });
    });
</script>
<?php include __DIR__ . '/admin-footer.php'; ?>
