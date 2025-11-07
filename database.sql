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





USE hms;

-- 0.1 LAB ORDERS (doctor schedules; labtech sees and completes)
CREATE TABLE IF NOT EXISTS lab_orders (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  patient_id BIGINT UNSIGNED NOT NULL,
  test_id BIGINT UNSIGNED NOT NULL,
  ordered_by BIGINT UNSIGNED NOT NULL,
  status ENUM('ordered','reported') NOT NULL DEFAULT 'ordered',
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (patient_id) REFERENCES patients(id),
  FOREIGN KEY (test_id) REFERENCES lab_tests(id),
  FOREIGN KEY (ordered_by) REFERENCES users(id)
) ENGINE=InnoDB;

-- 0.2 PHARMACY DISPENSE LOG (history of dispensed drugs)
CREATE TABLE IF NOT EXISTS pharmacy_dispenses (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  prescription_item_id BIGINT UNSIGNED NOT NULL,
  qty INT NOT NULL DEFAULT 1,
  dispensed_by BIGINT UNSIGNED NOT NULL,
  dispensed_at DATETIME NOT NULL,
  FOREIGN KEY (prescription_item_id) REFERENCES prescription_items(id),
  FOREIGN KEY (dispensed_by) REFERENCES users(id)
) ENGINE=InnoDB;

-- helpful index for patient search
CREATE INDEX idx_patients_name ON patients(last_name, first_name);
CREATE INDEX idx_patients_no ON patients(patient_no);



-- 1) Add a stock column (safe if it doesn't exist yet)
ALTER TABLE drugs
  ADD COLUMN stock INT NOT NULL DEFAULT 0 AFTER strength;

-- 2) If you previously stored quantity in another column, copy it over (optional)
-- (Uncomment the one that matches your schema)
-- UPDATE drugs SET stock = quantity       WHERE stock = 0 AND quantity       IS NOT NULL;
-- UPDATE drugs SET stock = qty_in_stock   WHERE stock = 0 AND qty_in_stock   IS NOT NULL;
-- UPDATE drugs SET stock = available_qty  WHERE stock = 0 AND available_qty  IS NOT NULL;

-- 3) Give everything a sane default so the dashboard & pharmacy work right away
UPDATE drugs SET stock = 50 WHERE stock = 0;



INSERT INTO drugs (name, strength, stock) VALUES
('Paracetamol', '500mg tablet', 120),
('Amoxicillin', '500mg capsule', 60),
('Ibuprofen', '200mg tablet', 200),
('Metformin', '500mg tablet', 80),
('Azithromycin', '250mg tablet', 50),
('Ciprofloxacin', '500mg tablet', 40),
('Cetirizine', '10mg tablet', 300),
('Lisinopril', '10mg tablet', 75),
('Atorvastatin', '20mg tablet', 110),
('Omeprazole', '20mg capsule', 150),
('Amlodipine', '5mg tablet', 100),
('Losartan', '50mg tablet', 95),
('Hydrochlorothiazide', '25mg tablet', 85),
('Simvastatin', '20mg tablet', 60),
('Prednisone', '10mg tablet', 45),
('Furosemide', '40mg tablet', 120),
('Insulin Glargine', '100IU/ml injection', 30),
('Salbutamol', '100mcg inhaler', 40),
('Doxycycline', '100mg capsule', 65),
('Clindamycin', '300mg capsule', 70),
('Ceftriaxone', '1g injection', 25),
('Ketoconazole', '200mg tablet', 55),
('Gentamicin', '80mg injection', 35),
('Vitamin C', '500mg tablet', 200),
('Zinc Sulphate', '20mg tablet', 180),
('Ferrous Sulphate', '325mg tablet', 140),
('Ranitidine', '150mg tablet', 95),
('Diazepam', '5mg tablet', 70),
('Lorazepam', '2mg tablet', 60),
('Tramadol', '50mg capsule', 85),
('Codeine', '30mg tablet', 25),
('Morphine', '10mg injection', 20),
('Aspirin', '75mg tablet', 300),
('Hydrocortisone', '100mg injection', 40),
('Cefuroxime', '500mg tablet', 90),
('Erythromycin', '250mg tablet', 50),
('Dexamethasone', '4mg injection', 25),
('Cough Syrup', '100ml bottle', 70),
('ORS', 'Sachet', 200),
('Antacid', '500mg tablet', 160),
('Chloroquine', '250mg tablet', 35),
('Artemether/Lumefantrine', '20/120mg tablet', 90),
('Insulin Regular', '100IU/ml injection', 25),
('Metronidazole', '400mg tablet', 100),
('Benzyl Penicillin', '1MU injection', 30),
('Diclofenac', '50mg tablet', 120),
('Amoxiclav', '625mg tablet', 70),
('Sodium Chloride', '500ml infusion', 40),
('Ringer’s Lactate', '500ml infusion', 45),
('Glucose 5%', '500ml infusion', 55);




