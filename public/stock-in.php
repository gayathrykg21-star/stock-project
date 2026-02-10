<?php
/**
 * MTI_SMS - Stock In Page
 */
$currentPage = 'stock-in';
require_once 'config/database.php';
require_once 'config/session.php';

// Require appropriate role
requireRole('STOCK_ADMIN,DEPT_IN_CHARGE');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemId = intval($_POST['item_id']);
    $qty = intval($_POST['qty']);
    $logDate = $_POST['log_date'];
    $remarks = trim($_POST['remarks']);
    
    if (empty($itemId) || $qty < 1) {
        setMessage('Select item and enter valid quantity!', 'error');
    } else {
        $item = dbFetchOne("SELECT * FROM items WHERE id = ?", array($itemId));
        if (!$item) {
            setMessage('Item not found!', 'error');
        } else {
            // Update item quantity
            dbQuery("UPDATE items SET quantity = quantity + ? WHERE id = ?", array($qty, $itemId));
            // Insert log
            dbQuery("INSERT INTO stock_in_logs (item_id, qty, log_date, remarks, created_by) VALUES (?, ?, ?, ?, ?)",
                array($itemId, $qty, $logDate ?: date('Y-m-d'), $remarks ?: 'Stock added', getUsername()));
            $newQty = $item['quantity'] + $qty;
            setMessage("Added $qty units to {$item['name']}. New stock: $newQty", 'success');
        }
    }
    
    header('Location: stock-in.php');
    exit;
}

// Fetch data
$items = dbFetchAll("SELECT * FROM items ORDER BY name ASC");
$logs = dbFetchAll("SELECT sl.*, i.name as item_name 
    FROM stock_in_logs sl 
    LEFT JOIN items i ON sl.item_id = i.id 
    ORDER BY sl.id DESC LIMIT 50");

require_once 'includes/header.php';
?>

<!-- Stock In Form -->
<div class="card mb-24">
    <div class="card-header">
        <h3><i class="fas fa-arrow-down" style="color: var(--mustard); margin-right: 8px;"></i> Stock In — Add Quantity</h3>
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
                    <label>Quantity to Add *</label>
                    <input type="number" name="qty" placeholder="Enter quantity" min="1" required>
                </div>
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" name="log_date" value="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="form-group">
                    <label>Remarks</label>
                    <input type="text" name="remarks" placeholder="Optional remarks">
                </div>
                <div class="full-width text-right">
                    <button type="submit" class="btn btn-success"><i class="fas fa-plus-circle"></i> Add Stock</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Stock In Logs -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-history" style="color: var(--mustard); margin-right: 8px;"></i> Stock In Logs</h3>
    </div>
    <div class="card-body p-0 overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Item</th>
                    <th>Qty Added</th>
                    <th>Date</th>
                    <th>Remarks</th>
                    <th>By</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $i => $log): ?>
                <tr>
                    <td><?php echo $i + 1; ?></td>
                    <td><strong><?php echo htmlspecialchars($log['item_name']); ?></strong></td>
                    <td><span class="qty-add">+<?php echo $log['qty']; ?></span></td>
                    <td><?php echo $log['log_date']; ?></td>
                    <td><?php echo htmlspecialchars($log['remarks']); ?></td>
                    <td><?php echo htmlspecialchars($log['created_by']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
