<?php
/**
 * MTI_SMS - Dispatched / Stock Out Page
 */
$currentPage = 'dispatched';
require_once 'config/database.php';
require_once 'config/session.php';

// Require appropriate role
requireRole('STOCK_ADMIN,DEPT_IN_CHARGE');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemId = intval($_POST['item_id']);
    $qty = intval($_POST['qty']);
    $issuedTo = trim($_POST['issued_to']);
    $logDate = $_POST['log_date'];
    $remarks = trim($_POST['remarks']);
    
    if (empty($itemId) || $qty < 1 || empty($issuedTo)) {
        setMessage('Fill all required fields!', 'error');
    } else {
        $item = dbFetchOne("SELECT * FROM items WHERE id = ?", array($itemId));
        if (!$item) {
            setMessage('Item not found!', 'error');
        } elseif ($qty > $item['quantity']) {
            setMessage("Insufficient stock! Available: {$item['quantity']}", 'error');
        } else {
            // Get and increment dispatch counter
            $counter = dbFetchOne("SELECT counter_value FROM dispatch_counter WHERE id = 1");
            $newCounter = $counter['counter_value'] + 1;
            dbQuery("UPDATE dispatch_counter SET counter_value = ? WHERE id = 1", array($newCounter));
            $dispatchCode = 'DSP-' . str_pad($newCounter, 3, '0', STR_PAD_LEFT);
            
            // Update item quantity
            dbQuery("UPDATE items SET quantity = quantity - ? WHERE id = ?", array($qty, $itemId));
            
            // Insert log
            dbQuery("INSERT INTO stock_out_logs (item_id, qty, issued_to, dispatch_code, log_date, remarks, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)",
                array($itemId, $qty, $issuedTo, $dispatchCode, $logDate ?: date('Y-m-d'), $remarks ?: '-', getUsername()));
            
            setMessage("Dispatched $qty units of {$item['name']} to $issuedTo. Code: $dispatchCode", 'success');
        }
    }
    
    header('Location: dispatched.php');
    exit;
}

// Fetch data
$items = dbFetchAll("SELECT * FROM items WHERE quantity > 0 ORDER BY name ASC");
$logs = dbFetchAll("SELECT sol.*, i.name as item_name 
    FROM stock_out_logs sol 
    LEFT JOIN items i ON sol.item_id = i.id 
    ORDER BY sol.id DESC LIMIT 50");

// Get next dispatch code for display
$counter = dbFetchOne("SELECT counter_value FROM dispatch_counter WHERE id = 1");
$nextCode = 'DSP-' . str_pad($counter['counter_value'] + 1, 3, '0', STR_PAD_LEFT);

require_once 'includes/header.php';
?>

<!-- Dispatch Form -->
<div class="card mb-24">
    <div class="card-header">
        <h3><i class="fas fa-truck" style="color: var(--mustard); margin-right: 8px;"></i> Dispatch / Issue Item (Stock Out)</h3>
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
                    <label>Issued To (Dept/Person) *</label>
                    <input type="text" name="issued_to" placeholder="e.g., Mechanical Dept" required>
                </div>
                <div class="form-group">
                    <label>Dispatch Code</label>
                    <input type="text" value="<?php echo $nextCode; ?>" readonly style="background: #eee;">
                </div>
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" name="log_date" value="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="form-group">
                    <label>Remarks</label>
                    <input type="text" name="remarks" placeholder="Optional">
                </div>
                <div class="full-width text-right">
                    <button type="submit" class="btn btn-danger"><i class="fas fa-paper-plane"></i> Dispatch</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Dispatch Logs -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-list" style="color: var(--mustard); margin-right: 8px;"></i> Dispatch Records</h3>
    </div>
    <div class="card-body p-0 overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Code</th>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Issued To</th>
                    <th>Date</th>
                    <th>By</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $i => $log): ?>
                <tr>
                    <td><?php echo $i + 1; ?></td>
                    <td><strong class="dispatch-code"><?php echo htmlspecialchars($log['dispatch_code']); ?></strong></td>
                    <td><strong><?php echo htmlspecialchars($log['item_name']); ?></strong></td>
                    <td><span class="qty-remove">-<?php echo $log['qty']; ?></span></td>
                    <td><?php echo htmlspecialchars($log['issued_to']); ?></td>
                    <td><?php echo $log['log_date']; ?></td>
                    <td><?php echo htmlspecialchars($log['created_by']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
