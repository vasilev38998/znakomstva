<?php
declare(strict_types=1);
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0c0f14">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="manifest" href="<?= BASE_URL ?>pwa/manifest.json">
    <link rel="apple-touch-icon" href="<?= BASE_URL ?>assets/icons/icon.svg">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/app.css">
</head>
<body>
<div class="app">
    <?= $content ?>
</div>
<script src="<?= BASE_URL ?>assets/js/app.js" defer></script>
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('<?= BASE_URL ?>pwa/service-worker.js');
        });
    }
</script>
</body>
</html>
