<?php
require_once __DIR__ . '/../config/database.php';

class Subscription {
    private $conn;
    private $user_id;
    
    public function __construct($db, $user_id) {
        $this->conn = $db;
        $this->user_id = $user_id;
    }
    
    /**
     * Get user's current active subscription
     */
    public function getCurrentSubscription() {
        $sql = "SELECT us.*, sp.name as plan_name, sp.price, sp.max_classes_per_day, 
                       sp.can_create_classes, sp.can_join_live, sp.features
                FROM user_subscriptions us
                JOIN subscription_plans sp ON us.plan_id = sp.id
                WHERE us.user_id = ? 
                AND us.status = 'active' 
                AND us.end_date >= CURDATE()
                ORDER BY us.end_date DESC
                LIMIT 1";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $this->user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $row['features'] = json_decode($row['features'], true);
            return $row;
        }
        
        return null;
    }
    
    /**
     * Check if user can join a live class
     */
    public function canJoinLiveClass($class_id = null) {
        $subscription = $this->getCurrentSubscription();
        
        // If no active subscription, check free tier
        if (!$subscription) {
            return $this->checkFreeTierAccess('join');
        }
        
        // Check if plan allows joining live classes
        if (!$subscription['can_join_live']) {
            return false;
        }
        
        // Check daily limit if applicable
        if ($subscription['max_classes_per_day'] !== null) {
            $today_usage = $this->getTodayUsage('join_class');
            if ($today_usage >= $subscription['max_classes_per_day']) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Check if user can create a class (for instructors)
     */
    public function canCreateClass() {
        $subscription = $this->getCurrentSubscription();
        
        if (!$subscription) {
            return $this->checkFreeTierAccess('create');
        }
        
        return $subscription['can_create_classes'];
    }
    
    /**
     * Record usage of subscription
     */
    public function recordUsage($action_type, $class_id = null) {
        $subscription = $this->getCurrentSubscription();
        
        if (!$subscription) {
            return false;
        }
        
        $sql = "INSERT INTO subscription_usage (user_id, subscription_id, class_id, action_type, action_date) 
                VALUES (?, ?, ?, ?, CURDATE())";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiis", $this->user_id, $subscription['id'], $class_id, $action_type);
        return $stmt->execute();
    }
    
    /**
     * Get today's usage count for a specific action
     */
    public function getTodayUsage($action_type) {
        $sql = "SELECT COUNT(*) as count 
                FROM subscription_usage 
                WHERE user_id = ? 
                AND action_type = ? 
                AND action_date = CURDATE()";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("is", $this->user_id, $action_type);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['count'] ?? 0;
    }
    
    /**
     * Check free tier access
     */
    private function checkFreeTierAccess($action) {
        // Get free plan
        $sql = "SELECT * FROM subscription_plans WHERE name = 'Free' AND is_active = 1 LIMIT 1";
        $result = $this->conn->query($sql);
        $free_plan = $result->fetch_assoc();
        
        if (!$free_plan) {
            return false;
        }
        
        if ($action === 'join') {
            // Check monthly limit for free tier (from features)
            $features = json_decode($free_plan['features'], true);
            $monthly_limit = $features['max_classes_per_month'] ?? 5;
            
            // Get this month's usage
            $sql = "SELECT COUNT(*) as count 
                    FROM subscription_usage 
                    WHERE user_id = ? 
                    AND action_type = 'join_class' 
                    AND MONTH(action_date) = MONTH(CURDATE())
                    AND YEAR(action_date) = YEAR(CURDATE())";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $this->user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return ($row['count'] < $monthly_limit);
        }
        
        return false;
    }
    
    /**
     * Get available subscription plans
     */
    public static function getAvailablePlans($conn) {
        $sql = "SELECT * FROM subscription_plans WHERE is_active = 1 ORDER BY price ASC";
        $result = $conn->query($sql);
        
        $plans = [];
        while ($row = $result->fetch_assoc()) {
            $row['features'] = json_decode($row['features'], true);
            $plans[] = $row;
        }
        
        return $plans;
    }
    
    /**
     * Subscribe user to a plan
     */
    public function subscribe($plan_id, $payment_method = null, $transaction_id = null) {
        // Get plan details
        $sql = "SELECT * FROM subscription_plans WHERE id = ? AND is_active = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $plan_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $plan = $result->fetch_assoc();
        
        if (!$plan) {
            return ['success' => false, 'message' => 'Invalid subscription plan'];
        }
        
        // Calculate dates
        $start_date = date('Y-m-d');
        $end_date = date('Y-m-d', strtotime("+{$plan['duration_days']} days"));
        
        // Expire any existing active subscriptions
        $expire_sql = "UPDATE user_subscriptions SET status = 'expired' 
                       WHERE user_id = ? AND status = 'active'";
        $expire_stmt = $this->conn->prepare($expire_sql);
        $expire_stmt->bind_param("i", $this->user_id);
        $expire_stmt->execute();
        
        // Create new subscription
        $insert_sql = "INSERT INTO user_subscriptions 
                       (user_id, plan_id, start_date, end_date, payment_method, transaction_id, status) 
                       VALUES (?, ?, ?, ?, ?, ?, 'active')";
        
        $insert_stmt = $this->conn->prepare($insert_sql);
        $insert_stmt->bind_param("iissss", 
            $this->user_id, $plan_id, $start_date, $end_date, 
            $payment_method, $transaction_id
        );
        
        if ($insert_stmt->execute()) {
            // Update user's current subscription
            $update_user_sql = "UPDATE users SET current_subscription_id = ? WHERE id = ?";
            $update_user_stmt = $this->conn->prepare($update_user_sql);
            $subscription_id = $insert_stmt->insert_id;
            $update_user_stmt->bind_param("ii", $subscription_id, $this->user_id);
            $update_user_stmt->execute();
            
            return [
                'success' => true, 
                'message' => 'Subscribed successfully',
                'subscription_id' => $subscription_id,
                'end_date' => $end_date
            ];
        }
        
        return ['success' => false, 'message' => 'Subscription failed'];
    }
    
    /**
     * Cancel subscription
     */
    public function cancelSubscription() {
        $sql = "UPDATE user_subscriptions SET status = 'cancelled', auto_renew = FALSE 
                WHERE user_id = ? AND status = 'active'";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $this->user_id);
        
        if ($stmt->execute()) {
            // Clear user's current subscription
            $update_user_sql = "UPDATE users SET current_subscription_id = NULL WHERE id = ?";
            $update_user_stmt = $this->conn->prepare($update_user_sql);
            $update_user_stmt->bind_param("i", $this->user_id);
            $update_user_stmt->execute();
            
            return ['success' => true, 'message' => 'Subscription cancelled'];
        }
        
        return ['success' => false, 'message' => 'Failed to cancel subscription'];
    }
    
    /**
     * Get subscription history
     */
    public function getHistory() {
        $sql = "SELECT us.*, sp.name as plan_name, sp.price 
                FROM user_subscriptions us
                JOIN subscription_plans sp ON us.plan_id = sp.id
                WHERE us.user_id = ?
                ORDER BY us.created_at DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $this->user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $history = [];
        while ($row = $result->fetch_assoc()) {
            $history[] = $row;
        }
        
        return $history;
    }
}
?>