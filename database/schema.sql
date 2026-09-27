-- Resume Analyzer AI - Database Schema
-- Run this once against a fresh database, e.g.:
--   mysql -u root -p resume_analyzer < database/schema.sql

CREATE DATABASE IF NOT EXISTS resume_analyzer CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE resume_analyzer;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE resumes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    stored_filename VARCHAR(255) NOT NULL,
    file_type ENUM('pdf', 'docx') NOT NULL,
    extracted_text LONGTEXT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE resume_analysis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resume_id INT NOT NULL,
    personal_info JSON,
    education JSON,
    skills JSON,
    technical_skills JSON,
    soft_skills JSON,
    experience JSON,
    projects JSON,
    certifications JSON,
    achievements JSON,
    resume_summary TEXT,
    resume_score TINYINT UNSIGNED,
    ats_score TINYINT UNSIGNED,
    strengths JSON,
    weaknesses JSON,
    missing_skills JSON,
    suggestions JSON,
    recommended_roles JSON,
    raw_ai_response LONGTEXT,
    analyzed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resume_id) REFERENCES resumes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE skills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    company VARCHAR(150),
    description TEXT NOT NULL,
    required_skills JSON NOT NULL COMMENT 'Array of skill names required for this job',
    experience_level ENUM('entry', 'mid', 'senior') DEFAULT 'entry',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE job_matches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resume_id INT NOT NULL,
    job_id INT NOT NULL,
    match_percentage TINYINT UNSIGNED NOT NULL,
    matched_skills JSON,
    missing_skills JSON,
    ai_explanation TEXT,
    matched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resume_id) REFERENCES resumes(id) ON DELETE CASCADE,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    UNIQUE KEY unique_resume_job (resume_id, job_id)
) ENGINE=InnoDB;

-- No admin account is seeded here. Run `php database/seed_admin.php` after
-- setting up the database to create the first admin with a real password hash.
