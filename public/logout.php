<?php
/**
 * MTI_SMS - Logout Handler
 */
require_once 'config/session.php';

logoutUser();
header('Location: index.php');
exit;
?>
