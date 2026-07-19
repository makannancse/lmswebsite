<?php
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/site.php';

$pdo = lwGetPdo();
$stmt = $pdo->prepare("SELECT * FROM page_sections WHERE page_id = 1 AND section_type = 'founder' AND status = 'active' ORDER BY sort_order ASC LIMIT 1");
$stmt->execute();
$founderSection = $stmt->fetch(PDO::FETCH_ASSOC);
$founderImg = $founderSection['section_image'] ?? $founderSection['image'] ?? '';
echo "FOUNDER IMG IS: " . $founderImg . "\n";
var_dump($founderSection);
