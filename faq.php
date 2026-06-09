<?php
require 'includes/db.php';
require 'includes/site.php';

$meta = getPageMeta('faq');
$sections = getPageSections('faq');

$pageTitle = $meta['title'];
$pageDescription = $meta['description'];
$pageOgImage = $meta['og_image'];
$currentPage = 'faq';

include 'includes/header.php';
include 'includes/navbar.php';
?>
<main>
    <?php renderPageSections($sections); ?>
</main>
<?php include 'includes/footer.php'; ?>
