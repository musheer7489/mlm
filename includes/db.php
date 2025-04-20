-- Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    image VARCHAR(255),
    sponsor_id INT,
    parent_id INT,
    position ENUM('left', 'right'),
    level INT DEFAULT 0,
    active BOOLEAN DEFAULT TRUE,
    is_admin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (sponsor_id) REFERENCES users(id),
    FOREIGN KEY (parent_id) REFERENCES users(id)
);

-- Product Table
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    stock INT NOT NULL,
    rating DECIMAL(2,1) DEFAULT 0,
    review_count INT DEFAULT 0,
    meta_title VARCHAR(100),
    meta_description VARCHAR(160),
    meta_keywords VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Orders Table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    payment_id VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Commission Levels Table
CREATE TABLE commission_levels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    level INT NOT NULL UNIQUE,
    percentage DECIMAL(5,2) NOT NULL,
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Commissions Table
CREATE TABLE commissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_id INT NOT NULL,
    level INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    percentage DECIMAL(5,2) NOT NULL,
    from_member INT NOT NULL,
    order_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'paid') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    paid_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (from_member) REFERENCES users(id)
);

-- Badges Table
CREATE TABLE badges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    level_required INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- User Badges Table
CREATE TABLE user_badges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    badge_id INT NOT NULL,
    earned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (badge_id) REFERENCES badges(id)
);

-- Testimonials Table
CREATE TABLE testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    rating INT NOT NULL,
    comment TEXT NOT NULL,
    approved BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Payouts Table
CREATE TABLE payouts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    period ENUM('weekly', 'monthly', 'custom') NOT NULL,
    from_date DATE,
    to_date DATE,
    distributor_count INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    processed_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL
);

-- Settings Table
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) NOT NULL UNIQUE,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Notifications Table
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Insert initial data
INSERT INTO products (name, description, price, stock, image) VALUES 
('Premium Health Supplement', 'Our flagship health product with all-natural ingredients for optimal wellness.', 1999.00, 100, 'product1.jpg');

INSERT INTO commission_levels (level, percentage, description) VALUES 
(1, 10.00, 'Direct referrals'),
(2, 5.00, 'Second level team'),
(3, 3.00, 'Third level team'),
(4, 2.00, 'Fourth level team'),
(5, 1.00, 'Fifth level team');

INSERT INTO badges (name, description, image, level_required) VALUES 
('Starter', 'Welcome to our team!', 'badge1.png', 0),
('Bronze', 'Achieved Level 1', 'badge2.png', 1),
('Silver', 'Achieved Level 2', 'badge3.png', 2),
('Gold', 'Achieved Level 3', 'badge4.png', 3),
('Platinum', 'Achieved Level 4', 'badge5.png', 4),
('Diamond', 'Achieved Level 5', 'badge6.png', 5);

INSERT INTO settings (setting_key, setting_value) VALUES 
('site_name', 'HealthPlus MLM'),
('site_email', 'info@healthplusmlm.com'),
('razorpay_key_id', 'your_razorpay_key_id'),
('razorpay_key_secret', 'your_razorpay_key_secret'),
('min_payout_amount', '500.00'),
('last_cron_run', NULL);

-- Create admin user
INSERT INTO users (username, password, email, full_name, is_admin) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@example.com', 'Admin User', TRUE);