<?php
/**
 * MTI_SMS - View Stock Page
 */
$currentPage = 'view-stock';
require_once 'config/database.php';
require_once 'includes/header.php';

$view = isset($_GET['view']) ? $_GET['view'] : 'item';

// Fetch data based on view
if ($view === 'dept') {
    $data = dbFetchAll("SELECT 
        department,
        COUNT(*) as total_items,
        SUM(quantity) as total_quantity,
        SUM(CASE WHEN quantity <= 5 THEN 1 ELSE 0 END) as low_stock_count
        FROM items GROUP BY department ORDER BY department");
} else {
    $data = dbFetchAll("SELECT i.*, c.name as category_name 
        FROM items i 
        LEFT JOIN categories c ON i.category_id = c.id 
        ORDER BY i.name ASC");
}
?>

<!-- View Toggle Buttons -->
<div style="display: flex; gap: 12px; margin-bottom: 20px; flex-wrap: wrap;">
    <a href="view-stock.php?view=item" class="btn <?php echo $view === 'item' ? 'btn-primary' : 'btn-mustard'; ?>">
        <i class="fas fa-box"></i> Item-wise
    </a>
    <a href="view-stock.php?view=dept" class="btn <?php echo $view === 'dept' ? 'btn-primary' : 'btn-mustard'; ?>">
        <i class="fas fa-building"></i> Department-wise
    </a>
</div>

<!-- Stock Table -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-warehouse" style="color: var(--mustard); margin-right: 8px;"></i> 
            <?php echo $view === 'item' ? 'Stock — Item Wise' : 'Stock — Department Wise'; ?>
        </h3>
    </div>
    <div class="card-body p-0 overflow-x-auto">
        <?php if ($view === 'item'): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Item</th>
                    <th>Category</th>
                    <th>Department</th>
                    <th>In Stock</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $i => $item): ?>
                <tr>
                    <td><?php echo $i + 1; ?></td>
                    <td><strong><?php echo htmlspecialchars($item['name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($item['category_name']); ?></td>
                    <td><?php echo htmlspecialchars($item['department']); ?></td>
                    <td>
                        <strong style="font-size: 16px; color: <?php echo $item['quantity'] <= 5 ? 'var(--mustard-dark)' : 'var(--navy)'; ?>;">
                            <?php echo $item['quantity']; ?>
                        </strong>
                    </td>
                    <td>
                        <?php if ($item['quantity'] <= 0): ?>
                        <span class="status-inactive">OUT OF STOCK</span>
                        <?php elseif ($item['quantity'] <= 5): ?>
                        <span class="low-stock"><i class="fas fa-exclamation-triangle"></i> LOW STOCK</span>
                        <?php else: ?>
                        <span class="status-active">IN STOCK</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Department</th>
                    <th>Total Items</th>
                    <th>Total Quantity</th>
                    <th>Low Stock Items</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $i => $dept): ?>
                <tr>
                    <td><?php echo $i + 1; ?></td>
                    <td><strong style="color: var(--navy);"><?php echo htmlspecialchars($dept['department']); ?></strong></td>
                    <td><?php echo $dept['total_items']; ?></td>
                    <td><strong><?php echo $dept['total_quantity']; ?></strong></td>
                    <td>
                        <?php if ($dept['low_stock_count'] > 0): ?>
                        <span class="low-stock"><?php echo $dept['low_stock_count']; ?> items</span>
                        <?php else: ?>
                        <span class="status-active">All OK</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
