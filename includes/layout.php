<?php
declare(strict_types=1);

/**
 * Shared page layout.
 *
 * @param string $pageTitle
 * @param string $activePage  index|rates|manual
 * @param callable $contentFn
 */
function renderLayout(string $pageTitle, string $activePage, callable $contentFn): void
{
    require_once __DIR__ . '/helpers.php';

    $appName = APP_NAME;
    $baseUrl = rtrim(APP_URL, '/');
    $year = date('Y');

    $navItems = [
        'index'  => ['href' => $baseUrl . '/index.php', 'key' => 'nav.converter'],
        'rates'  => ['href' => $baseUrl . '/rates.php', 'key' => 'nav.rates'],
        'manual' => ['href' => $baseUrl . '/manual.php', 'key' => 'nav.manual'],
    ];
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Modern currency converter with live exchange rates, historical charts, and multi-language support.">
    <title><?= e($pageTitle) ?> | <?= e($appName) ?></title>
    <link rel="stylesheet" href="<?= e($baseUrl) ?>/assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <div class="app-wrapper">
        <header class="site-header">
            <div class="container header-inner">
                <a href="<?= e($baseUrl) ?>/index.php" class="logo">
                    <span class="logo-icon">&#x1F4B1;</span>
                    <span class="logo-text" data-i18n="app.name"><?= e($appName) ?></span>
                </a>
                <nav class="main-nav" aria-label="Main navigation">
                    <?php foreach ($navItems as $key => $item): ?>
                        <a href="<?= e($item['href']) ?>"
                           class="nav-link<?= $activePage === $key ? ' active' : '' ?>"
                           data-i18n="<?= e($item['key']) ?>">
                            <?= e(ucfirst($key)) ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
                <div class="header-actions">
                    <div class="lang-switcher" role="group" aria-label="Language">
                        <button type="button" class="lang-btn active" data-lang="en">EN</button>
                        <button type="button" class="lang-btn" data-lang="zh">中文</button>
                    </div>
                    <button type="button" class="menu-toggle" aria-label="Toggle menu" aria-expanded="false">
                        <span></span><span></span><span></span>
                    </button>
                </div>
            </div>
        </header>

        <main class="site-main">
            <div class="container">
                <?php $contentFn(); ?>
            </div>
        </main>

        <footer class="site-footer">
            <div class="container footer-inner">
                <p>&copy; <?= $year ?> <?= e($appName) ?>. <span data-i18n="footer.rights">All rights reserved.</span></p>
                <p class="footer-note" data-i18n="footer.disclaimer">Exchange rates are for informational purposes only.</p>
            </div>
        </footer>
    </div>

    <script src="<?= e($baseUrl) ?>/assets/js/flags.js"></script>
    <script src="<?= e($baseUrl) ?>/assets/js/i18n.js"></script>
    <script src="<?= e($baseUrl) ?>/assets/js/app.js"></script>
</body>
</html>
    <?php
}
