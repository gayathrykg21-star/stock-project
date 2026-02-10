<?php
/**
 * MTI_SMS - Item Requests Page
 */
$currentPage = 'requests';
require_once 'config/database.php';
require_once 'config/session.php';
requireLogin();

$user = getCurrentUser();
$isAdmin = in_array($user['role'], array('STOCK_ADMIN', 'HOD'));

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    if ($action === 'submit') {
        $itemId = intval($_POST['item_id']);
        $qty = intval($_POST['qty']);
        $reason = trim($_POST['reason']);
        
        if (empty($itemId) || $qty < 1 || empty($reason)) {
            setMessage('Fill all request fields!', 'error');
        } else {
            dbQuery("INSERT INTO item_requests (item_id, qty, requested_by, reason, request_date, status) VALUES (?, ?, ?, ?, ?, 'pending')",
                array($itemId, $qty, getUsername(), $reason, date('Y-m-d')));
            setMessage('Request submitted successfully!', 'success');
        }
    } elseif ($action === 'approve' && $isAdmin) {
        $requestId = intval($_POST['request_id']);
        $request = dbFetchOne("SELECT * FROM item_requests WHERE id = ? AND status = 'pending'", array($requestId));
        
        if ($request) {
            $item = dbFetchOne("SELECT * FROM items WHERE id = ?", array($request['item_id']));
            if (!$item) {
                setMessage('Item not found!', 'error');
            } elseif ($request['qty'] > $item['quantity']) {
                setMessage("Insufficient stock! Available: {$item['quantity']}", 'error');
            } else {
                // Update request status
                dbQuery("UPDATE item_requests SET status = 'approved', approved_by = ?, approved_at = NOW() WHERE id = ?",
                    array(getUsername(), $requestId));
                
                // Update item quantity
                dbQuery("UPDATE items SET quantity = quantity - ? WHERE id = ?", array($request['qty'], $request['item_id']));
                
                // Generate dispatch code
                $counter = dbFetchOne("SELECT counter_value FROM dispatch_counter WHERE id = 1");
                $newCounter = $counter['counter_value'] + 1;
                dbQuery("UPDATE dispatch_counter SET counter_value = ? WHERE id = 1", array($newCounter));
                $dispatchCode = 'REQ-' . str_pad($newCounter, 3, '0', STR_PAD_LEFT);
                
                // Create stock out log
                dbQuery("INSERT INTO stock_out_logs (item_id, qty, issued_to, dispatch_code, log_date, remarks, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)",
                    array($request['item_id'], $request['qty'], "Request #{$request['id']} by {$request['requested_by']}", 
                          $dispatchCode, date('Y-m-d'), $request['reason'], getUsername()));
                
                setMessage("Request #{$requestId} approved. Stock updated.", 'success');
            }
        }
    } elseif ($action === 'reject' && $isAdmin) {
        $requestId = intval($_POST['request_id']);
        dbQuery("UPDATE item_requests SET status = 'rejected', approved_by = ?, approved_at = NOW() WHERE id = ? AND status = 'pending'",
            array(getUsername(), $requestId));
        setMessage("Request #{$requestId} rejected.", 'warning');
    }
    
    header('Location: requests.php');
    exit;
}

// Fetch data
$items = dbFetchAll("SELECT * FROM items ORDER BY name ASC");

if ($isAdmin) {
    $requests = dbFetchAll("SELECT ir.*, i.name as item_name 
        FROM item_requests ir 
        LEFT JOIN items i ON ir.item_id = i.id 
        ORDER BY ir.id DESC");
} else {
    $requests = dbFetchAll("SELECT ir.*, i.name as item_name 
        FROM item_requests ir 
        LEFT JOIN items i ON ir.item_id = i.id 
        WHERE ir.requested_by = ?
        ORDER BY ir.id DESC", array(getUsername()));
}

require_once 'includes/header.php';
?>

<!-- Submit Request Form -->
<div class="card mb-24">
    <div class="card-header">
        <h3><i class="fas fa-paper-plane" style="color: var(--mustard); margin-right: 8px;"></i> Submit Item Request</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="">
            <input type="hidden" name="action" value="submit">
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
                    <label>Quantity Needed *</label>
                    <input type="number" name="qty" placeholder="Qty" min="1" required>
                </div>
                <div class="form-group full-width">
                    <label>Reason / Purpose *</label>
                    <input type="text" name="reason" placeholder="Why do you need this item?" required>
                </div>
                <div class="full-width text-right">
                    <button type="submit" class="btn btn-mustard"><i class="fas fa-paper-plane"></i> Submit Request</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Requests Table -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-inbox" style="color: var(--mustard); margin-right: 8px;"></i> 
            <?php echo $isAdmin ? 'All Item Requests' : 'My Requests'; ?>
        </h3>
        <span class="badge"><?php echo count($requests); ?></span>
    </div>
    <div class="card-body p-0 overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Requested By</th>
                    <th>Reason</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $i => $req): ?>
                <tr>
                    <td><?php echo $i + 1; ?></td>
                    <td><strong><?php echo htmlspecialchars($req['item_name']); ?></strong></td>
                    <td><?php echo $req['qty']; ?></td>
                    <td><?php echo htmlspecialchars($req['requested_by']); ?></td>
                    <td><?php echo htmlspecialchars($req['reason']); ?></td>
                    <td><?php echo $req['request_date']; ?></td>
                    <td>
                        <?php if ($req['status'] === 'pending'): ?>
                        <span class="status-pending">PENDING</span>
                        <?php elseif ($req['status'] === 'approved'): ?>
                        <span class="status-active">APPROVED</span>
                        <?php else: ?>
                        <span class="status-inactive">REJECTED</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($isAdmin && $req['status'] === 'pending'): ?>
                        <form method="POST" action="" style="display: inline;">
                            <input type="hidden" name="action" value="approve">
                            <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-success" style="margin-right: 4px;">
                                <i class="fas fa-check"></i>
                            </button>
                        </form>
                        <form method="POST" action="" style="display: inline;">
                            <input type="hidden" name="action" value="reject">
                            <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="fas fa-times"></i>
                            </button>
                        </form>
                        <?php else: ?>
                        -
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
