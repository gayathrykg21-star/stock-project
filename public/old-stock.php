<?php
/**
 * MTI_SMS - Old Stock Page
 */
$currentPage = 'old-stock';
require_once 'config/database.php';
require_once 'config/session.php';

// Require appropriate role
requireRole('STOCK_ADMIN,HOD,DEPT_IN_CHARGE');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemId = intval($_POST['item_id']);
    $qty = intval($_POST['qty']);
    $reason = trim($_POST['reason']);
    $logDate = $_POST['log_date'];
    
    if (empty($itemId) || $qty < 1) {
        setMessage('Select item and enter quantity!', 'error');
    } else {
        $item = dbFetchOne("SELECT * FROM items WHERE id = ?", array($itemId));
        if (!$item) {
            setMessage('Item not found!', 'error');
        } elseif ($qty > $item['quantity']) {
            setMessage('Cannot mark more than available stock!', 'error');
        } else {
            // Update item quantity
            dbQuery("UPDATE items SET quantity = quantity - ? WHERE id = ?", array($qty, $itemId));
            // Insert log
            dbQuery("INSERT INTO old_stock (item_id, qty, reason, log_date, created_by) VALUES (?, ?, ?, ?, ?)",
                array($itemId, $qty, $reason, $logDate ?: date('Y-m-d'), getUsername()));
            setMessage("$qty units of {$item['name']} marked as $reason.", 'success');
        }
    }
    
    header('Location: old-stock.php');
    exit;
}

// Fetch data
$items = dbFetchAll("SELECT * FROM items WHERE quantity > 0 ORDER BY name ASC");
$logs = dbFetchAll("SELECT os.*, i.name as item_name 
    FROM old_stock os 
    LEFT JOIN items i ON os.item_id = i.id 
    ORDER BY os.id DESC LIMIT 50");

require_once 'includes/header.php';
?>

<!-- Old Stock Form -->
<div class="card mb-24">
    <div class="card-header">
        <h3><i class="fas fa-archive" style="color: var(--mustard); margin-right: 8px;"></i> Register Old / Damaged Stock</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="">
            <div class="form-grid">
                <div class="form-group">
                    <label>Select Item *</label>
                    <select name="item_id" required>
                        <option value="">-- Select Item --</option>
                        <?php foreach ($items as $item): ?>
                        <option value="<?php echo $item['id']; ?>"><?php echo htmlspecialchars($item['name']); ?> (Qty: <?php echo $item['quantity']; ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Quantity *</label>
                    <input type="number" name="qty" placeholder="Qty" min="1" required>
                </div>
                <div class="form-group">
                    <label>Reason *</label>
                    <select name="reason" required>
                        <option value="Damaged">Damaged</option>
                        <option value="Obsolete">Obsolete</option>
                        <option value="Expired">Expired</option>
                        <option value="Non-functional">Non-functional</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" name="log_date" value="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="full-width text-right">
                    <button type="submit" class="btn btn-danger"><i class="fas fa-archive"></i> Register Old Stock</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Old Stock Logs -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-list" style="color: var(--mustard); margin-right: 8px;"></i> Old Stock Records</h3>
    </div>
    <div class="card-body p-0 overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Reason</th>
                    <th>Date</th>
                    <th>Recorded By</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $i => $log): ?>
                <tr>
                    <td><?php echo $i + 1; ?></td>
                    <td><strong><?php echo htmlspecialchars($log['item_name']); ?></strong></td>
                    <td><?php echo $log['qty']; ?></td>
                    <td><span class="reason-badge"><?php echo htmlspecialchars($log['reason']); ?></span></td>
                    <td><?php echo $log['log_date']; ?></td>
                    <td><?php echo htmlspecialchars($log['created_by']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
