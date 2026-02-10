<?php
/**
 * MTI_SMS - Dashboard / Home Page
 */
$currentPage = 'dashboard';
require_once 'config/database.php';
require_once 'includes/header.php';

// Get statistics
$totalItems = dbFetchOne("SELECT COUNT(*) as cnt FROM items")['cnt'];
$totalCategories = dbFetchOne("SELECT COUNT(*) as cnt FROM categories")['cnt'];
$totalStockIn = dbFetchOne("SELECT COALESCE(SUM(qty), 0) as total FROM stock_in_logs")['total'];
$totalStockOut = dbFetchOne("SELECT COALESCE(SUM(qty), 0) as total FROM stock_out_logs")['total'];

$user = getCurrentUser();
?>

<!-- Welcome Banner -->
<div class="welcome-banner">
    <h1>Welcome, <span><?php echo htmlspecialchars($user['full_name']); ?></span>!</h1>
    <p>You are logged in as <strong><?php echo htmlspecialchars($user['role']); ?></strong>. Manage your college stock efficiently.</p>
</div>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(27,42,74,0.1); color: var(--navy);">
            <i class="fas fa-boxes-stacked"></i>
        </div>
        <div class="stat-value"><?php echo $totalItems; ?></div>
        <div class="stat-label">Total Items</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(212,160,23,0.15); color: var(--mustard);">
            <i class="fas fa-layer-group"></i>
        </div>
        <div class="stat-value"><?php echo $totalCategories; ?></div>
        <div class="stat-label">Categories</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(39,174,96,0.12); color: #27AE60;">
            <i class="fas fa-arrow-down"></i>
        </div>
        <div class="stat-value"><?php echo $totalStockIn; ?></div>
        <div class="stat-label">Stock In (Total)</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(231,76,60,0.12); color: #E74C3C;">
            <i class="fas fa-arrow-up"></i>
        </div>
        <div class="stat-value"><?php echo $totalStockOut; ?></div>
        <div class="stat-label">Stock Out (Total)</div>
    </div>
</div>

<!-- Banner Image -->
<div class="banner-image">
    <div class="banner-content">
        <i class="fas fa-university"></i>
        <h2>College Infrastructure & Stock Hub</h2>
        <p>Centralized inventory management for all departments</p>
    </div>
</div>

<!-- About & Contact -->
<div class="two-col-grid">
    <div class="about-card">
        <h3><i class="fas fa-info-circle"></i> About MTI_SMS</h3>
        <p>The <strong>MTI Stock Management System (MTI_SMS)</strong> is a comprehensive web-based application designed specifically for college administration to manage, track, and control all stock and inventory items across departments. The system provides real-time tracking of stock movements, facilitates inter-department item requests, manages dispatching, and maintains complete audit logs. Built with role-based access control, it ensures secure and efficient stock operations for Stock Admins, HODs, Department In-Charges, and Staff members.</p>
        <div style="margin-top: 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
            
            
        </div>
    </div>
    <div>
        <div class="contact-card">
            <h4><i class="fas fa-phone"></i> Contact Authorities</h4>
            <div class="contact-item"><i class="fas fa-user-tie"></i> <div><strong>Principal</strong><br>Mrs.Seena I T</div></div>
            <div class="contact-item"><i class="fas fa-envelope"></i> principal@mti.edu.in</div>
            <div class="contact-item"><i class="fas fa-phone"></i> +91-9876543210</div>
            <hr>
            <div class="contact-item"><i class="fas fa-user-gear"></i> <div><strong>Stock Admin</strong><br>Mr.Bibin P Gopal</div></div>
            <div class="contact-item"><i class="fas fa-envelope"></i> stock@mti.edu.in</div>
            <div class="contact-item"><i class="fas fa-phone"></i> +91-9876543211</div>
            <hr>
            <div class="contact-item"><i class="fas fa-building"></i> <div><strong>Maharaja's Technological Institute</strong><br>Thrissur, Kerala 680001</div></div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
