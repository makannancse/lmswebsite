<?php

require_once __DIR__ . '/bootstrap.php';
lwBootstrapApplication();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/site.php';

function bootCmsPage(string $pageName, bool $withForms = false): array
{
    if ($withForms) {
        require_once __DIR__ . '/form-handler.php';
    }

    $meta = getPageMeta($pageName);

    return [
        'sections' => getPageSections($pageName),
        'meta' => $meta,
        'pageTitle' => $meta['title'],
        'pageDescription' => $meta['description'],
        'pageOgImage' => $meta['og_image'],
        'currentPage' => $pageName,
    ];
}

function renderCmsPage(string $pageName, bool $withForms = false): void
{
    $page = bootCmsPage($pageName, $withForms);
    extract($page, EXTR_SKIP);

    include __DIR__ . '/header.php';
    include __DIR__ . '/navbar.php';
    echo '<main>';
    renderPageSections($sections);
    echo '</main>';
    include __DIR__ . '/footer.php';
}
