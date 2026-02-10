<?php
/**
 * MTI_SMS - Header Template with Sidebar & Navbar
 */
require_once __DIR__ . '/../config/session.php';
requireLogin();

$user = getCurrentUser();
$currentPage = isset($currentPage) ? $currentPage : 'dashboard';

// Get pending requests count
require_once __DIR__ . '/../config/database.php';
$pendingCount = dbFetchOne("SELECT COUNT(*) as cnt FROM item_requests WHERE status = 'pending'");
$pendingCount = $pendingCount ? $pendingCount['cnt'] : 0;

// Menu configuration
$menuItems = array(
    'main' => array(
        array('page' => 'dashboard', 'icon' => 'fa-home', 'label' => 'Home', 'access' => ''),
    ),
    'management' => array(
        array('page' => 'users', 'icon' => 'fa-user-plus', 'label' => 'Add User', 'access' => 'STOCK_ADMIN'),
        array('page' => 'categories', 'icon' => 'fa-layer-group', 'label' => 'Category', 'access' => 'STOCK_ADMIN,HOD'),
        array('page' => 'items', 'icon' => 'fa-clipboard-list', 'label' => 'Item Register', 'access' => 'STOCK_ADMIN,HOD,DEPT_IN_CHARGE'),
    ),
    'stock' => array(
        array('page' => 'stock-in', 'icon' => 'fa-arrow-down', 'label' => 'Stock In (Forms)', 'access' => 'STOCK_ADMIN,DEPT_IN_CHARGE'),
        array('page' => 'view-stock', 'icon' => 'fa-warehouse', 'label' => 'View Stock', 'access' => ''),
        array('page' => 'old-stock', 'icon' => 'fa-archive', 'label' => 'Old Stock', 'access' => 'STOCK_ADMIN,HOD,DEPT_IN_CHARGE'),
        array('page' => 'dispatched', 'icon' => 'fa-truck', 'label' => 'Dispatched / Stock Out', 'access' => 'STOCK_ADMIN,DEPT_IN_CHARGE'),
    ),
    'requests' => array(
        array('page' => 'requests', 'icon' => 'fa-paper-plane', 'label' => 'Item Requests', 'access' => '', 'badge' => $pendingCount),
    ),
);

$pageTitles = array(
    'dashboard' => 'Dashboard',
    'users' => 'User Management',
    'categories' => 'Category Management',
    'items' => 'Item Register',
    'stock-in' => 'Stock In (Forms)',
    'view-stock' => 'View Stock',
    'old-stock' => 'Old Stock',
    'dispatched' => 'Dispatched / Stock Out',
    'requests' => 'Item Requests',
);

$pageTitle = isset($pageTitles[$currentPage]) ? $pageTitles[$currentPage] : 'Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MTI_SMS - <?php echo htmlspecialchars($pageTitle); ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>

<!-- Toast Notification -->
<?php $flash = getMessage(); if ($flash): ?>
<div class="toast <?php echo $flash['type']; ?>" id="toast">
    <?php echo htmlspecialchars($flash['message']); ?>
</div>
<script>setTimeout(function() { document.getElementById('toast').style.display = 'none'; }, 3500);</script>
<?php endif; ?>

<div class="dashboard-layout">
    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="s-logo"><i class="fas fa-boxes-stacked"></i></div>
            <div class="s-title">
                <h2>MTI_SMS</h2>
                <span>Stock Management</span>
            </div>
        </div>
        <div class="sidebar-menu">
            <div class="menu-section">Main</div>
            <?php foreach ($menuItems['main'] as $item): ?>
                <?php if (empty($item['access']) || hasAccess($item['access'])): ?>
                <a class="menu-item <?php echo $currentPage === $item['page'] ? 'active' : ''; ?>" href="<?php echo $item['page']; ?>.php">
                    <i class="fas <?php echo $item['icon']; ?>"></i> <?php echo $item['label']; ?>
                    <?php if (isset($item['badge']) && $item['badge'] > 0): ?>
                    <span class="badge-count"><?php echo $item['badge']; ?></span>
                    <?php endif; ?>
                </a>
                <?php endif; ?>
            <?php endforeach; ?>
            
            <div class="menu-section">Management</div>
            <?php foreach ($menuItems['management'] as $item): ?>
                <?php if (empty($item['access']) || hasAccess($item['access'])): ?>
                <a class="menu-item <?php echo $currentPage === $item['page'] ? 'active' : ''; ?>" href="<?php echo $item['page']; ?>.php">
                    <i class="fas <?php echo $item['icon']; ?>"></i> <?php echo $item['label']; ?>
                </a>
                <?php endif; ?>
            <?php endforeach; ?>
            
            <div class="menu-section">Stock Operations</div>
            <?php foreach ($menuItems['stock'] as $item): ?>
                <?php if (empty($item['access']) || hasAccess($item['access'])): ?>
                <a class="menu-item <?php echo $currentPage === $item['page'] ? 'active' : ''; ?>" href="<?php echo $item['page']; ?>.php">
                    <i class="fas <?php echo $item['icon']; ?>"></i> <?php echo $item['label']; ?>
                </a>
                <?php endif; ?>
            <?php endforeach; ?>
            
            <div class="menu-section">Requests</div>
            <?php foreach ($menuItems['requests'] as $item): ?>
                <?php if (empty($item['access']) || hasAccess($item['access'])): ?>
                <a class="menu-item <?php echo $currentPage === $item['page'] ? 'active' : ''; ?>" href="<?php echo $item['page']; ?>.php">
                    <i class="fas <?php echo $item['icon']; ?>"></i> <?php echo $item['label']; ?>
                    <?php if (isset($item['badge']) && $item['badge'] > 0): ?>
                    <span class="badge-count"><?php echo $item['badge']; ?></span>
                    <?php endif; ?>
                </a>
                <?php endif; ?>
            <?php endforeach; ?>
            
            <div class="menu-section">Account</div>
            <a class="menu-item" href="logout.php">
                <i class="fas fa-right-from-bracket"></i> Logout
            </a>
        </div>
    </nav>

    <!-- Top Navbar -->
    <header class="top-navbar">
        <div class="left-section">
            <div class="hamburger" onclick="toggleSidebar()"><i class="fas fa-bars"></i></div>
            <span class="page-title"><?php echo htmlspecialchars($pageTitle); ?></span>
        </div>
        <div class="right-section">
            <div class="datetime">
                <div class="date" id="currentDate"></div>
                <div class="time" id="currentTime"></div>
            </div>
            <a href="requests.php" class="notif-btn" title="Pending Requests: <?php echo $pendingCount; ?>">
                <i class="fas fa-bell"></i>
                <?php if ($pendingCount > 0): ?>
                <span class="notif-dot"></span>
                <?php endif; ?>
            </a>
            <div class="user-info">
                <div class="user-avatar"><?php echo htmlspecialchars(getUserInitials()); ?></div>
                <div>
                    <div class="user-name"><?php echo htmlspecialchars($user['full_name']); ?></div>
                    <div class="user-role"><?php echo htmlspecialchars($user['role']); ?></div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
