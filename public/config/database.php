<?php
/**
 * MTI_SMS - Database Configuration
 * Compatible with PHP 5.5+ and MySQL 5.5+
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'mti_sms');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8');

/**
 * Database connection class
 */
class Database {
    private static $instance = null;
    private $conn;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            );
            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Database Connection Failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    // Prevent cloning
    private function __clone() {}
}

/**
 * Get database connection
 */
function getDB() {
    return Database::getInstance()->getConnection();
}

/**
 * Execute a query and return results
 */
function dbQuery($sql, $params = array()) {
    $db = getDB();
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

/**
 * Fetch all results
 */
function dbFetchAll($sql, $params = array()) {
    return dbQuery($sql, $params)->fetchAll();
}

/**
 * Fetch single row
 */
function dbFetchOne($sql, $params = array()) {
    return dbQuery($sql, $params)->fetch();
}

/**
 * Get last insert ID
 */
function dbLastId() {
    return getDB()->lastInsertId();
}

/**
 * Get row count
 */
function dbCount($sql, $params = array()) {
    return dbQuery($sql, $params)->rowCount();
}
?>
