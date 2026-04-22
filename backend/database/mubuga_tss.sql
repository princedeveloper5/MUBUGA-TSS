CREATE DATABASE IF NOT EXISTS mubuga_tss
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE mubuga_tss;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'admin', 'editor') NOT NULL DEFAULT 'admin',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS pages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(160) NOT NULL,
    slug VARCHAR(160) NOT NULL UNIQUE,
    excerpt TEXT NULL,
    content LONGTEXT NULL,
    banner_image VARCHAR(255) NULL,
    meta_title VARCHAR(160) NULL,
    meta_description VARCHAR(255) NULL,
    status ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_pages_created_by
        FOREIGN KEY (created_by) REFERENCES users(id)
        ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS programs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    slug VARCHAR(150) NOT NULL UNIQUE,
    short_description TEXT NULL,
    description LONGTEXT NULL,
    duration VARCHAR(100) NULL,
    department VARCHAR(120) NULL,
    cover_image VARCHAR(255) NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS staff (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    job_title VARCHAR(150) NOT NULL,
    bio TEXT NULL,
    email VARCHAR(150) NULL,
    phone VARCHAR(50) NULL,
    photo VARCHAR(255) NULL,
    department VARCHAR(120) NULL,
    display_order INT NOT NULL DEFAULT 0,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS news (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    slug VARCHAR(180) NOT NULL UNIQUE,
    summary TEXT NULL,
    content LONGTEXT NULL,
    featured_image VARCHAR(255) NULL,
    published_at DATETIME NULL,
    status ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_news_created_by
        FOREIGN KEY (created_by) REFERENCES users(id)
        ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS gallery (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    caption TEXT NULL,
    category VARCHAR(100) NULL,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS contact_messages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(50) NULL,
    subject VARCHAR(180) NULL,
    message_body TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS admissions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    applicant_name VARCHAR(150) NOT NULL,
    gender ENUM('male', 'female', 'other') NULL,
    date_of_birth DATE NULL,
    parent_name VARCHAR(150) NULL,
    parent_phone VARCHAR(50) NULL,
    email VARCHAR(150) NULL,
    address VARCHAR(255) NULL,
    previous_school VARCHAR(180) NULL,
    preferred_program_id INT UNSIGNED NULL,
    intake_year YEAR NULL,
    notes TEXT NULL,
    status ENUM('pending', 'reviewed', 'accepted', 'rejected') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_admissions_program
        FOREIGN KEY (preferred_program_id) REFERENCES programs(id)
        ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS downloads (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    description TEXT NULL,
    category VARCHAR(100) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL UNIQUE,
    source VARCHAR(100) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO settings (setting_key, setting_value) VALUES
    ('school_name', 'Mubuga TSS'),
    ('school_motto', 'Excellence in technical education'),
    ('school_email', 'info@mubugatss.rw'),
    ('school_phone', '+250 7XX XXX XXX'),
    ('school_address', 'Mubuga, Rwanda')
ON DUPLICATE KEY UPDATE
    setting_value = VALUES(setting_value);

INSERT INTO programs (title, slug, short_description, status) VALUES
    ('Software Development', 'software-development', 'Programming, web development, and digital problem solving.', 'active'),
    ('Electrical Technology', 'electrical-technology', 'Electrical installation, maintenance, and practical workshop training.', 'active')
ON DUPLICATE KEY UPDATE
    title = VALUES(title),
    short_description = VALUES(short_description),
    status = VALUES(status);

INSERT INTO staff (full_name, job_title, bio, photo, display_order, is_featured, status)
SELECT * FROM (
    SELECT
        'Mubuga TSS Administration' AS full_name,
        'School Leadership' AS job_title,
        'Our leadership team is committed to growing a disciplined, innovative, and student-centered technical school community.' AS bio,
        'assets/images/master.jpeg' AS photo,
        1 AS display_order,
        1 AS is_featured,
        'active' AS status
) AS tmp
WHERE NOT EXISTS (
    SELECT 1 FROM staff WHERE full_name = 'Mubuga TSS Administration'
);

INSERT INTO staff (full_name, job_title, bio, photo, display_order, is_featured, status)
SELECT * FROM (
    SELECT
        'Training and Learning Team' AS full_name,
        'Academic Direction' AS job_title,
        'We support learners through strong instruction, practical coaching, and clear pathways into employment and further studies.' AS bio,
        'assets/images/master.jpeg' AS photo,
        2 AS display_order,
        0 AS is_featured,
        'active' AS status
) AS tmp
WHERE NOT EXISTS (
    SELECT 1 FROM staff WHERE full_name = 'Training and Learning Team'
);

INSERT INTO news (title, slug, summary, featured_image, published_at, status)
SELECT * FROM (
    SELECT
        'Practical Learning Across Both Trades' AS title,
        'practical-learning-across-both-trades' AS slug,
        'Students engage in hands-on activities that connect classroom concepts to software projects and electrical workshop tasks.' AS summary,
        'assets/images/mb1.jfif' AS featured_image,
        NOW() AS published_at,
        'published' AS status
) AS tmp
WHERE NOT EXISTS (
    SELECT 1 FROM news WHERE slug = 'practical-learning-across-both-trades'
);

INSERT INTO news (title, slug, summary, featured_image, published_at, status)
SELECT * FROM (
    SELECT
        'Career-Focused Technical Education' AS title,
        'career-focused-technical-education' AS slug,
        'Mubuga TSS prepares learners with relevant skills, confidence, and a mindset for solving real community and industry challenges.' AS summary,
        'assets/images/mb3.jfif' AS featured_image,
        NOW() AS published_at,
        'published' AS status
) AS tmp
WHERE NOT EXISTS (
    SELECT 1 FROM news WHERE slug = 'career-focused-technical-education'
);

INSERT INTO news (title, slug, summary, featured_image, published_at, status)
SELECT * FROM (
    SELECT
        'School Community and Growth' AS title,
        'school-community-and-growth' AS slug,
        'Our school promotes discipline, collaboration, and innovation as core values in daily learning and school life.' AS summary,
        'assets/images/mb2.jfif' AS featured_image,
        NOW() AS published_at,
        'published' AS status
) AS tmp
WHERE NOT EXISTS (
    SELECT 1 FROM news WHERE slug = 'school-community-and-growth'
);

INSERT INTO gallery (title, image_path, caption, category, is_featured)
SELECT * FROM (
    SELECT
        'Students in the coding lab' AS title,
        'assets/images/mb3.jfif' AS image_path,
        'Software Development learners working on practical projects and digital skills.' AS caption,
        'academics' AS category,
        1 AS is_featured
) AS tmp
WHERE NOT EXISTS (
    SELECT 1 FROM gallery WHERE title = 'Students in the coding lab'
);

INSERT INTO gallery (title, image_path, caption, category, is_featured)
SELECT * FROM (
    SELECT
        'Electrical workshop practice' AS title,
        'assets/images/IM5.jpg' AS image_path,
        'Hands-on training focused on installation, troubleshooting, and safe technical work.' AS caption,
        'workshops' AS category,
        1 AS is_featured
) AS tmp
WHERE NOT EXISTS (
    SELECT 1 FROM gallery WHERE title = 'Electrical workshop practice'
);

INSERT INTO gallery (title, image_path, caption, category, is_featured)
SELECT * FROM (
    SELECT
        'School community life' AS title,
        'assets/images/mb1.jfif' AS image_path,
        'A disciplined and supportive environment that encourages growth and teamwork.' AS caption,
        'community' AS category,
        1 AS is_featured
) AS tmp
WHERE NOT EXISTS (
    SELECT 1 FROM gallery WHERE title = 'School community life'
);
