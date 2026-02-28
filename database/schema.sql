CREATE DATABASE IF NOT EXISTS dance_learning;
USE dance_learning;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('instructor', 'student') NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Dance classes table
CREATE TABLE dance_classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    instructor_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    dance_style VARCHAR(100),
    level ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
    scheduled_date DATE NOT NULL,
    scheduled_time TIME NOT NULL,
    duration INT DEFAULT 60,
    meeting_link VARCHAR(255) UNIQUE, -- Store unique meeting ID
    status ENUM('upcoming', 'live', 'completed', 'cancelled') DEFAULT 'upcoming',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (instructor_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Class enrollments table
CREATE TABLE class_enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT NOT NULL,
    student_id INT NOT NULL,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('registered', 'attended', 'cancelled') DEFAULT 'registered',
    FOREIGN KEY (class_id) REFERENCES dance_classes(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (class_id, student_id)
);

-- Add signaling table for WebRTC peer connections
CREATE TABLE IF NOT EXISTS signaling (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_id VARCHAR(100) NOT NULL,
    user_id INT NOT NULL,
    user_role ENUM('instructor', 'student') NOT NULL,
    message_type ENUM('offer', 'answer', 'candidate', 'join', 'leave') NOT NULL,
    message TEXT NOT NULL,
    target_user_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_room (room_id),
    INDEX idx_room_user (room_id, user_id)
);

-- Add active users tracking
CREATE TABLE IF NOT EXISTS active_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_id VARCHAR(100) NOT NULL,
    user_id INT NOT NULL,
    user_role ENUM('instructor', 'student') NOT NULL,
    last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_room_user (room_id, user_id),
    INDEX idx_room_active (room_id, last_seen)
);

-- Add subscription plans table
CREATE TABLE IF NOT EXISTS subscription_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    duration_days INT NOT NULL, -- 30 for monthly, 365 for yearly
    max_classes_per_day INT DEFAULT NULL, -- NULL for unlimited
    can_create_classes BOOLEAN DEFAULT FALSE, -- For instructors
    can_join_live BOOLEAN DEFAULT TRUE,
    has_recording BOOLEAN DEFAULT FALSE,
    priority_support BOOLEAN DEFAULT FALSE,
    features JSON, -- Store additional features as JSON
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Add user subscriptions table
CREATE TABLE IF NOT EXISTS user_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    plan_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('active', 'expired', 'cancelled', 'pending') DEFAULT 'active',
    payment_method VARCHAR(50),
    transaction_id VARCHAR(255),
    auto_renew BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES subscription_plans(id) ON DELETE CASCADE,
    INDEX idx_user_status (user_id, status),
    INDEX idx_expiry (end_date)
);

-- Add subscription usage tracking
CREATE TABLE IF NOT EXISTS subscription_usage (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subscription_id INT NOT NULL,
    class_id INT,
    action_type ENUM('join_class', 'create_class', 'recording') NOT NULL,
    action_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (subscription_id) REFERENCES user_subscriptions(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES dance_classes(id) ON DELETE SET NULL,
    INDEX idx_user_date (user_id, action_date)
);

-- Insert default subscription plans
INSERT INTO subscription_plans (name, description, price, duration_days, max_classes_per_day, can_create_classes, can_join_live, has_recording, priority_support, features) VALUES
('Free', 'Basic access to live classes', 0.00, 30, 1, FALSE, TRUE, FALSE, FALSE, '{"max_classes_per_month": 5, "can_chat": true, "can_ask_questions": true}'),
('Monthly', 'Full access to all features', 29.99, 30, NULL, TRUE, TRUE, TRUE, FALSE, '{"unlimited_classes": true, "recording_access": true, "priority_booking": true}'),
('Yearly', 'Best value for regular dancers', 299.99, 365, NULL, TRUE, TRUE, TRUE, TRUE, '{"unlimited_classes": true, "recording_access": true, "priority_booking": true, "priority_support": true, "private_sessions": 2}');

-- Add subscription_id to users table (optional, for quick access)
ALTER TABLE users ADD COLUMN current_subscription_id INT NULL AFTER role;

-- Create indexes
CREATE INDEX idx_classes_status ON dance_classes(status);
CREATE INDEX idx_classes_scheduled ON dance_classes(scheduled_date, scheduled_time);
CREATE INDEX idx_enrollments_class ON class_enrollments(class_id);
CREATE INDEX idx_enrollments_student ON class_enrollments(student_id);