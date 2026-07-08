<?php
require 'includes/db.php';
require 'includes/site.php';
require 'includes/form-handler.php';

$meta = getPageMeta('contact');
$sections = getPageSections('contact');

$pageTitle = $meta['title'];
$pageDescription = $meta['description'];
$pageOgImage = $meta['og_image'];
$currentPage = 'contact';

include 'includes/header.php';
include 'includes/navbar.php';
?>
<main>
    <?php renderPageSections($sections); ?>
</main>
<?php include 'includes/footer.php'; ?>
