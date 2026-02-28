<?php
// Only start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

class Auth {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function login($username, $password) {
        $sql = "SELECT id, username, password, role, full_name FROM users WHERE username = ? OR email = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['role'] = $row['role'];
                $_SESSION['full_name'] = $row['full_name'];
                $_SESSION['logged_in'] = true;
                return true;
            }
        }
        return false;
    }
    
    public function register($data) {
        // Check if username or email already exists
        $check_sql = "SELECT id FROM users WHERE username = ? OR email = ?";
        $check_stmt = $this->conn->prepare($check_sql);
        $check_stmt->bind_param("ss", $data['username'], $data['email']);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            return false; // User already exists
        }
        
        $sql = "INSERT INTO users (username, email, password, role, full_name) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt->bind_param("sssss", 
            $data['username'], 
            $data['email'], 
            $hashedPassword, 
            $data['role'], 
            $data['full_name']
        );
        
        if ($stmt->execute()) {
            // Auto login after registration
            $_SESSION['user_id'] = $stmt->insert_id;
            $_SESSION['username'] = $data['username'];
            $_SESSION['role'] = $data['role'];
            $_SESSION['full_name'] = $data['full_name'];
            $_SESSION['logged_in'] = true;
            return true;
        }
        return false;
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['user_id']) && isset($_SESSION['logged_in']);
    }
    
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header("Location: " . BASE_PATH . "login.php");
            exit();
        }
    }
    
    public function requireRole($role) {
        if ($_SESSION['role'] !== $role) {
            header("Location: " . BASE_PATH . $_SESSION['role'] . "/dashboard.php");
            exit();
        }
    }
    
    public function logout() {
        session_destroy();
        header("Location: " . BASE_PATH . "index.php");
        exit();
    }
    
    public function getCurrentUser() {
        if ($this->isLoggedIn()) {
            // Verify user still exists in database
            $sql = "SELECT id, username, role, full_name FROM users WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                return [
                    'id' => $row['id'],
                    'username' => $row['username'],
                    'role' => $row['role'],
                    'full_name' => $row['full_name']
                ];
            } else {
                // User doesn't exist in database, clear session
                $this->logout();
                return null;
            }
        }
        return null;
    }
    
    /**
     * Check if user has subscription access for a specific action
     */
    public function checkSubscriptionAccess($action, $class_id = null) {
        // First check if user is logged in
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        // Include subscription class
        require_once __DIR__ . '/subscription.php';
        
        $subscription = new Subscription($this->conn, $_SESSION['user_id']);
        
        switch ($action) {
            case 'join_class':
                return $subscription->canJoinLiveClass($class_id);
            case 'create_class':
                return $subscription->canCreateClass();
            default:
                return false;
        }
    }
    
    /**
     * Record subscription usage
     */
    public function recordSubscriptionUsage($action, $class_id = null) {
        // First check if user is logged in
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        // Include subscription class
        require_once __DIR__ . '/subscription.php';
        
        $subscription = new Subscription($this->conn, $_SESSION['user_id']);
        return $subscription->recordUsage($action, $class_id);
    }
    
    /**
     * Get user's current subscription
     */
    public function getCurrentSubscription() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        require_once __DIR__ . '/subscription.php';
        
        $subscription = new Subscription($this->conn, $_SESSION['user_id']);
        return $subscription->getCurrentSubscription();
    }
}

// Initialize auth
$auth = new Auth($conn);
?>