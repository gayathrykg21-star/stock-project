<?php
/**
 * MTI_SMS - Session Management
 * Compatible with PHP 5.5+
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user data
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    return array(
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'full_name' => $_SESSION['full_name'],
        'role' => $_SESSION['role'],
        'department' => $_SESSION['department']
    );
}

/**
 * Get current username
 */
function getUsername() {
    return isset($_SESSION['username']) ? $_SESSION['username'] : '';
}

/**
 * Get current user's full name
 */
function getFullName() {
    return isset($_SESSION['full_name']) ? $_SESSION['full_name'] : '';
}

/**
 * Get current user's role
 */
function getRole() {
    return isset($_SESSION['role']) ? $_SESSION['role'] : '';
}

/**
 * Check if current user has access to a feature
 */
function hasAccess($allowedRoles) {
    if (empty($allowedRoles)) {
        return true;
    }
    $roles = is_array($allowedRoles) ? $allowedRoles : explode(',', $allowedRoles);
    return in_array(getRole(), $roles);
}

/**
 * Require login - redirect to login page if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: index.php');
        exit;
    }
}

/**
 * Require specific role(s)
 */
function requireRole($allowedRoles) {
    requireLogin();
    if (!hasAccess($allowedRoles)) {
        $_SESSION['error'] = 'Access denied for your role!';
        header('Location: dashboard.php');
        exit;
    }
}

/**
 * Set session flash message
 */
function setMessage($message, $type = 'success') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

/**
 * Get and clear flash message
 */
function getMessage() {
    if (isset($_SESSION['flash_message'])) {
        $msg = array(
            'message' => $_SESSION['flash_message'],
            'type' => isset($_SESSION['flash_type']) ? $_SESSION['flash_type'] : 'success'
        );
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        return $msg;
    }
    return null;
}

/**
 * Login user
 */
function loginUser($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['department'] = $user['department'];
}

/**
 * Logout user
 */
function logoutUser() {
    session_unset();
    session_destroy();
}

/**
 * Get user initials for avatar
 */
function getUserInitials() {
    $name = getFullName();
    $parts = explode(' ', $name);
    $initials = '';
    foreach ($parts as $part) {
        $initials .= strtoupper(substr($part, 0, 1));
    }
    return substr($initials, 0, 2);
}
?>
