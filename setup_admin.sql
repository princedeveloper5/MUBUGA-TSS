-- Database Setup Script for Mubuga TSS Admin System
-- Run this script in your MySQL database to create required tables and admin user

-- Create users table if it doesn't exist
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'admin', 'staff', 'student') NOT NULL DEFAULT 'staff',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login_at TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create settings table if it doesn't exist
CREATE TABLE IF NOT EXISTS settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create other required tables
CREATE TABLE IF NOT EXISTS pages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(100) NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    excerpt TEXT,
    content LONGTEXT,
    banner_image VARCHAR(255),
    status ENUM('published', 'draft') NOT NULL DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS programs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    short_description TEXT,
    long_description LONGTEXT,
    cover_image VARCHAR(255),
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS news (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    summary TEXT,
    content LONGTEXT,
    featured_image VARCHAR(255),
    status ENUM('published', 'draft') NOT NULL DEFAULT 'draft',
    published_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_status (status),
    INDEX idx_published (published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS gallery (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    caption TEXT,
    image_path VARCHAR(255) NOT NULL,
    category VARCHAR(100) DEFAULT 'general',
    media_type ENUM('image', 'video') NOT NULL DEFAULT 'image',
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_type (media_type),
    INDEX idx_featured (is_featured),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS staff (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    job_title VARCHAR(255) NOT NULL,
    bio TEXT,
    photo VARCHAR(255),
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_featured (is_featured),
    INDEX idx_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user (password: admin123)
INSERT IGNORE INTO users (full_name, email, password_hash, role, is_active) 
VALUES (
    'System Administrator',
    'admin@mubuga.tss',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: admin123
    'super_admin',
    1
);

-- Insert basic settings
INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
('school_name', 'Mubuga Technical Secondary School'),
('school_email', 'info@mubuga.tss'),
('school_phone', '+250 123 456 789'),
('school_address', 'Kigali, Rwanda'),
('school_motto', 'Excellence in Technical Education'),
('theme_mode', 'light'),
('homepage_notice', '');

-- Insert sample programs
INSERT IGNORE INTO programs (title, short_description, long_description, cover_image, status, display_order) VALUES
('Software Development', 'Learn programming and web development skills', 'Comprehensive training in modern programming languages, web technologies, and software development practices.', 'assets/images/software development 1.jpeg', 'active', 1),
('Electrical Technology', 'Master electrical installation and maintenance', 'Hands-on training in electrical systems, installation practices, and maintenance procedures.', 'assets/images/electrical technology 1.jpeg', 'active', 2);

-- Insert sample news
INSERT IGNORE INTO news (title, slug, summary, content, featured_image, status, published_at) VALUES
('Welcome to Mubuga TSS', 'welcome-to-mubuga-tss', 'We are excited to welcome you to our technical secondary school.', 'Mubuga Technical Secondary School offers quality technical education in Software Development and Electrical Technology.', 'assets/images/students.jfif', 'published', NOW()),
('New Academic Year 2024', 'new-academic-year-2024', 'Enrollment now open for the 2024 academic year.', 'Applications are now being accepted for our technical programs. Join us for quality education and practical skills.', 'assets/images/school view 8.jpeg', 'published', NOW());

-- Insert sample gallery items
INSERT IGNORE INTO gallery (title, caption, image_path, category, media_type, is_featured, status) VALUES
('School Campus', 'Beautiful view of our school campus', 'assets/images/school view 8.jpeg', 'campus', 'image', 1, 'active'),
('Computer Lab', 'Modern computer laboratory for software development', 'assets/images/software development 4.jpeg', 'campus', 'image', 1, 'active'),
('Electrical Workshop', 'Well-equipped electrical workshop', 'assets/images/electrical technology 2.jpeg', 'campus', 'image', 1, 'active'),
('Students in Class', 'Students learning in our modern classrooms', 'assets/images/student in practical.jpeg', 'general', 'image', 0, 'active');

-- Insert sample staff
INSERT IGNORE INTO staff (full_name, job_title, bio, photo, status, is_featured, display_order) VALUES
('John Smith', 'School Director', 'Experienced educator with over 15 years in technical education administration.', 'assets/images/master.jpeg', 'active', 1, 1),
('Jane Doe', 'Software Development Instructor', 'Specialized in programming and web development with industry experience.', 'assets/images/master.jpeg', 'active', 1, 2),
('Robert Johnson', 'Electrical Technology Instructor', 'Expert in electrical systems and practical workshop training.', 'assets/images/master.jpeg', 'active', 1, 3);

-- Create admin activity log table
CREATE TABLE IF NOT EXISTS admin_activity_log (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    action_type VARCHAR(60) NOT NULL,
    entity_type VARCHAR(60) NOT NULL,
    entity_id INT UNSIGNED NULL,
    title VARCHAR(190) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_activity_created_at (created_at),
    INDEX idx_activity_entity (entity_type, entity_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create admin notifications table
CREATE TABLE IF NOT EXISTS admin_notifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    notification_type VARCHAR(60) NOT NULL,
    title VARCHAR(190) NOT NULL,
    message TEXT NULL,
    link_target VARCHAR(255) NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_notification_user (user_id),
    INDEX idx_notification_read (is_read),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Success message
SELECT 'Database setup completed successfully!' as message;
