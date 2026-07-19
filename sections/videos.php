<?php
$videoSettings = getSectionSettings($section);
$videoModalId = 'videoModal-' . (int) ($section['id'] ?? 0);
$contentItems = parseStructuredLines($section['content'] ?? '', 4);
$useVideoLibrary = strtolower(trim((string) ($videoSettings['source'] ?? ''))) === 'sample_videos';
$videos = [];

if (!$useVideoLibrary && $contentItems !== []) {
    foreach ($contentItems as $item) {
        $videos[] = [
            'title' => trim((string) ($item[0] ?? '')),
            'description' => trim((string) ($item[1] ?? '')),
            'thumbnail' => trim((string) ($item[2] ?? '')),
            'video_url' => trim((string) ($item[3] ?? '')),
            'video_file' => '',
        ];
    }
} else {
    $videos = getSampleVideos();
}
?>
<section class="section-muted">
    <div class="container">
        <div class="section-heading text-center mx-auto">
            <span class="section-kicker"><?= htmlspecialchars(getSectionKicker($section, !empty($videoSettings['source']) ? 'Sample Classes' : 'Classroom Preview')) ?></span>
            <h2 class="section-title mt-3"><?= htmlspecialchars($section['title'] ?? '') ?></h2>
            <?php if (!empty($section['subtitle'])): ?>
                <p class="section-subtitle mx-auto mt-3"><?= htmlspecialchars($section['subtitle']) ?></p>
            <?php endif; ?>
        </div>

        <div class="row g-4">
            <?php foreach ($videos as $video): ?>
                <?php $videoSrc = (string) ($video['video_file'] ?: $video['video_url']); ?>
                <div class="col-lg-4 col-md-6">
                    <article class="card video-card h-100 overflow-hidden">
                        <button
                            type="button"
                            class="video-trigger btn p-0 border-0 text-start h-100"
                            data-bs-toggle="modal"
                            data-bs-target="#<?= htmlspecialchars($videoModalId) ?>"
                            data-video-src="<?= htmlspecialchars($videoSrc) ?>"
                            data-video-type="<?= !empty($video['video_file']) ? 'file' : 'url' ?>"
                        >
                            <div class="video-thumbnail">
                                <?php $thumbSrc = getVideoThumbnail($video); ?>
                                <?php if ($thumbSrc === 'video-preview' && !empty($video['video_file'])): ?>
                                    <video class="card-img-top video-preview-el" muted preload="metadata" src="<?= htmlspecialchars($video['video_file']) ?>#t=1" onmouseenter="this.play()" onmouseleave="this.pause();this.currentTime=1;"></video>
                                <?php else: ?>
                                    <img src="<?= htmlspecialchars($thumbSrc) ?>" alt="<?= htmlspecialchars($video['title']) ?>" class="card-img-top" loading="lazy" decoding="async">
                                <?php endif; ?>
                                <div class="video-play-overlay">
                                    <span class="video-play-button">
                                        <i class="bi bi-play-fill"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="card-body p-4">
                                <h3 class="h5 mb-2"><?= htmlspecialchars($video['title']) ?></h3>
                                <p class="text-muted mb-0"><?= htmlspecialchars($video['description']) ?></p>
                            </div>
                        </button>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<div class="modal fade" id="<?= htmlspecialchars($videoModalId) ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 overflow-hidden">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">Sample Class Video</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 p-lg-4">
                <div class="ratio ratio-16x9 bg-dark rounded-4 overflow-hidden">
                    <iframe class="w-100 h-100 d-none" allowfullscreen></iframe>
                    <video class="w-100 h-100 d-none" controls playsinline preload="none"></video>
                </div>
            </div>
        </div>
    </div>
</div>
