<?php

// Database configuration
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        // Database credentials for LOCAL database (tripistry_DB)
        $host = 'localhost';           // XAMPP MySQL runs on localhost
        $username = 'root';            // Default XAMPP username
        $password = '';                // Default XAMPP password is empty
        $database = 'tripistry_DB';    // Your database name
        
        // If you changed MySQL port to 3307 (Solution 2), uncomment this:
        // $host = 'localhost:3307';
        
        try {
            $this->connection = new mysqli($host, $username, $password, $database);
            
            // Check connection
            if ($this->connection->connect_error) {
                throw new Exception("Connection failed: " . $this->connection->connect_error);
            }
            
            // Set charset to UTF-8
            $this->connection->set_charset("utf8mb4");
            
            //echo "Successfully connected to tripistry_DB database!";
            
        } catch (Exception $e) {
            die("Database connection error: " . $e->getMessage());
        }
    }
    
    // Singleton pattern to ensure single database connection
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
}

// Start session for user management (will be used in PA4)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Global functions for PA4 (keep for now, will be used later)
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['api_key']);
}

function getCurrentUser() {
    if (isLoggedIn()) {
        return [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'],
            'email' => $_SESSION['user_email'],
            'api_key' => $_SESSION['api_key']
        ];
    }
    return null;
}

// Define base URL for paths - UPDATE THIS to match your XAMPP setup
define('BASE_URL', 'http://localhost/tripistry');  // Change this path as needed
define('ROOT_PATH', __DIR__ . '/..');
?>