<?php
require 'includes/db.php';
require 'includes/site.php';
require 'includes/form-handler.php';

$meta = getPageMeta('home');
$sections = getPageSections('home');

$pageTitle = $meta['title'];
$pageDescription = $meta['description'];
$pageOgImage = $meta['og_image'];
$currentPage = 'home';

include 'includes/header.php';
include 'includes/navbar.php';
?>
<main>
    <?php if (!empty($sections)): ?>
        <?php renderPageSections($sections); ?>
    <?php else: ?>
        <section class="py-5">
            <div class="container">
                <div class="alert alert-info">No active homepage sections found. Please create sections in the admin panel.</div>
            </div>
        </section>
    <?php endif; ?>
</main>
<?php include 'includes/footer.php'; ?>
