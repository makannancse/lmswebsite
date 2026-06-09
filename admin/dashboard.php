<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/site.php';
requireAdminLogin();

$pageTitle = 'Dashboard';
$totalLeads = (int) $pdo->query('SELECT COUNT(*) FROM leads')->fetchColumn();
$totalPages = (int) $pdo->query('SELECT COUNT(*) FROM pages')->fetchColumn();
$totalSections = (int) $pdo->query('SELECT COUNT(*) FROM page_sections')->fetchColumn();
$totalMenus = (int) $pdo->query('SELECT COUNT(*) FROM menus WHERE status = "active"')->fetchColumn();
$totalVideos = (int) $pdo->query('SELECT COUNT(*) FROM sample_videos WHERE status = "active"')->fetchColumn();
$recentLeads = $pdo->query('SELECT * FROM leads ORDER BY created_at DESC LIMIT 5')->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/admin-header.php';
?>
<div class="row g-4 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="card admin-card p-4 h-100">
            <h6 class="text-uppercase text-muted">Pages</h6>
            <h2 class="mt-3"><?= $totalPages ?></h2>
            <p class="text-muted mb-0">SEO-enabled CMS pages available for editing.</p>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card admin-card p-4 h-100">
            <h6 class="text-uppercase text-muted">Sections</h6>
            <h2 class="mt-3"><?= $totalSections ?></h2>
            <p class="text-muted mb-0">Active and inactive blocks across the website.</p>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card admin-card p-4 h-100">
            <h6 class="text-uppercase text-muted">Menus</h6>
            <h2 class="mt-3"><?= $totalMenus ?></h2>
            <p class="text-muted mb-0">Frontend menu items currently visible.</p>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card admin-card p-4 h-100">
            <h6 class="text-uppercase text-muted">Videos</h6>
            <h2 class="mt-3"><?= $totalVideos ?></h2>
            <p class="text-muted mb-0">Sample class videos ready on the homepage.</p>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-8">
        <div class="card admin-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-1">Recent leads</h5>
                    <p class="text-muted mb-0">Latest inquiries collected by your dynamic forms.</p>
                </div>
                <a href="leads.php" class="btn btn-outline-primary btn-sm">View all</a>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Source</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentLeads as $lead): ?>
                            <tr>
                                <td><?= htmlspecialchars($lead['name']) ?></td>
                                <td><?= htmlspecialchars($lead['email']) ?></td>
                                <td><?= htmlspecialchars($lead['phone']) ?></td>
                                <td><?= htmlspecialchars($lead['source']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card admin-card p-4 h-100">
            <h5 class="mb-3">Quick actions</h5>
            <div class="d-grid gap-3">
                <a href="pages.php" class="btn btn-primary">Manage Pages</a>
                <a href="sections.php" class="btn btn-outline-primary">Reorder Sections</a>
                <a href="menus.php" class="btn btn-outline-primary">Update Menus</a>
                <a href="sample_videos.php" class="btn btn-outline-primary">Edit Videos</a>
                <a href="settings.php" class="btn btn-outline-primary">Global Settings</a>
            </div>
            <hr class="my-4">
            <p class="text-muted mb-0">Site phone is currently <strong><?= htmlspecialchars(getSetting('site_phone')) ?></strong>.</p>
        </div>
    </div>
</div>
<?php include __DIR__ . '/admin-footer.php'; ?>
