<?php
/**
 * MTI_SMS - User Management Page
 */
$currentPage = 'users';
require_once 'config/database.php';
require_once 'config/session.php';

// Require STOCK_ADMIN role
requireRole('STOCK_ADMIN');

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    if ($action === 'add') {
        $fullName = trim($_POST['full_name']);
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
        $email = trim($_POST['email']);
        $role = trim($_POST['role']);
        $department = trim($_POST['department']);
        
        if (empty($fullName) || empty($username) || empty($password) || empty($role) || empty($department)) {
            setMessage('Please fill all required fields!', 'error');
        } else {
            // Check if username exists
            $exists = dbFetchOne("SELECT id FROM users WHERE username = ?", array($username));
            if ($exists) {
                setMessage('Username already exists!', 'error');
            } else {
                dbQuery("INSERT INTO users (full_name, username, password, email, role, department, status) VALUES (?, ?, ?, ?, ?, ?, 'active')",
                    array($fullName, $username, $password, $email, $role, $department));
                setMessage('User added successfully!', 'success');
            }
        }
    } elseif ($action === 'toggle') {
        $userId = intval($_POST['user_id']);
        $user = dbFetchOne("SELECT * FROM users WHERE id = ?", array($userId));
        if ($user) {
            $newStatus = $user['status'] === 'active' ? 'inactive' : 'active';
            dbQuery("UPDATE users SET status = ? WHERE id = ?", array($newStatus, $userId));
            setMessage("User {$user['full_name']} " . ($newStatus === 'active' ? 'activated' : 'deactivated') . ".", $newStatus === 'active' ? 'success' : 'warning');
        }
    }
    
    header('Location: users.php');
    exit;
}

// Fetch all users
$users = dbFetchAll("SELECT * FROM users ORDER BY id ASC");

require_once 'includes/header.php';
?>

<!-- Add User Form -->
<div class="card mb-24">
    <div class="card-header">
        <h3><i class="fas fa-user-plus" style="color: var(--mustard); margin-right: 8px;"></i> Add New User</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="">
            <input type="hidden" name="action" value="add">
            <div class="form-grid">
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="full_name" placeholder="Enter full name" required>
                </div>
                <div class="form-group">
                    <label>Username *</label>
                    <input type="text" name="username" placeholder="Enter username" required>
                </div>
                <div class="form-group">
                    <label>Password *</label>
                    <input type="password" name="password" placeholder="Enter password" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="Enter email">
                </div>
                <div class="form-group">
                    <label>Role *</label>
                    <select name="role" required>
                        <option value="">-- Select Role --</option>
                        <option value="STOCK_ADMIN">STOCK_ADMIN</option>
                        <option value="HOD">HOD</option>
                        <option value="DEPT_IN_CHARGE">DEPT_IN_CHARGE</option>
                        <option value="STAFF">STAFF</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Department *</label>
                    <select name="department" required>
                        <option value="">-- Select Department --</option>
                        <option value="Computer Science">Computer Science</option>
                        <option value="Mechanical">Mechanical</option>
                        <option value="Electrical">Electrical</option>
                        <option value="Civil">Civil</option>
                        <option value="Electronics">Electronics</option>
                        <option value="Admin">Admin</option>
                    </select>
                </div>
                <div class="full-width text-right">
                    <button type="submit" class="btn btn-mustard"><i class="fas fa-save"></i> Save User</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Users Table -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-users" style="color: var(--mustard); margin-right: 8px;"></i> All Users</h3>
        <span class="badge"><?php echo count($users); ?></span>
    </div>
    <div class="card-body p-0 overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Full Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Department</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $i => $u): ?>
                <tr>
                    <td><?php echo $i + 1; ?></td>
                    <td><strong><?php echo htmlspecialchars($u['full_name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($u['username']); ?></td>
                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                    <td><span class="role-badge"><?php echo htmlspecialchars($u['role']); ?></span></td>
                    <td><?php echo htmlspecialchars($u['department']); ?></td>
                    <td><span class="status-<?php echo $u['status']; ?>"><?php echo strtoupper($u['status']); ?></span></td>
                    <td>
                        <form method="POST" action="" style="display: inline;">
                            <input type="hidden" name="action" value="toggle">
                            <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                            <button type="submit" class="btn btn-sm <?php echo $u['status'] === 'active' ? 'btn-danger' : 'btn-success'; ?>">
                                <i class="fas fa-<?php echo $u['status'] === 'active' ? 'ban' : 'check'; ?>"></i>
                                <?php echo $u['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
