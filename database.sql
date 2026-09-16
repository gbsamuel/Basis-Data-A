-- ==========================================================
-- SIREKA - Sistem Informasi Rekrutmen & Kandidat
-- Database: sireka_db
-- Dedicated Single-Company IT Career & Recruitment Portal
-- Perusahaan: PT Solusi Teknologi Nusantara (Enterprise Cloud & AI Solutions)
-- Mata Kuliah: Basis Data
-- ==========================================================

DROP DATABASE IF EXISTS `sireka_db`;
CREATE DATABASE `sireka_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `sireka_db`;

SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------------------------------------
-- 1. Table: company (Dedicated IT Company Profile)
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `company`;
CREATE TABLE `company` (
    `id_company` INT AUTO_INCREMENT PRIMARY KEY,
    `nama_company` VARCHAR(150) NOT NULL,
    `alamat` TEXT NOT NULL,
    `industri` VARCHAR(100) NOT NULL,
    `email_corporate` VARCHAR(100) NOT NULL,
    `no_telepon` VARCHAR(30) NOT NULL,
    `deskripsi` TEXT,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ----------------------------------------------------------
-- 2. Table: user_all (Admin HR, Interviewer, User/Pelamar)
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `user_all`;
CREATE TABLE `user_all` (
    `nik` VARCHAR(20) PRIMARY KEY,
    `nama` VARCHAR(150) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `no_telepon` VARCHAR(30) NOT NULL,
    `tanggal_lahir` DATE NOT NULL,
    `pendidikan_terakhir` VARCHAR(50) NOT NULL,
    `tahun_lulus` YEAR NOT NULL,
    `alamat` TEXT NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('user', 'admin', 'interviewer') NOT NULL DEFAULT 'user',
    `profile_photo` VARCHAR(255) DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ----------------------------------------------------------
-- 3. Table: company_admin
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `company_admin`;
CREATE TABLE `company_admin` (
    `id_admin` INT AUTO_INCREMENT PRIMARY KEY,
    `id_company` INT NOT NULL,
    `nik` VARCHAR(20) NOT NULL,
    `position` VARCHAR(100) NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_admin_company` FOREIGN KEY (`id_company`) REFERENCES `company` (`id_company`) ON DELETE CASCADE,
    CONSTRAINT `fk_admin_user` FOREIGN KEY (`nik`) REFERENCES `user_all` (`nik`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------------------------------------
-- 4. Table: division (Internal IT Divisions)
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `division`;
CREATE TABLE `division` (
    `id_division` INT AUTO_INCREMENT PRIMARY KEY,
    `id_company` INT NOT NULL,
    `nama_divisi` VARCHAR(100) NOT NULL,
    `deskripsi` TEXT,
    CONSTRAINT `fk_division_company` FOREIGN KEY (`id_company`) REFERENCES `company` (`id_company`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------------------------------------
-- 5. Table: job (Internal IT Vacancies)
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `job`;
CREATE TABLE `job` (
    `id_job` INT AUTO_INCREMENT PRIMARY KEY,
    `id_division` INT NOT NULL,
    `nama_job` VARCHAR(150) NOT NULL,
    `job_type` ENUM('Kerja', 'Magang', 'Management Trainee') NOT NULL DEFAULT 'Kerja',
    `deskripsi` TEXT NOT NULL,
    `requirements` TEXT NOT NULL,
    `responsibilities` TEXT NOT NULL,
    `education_requirement` VARCHAR(50) NOT NULL,
    `experience_requirement` VARCHAR(50) NOT NULL,
    `salary_min` DECIMAL(12,2) DEFAULT NULL,
    `salary_max` DECIMAL(12,2) DEFAULT NULL,
    `location` VARCHAR(100) NOT NULL,
    `deadline` DATE NOT NULL,
    `status` ENUM('Open', 'Closed', 'Draft') NOT NULL DEFAULT 'Open',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_job_division` FOREIGN KEY (`id_division`) REFERENCES `division` (`id_division`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------------------------------------
-- 6. Table: skill (IT & Professional Skills Catalog)
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `skill`;
CREATE TABLE `skill` (
    `id_skill` INT AUTO_INCREMENT PRIMARY KEY,
    `nama_skill` VARCHAR(100) NOT NULL UNIQUE,
    `category` VARCHAR(50) NOT NULL DEFAULT 'General'
) ENGINE=InnoDB;

-- ----------------------------------------------------------
-- 7. Junction Table: user_skill (Many-to-Many User <-> Skill)
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `user_skill`;
CREATE TABLE `user_skill` (
    `nik` VARCHAR(20) NOT NULL,
    `id_skill` INT NOT NULL,
    `level` ENUM('Beginner', 'Intermediate', 'Advanced', 'Expert') NOT NULL DEFAULT 'Intermediate',
    PRIMARY KEY (`nik`, `id_skill`),
    CONSTRAINT `fk_userskill_user` FOREIGN KEY (`nik`) REFERENCES `user_all` (`nik`) ON DELETE CASCADE,
    CONSTRAINT `fk_userskill_skill` FOREIGN KEY (`id_skill`) REFERENCES `skill` (`id_skill`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------------------------------------
-- 8. Junction Table: job_skill (Many-to-Many Job <-> Skill)
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `job_skill`;
CREATE TABLE `job_skill` (
    `id_job` INT NOT NULL,
    `id_skill` INT NOT NULL,
    `is_required` TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (`id_job`, `id_skill`),
    CONSTRAINT `fk_jobskill_job` FOREIGN KEY (`id_job`) REFERENCES `job` (`id_job`) ON DELETE CASCADE,
    CONSTRAINT `fk_jobskill_skill` FOREIGN KEY (`id_skill`) REFERENCES `skill` (`id_skill`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------------------------------------
-- 9. Table: application
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `application`;
CREATE TABLE `application` (
    `id_application` INT AUTO_INCREMENT PRIMARY KEY,
    `nik` VARCHAR(20) NOT NULL,
    `id_job` INT NOT NULL,
    `cv_file` VARCHAR(255) NOT NULL,
    `cover_letter` TEXT,
    `portfolio_url` VARCHAR(255) DEFAULT NULL,
    `match_score` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `applied_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `current_status` ENUM(
        'Applied', 
        'HR Review', 
        'Document Screening', 
        'Interview Scheduling', 
        'Interview', 
        'Final Decision', 
        'Accepted', 
        'Rejected', 
        'Talent Pool'
    ) NOT NULL DEFAULT 'Applied',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_app_user` FOREIGN KEY (`nik`) REFERENCES `user_all` (`nik`) ON DELETE CASCADE,
    CONSTRAINT `fk_app_job` FOREIGN KEY (`id_job`) REFERENCES `job` (`id_job`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------------------------------------
-- 10. Table: candidate_stage_history (Tracking recruitment timeline)
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `candidate_stage_history`;
CREATE TABLE `candidate_stage_history` (
    `id_history` INT AUTO_INCREMENT PRIMARY KEY,
    `id_application` INT NOT NULL,
    `stage` VARCHAR(50) NOT NULL,
    `status` VARCHAR(50) NOT NULL,
    `notes` TEXT,
    `changed_by` VARCHAR(20) DEFAULT NULL,
    `changed_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_history_app` FOREIGN KEY (`id_application`) REFERENCES `application` (`id_application`) ON DELETE CASCADE,
    CONSTRAINT `fk_history_user` FOREIGN KEY (`changed_by`) REFERENCES `user_all` (`nik`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ----------------------------------------------------------
-- 11. Table: interviewer
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `interviewer`;
CREATE TABLE `interviewer` (
    `id_interviewer` INT AUTO_INCREMENT PRIMARY KEY,
    `id_company` INT NOT NULL,
    `nama` VARCHAR(150) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `no_telepon` VARCHAR(30) NOT NULL,
    `position` VARCHAR(100) NOT NULL,
    CONSTRAINT `fk_interviewer_company` FOREIGN KEY (`id_company`) REFERENCES `company` (`id_company`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------------------------------------
-- 12. Table: interview
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `interview`;
CREATE TABLE `interview` (
    `id_interview` INT AUTO_INCREMENT PRIMARY KEY,
    `id_application` INT NOT NULL,
    `id_interviewer` INT NOT NULL,
    `tanggal` DATE NOT NULL,
    `waktu` TIME NOT NULL,
    `type` ENUM('Online', 'Offline') NOT NULL DEFAULT 'Online',
    `location` VARCHAR(255) DEFAULT NULL,
    `meeting_link` VARCHAR(255) DEFAULT NULL,
    `notes` TEXT,
    `status` ENUM('Scheduled', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Scheduled',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_interview_app` FOREIGN KEY (`id_application`) REFERENCES `application` (`id_application`) ON DELETE CASCADE,
    CONSTRAINT `fk_interview_interviewer` FOREIGN KEY (`id_interviewer`) REFERENCES `interviewer` (`id_interviewer`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------------------------------------
-- 13. Table: talent_pool
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `talent_pool`;
CREATE TABLE `talent_pool` (
    `id_talent_pool` INT AUTO_INCREMENT PRIMARY KEY,
    `nik` VARCHAR(20) NOT NULL,
    `id_company` INT NOT NULL,
    `source_application` INT DEFAULT NULL,
    `reason` TEXT NOT NULL,
    `status` ENUM('Available', 'Considered', 'Hired', 'Inactive') NOT NULL DEFAULT 'Available',
    `added_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_talent_user` FOREIGN KEY (`nik`) REFERENCES `user_all` (`nik`) ON DELETE CASCADE,
    CONSTRAINT `fk_talent_company` FOREIGN KEY (`id_company`) REFERENCES `company` (`id_company`) ON DELETE CASCADE,
    CONSTRAINT `fk_talent_app` FOREIGN KEY (`source_application`) REFERENCES `application` (`id_application`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ----------------------------------------------------------
-- 14. Table: loa (Letter of Acceptance)
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `loa`;
CREATE TABLE `loa` (
    `id_loa` INT AUTO_INCREMENT PRIMARY KEY,
    `id_application` INT NOT NULL UNIQUE,
    `loa_number` VARCHAR(100) NOT NULL UNIQUE,
    `issue_date` DATE NOT NULL,
    `join_date` DATE NOT NULL,
    `position` VARCHAR(150) NOT NULL,
    `division` VARCHAR(100) NOT NULL,
    `status` ENUM('Issued', 'Accepted', 'Declined') NOT NULL DEFAULT 'Issued',
    `authorized_by` VARCHAR(150) NOT NULL,
    `notes` TEXT,
    CONSTRAINT `fk_loa_app` FOREIGN KEY (`id_application`) REFERENCES `application` (`id_application`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------------------------------------
-- 15. Table: complaint
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `complaint`;
CREATE TABLE `complaint` (
    `id_complaint` INT AUTO_INCREMENT PRIMARY KEY,
    `nik` VARCHAR(20) NOT NULL,
    `subject` VARCHAR(150) NOT NULL,
    `category` VARCHAR(50) NOT NULL,
    `description` TEXT NOT NULL,
    `attachment` VARCHAR(255) DEFAULT NULL,
    `status` ENUM('Submitted', 'In Review', 'Resolved', 'Closed') NOT NULL DEFAULT 'Submitted',
    `admin_response` TEXT,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `resolved_at` DATETIME DEFAULT NULL,
    CONSTRAINT `fk_complaint_user` FOREIGN KEY (`nik`) REFERENCES `user_all` (`nik`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------------------------------------
-- 16. Table: feedback
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `feedback`;
CREATE TABLE `feedback` (
    `id_feedback` INT AUTO_INCREMENT PRIMARY KEY,
    `nik` VARCHAR(20) NOT NULL,
    `rating` INT NOT NULL CHECK (`rating` BETWEEN 1 AND 5),
    `message` TEXT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_feedback_user` FOREIGN KEY (`nik`) REFERENCES `user_all` (`nik`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------------------------------------
-- 17. Table: system_settings
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `system_settings`;
CREATE TABLE `system_settings` (
    `setting_key` VARCHAR(50) PRIMARY KEY,
    `setting_value` TEXT NOT NULL
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;

-- ==========================================================
-- SEED DATA (Dedicated IT Corporate & Recruitment Demo)
-- ==========================================================

-- 1. System Settings
INSERT INTO `system_settings` (`setting_key`, `setting_value`) VALUES
('helpdesk_whatsapp', '6281234567890'),
('helpdesk_email', 'career@solusiteknologi.co.id'),
('platform_name', 'SIREKA - IT Career & Recruitment Portal'),
('company_brand', 'PT Solusi Teknologi Nusantara'),
('loa_authorized_signer', 'Dr. Hendra Gunawan, S.Kom., M.M. (VP Human Capital & People Ops)');

-- 2. Single IT Company Profile
INSERT INTO `company` (`id_company`, `nama_company`, `alamat`, `industri`, `email_corporate`, `no_telepon`, `deskripsi`) VALUES
(1, 'PT Solusi Teknologi Nusantara', 'Cyber 2 Tower Lt. 18, Jl. H.R. Rasuna Said Blok X-5, Jakarta Selatan', 'Information Technology & Software Engineering', 'career@solusiteknologi.co.id', '021-52901122', 'Perusahaan pengembang solusi teknologi enterprise, platform cloud native, integrasi data analitik, dan kecerdasan buatan (AI) terdepan di Indonesia.');

-- 3. Internal IT Divisions
INSERT INTO `division` (`id_division`, `id_company`, `nama_divisi`, `deskripsi`) VALUES
(1, 1, 'Software Engineering', 'Pengembangan arsitektur backend, frontend modern, web platform, dan RESTful microservices.'),
(2, 1, 'Data Science & AI', 'Pemodelan machine learning, rekayasa data pipeline, big data analytics, dan visualisasi business intelligence.'),
(3, 1, 'Cloud Infrastructure & DevOps', 'Manajemen multi-cloud (AWS/GCP), orkestrasi Kubernetes, pipeline CI/CD, dan pemantauan sistem 24/7.'),
(4, 1, 'Cybersecurity & Compliance', 'Penetration testing, analisis kerentanan keamanan siber, tata kelola data, dan standar ISO 27001.'),
(5, 1, 'Product Management & UI/UX', 'Riset pengalaman pengguna, desain UI/UX berbasis Figma, serta perumusan roadmap produk teknologi.'),
(6, 1, 'Quality Assurance & Testing', 'Pengujian software otomatis (automation testing), API testing, bug tracking, dan benchmark performa sistem.'),
(7, 1, 'Tech Talent & People Operations', 'Talent acquisition teknologi, pelatihan kompetensi developer, kultur rekayasa, dan manajemen people operations.');

-- 4. Skills Catalog (IT Focused)
INSERT INTO `skill` (`id_skill`, `nama_skill`, `category`) VALUES
(1, 'Python', 'Programming & Data'),
(2, 'SQL', 'Database'),
(3, 'Excel & Analytics', 'Analytics'),
(4, 'Data Analysis & Tableau', 'Analytics'),
(5, 'Machine Learning & PyTorch', 'Data & AI'),
(6, 'PHP & Laravel', 'Backend Web'),
(7, 'JavaScript & TypeScript', 'Frontend & Backend'),
(8, 'React & Next.js', 'Frontend'),
(9, 'MySQL & PostgreSQL', 'Database'),
(10, 'Docker & Kubernetes', 'DevOps & Cloud'),
(11, 'AWS / GCP Cloud Architecture', 'DevOps & Cloud'),
(12, 'UI/UX Design & Figma', 'Design & Product'),
(13, 'Automated Testing (Selenium/Cypress)', 'Quality Assurance'),
(14, 'Cybersecurity & Ethical Hacking', 'Security'),
(15, 'Public Speaking & Tech Pitch', 'Soft Skills'),
(16, 'Leadership & Agile/Scrum', 'Management'),
(17, 'Git & Version Control', 'Tools & DevOps'),
(18, 'Linux System Administration', 'Infrastructure');

-- 5. Users (Admin, Interviewer, Applicants)
-- admin123 => $2y$12$0MmT6xdf2xYauP.U26CUp.KueKdg1ZKplA9hs8JDvk6glncOxHuEa
-- user123  => $2y$12$Fr70W3R5fPZqOdTmGC1NPOCdgM2QjK7qnh6QhiwcXKe2CVGdgxhQi

INSERT INTO `user_all` (`nik`, `nama`, `email`, `no_telepon`, `tanggal_lahir`, `pendidikan_terakhir`, `tahun_lulus`, `alamat`, `password`, `role`, `profile_photo`) VALUES
-- Admin
('3171012301900001', 'Admin HR Corporate', 'admin@sireka.com', '081199887766', '1990-01-23', 'S1 Sistem Informasi', 2012, 'Jl. HR Rasuna Said Kav 10, Jakarta Selatan', '$2y$12$0MmT6xdf2xYauP.U26CUp.KueKdg1ZKplA9hs8JDvk6glncOxHuEa', 'admin', NULL),
-- Interviewers (Internal IT Leads)
('3171012503880002', 'Dewi Lestari, M.Psi', 'dewi.recruiter@sireka.com', '081233445566', '1988-03-25', 'S2 Psikologi Industri', 2013, 'Jl. Tebet Barat Dalam No. 12, Jakarta Selatan', '$2y$12$0MmT6xdf2xYauP.U26CUp.KueKdg1ZKplA9hs8JDvk6glncOxHuEa', 'interviewer', NULL),
-- Applicants (12 Candidates)
('3201011505990001', 'Budi Santoso', 'budi.santoso@gmail.com', '081234567891', '1999-05-15', 'S1 Teknik Informatika', 2022, 'Jl. Melati No. 14, Depok, Jawa Barat', '$2y$12$Fr70W3R5fPZqOdTmGC1NPOCdgM2QjK7qnh6QhiwcXKe2CVGdgxhQi', 'user', NULL),
('3201012008980002', 'Siti Rahmawati', 'siti.rahmawati@gmail.com', '081234567892', '1998-08-20', 'S1 Statistika', 2021, 'Jl. Anggrek Raya No. 5, Bogor, Jawa Barat', '$2y$12$Fr70W3R5fPZqOdTmGC1NPOCdgM2QjK7qnh6QhiwcXKe2CVGdgxhQi', 'user', NULL),
('3174021102000003', 'Andi Pratama', 'andi.pratama@gmail.com', '081234567893', '2000-02-11', 'S1 Manajemen Bisnis & IT', 2023, 'Jl. Kebon Jeruk No. 88, Jakarta Barat', '$2y$12$Fr70W3R5fPZqOdTmGC1NPOCdgM2QjK7qnh6QhiwcXKe2CVGdgxhQi', 'user', NULL),
('3175030401970004', 'Rizky Fadillah', 'rizky.fadillah@gmail.com', '081234567894', '1997-01-04', 'S1 Ilmu Komputer', 2020, 'Jl. Duren Sawit No. 3, Jakarta Timur', '$2y$12$Fr70W3R5fPZqOdTmGC1NPOCdgM2QjK7qnh6QhiwcXKe2CVGdgxhQi', 'user', NULL),
('3578011212010005', 'Maya Indah Permata', 'maya.permata@gmail.com', '081234567895', '2001-12-12', 'S1 Sistem Informasi', 2023, 'Jl. Darmo No. 40, Surabaya, Jawa Timur', '$2y$12$Fr70W3R5fPZqOdTmGC1NPOCdgM2QjK7qnh6QhiwcXKe2CVGdgxhQi', 'user', NULL),
('3578022409990006', 'Deni Kurniawan', 'deni.kurniawan@gmail.com', '081234567896', '1999-09-24', 'S1 Teknik Komputer', 2022, 'Jl. Manyar Kertoarjo No. 17, Surabaya', '$2y$12$Fr70W3R5fPZqOdTmGC1NPOCdgM2QjK7qnh6QhiwcXKe2CVGdgxhQi', 'user', NULL),
('3171011406020007', 'Fadhil Rahman', 'fadhil.rahman@gmail.com', '081234567897', '2002-06-14', 'D3 Manajemen Informatika', 2024, 'Jl. Cempaka Putih No. 19, Jakarta Pusat', '$2y$12$Fr70W3R5fPZqOdTmGC1NPOCdgM2QjK7qnh6QhiwcXKe2CVGdgxhQi', 'user', NULL),
('3275010904990008', 'Nadia Safitri', 'nadia.safitri@gmail.com', '081234567898', '1999-04-09', 'S1 Desain Komunikasi Visual', 2021, 'Jl. Ahmad Yani No. 55, Bekasi, Jawa Barat', '$2y$12$Fr70W3R5fPZqOdTmGC1NPOCdgM2QjK7qnh6QhiwcXKe2CVGdgxhQi', 'user', NULL),
('3276012803000009', 'Reza Firmansyah', 'reza.firmansyah@gmail.com', '081234567899', '2000-03-28', 'S1 Teknik Informatika', 2022, 'Jl. Margonda No. 120, Depok', '$2y$12$Fr70W3R5fPZqOdTmGC1NPOCdgM2QjK7qnh6QhiwcXKe2CVGdgxhQi', 'user', NULL),
('3374011807980010', 'Clarissa Putri', 'clarissa.putri@gmail.com', '081234567810', '1998-07-18', 'S1 Desain Produk & Multimedia', 2020, 'Jl. Pemuda No. 70, Semarang', '$2y$12$Fr70W3R5fPZqOdTmGC1NPOCdgM2QjK7qnh6QhiwcXKe2CVGdgxhQi', 'user', NULL),
('3471010311010011', 'Kevin Sanjaya', 'kevin.sanjaya@gmail.com', '081234567811', '2001-11-03', 'S1 Ilmu Ekonomi', 2023, 'Jl. Kaliurang KM 5, Sleman, Yogyakarta', '$2y$12$Fr70W3R5fPZqOdTmGC1NPOCdgM2QjK7qnh6QhiwcXKe2CVGdgxhQi', 'user', NULL),
('3172011910000012', 'Annisa Zahra', 'annisa.zahra@gmail.com', '081234567812', '2000-10-19', 'S1 Psikologi', 2022, 'Jl. Kelapa Gading Boulevard No. 2, Jakarta Utara', '$2y$12$Fr70W3R5fPZqOdTmGC1NPOCdgM2QjK7qnh6QhiwcXKe2CVGdgxhQi', 'user', NULL);

-- 6. Company Admin Mapping
INSERT INTO `company_admin` (`id_admin`, `id_company`, `nik`, `position`) VALUES
(1, 1, '3171012301900001', 'Head of Talent Acquisition & People Ops');

-- 7. Interviewers (Internal IT Technical & HR Leads)
INSERT INTO `interviewer` (`id_interviewer`, `id_company`, `nama`, `email`, `no_telepon`, `position`) VALUES
(1, 1, 'Dewi Lestari, M.Psi', 'dewi.recruiter@sireka.com', '081233445566', 'Senior Tech Recruiter & People Lead'),
(2, 1, 'Bambang Triatmojo, S.Kom', 'bambang.techlead@solusiteknologi.co.id', '081288776655', 'Lead Backend Architect'),
(3, 1, 'Farhan Hakim, M.Sc', 'farhan.ai@solusiteknologi.co.id', '081399887766', 'Head of Data & AI Engineering'),
(4, 1, 'Rina Anggraini, S.T.', 'rina.cloud@solusiteknologi.co.id', '081311223344', 'Principal Cloud & DevOps Specialist');

-- 8. User Skills (Junction Table: user_skill)
INSERT INTO `user_skill` (`nik`, `id_skill`, `level`) VALUES
-- Budi Santoso (Python, SQL, Excel, Data Analysis, Machine Learning)
('3201011505990001', 1, 'Advanced'),
('3201011505990001', 2, 'Expert'),
('3201011505990001', 3, 'Advanced'),
('3201011505990001', 4, 'Advanced'),
('3201011505990001', 5, 'Intermediate'),
-- Siti Rahmawati (Python, SQL, Excel, Data Analysis)
('3201012008980002', 1, 'Intermediate'),
('3201012008980002', 2, 'Advanced'),
('3201012008980002', 3, 'Expert'),
('3201012008980002', 4, 'Advanced'),
-- Andi Pratama (Leadership, Public Speaking, Tech Management)
('3174021102000003', 15, 'Expert'),
('3174021102000003', 16, 'Advanced'),
('3174021102000003', 17, 'Intermediate'),
-- Rizky Fadillah (PHP, JavaScript, MySQL, React, Docker)
('3175030401970004', 6, 'Expert'),
('3175030401970004', 7, 'Advanced'),
('3175030401970004', 8, 'Advanced'),
('3175030401970004', 9, 'Expert'),
('3175030401970004', 10, 'Intermediate'),
-- Maya Indah Permata (QA, Automated Testing, SQL)
('3578011212010005', 2, 'Advanced'),
('3578011212010005', 9, 'Advanced'),
('3578011212010005', 13, 'Intermediate'),
-- Deni Kurniawan (Cloud, Linux, Docker, AWS)
('3578022409990006', 10, 'Advanced'),
('3578022409990006', 11, 'Advanced'),
('3578022409990006', 18, 'Expert'),
-- Fadhil Rahman (PHP, JavaScript, MySQL)
('3171011406020007', 6, 'Intermediate'),
('3171011406020007', 7, 'Beginner'),
('3171011406020007', 9, 'Intermediate'),
-- Nadia Safitri (UI/UX, Figma, Product Design)
('3275010904990008', 12, 'Expert'),
('3275010904990008', 15, 'Advanced'),
-- Reza Firmansyah (Python, SQL, Cloud, Docker)
('3276012803000009', 1, 'Intermediate'),
('3276012803000009', 2, 'Advanced'),
('3276012803000009', 10, 'Intermediate'),
('3276012803000009', 17, 'Advanced');

-- 9. Dedicated IT Vacancies (All under Company 1, distributed across IT Divisions)
INSERT INTO `job` (`id_job`, `id_division`, `nama_job`, `job_type`, `deskripsi`, `requirements`, `responsibilities`, `education_requirement`, `experience_requirement`, `salary_min`, `salary_max`, `location`, `deadline`, `status`) VALUES
-- 1. Data Analyst (Divisi 2: Data Science & AI)
(1, 2, 'Data Analyst', 'Kerja', 'Bertanggung jawab dalam mengolah, menganalisis data bisnis digital enterprise, dan membangun dashboard visual interaktif untuk pengambilan keputusan manajemen.', 'S1 Informatika/Statistika/Matematika, menguasai Python, SQL, Excel analitik, dan Tableau/PowerBI.', 'Membangun pipeline query SQL teroptimasi, menganalisis pola churn dan retensi pengguna, menyusun laporan insight bulanan.', 'S1', '1-2 Tahun', 8000000, 12000000, 'Jakarta Selatan (Hybrid)', '2026-10-30', 'Open'),

-- 2. Senior Backend Engineer (Divisi 1: Software Engineering)
(2, 1, 'Senior Backend Engineer', 'Kerja', 'Merancang dan mengembangkan arsitektur microservices performa tinggi, API gateway, dan integrasi database terdistribusi.', 'S1 Ilmu Komputer, minimal 2 tahun pengalaman PHP, MySQL/PostgreSQL, JavaScript, Docker, dan arsitektur REST/gRPC.', 'Mengoptimalkan query database relasional, mengimplementasikan caching Redis, memastikan ketersediaan API 99.9%.', 'S1', '2-4 Tahun', 14000000, 20000000, 'Jakarta Selatan (Hybrid)', '2026-10-15', 'Open'),

-- 3. Cloud DevOps Specialist (Divisi 3: Cloud Infrastructure & DevOps)
(3, 3, 'Cloud DevOps Specialist', 'Kerja', 'Mengelola orkestrasi klaster Kubernetes pada arsitektur AWS/GCP, otomatisasi pipeline CI/CD, dan observabilitas sistem.', 'S1 Teknik Komputer/Informatika, mahir Docker, Kubernetes, AWS/GCP, Linux, dan Terraform.', 'Mengotomasi proses deployment zero-downtime, memonitor metrik Prometheus & Grafana, menangani insiden infrastruktur.', 'S1', '2-3 Tahun', 13000000, 18000000, 'Jakarta Selatan / Remote', '2026-11-01', 'Open'),

-- 4. Cyber Security Analyst (Divisi 4: Cybersecurity & Compliance)
(4, 4, 'Cyber Security Analyst', 'Kerja', 'Melakukan vulnerability scanning, penetration testing sistem web/mobile, serta audit kepatuhan keamanan informasi ISO 27001.', 'S1 Ilmu Komputer/Keamanan Siber, memahami OWASP Top 10, penetration testing, dan analisis insiden SIEM.', 'Mendeteksi potensi ancaman keamanan jaringan, melakukan simulasi penyerangan etis, memberikan rekomendasi mitigasi.', 'S1', '1-3 Tahun', 11000000, 16000000, 'Jakarta Selatan (Onsite)', '2026-10-25', 'Open'),

-- 5. UI/UX Product Designer (Divisi 5: Product Management & UI/UX)
(5, 5, 'UI/UX Product Designer', 'Kerja', 'Merancang user experience, wireframe, design system, dan prototipe interaktif untuk aplikasi web & mobile enterprise.', 'S1 Desain Komunikasi Visual/Sistem Informasi, mahir Figma, user testing, prototyping, dan desain responsif.', 'Melakukan riset pengguna (usability testing), membuat design system komprehensif, berkolaborasi erat dengan frontend engineer.', 'S1', '1-3 Tahun', 8500000, 13000000, 'Jakarta Selatan (Hybrid)', '2026-10-20', 'Open'),

-- 6. Internship Data Science & AI (Divisi 2: Data Science & AI)
(6, 2, 'Internship Data Science & AI', 'Magang', 'Program magang intensif 6 bulan bagi mahasiswa tingkat akhir untuk mengeksplorasi machine learning, NLP, dan big data.', 'Mahasiswa aktif tingkat akhir / fresh graduate yang menguasai Python, SQL, dan dasar Machine Learning.', 'Membantu data scientist dalam cleansing data mentah, eksperimen model prediktif, dan dokumentasi analitik.', 'S1 (Mahasiswa Akhir / Fresh)', 'Fresh Graduate', 3500000, 4500000, 'Jakarta Selatan (Hybrid)', '2026-10-10', 'Open'),

-- 7. Internship Web Developer (Divisi 1: Software Engineering)
(7, 1, 'Internship Web Developer', 'Magang', 'Mendukung pembuatan modul web responsif dan integrasi API untuk platform rekrutmen dan sistem internal perusahaan.', 'Memahami dasar PHP, JavaScript, MySQL, HTML5/CSS3, dan penggunaan Git.', 'Memperbaiki issue frontend, menguji endpoint API, dan membuat dokumentasi teknis fitur web.', 'D3 / S1', 'Fresh Graduate', 3000000, 4000000, 'Jakarta Selatan (Hybrid)', '2026-10-12', 'Open'),

-- 8. QA Automation Engineer (Divisi 6: Quality Assurance & Testing)
(8, 6, 'QA Automation Engineer', 'Kerja', 'Membangun script pengujian otomatis (automated testing) end-to-end, API testing, dan regression test.', 'S1 Informatika, memiliki pemahaman automation tools seperti Selenium/Cypress/Postman, SQL, dan dasar pemrograman.', 'Menulis test case otomatis, menjalankan stress test sebelum deployment produksi, mendokumentasikan bug laporan kualitas.', 'S1', '1-2 Tahun', 7500000, 11000000, 'Jakarta Selatan (Hybrid)', '2026-10-18', 'Open'),

-- 9. Management Trainee - IT Leadership (Divisi 1: Software Engineering)
(9, 1, 'Management Trainee - IT Leadership', 'Management Trainee', 'Program akselerasi kepemimpinan teknologi 12 bulan untuk mencetak calon Engineering Lead dan Chief Technology Officer masa depan.', 'S1/S2 Ilmu Komputer/Sistem Informasi IPK min 3.25, memiliki jiwa leadership kuat, menguasai konsep database SQL dan Python.', 'Rotasi departemen teknologi (cloud, product, software engineering), memimpin inisiatif arsitektur strategis.', 'S1 / S2', 'Fresh Graduate - 1 Tahun', 10000000, 13000000, 'Jakarta Selatan (Onsite)', '2026-11-15', 'Open'),

-- 10. Mobile Application Developer (Divisi 1: Software Engineering)
(10, 1, 'Mobile Application Developer', 'Kerja', 'Mengembangkan aplikasi mobile native/cross-platform (Flutter/React Native) dengan performa responsif dan antarmuka elegan.', 'Minimal 1 tahun pengalaman mobile app development, menguasai JavaScript/TypeScript atau Flutter/Dart.', 'Mengintegrasikan REST API dengan antarmuka mobile, mengoptimalkan konsumsi memori dan offline storage.', 'S1', '1-2 Tahun', 9000000, 14000000, 'Jakarta Selatan (Hybrid)', '2026-11-20', 'Open'),

-- 11. AI & Machine Learning Specialist (Divisi 2: Data Science & AI)
(11, 2, 'AI & Machine Learning Specialist', 'Kerja', 'Mengembangkan model generative AI, transformer, dan computer vision untuk otomatisasi proses bisnis enterprise.', 'S1/S2 Informatika/Matematika, menguasai Python, PyTorch/TensorFlow, SQL, dan teknik fine-tuning LLM.', 'Merancang model deep learning siap produksi, mengevaluasi akurasi model, mendeploy model ke inferensi endpoint.', 'S1 / S2', '2-3 Tahun', 15000000, 22000000, 'Jakarta Selatan (Hybrid)', '2026-11-10', 'Open'),

-- 12. IT Support & System Administrator (Divisi 3: Cloud Infrastructure & DevOps)
(12, 3, 'IT Support & System Administrator', 'Kerja', 'Memelihara infrastruktur jaringan kantor, perangkat keras engineer, sistem operasional Linux/Windows, dan security endpoint.', 'D3/S1 Teknik Komputer, menguasai Linux administration, routing mikrotik, dan troubleshooting hardware.', 'Membantu onboarding perangkat engineer baru, memonitor bandwidth internet kantor, mengelola inventaris IT.', 'D3 / S1', '1 Tahun', 6500000, 9000000, 'Jakarta Selatan (Onsite)', '2026-10-31', 'Closed');

-- 10. Job Skills (Junction Table: job_skill)
-- Data Analyst (id_job=1): Python (1), SQL (2), Excel (3), Data Analysis (4), Machine Learning (5)
INSERT INTO `job_skill` (`id_job`, `id_skill`, `is_required`) VALUES
(1, 1, 1),
(1, 2, 1),
(1, 3, 1),
(1, 4, 1),
(1, 5, 1);

-- Senior Backend Engineer (id_job=2): PHP (6), MySQL (9), JavaScript (7), SQL (2)
INSERT INTO `job_skill` (`id_job`, `id_skill`, `is_required`) VALUES
(2, 6, 1),
(2, 9, 1),
(2, 7, 1),
(2, 2, 1);

-- Cloud DevOps Specialist (id_job=3): Docker/K8s (10), AWS/GCP (11), Linux (18), Git (17)
INSERT INTO `job_skill` (`id_job`, `id_skill`, `is_required`) VALUES
(3, 10, 1),
(3, 11, 1),
(3, 18, 1),
(3, 17, 1);

-- Cyber Security Analyst (id_job=4): Cybersecurity (14), Linux (18), SQL (2)
INSERT INTO `job_skill` (`id_job`, `id_skill`, `is_required`) VALUES
(4, 14, 1),
(4, 18, 1),
(4, 2, 1);

-- UI/UX Product Designer (id_job=5): UI/UX Design (12), Public Speaking (15)
INSERT INTO `job_skill` (`id_job`, `id_skill`, `is_required`) VALUES
(5, 12, 1),
(5, 15, 1);

-- Internship Data Science & AI (id_job=6): Python (1), SQL (2), Machine Learning (5)
INSERT INTO `job_skill` (`id_job`, `id_skill`, `is_required`) VALUES
(6, 1, 1),
(6, 2, 1),
(6, 5, 1);

-- Internship Web Developer (id_job=7): PHP (6), JavaScript (7), MySQL (9)
INSERT INTO `job_skill` (`id_job`, `id_skill`, `is_required`) VALUES
(7, 6, 1),
(7, 7, 1),
(7, 9, 1);

-- QA Automation Engineer (id_job=8): Automated Testing (13), SQL (2), JavaScript (7)
INSERT INTO `job_skill` (`id_job`, `id_skill`, `is_required`) VALUES
(8, 13, 1),
(8, 2, 1),
(8, 7, 1);

-- Management Trainee - IT Leadership (id_job=9): Leadership (16), Python (1), SQL (2)
INSERT INTO `job_skill` (`id_job`, `id_skill`, `is_required`) VALUES
(9, 16, 1),
(9, 1, 1),
(9, 2, 1);

-- Mobile Application Developer (id_job=10): JavaScript (7), React (8), Git (17)
INSERT INTO `job_skill` (`id_job`, `id_skill`, `is_required`) VALUES
(10, 7, 1),
(10, 8, 1),
(10, 17, 1);

-- AI & Machine Learning Specialist (id_job=11): Python (1), Machine Learning (5), SQL (2)
INSERT INTO `job_skill` (`id_job`, `id_skill`, `is_required`) VALUES
(11, 1, 1),
(11, 5, 1),
(11, 2, 1);

-- IT Support & System Administrator (id_job=12): Linux (18), Git (17)
INSERT INTO `job_skill` (`id_job`, `id_skill`, `is_required`) VALUES
(12, 18, 1),
(12, 17, 1);

-- 11. Applications (Demonstrative Statuses for Presentation)
INSERT INTO `application` (`id_application`, `nik`, `id_job`, `cv_file`, `cover_letter`, `portfolio_url`, `match_score`, `applied_at`, `current_status`) VALUES
(1, '3201011505990001', 1, 'cv_budi_santoso.pdf', 'Saya memiliki ketertarikan tinggi pada analisis data dan machine learning dengan rekam jejak sertifikasi Google Data Analytics.', 'https://github.com/budisantoso/portfolio', 100.00, '2026-09-01 09:15:00', 'Accepted'),
(2, '3201012008980002', 1, 'cv_siti_rahmawati.pdf', 'Lulusan Statistika dengan pengalaman analisis big data dan visualisasi insight bisnis menggunakan Python dan Tableau.', 'https://sitistat.github.io', 80.00, '2026-09-02 10:30:00', 'Interview'),
(3, '3175030401970004', 2, 'cv_rizky_fadillah.pdf', 'Senior backend engineer dengan pengalaman arsitektur microservices dan optimasi database relasional berskala besar.', 'https://rizkyfad.dev', 100.00, '2026-09-03 14:00:00', 'Document Screening'),
(4, '3578011212010005', 8, 'cv_maya_permata.pdf', 'QA engineer yang teliti dengan keahlian otomatisasi pengujian sistem dan verifikasi integritas data.', 'https://linkedin.com/in/mayapermata', 75.00, '2026-09-04 11:20:00', 'HR Review'),
(5, '3578022409990006', 3, 'cv_deni_kurniawan.pdf', 'DevOps engineer berpengalaman dalam otomatisasi pipeline Kubernetes dan monitoring infrastruktur AWS.', 'https://denikurnia.id', 100.00, '2026-09-05 16:45:00', 'Applied'),
(6, '3174021102000003', 9, 'cv_andi_pratama.pdf', 'Memiliki jiwa kepemimpinan aktif sebagai ketua himpunan dan minat mendalam dalam strategic IT governance.', 'https://andipratama.me', 66.67, '2026-08-20 08:30:00', 'Talent Pool'),
(7, '3471010311010011', 9, 'cv_kevin_sanjaya.pdf', 'Tertarik mempelajari manajemen teknologi walau berlatar belakang ekonomi umum.', NULL, 0.00, '2026-08-15 13:00:00', 'Rejected'),
(8, '3171011406020007', 7, 'cv_fadhil_rahman.pdf', 'Mahasiswa tingkat akhir bersemangat untuk mempraktikkan keterampilan pemrograman web modern dan REST API.', 'https://github.com/fadhilrahman', 100.00, '2026-09-02 15:10:00', 'Interview');

-- 12. Candidate Stage History (Recruitment Tracking Timeline)
INSERT INTO `candidate_stage_history` (`id_application`, `stage`, `status`, `notes`, `changed_by`, `changed_at`) VALUES
-- Application 1 (Budi Santoso - Accepted & Received LoA)
(1, 'Applied', 'Completed', 'Berkas lamaran Data Analyst berhasil disubmit.', NULL, '2026-09-01 09:15:00'),
(1, 'HR Review', 'Completed', 'Profil kandidat memenuhi kualifikasi teknis data science.', '3171012301900001', '2026-09-02 10:00:00'),
(1, 'Document Screening', 'Completed', 'CV, Ijazah S1, dan portofolio GitHub telah diverifikasi valid.', '3171012301900001', '2026-09-03 11:30:00'),
(1, 'Interview Scheduling', 'Completed', 'Jadwal wawancara teknis & user ditetapkan via Google Meet.', '3171012301900001', '2026-09-04 14:00:00'),
(1, 'Interview', 'Completed', 'Hasil interview teknis sangat memuaskan, penguasaan SQL query kompleks.', '3171012301900001', '2026-09-06 15:00:00'),
(1, 'Final Decision', 'Completed', 'Kandidat direkomendasikan diterima oleh Lead Data dan HR.', '3171012301900001', '2026-09-07 09:00:00'),
(1, 'Accepted', 'Current', 'Selamat! Anda diterima bekerja sebagai Data Analyst di PT Solusi Teknologi Nusantara. Silakan periksa dan cetak LoA resmi.', '3171012301900001', '2026-09-07 10:00:00'),

-- Application 2 (Siti Rahmawati - Currently in Interview)
(2, 'Applied', 'Completed', 'Berkas lamaran berhasil disubmit.', NULL, '2026-09-02 10:30:00'),
(2, 'HR Review', 'Completed', 'Kemampuan statistika dan SQL sangat relevan dengan kebutuhan divisi data.', '3171012301900001', '2026-09-03 09:45:00'),
(2, 'Document Screening', 'Completed', 'Dokumen terverifikasi lengkap.', '3171012301900001', '2026-09-04 13:00:00'),
(2, 'Interview Scheduling', 'Completed', 'Jadwal interview telah dikirimkan ke dashboard kandidat.', '3171012301900001', '2026-09-05 10:00:00'),
(2, 'Interview', 'Current', 'Wawancara kompetensi dijadwalkan bersama Tim Rekrutmen & Lead Analitik.', '3171012301900001', '2026-09-05 10:30:00'),

-- Application 3 (Rizky Fadillah - Document Screening)
(3, 'Applied', 'Completed', 'Berkas lamaran Senior Backend Engineer diterima.', NULL, '2026-09-03 14:00:00'),
(3, 'HR Review', 'Completed', 'Keahlian PHP microservices dan optimasi MySQL sangat sesuai.', '3171012301900001', '2026-09-04 16:00:00'),
(3, 'Document Screening', 'Current', 'Verifikasi riwayat portofolio arsitektur backend dan referensi kerja.', '3171012301900001', '2026-09-05 09:00:00'),

-- Application 4 (Maya Indah - HR Review)
(4, 'Applied', 'Completed', 'Lamaran QA Automation berhasil disubmit.', NULL, '2026-09-04 11:20:00'),
(4, 'HR Review', 'Current', 'Kandidat sedang dalam proses peninjauan oleh tim Tech Recruiter.', '3171012301900001', '2026-09-05 11:00:00'),

-- Application 5 (Deni Kurniawan - Applied)
(5, 'Applied', 'Current', 'Lamaran baru masuk untuk posisi Cloud DevOps, menunggu antrean peninjauan HR.', NULL, '2026-09-05 16:45:00'),

-- Application 6 (Andi Pratama - Talent Pool)
(6, 'Applied', 'Completed', 'Lamaran disubmit.', NULL, '2026-08-20 08:30:00'),
(6, 'HR Review', 'Completed', 'Kandidat memiliki potensi kepemimpinan yang baik.', '3171012301900001', '2026-08-21 10:00:00'),
(6, 'Document Screening', 'Completed', 'Dokumen lengkap.', '3171012301900001', '2026-08-22 14:00:00'),
(6, 'Interview', 'Completed', 'Wawancara berjalan lancar, namun kuota batch saat ini telah terpenuhi.', '3171012301900001', '2026-08-25 15:30:00'),
(6, 'Talent Pool', 'Current', 'Kandidat potensial disimpan ke dalam Talent Pool untuk dihubungi pada pembukaan posisi engineering berikutnya.', '3171012301900001', '2026-08-26 09:00:00'),

-- Application 7 (Kevin Sanjaya - Rejected)
(7, 'Applied', 'Completed', 'Lamaran disubmit.', NULL, '2026-08-15 13:00:00'),
(7, 'HR Review', 'Completed', 'Kandidat belum memenuhi prasyarat teknis IT Leadership.', '3171012301900001', '2026-08-16 11:00:00'),
(7, 'Rejected', 'Current', 'Mohon maaf, profil Anda belum sesuai dengan kualifikasi teknis yang dipersyaratkan untuk posisi ini.', '3171012301900001', '2026-08-17 10:00:00'),

-- Application 8 (Fadhil Rahman - Interview)
(8, 'Applied', 'Completed', 'Lamaran magang developer disubmit.', NULL, '2026-09-02 15:10:00'),
(8, 'HR Review', 'Completed', 'Kandidat memiliki dasar web programming yang baik.', '3171012301900001', '2026-09-03 14:00:00'),
(8, 'Document Screening', 'Completed', 'Transkrip nilai dan portofolio GitHub sesuai.', '3171012301900001', '2026-09-04 10:00:00'),
(8, 'Interview', 'Current', 'Undangan wawancara teknis magang developer.', '3171012301900001', '2026-09-05 14:00:00');

-- 13. Interview Schedules
INSERT INTO `interview` (`id_interview`, `id_application`, `id_interviewer`, `tanggal`, `waktu`, `type`, `location`, `meeting_link`, `notes`, `status`) VALUES
(1, 2, 1, '2026-09-15', '10:00:00', 'Online', 'Online via Google Meet', 'https://meet.google.com/abc-sireka-rec', 'Wawancara kompetensi analitik dan kepribadian. Mohon siapkan presentasi studi kasus data.', 'Scheduled'),
(2, 8, 2, '2026-09-16', '14:00:00', 'Online', 'Online via Google Meet', 'https://meet.google.com/xyz-dev-intern', 'Live coding review dan diskusi proyek web programming.', 'Scheduled');

-- 14. Talent Pool Records (All belonging to Company 1)
INSERT INTO `talent_pool` (`id_talent_pool`, `nik`, `id_company`, `source_application`, `reason`, `status`, `added_at`) VALUES
(1, '3174021102000003', 1, 6, 'Kandidat memiliki leadership dan technical adaptability yang sangat baik. Kuota batch penuh, diprioritaskan saat ekspansi tim kuartal berikutnya.', 'Available', '2026-08-26 09:00:00'),
(2, '3275010904990008', 1, NULL, 'Kandidat memiliki portofolio UI/UX Figma berstandar tinggi, disimpan untuk kebutuhan proyek produk baru.', 'Available', '2026-09-01 10:00:00');

-- 15. Letter of Acceptance (LoA) - Official from PT Solusi Teknologi Nusantara
INSERT INTO `loa` (`id_loa`, `id_application`, `loa_number`, `issue_date`, `join_date`, `position`, `division`, `status`, `authorized_by`, `notes`) VALUES
(1, 1, 'LOA/STN-HC/2026/IX/0042', '2026-09-07', '2026-10-01', 'Data Analyst', 'Data Science & AI', 'Issued', 'Dr. Hendra Gunawan, S.Kom., M.M. (VP Human Capital & People Ops)', 'Selamat bergabung di keluarga besar PT Solusi Teknologi Nusantara. Harap melengkapi dokumen onboarding digital sebelum tanggal bergabung.');

-- 16. Complaints / Helpdesk Tickets
INSERT INTO `complaint` (`id_complaint`, `nik`, `subject`, `category`, `description`, `attachment`, `status`, `admin_response`, `created_at`, `resolved_at`) VALUES
(1, '3201012008980002', 'Pertanyaan Mengenai Tautan Google Meet Interview', 'Interview Schedule', 'Tautan meeting Google Meet apakah bisa diakses menggunakan akun email non-institusi?', NULL, 'Resolved', 'Tautan meeting bersifat terbuka untuk semua akun Google. Tim rekrutmen akan mengizinkan masuk saat sesi dimulai.', '2026-09-06 08:30:00', '2026-09-06 09:15:00'),
(2, '3175030401970004', 'Konfirmasi Format Upload CV', 'Pendaftaran', 'Apakah CV boleh mencantumkan link repositori GitHub dan sertifikasi online?', NULL, 'Closed', 'Sangat disarankan mencantumkan link portofolio aktif pada kolom cover letter atau portfolio URL.', '2026-09-04 10:00:00', '2026-09-04 10:30:00');

-- 17. Feedback
INSERT INTO `feedback` (`id_feedback`, `nik`, `rating`, `message`, `created_at`) VALUES
(1, '3201011505990001', 5, 'Fitur Match Score sangat membantu mengetahui kecocokan skill saya dengan kebutuhan lowongan, dan alur tracking status sangat transparan!', '2026-09-07 11:30:00'),
(2, '3174021102000003', 5, 'Sangat menghargai kultur engineering PT Solusi Teknologi Nusantara dan fitur Talent Pool sehingga kandidat tetap diberi peluang di masa depan.', '2026-08-27 14:20:00');
