<?php
require 'includes/db.php';
require 'includes/site.php';

$meta = getPageMeta('teachers');
$sections = getPageSections('teachers');

$pageTitle = $meta['title'];
$pageDescription = $meta['description'];
$pageOgImage = $meta['og_image'];
$currentPage = 'teachers';

include 'includes/header.php';
include 'includes/navbar.php';
?>
<main>
    <?php renderPageSections($sections); ?>
</main>
<?php include 'includes/footer.php'; ?>
