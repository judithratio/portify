CREATE DATABASE IF NOT EXISTS portify CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE portify;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS certifications;
DROP TABLE IF EXISTS skills;
DROP TABLE IF EXISTS education;
DROP TABLE IF EXISTS experience;
DROP TABLE IF EXISTS projects;
DROP TABLE IF EXISTS profiles;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    google_id VARCHAR(255) UNIQUE NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NULL,
    role ENUM('admin','user') NOT NULL DEFAULT 'user',
    account_status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE profiles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL UNIQUE,
    full_name VARCHAR(255) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    phone VARCHAR(50) DEFAULT NULL,
    professional_title VARCHAR(255) DEFAULT NULL,
    bio TEXT DEFAULT NULL,
    professional_summary TEXT DEFAULT NULL,
    profile_image VARCHAR(500) DEFAULT NULL,
    github_url VARCHAR(500) DEFAULT NULL,
    linkedin_url VARCHAR(500) DEFAULT NULL,
    facebook_url VARCHAR(500) DEFAULT NULL,
    website_url VARCHAR(500) DEFAULT NULL,
    portfolio_public TINYINT(1) NOT NULL DEFAULT 0,
    show_about TINYINT(1) NOT NULL DEFAULT 1,
    show_projects TINYINT(1) NOT NULL DEFAULT 1,
    show_experience TINYINT(1) NOT NULL DEFAULT 1,
    show_education TINYINT(1) NOT NULL DEFAULT 1,
    show_skills TINYINT(1) NOT NULL DEFAULT 1,
    show_certifications TINYINT(1) NOT NULL DEFAULT 1,
    show_socials TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_profiles_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE projects (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    project_type ENUM('general','creative') NOT NULL DEFAULT 'general',
    title VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    role VARCHAR(255) DEFAULT NULL,
    tech_stack TEXT DEFAULT NULL,
    subject_matter TEXT DEFAULT NULL,
    medium VARCHAR(255) DEFAULT NULL,
    image VARCHAR(500) DEFAULT NULL,
    website_url VARCHAR(500) DEFAULT NULL,
    github_url VARCHAR(500) DEFAULT NULL,
    start_date DATE DEFAULT NULL,
    end_date DATE DEFAULT NULL,
    duration VARCHAR(100) DEFAULT NULL,
    date_created DATE DEFAULT NULL,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    is_public TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_projects_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE experience (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    job_title VARCHAR(255) DEFAULT NULL,
    company VARCHAR(255) DEFAULT NULL,
    location VARCHAR(255) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    start_date DATE DEFAULT NULL,
    end_date DATE DEFAULT NULL,
    is_current TINYINT(1) NOT NULL DEFAULT 0,
    company_url VARCHAR(500) DEFAULT NULL,
    is_public TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_experience_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE education (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    institution VARCHAR(255) DEFAULT NULL,
    degree VARCHAR(255) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    start_date DATE DEFAULT NULL,
    end_date DATE DEFAULT NULL,
    is_current TINYINT(1) NOT NULL DEFAULT 0,
    institution_url VARCHAR(500) DEFAULT NULL,
    is_public TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_education_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE skills (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    skill_name VARCHAR(255) NOT NULL,
    category VARCHAR(255) DEFAULT NULL,
    proficiency TINYINT UNSIGNED NOT NULL DEFAULT 0,
    is_public TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_skills_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE certifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    issuing_organization VARCHAR(255) DEFAULT NULL,
    issue_date DATE DEFAULT NULL,
    expiration_date DATE DEFAULT NULL,
    credential_id VARCHAR(255) DEFAULT NULL,
    credential_url VARCHAR(500) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    certificate_file VARCHAR(500) DEFAULT NULL,
    is_public TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_certifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO users (email, role, account_status)
VALUES ('admin@portify.local', 'admin', 'active')
ON DUPLICATE KEY UPDATE role='admin', account_status='active';

INSERT INTO profiles (user_id, full_name, professional_title, bio, professional_summary, portfolio_public)
SELECT id, 'Portify Administrator', 'Administrator', 'Portify system administrator.', 'Portfolio management system administrator.', 0
FROM users
WHERE email='admin@portify.local'
AND NOT EXISTS (SELECT 1 FROM profiles p WHERE p.user_id=users.id);
