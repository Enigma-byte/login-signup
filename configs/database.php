<?php
require_once __DIR__ . '/config.php';

/**
 * Database Connection Class
 * Handles database connections and operations for the Enigma application
 */
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn = null;
    private static $instance = null;

    /**
     * Private constructor to prevent direct creation
     */
    private function __construct() {
        // Ensure environment variables are loaded
        if (!Config::has('DB_HOST')) {
            Config::load();
        }

        // Load configuration from environment variables
        $this->host = Config::get('DB_HOST', 'localhost');
        $this->db_name = Config::get('DB_NAME', 'Enigma');
        $this->username = Config::get('DB_USER', 'root');
        $this->password = Config::get('DB_PASS', 'admin');
    }

    /**
     * Get singleton instance
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get the database connection
     * @return PDO|null Database connection
     */
    public function getConnection() {
        try {
            if ($this->conn === null) {
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_PERSISTENT => false,
                    PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => true
                ];

                if (getenv('DB_SSL_CA')) {
                    $options[PDO::MYSQL_ATTR_SSL_CA] = getenv('DB_SSL_CA');
                }

                $this->conn = new PDO(
                    "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                    $this->username,
                    $this->password,
                    $options
                );
            }
            return $this->conn;
        } catch(PDOException $e) {
            error_log("Connection Error: " . $e->getMessage());
            throw new Exception("Database connection failed. Please try again later.");
        }
    }

    /**
     * Close the database connection
     */
    public function closeConnection() {
        $this->conn = null;
    }

    /**
     * Execute a query with parameters
     * @param string $query SQL query
     * @param array $params Parameters for the query
     * @return array|false Query results or false on failure
     * @throws Exception on query error
     */
    public function query($query, $params = []) {
        try {
            $stmt = $this->getConnection()->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Query Error: " . $e->getMessage());
            throw new Exception("Database query failed. Please try again later.");
        }
    }

    /**
     * Execute an insert/update/delete query and return affected rows
     * @param string $query SQL query
     * @param array $params Parameters for the query
     * @return int Number of affected rows
     * @throws Exception on query error
     */
    public function execute($query, $params = []) {
        try {
            $stmt = $this->getConnection()->prepare($query);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch(PDOException $e) {
            error_log("Execute Error: " . $e->getMessage());
            throw new Exception("Database operation failed. Please try again later.");
        }
    }

    /**
     * Begin a transaction
     */
    public function beginTransaction() {
        return $this->getConnection()->beginTransaction();
    }

    /**
     * Commit a transaction
     */
    public function commit() {
        return $this->getConnection()->commit();
    }

    /**
     * Rollback a transaction
     */
    public function rollback() {
        return $this->getConnection()->rollBack();
    }

    /**
     * Prevent cloning of the instance
     */
    private function __clone() {}

    /**
     * Prevent unserialize of the instance
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

// Example usage:
/*
try {
    $db = Database::getInstance();

    // Simple query
    $users = $db->query("SELECT * FROM Users WHERE Role = ?", ['Admin']);

    // Transaction example
    $db->beginTransaction();
    try {
        $db->execute("UPDATE Users SET LastLogin = NOW() WHERE UserId = ?", [1]);
        $db->commit();
    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }
} catch (Exception $e) {
    // Handle error
    error_log($e->getMessage());
}
*/
