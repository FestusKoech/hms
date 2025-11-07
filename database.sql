CREATE DATABASE IF NOT EXISTS hms CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE hms;

CREATE TABLE users (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(191) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','doctor','pharmacist','labtech','receptionist') NOT NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO users(name,email,password,role)
VALUES ('System Admin','admin@hospital.local', '{PASSWORD_HASH}', 'admin');

-- Replace {PASSWORD_HASH} with output of: password_hash('Admin@123', PASSWORD_DEFAULT)

CREATE TABLE patients (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  patient_no VARCHAR(30) NOT NULL UNIQUE,
  first_name VARCHAR(80) NOT NULL,
  last_name VARCHAR(80) NOT NULL,
  dob DATE NULL,
  sex ENUM('M','F','O') NULL,
  contact VARCHAR(60) NULL,
  address VARCHAR(255) NULL,
  emergency_contact VARCHAR(120) NULL,
  created_by BIGINT UNSIGNED NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE appointments (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  patient_id BIGINT UNSIGNED NOT NULL,
  doctor_id BIGINT UNSIGNED NOT NULL,
  starts_at DATETIME NOT NULL,
  ends_at DATETIME NOT NULL,
  reason VARCHAR(255) NULL,
  status ENUM('scheduled','completed','cancelled') NOT NULL DEFAULT 'scheduled',
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (patient_id) REFERENCES patients(id),
  FOREIGN KEY (doctor_id) REFERENCES users(id)
) ENGINE=InnoDB;














USE hms;

-- DRUGS (Pharmacy catalog)
CREATE TABLE IF NOT EXISTS drugs (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  sku VARCHAR(60) UNIQUE NOT NULL,
  name VARCHAR(160) NOT NULL,
  form VARCHAR(60) NULL,         -- tablet/syrup/capsule
  strength VARCHAR(60) NULL,     -- 500mg, 5mg/5ml
  qty_on_hand INT NOT NULL DEFAULT 0,
  reorder_level INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- PRESCRIPTIONS (header)
CREATE TABLE IF NOT EXISTS prescriptions (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  patient_id BIGINT UNSIGNED NOT NULL,
  doctor_id BIGINT UNSIGNED NOT NULL,
  notes TEXT NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (patient_id) REFERENCES patients(id),
  FOREIGN KEY (doctor_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- PRESCRIPTION ITEMS (lines)
CREATE TABLE IF NOT EXISTS prescription_items (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  prescription_id BIGINT UNSIGNED NOT NULL,
  drug_id BIGINT UNSIGNED NOT NULL,
  dosage VARCHAR(120) NOT NULL,   -- "1 tab"
  frequency VARCHAR(60) NOT NULL, -- "t.i.d."
  duration_days INT NOT NULL,     -- "5"
  dispensed TINYINT(1) NOT NULL DEFAULT 0,
  FOREIGN KEY (prescription_id) REFERENCES prescriptions(id),
  FOREIGN KEY (drug_id) REFERENCES drugs(id)
) ENGINE=InnoDB;

-- LAB TEST CATALOG
CREATE TABLE IF NOT EXISTS lab_tests (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  code VARCHAR(40) UNIQUE NOT NULL,
  name VARCHAR(160) NOT NULL,
  normal_range VARCHAR(120) NULL,
  unit VARCHAR(40) NULL
) ENGINE=InnoDB;

-- LAB REPORTS (results for a patient)
CREATE TABLE IF NOT EXISTS lab_reports (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  patient_id BIGINT UNSIGNED NOT NULL,
  test_id BIGINT UNSIGNED NOT NULL,
  ordered_by BIGINT UNSIGNED NOT NULL,   -- doctor
  result_value VARCHAR(120) NULL,
  result_text TEXT NULL,
  reported_by BIGINT UNSIGNED NULL,      -- labtech
  reported_at DATETIME NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (patient_id) REFERENCES patients(id),
  FOREIGN KEY (test_id) REFERENCES lab_tests(id),
  FOREIGN KEY (ordered_by) REFERENCES users(id),
  FOREIGN KEY (reported_by) REFERENCES users(id)
) ENGINE=InnoDB;

-- PATIENT REPORTS (doctor notes / discharge summaries)
CREATE TABLE IF NOT EXISTS patient_reports (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  patient_id BIGINT UNSIGNED NOT NULL,
  doctor_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(160) NOT NULL,
  body TEXT NOT NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (patient_id) REFERENCES patients(id),
  FOREIGN KEY (doctor_id) REFERENCES users(id)
) ENGINE=InnoDB;



--







INSERT IGNORE INTO lab_tests(code,name,normal_range,unit) VALUES
('FBC','Full Blood Count',NULL,NULL),
('LFT','Liver Function Test',NULL,NULL),
('CRP','C-Reactive Protein','<5','mg/L');

INSERT IGNORE INTO drugs(sku,name,form,strength,qty_on_hand,reorder_level) VALUES
('AMOX500','Amoxicillin','tablet','500mg',100,20),
('PARA500','Paracetamol','tablet','500mg',200,50),
('IBU400','Ibuprofen','tablet','400mg',150,30);







INSERT INTO users(name,email,password,role) VALUES
('Dr. Asha','doctor@afia.ac.ke','$2y$10$DwYwr7Xidk5v9e3zOQ.GteQxIE0bJHLEqYq6VTCvIxj8NQz4bznRm','doctor'),
('Pharm. Brian','pharmacist@afia.ac.ke','$2y$10$DwYwr7Xidk5v9e3zOQ.GteQxIE0bJHLEqYq6VTCvIxj8NQz4bznRm','pharmacist'),
('Lab Tech Carol','lab@afia.ac.ke','$2y$10$DwYwr7Xidk5v9e3zOQ.GteQxIE0bJHLEqYq6VTCvIxj8NQz4bznRm','labtech'),
('Recep. Diana','reception@afia.ac.ke','$2y$10$DwYwr7Xidk5v9e3zOQ.GteQxIE0bJHLEqYq6VTCvIxj8NQz4bznRm','receptionist');
-- password for all: Admin@123



USE hms;

/* Password hash for: Admin@123  */
SET @HASH := '$2y$10$DwYwr7Xidk5v9e3zOQ.GteQxIE0bJHLEqYq6VTCvIxj8NQz4bznRm';

/* Create users if they don't already exist */
INSERT INTO users (name,email,password,role)
SELECT 'Dr. Asha','doctor@hospital.local',@HASH,'doctor'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email='doctor@hospital.local');

INSERT INTO users (name,email,password,role)
SELECT 'Pharm. Brian','pharmacist@hospital.local',@HASH,'pharmacist'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email='pharmacist@hospital.local');

INSERT INTO users (name,email,password,role)
SELECT 'Lab Tech Carol','lab@hospital.local',@HASH,'labtech'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email='lab@hospital.local');

INSERT INTO users (name,email,password,role)
SELECT 'Recep. Diana','reception@hospital.local',@HASH,'receptionist'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email='reception@hospital.local');
