<?php
require 'includes/db.php';
require 'includes/site.php';

$meta = getPageMeta('about');
$sections = getPageSections('about');

$pageTitle = $meta['title'];
$pageDescription = $meta['description'];
$pageOgImage = $meta['og_image'];
$currentPage = 'about';

include 'includes/header.php';
include 'includes/navbar.php';
?>
<main>
    <?php renderPageSections($sections); ?>
</main>
<?php include 'includes/footer.php'; ?>
