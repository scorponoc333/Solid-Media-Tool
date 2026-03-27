<?php
$branding = (new BrandingService())->get($GLOBALS['client_id']);
$primaryColor = $branding['primary_color'] ?? '#6366f1';
$secondaryColor = $branding['secondary_color'] ?? '#8b5cf6';
$companyName = $branding['company_name'] ?? APP_NAME;
$logoUrl = $branding['logo_url'] ?? '';
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = rtrim(parse_url(BASE_URL, PHP_URL_PATH) ?: '', '/');
if ($basePath && str_starts_with($currentPath, $basePath)) {
    $currentPath = substr($currentPath, strlen($basePath));
}
$currentPath = $currentPath ?: '/';

$darkMode = $_COOKIE['darkMode'] ?? 'false';
$isDark = $darkMode === 'true';

// Compute RGB values for primary and secondary
$priR = hexdec(substr(ltrim($primaryColor, '#'), 0, 2));
$priG = hexdec(substr(ltrim($primaryColor, '#'), 2, 2));
$priB = hexdec(substr(ltrim($primaryColor, '#'), 4, 2));
$secR = hexdec(substr(ltrim($secondaryColor, '#'), 0, 2));
$secG = hexdec(substr(ltrim($secondaryColor, '#'), 2, 2));
$secB = hexdec(substr(ltrim($secondaryColor, '#'), 4, 2));
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= $isDark ? 'dark' : 'light' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?> — <?= htmlspecialchars($companyName) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/app.css">
    <style>
        :root {
            --primary: <?= htmlspecialchars($primaryColor) ?>;
            --primary-rgb: <?= "$priR,$priG,$priB" ?>;
            --secondary: <?= htmlspecialchars($secondaryColor) ?>;
            --secondary-rgb: <?= "$secR,$secG,$secB" ?>;
            --sidebar-gradient: linear-gradient(180deg, <?= htmlspecialchars($primaryColor) ?> 0%, #0a0a0a 60%, #000000 100%);
        }
    </style>
</head>
<body>

<div class="app-layout">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <?php if ($logoUrl): ?>
                <img src="<?= htmlspecialchars($logoUrl) ?>" alt="<?= htmlspecialchars($companyName) ?>" class="sidebar-logo sidebar-expanded-only">
            <?php else: ?>
                <span class="sidebar-brand-text"><?= htmlspecialchars($companyName) ?></span>
            <?php endif; ?>
            <div class="sidebar-collapsed-initial"><?= strtoupper(substr($companyName, 0, 1)) ?></div>
        </div>

        <nav class="sidebar-nav">
            <a href="<?= BASE_URL ?>/dashboard" class="nav-item <?= in_array($currentPath, ['/', '/dashboard']) ? 'active' : '' ?>">
                <i class="fas fa-th-large"></i>
                <span>Dashboard</span>
            </a>
            <a href="<?= BASE_URL ?>/generator" class="nav-item <?= str_starts_with($currentPath, '/generator') ? 'active' : '' ?>">
                <i class="fas fa-magic"></i>
                <span>Generator</span>
            </a>
            <a href="<?= BASE_URL ?>/posts" class="nav-item <?= str_starts_with($currentPath, '/posts') ? 'active' : '' ?>">
                <i class="fas fa-edit"></i>
                <span>Posts</span>
            </a>
            <a href="<?= BASE_URL ?>/calendar" class="nav-item <?= str_starts_with($currentPath, '/calendar') ? 'active' : '' ?>">
                <i class="fas fa-calendar-alt"></i>
                <span>Calendar</span>
            </a>
            <a href="<?= BASE_URL ?>/reporting" class="nav-item <?= str_starts_with($currentPath, '/reporting') ? 'active' : '' ?>">
                <i class="fas fa-chart-bar"></i>
                <span>Reports</span>
            </a>

            <div class="nav-divider"></div>

            <a href="<?= BASE_URL ?>/branding" class="nav-item <?= str_starts_with($currentPath, '/branding') ? 'active' : '' ?>">
                <i class="fas fa-palette"></i>
                <span>Branding</span>
            </a>
            <a href="<?= BASE_URL ?>/memory" class="nav-item <?= str_starts_with($currentPath, '/memory') ? 'active' : '' ?>">
                <i class="fas fa-brain"></i>
                <span>Memory</span>
            </a>
            <a href="<?= BASE_URL ?>/docs" class="nav-item <?= str_starts_with($currentPath, '/docs') ? 'active' : '' ?>">
                <i class="fas fa-book"></i>
                <span>Docs</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="user-avatar">
                    <?= strtoupper(substr($_SESSION['first_name'] ?? $_SESSION['username'] ?? 'U', 0, 1)) ?>
                </div>
                <div class="user-info">
                    <span class="user-name"><?= htmlspecialchars($_SESSION['first_name'] ?: ($_SESSION['username'] ?? 'User')) ?></span>
                    <span class="user-role"><?= htmlspecialchars(ucfirst($_SESSION['role'] ?? 'admin')) ?></span>
                </div>
            </div>
            <a href="<?= BASE_URL ?>/logout" class="nav-item logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="main-wrapper">
        <!-- Top Bar -->
        <header class="topbar">
            <button class="sidebar-toggle" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <h2 class="topbar-title"><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></h2>
            <div class="topbar-actions">
                <button class="theme-toggle" onclick="toggleTheme()" title="Toggle dark mode">
                    <i class="fas fa-moon" id="theme-icon"></i>
                </button>
            </div>
        </header>

        <!-- Page Content -->
        <main class="main-content">
            <?= $content ?>
        </main>
    </div>
</div>

<!-- Modal System -->
<?= ModalService::render() ?>

<!-- Toast Container -->
<div id="toast-container"></div>

<script src="<?= BASE_URL ?>/js/app.js"></script>
</body>
</html>
