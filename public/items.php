<?php
/**
 * MTI_SMS - Item Register Page
 */
$currentPage = 'items';
require_once 'config/database.php';
require_once 'config/session.php';

// Require appropriate role
requireRole('STOCK_ADMIN,HOD,DEPT_IN_CHARGE');

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    if ($action === 'add') {
        $name = trim($_POST['name']);
        $categoryId = intval($_POST['category_id']);
        $subcategoryId = intval($_POST['subcategory_id']);
        $department = trim($_POST['department']);
        $quantity = intval($_POST['quantity']);
        $description = trim($_POST['description']);
        $entryDate = $_POST['entry_date'];
        
        if (empty($name) || empty($categoryId) || empty($department)) {
            setMessage('Fill item name, category, and department!', 'error');
        } else {
            dbQuery("INSERT INTO items (name, category_id, subcategory_id, department, quantity, description, entry_date) VALUES (?, ?, ?, ?, ?, ?, ?)",
                array($name, $categoryId, $subcategoryId ?: null, $department, $quantity, $description, $entryDate ?: date('Y-m-d')));
            setMessage('Item registered successfully!', 'success');
        }
    } elseif ($action === 'delete') {
        $itemId = intval($_POST['item_id']);
        dbQuery("DELETE FROM items WHERE id = ?", array($itemId));
        setMessage('Item deleted.', 'warning');
    }
    
    header('Location: items.php');
    exit;
}

// Fetch data
$categories = dbFetchAll("SELECT * FROM categories ORDER BY name ASC");
$subcategories = dbFetchAll("SELECT * FROM subcategories ORDER BY name ASC");
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if (!empty($search)) {
    $items = dbFetchAll("SELECT i.*, c.name as category_name, s.name as subcategory_name 
        FROM items i 
        LEFT JOIN categories c ON i.category_id = c.id 
        LEFT JOIN subcategories s ON i.subcategory_id = s.id 
        WHERE i.name LIKE ? OR c.name LIKE ? OR i.department LIKE ?
        ORDER BY i.id DESC",
        array("%$search%", "%$search%", "%$search%"));
} else {
    $items = dbFetchAll("SELECT i.*, c.name as category_name, s.name as subcategory_name 
        FROM items i 
        LEFT JOIN categories c ON i.category_id = c.id 
        LEFT JOIN subcategories s ON i.subcategory_id = s.id 
        ORDER BY i.id DESC");
}

// Build subcategory JSON for JavaScript
$subcatJson = array();
foreach ($subcategories as $sc) {
    if (!isset($subcatJson[$sc['category_id']])) {
        $subcatJson[$sc['category_id']] = array();
    }
    $subcatJson[$sc['category_id']][] = array('id' => $sc['id'], 'name' => $sc['name']);
}

require_once 'includes/header.php';
?>

<!-- Add Item Form -->
<div class="card mb-24">
    <div class="card-header">
        <h3><i class="fas fa-plus-circle" style="color: var(--mustard); margin-right: 8px;"></i> Register New Item</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="">
            <input type="hidden" name="action" value="add">
            <div class="form-grid">
                <div class="form-group">
                    <label>Item Name *</label>
                    <input type="text" name="name" placeholder="Enter item name" required>
                </div>
                <div class="form-group">
                    <label>Category *</label>
                    <select name="category_id" id="categorySelect" onchange="updateSubcategories()" required>
                        <option value="">-- Select --</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Subcategory</label>
                    <select name="subcategory_id" id="subcategorySelect">
                        <option value="">-- Select --</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Department *</label>
                    <select name="department" required>
                        <option value="">-- Select --</option>
                        <option value="Computer Science">Computer Science</option>
                        <option value="Mechanical">Mechanical</option>
                        <option value="Electrical">Electrical</option>
                        <option value="Civil">Civil</option>
                        <option value="Electronics">Electronics</option>
                        <option value="Admin">Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Initial Quantity</label>
                    <input type="number" name="quantity" placeholder="0" min="0" value="0">
                </div>
                <div class="form-group">
                    <label>Entry Date</label>
                    <input type="date" name="entry_date" value="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="form-group full-width">
                    <label>Description</label>
                    <input type="text" name="description" placeholder="Brief description">
                </div>
                <div class="full-width text-right">
                    <button type="submit" class="btn btn-mustard"><i class="fas fa-save"></i> Register Item</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Items Table -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-clipboard-list" style="color: var(--mustard); margin-right: 8px;"></i> Registered Items</h3>
        <span class="badge"><?php echo count($items); ?></span>
    </div>
    <div class="card-body" style="padding: 0 0 8px 0;">
        <div class="search-bar" style="padding: 16px 16px 0;">
            <form method="GET" action="" style="display: flex; gap: 12px; flex: 1;">
                <input type="text" name="search" placeholder="Search items..." value="<?php echo htmlspecialchars($search); ?>" style="flex: 1;">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
                <?php if (!empty($search)): ?>
                <a href="items.php" class="btn btn-danger btn-sm"><i class="fas fa-times"></i></a>
                <?php endif; ?>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Item Name</th>
                        <th>Category</th>
                        <th>Subcategory</th>
                        <th>Dept</th>
                        <th>Qty</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $i => $item): ?>
                    <tr>
                        <td><?php echo $i + 1; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                            <?php if (!empty($item['description'])): ?>
                            <br><small style="color: var(--light-text);"><?php echo htmlspecialchars($item['description']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($item['category_name']); ?></td>
                        <td><?php echo htmlspecialchars($item['subcategory_name'] ?: '-'); ?></td>
                        <td><?php echo htmlspecialchars($item['department']); ?></td>
                        <td>
                            <?php if ($item['quantity'] <= 5): ?>
                            <span class="low-stock"><i class="fas fa-exclamation-triangle"></i> <?php echo $item['quantity']; ?></span>
                            <?php else: ?>
                            <strong><?php echo $item['quantity']; ?></strong>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $item['entry_date']; ?></td>
                        <td>
                            <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Delete this item?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
var subcategories = <?php echo json_encode($subcatJson); ?>;

function updateSubcategories() {
    var catId = document.getElementById('categorySelect').value;
    var subSelect = document.getElementById('subcategorySelect');
    subSelect.innerHTML = '<option value="">-- Select --</option>';
    
    if (catId && subcategories[catId]) {
        subcategories[catId].forEach(function(sub) {
            var opt = document.createElement('option');
            opt.value = sub.id;
            opt.textContent = sub.name;
            subSelect.appendChild(opt);
        });
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>
