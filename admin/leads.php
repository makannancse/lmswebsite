<?php
require_once __DIR__ . '/auth.php';
requireAdminLogin();

$pageTitle = 'Website Leads';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lead_id'], $_POST['status'])) {
    $allowedStatuses = ['new', 'contacted', 'converted', 'closed'];
    $status = (string) $_POST['status'];
    if (in_array($status, $allowedStatuses, true)) {
        $stmt = $pdo->prepare('UPDATE leads SET status = :status WHERE id = :id');
        $stmt->execute([
            ':status' => $status,
            ':id' => (int) $_POST['lead_id'],
        ]);
    }
    header('Location: leads.php?updated=1');
    exit;
}

$leads = $pdo->query('SELECT * FROM leads ORDER BY created_at DESC')->fetchAll(PDO::FETCH_ASSOC);
$statusLabels = [
    'new' => 'New',
    'contacted' => 'Contacted',
    'converted' => 'Converted',
    'closed' => 'Closed',
];

include __DIR__ . '/admin-header.php';
?>
<?php if (!empty($_GET['updated'])): ?>
    <div class="alert alert-success">Lead status updated.</div>
<?php endif; ?>
<div class="card admin-card p-4">
    <h5>Website Leads</h5>
    <p class="text-muted">All demo requests, enrollments, and contact form submissions appear here.</p>
    <div class="table-responsive mt-4">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Course</th>
                    <th>Message</th>
                    <th>Submitted</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($leads)): ?>
                    <tr><td colspan="7" class="text-center text-muted">No leads available yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($leads as $lead): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($lead['name']) ?></strong>
                                <?php if (!empty($lead['parent_name']) || !empty($lead['student_name'])): ?>
                                    <div class="small text-muted">
                                        <?php if (!empty($lead['parent_name'])): ?>Parent: <?= htmlspecialchars($lead['parent_name']) ?><?php endif; ?>
                                        <?php if (!empty($lead['student_name'])): ?><br>Student: <?= htmlspecialchars($lead['student_name']) ?><?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                <div class="small text-muted"><?= htmlspecialchars($lead['source']) ?></div>
                            </td>
                            <td><?= htmlspecialchars($lead['email']) ?></td>
                            <td><?= htmlspecialchars($lead['phone']) ?></td>
                            <td><?= htmlspecialchars($lead['course'] ?? '') ?></td>
                            <td><?= htmlspecialchars($lead['message'] ?? '') ?></td>
                            <td><?= htmlspecialchars($lead['created_at']) ?></td>
                            <td>
                                <form method="post" class="d-flex gap-2 align-items-center">
                                    <input type="hidden" name="lead_id" value="<?= (int) $lead['id'] ?>">
                                    <select name="status" class="form-select form-select-sm">
                                        <?php foreach ($statusLabels as $value => $label): ?>
                                            <option value="<?= htmlspecialchars($value) ?>" <?= ($lead['status'] ?? 'new') === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-outline-primary">Save</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include __DIR__ . '/admin-footer.php'; ?>
