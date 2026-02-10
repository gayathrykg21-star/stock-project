<?php
/**
 * MTI_SMS - Category Management Page
 */
$currentPage = 'categories';
require_once 'config/database.php';
require_once 'config/session.php';

// Require STOCK_ADMIN or HOD role
requireRole('STOCK_ADMIN,HOD');

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    if ($action === 'add_category') {
        $name = trim($_POST['category_name']);
        if (empty($name)) {
            setMessage('Enter category name!', 'error');
        } else {
            $exists = dbFetchOne("SELECT id FROM categories WHERE LOWER(name) = LOWER(?)", array($name));
            if ($exists) {
                setMessage('Category already exists!', 'error');
            } else {
                dbQuery("INSERT INTO categories (name) VALUES (?)", array($name));
                setMessage('Category added!', 'success');
            }
        }
    } elseif ($action === 'add_subcategory') {
        $categoryId = intval($_POST['category_id']);
        $name = trim($_POST['subcategory_name']);
        if (empty($categoryId) || empty($name)) {
            setMessage('Select category and enter subcategory name!', 'error');
        } else {
            dbQuery("INSERT INTO subcategories (category_id, name) VALUES (?, ?)", array($categoryId, $name));
            setMessage('Subcategory added!', 'success');
        }
    } elseif ($action === 'delete_category') {
        $categoryId = intval($_POST['category_id']);
        // Check if items exist
        $hasItems = dbFetchOne("SELECT id FROM items WHERE category_id = ? LIMIT 1", array($categoryId));
        if ($hasItems) {
            setMessage('Cannot delete — items exist in this category!', 'error');
        } else {
            dbQuery("DELETE FROM subcategories WHERE category_id = ?", array($categoryId));
            dbQuery("DELETE FROM categories WHERE id = ?", array($categoryId));
            setMessage('Category deleted.', 'warning');
        }
    }
    
    header('Location: categories.php');
    exit;
}

// Fetch categories
$categories = dbFetchAll("SELECT * FROM categories ORDER BY id ASC");
$subcategories = dbFetchAll("SELECT * FROM subcategories ORDER BY category_id, id ASC");
$itemCounts = dbFetchAll("SELECT category_id, COUNT(*) as cnt FROM items GROUP BY category_id");

// Map item counts
$itemCountMap = array();
foreach ($itemCounts as $ic) {
    $itemCountMap[$ic['category_id']] = $ic['cnt'];
}

// Map subcategories
$subcatMap = array();
foreach ($subcategories as $sc) {
    if (!isset($subcatMap[$sc['category_id']])) {
        $subcatMap[$sc['category_id']] = array();
    }
    $subcatMap[$sc['category_id']][] = $sc;
}

require_once 'includes/header.php';
?>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
    <!-- Add Category -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-folder-plus" style="color: var(--mustard); margin-right: 8px;"></i> Add Category</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <input type="hidden" name="action" value="add_category">
                <div class="form-group">
                    <label>Category Name</label>
                    <input type="text" name="category_name" placeholder="e.g., Electronics" required>
                </div>
                <button type="submit" class="btn btn-mustard"><i class="fas fa-plus"></i> Add Category</button>
            </form>
        </div>
    </div>
    
    <!-- Add Subcategory -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-folder-tree" style="color: var(--mustard); margin-right: 8px;"></i> Add Subcategory</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <input type="hidden" name="action" value="add_subcategory">
                <div class="form-group">
                    <label>Parent Category</label>
                    <select name="category_id" required>
                        <option value="">-- Select --</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Subcategory Name</label>
                    <input type="text" name="subcategory_name" placeholder="e.g., Resistors" required>
                </div>
                <button type="submit" class="btn btn-mustard"><i class="fas fa-plus"></i> Add Subcategory</button>
            </form>
        </div>
    </div>
</div>

<!-- Categories Table -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-layer-group" style="color: var(--mustard); margin-right: 8px;"></i> Categories & Subcategories</h3>
    </div>
    <div class="card-body p-0 overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Category</th>
                    <th>Subcategories</th>
                    <th>Items Count</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $i => $cat): ?>
                <?php 
                    $subs = isset($subcatMap[$cat['id']]) ? $subcatMap[$cat['id']] : array();
                    $itemCount = isset($itemCountMap[$cat['id']]) ? $itemCountMap[$cat['id']] : 0;
                ?>
                <tr>
                    <td><?php echo $i + 1; ?></td>
                    <td><strong style="color: var(--navy);"><?php echo htmlspecialchars($cat['name']); ?></strong></td>
                    <td>
                        <?php if (count($subs) > 0): ?>
                            <?php foreach ($subs as $sub): ?>
                            <span class="subcat-badge"><?php echo htmlspecialchars($sub['name']); ?></span>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span style="color: #aaa;">None</span>
                        <?php endif; ?>
                    </td>
                    <td><span class="count-badge"><?php echo $itemCount; ?></span></td>
                    <td>
                        <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Delete this category?');">
                            <input type="hidden" name="action" value="delete_category">
                            <input type="hidden" name="category_id" value="<?php echo $cat['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
