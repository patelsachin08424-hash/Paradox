-- =============================================================================
-- ONLINE HEALTHCARE MANAGEMENT SYSTEM
-- University Project Database Schema (MySQL / MariaDB)
-- Contains: users, patients, doctors, appointments, medical_records, prescriptions
-- Author: University Project Development Team
-- =============================================================================


USE `healthcare_db`;

-- -----------------------------------------------------------------------------
USE `if0_42934623_pulsehealth`;
-- Roles: 'admin', 'doctor', 'patient'
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(60) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'doctor', 'patient') NOT NULL,
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -----------------------------------------------------------------------------
-- 2. PATIENTS TABLE (Personal, demographic, and medical background)
-- Linked to users table via user_id
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `patients` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `full_name` VARCHAR(100) NOT NULL,
    `dob` DATE NULL,
    `gender` ENUM('Male', 'Female', 'Other') DEFAULT 'Male',
    `phone` VARCHAR(20) NOT NULL,
    `blood_group` VARCHAR(5) DEFAULT 'O+',
    `address` TEXT NULL,
    `emergency_contact` VARCHAR(100) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------------------------------
-- 3. DOCTORS TABLE (Specialization, room, fee, availability schedule)
-- Linked to users table via user_id
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `doctors` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `full_name` VARCHAR(100) NOT NULL,
    `specialization` VARCHAR(100) NOT NULL,
    `qualification` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20) NOT NULL,
    `room_no` VARCHAR(20) NOT NULL,
    `consultation_fee` DECIMAL(8, 2) NOT NULL DEFAULT 500.00,
    `bio` TEXT NULL,
    `available_days` VARCHAR(100) DEFAULT 'Mon - Fri, 09:00 AM - 05:00 PM',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------------------------------
-- 4. APPOINTMENTS TABLE (Booking schedule and consultation status)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `appointments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `patient_id` INT NOT NULL,
    `doctor_id` INT NOT NULL,
    `appointment_date` DATE NOT NULL,
    `appointment_time` TIME NOT NULL,
    `reason` TEXT NOT NULL,
    `status` ENUM('Pending', 'Confirmed', 'Completed', 'Cancelled') DEFAULT 'Pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`patient_id`) REFERENCES `patients`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`doctor_id`) REFERENCES `doctors`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------------------------------
-- 5. MEDICAL_RECORDS TABLE (Clinical diagnoses, test observations, symptoms)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `medical_records` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `patient_id` INT NOT NULL,
    `doctor_id` INT NOT NULL,
    `diagnosis` VARCHAR(255) NOT NULL,
    `symptoms` TEXT NOT NULL,
    `test_results` TEXT NULL,
    `notes` TEXT NULL,
    `recorded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`patient_id`) REFERENCES `patients`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`doctor_id`) REFERENCES `doctors`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------------------------------
-- 6. PRESCRIPTIONS TABLE (Prescribed medications, dosages, usage notes)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `prescriptions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `appointment_id` INT NULL,
    `patient_id` INT NOT NULL,
    `doctor_id` INT NOT NULL,
    `medicines` TEXT NOT NULL,
    `dosage` TEXT NOT NULL,
    `instructions` TEXT NULL,
    `prescribed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`appointment_id`) REFERENCES `appointments`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`patient_id`) REFERENCES `patients`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`doctor_id`) REFERENCES `doctors`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =============================================================================
-- SAMPLE SEED DATA FOR DEMO & UNIVERSITY EVALUATION
-- Default Passwords:
-- Admin:    admin@hospital.com   / admin123
-- Doctors:  doctor.smith@...     / doctor123
--           doctor.elena@...     / doctor123
-- Patients: patient.john@...     / patient123
--           patient.sarah@...    / patient123
-- =============================================================================

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `status`) VALUES
(1, 'admin', 'admin@hospital.com', '$2y$10$HdLIvuiFLw5SnmkIBDX2KuJ3yX88yGJyn.FUd9exoT.vJ45njSSQ.', 'admin', 'active'),
(2, 'dr_smith', 'doctor.smith@hospital.com', '$2y$10$nBJMGgRrq8Kt0QJMUlUMqOdKdHMAAd7f8GMdOQdO7mpvYEdCP7b6O', 'doctor', 'active'),
(3, 'dr_elena', 'doctor.elena@hospital.com', '$2y$10$nBJMGgRrq8Kt0QJMUlUMqOdKdHMAAd7f8GMdOQdO7mpvYEdCP7b6O', 'doctor', 'active'),
(4, 'dr_marcus', 'doctor.marcus@hospital.com', '$2y$10$nBJMGgRrq8Kt0QJMUlUMqOdKdHMAAd7f8GMdOQdO7mpvYEdCP7b6O', 'doctor', 'active'),
(5, 'john_doe', 'patient.john@gmail.com', '$2y$10$x5eQqr0mW4ctIrhbbNB0Z.HfP.g24ZbzEJo5Tw5WT4J5U6GcqagpO', 'patient', 'active'),
(6, 'sarah_connor', 'patient.sarah@gmail.com', '$2y$10$x5eQqr0mW4ctIrhbbNB0Z.HfP.g24ZbzEJo5Tw5WT4J5U6GcqagpO', 'patient', 'active')
ON DUPLICATE KEY UPDATE `id`=`id`;

INSERT INTO `doctors` (`id`, `user_id`, `full_name`, `specialization`, `qualification`, `phone`, `room_no`, `consultation_fee`, `bio`, `available_days`) VALUES
(1, 2, 'Dr. Robert Smith, MD', 'Cardiology', 'MD (Cardiology), FACC', '+1 (555) 234-5678', 'Suite 302', 750.00, 'Senior Cardiologist with 14+ years experience in preventive cardiology, coronary interventions, and hypertension management.', 'Mon - Thu, 09:00 AM - 03:00 PM'),
(2, 3, 'Dr. Elena Rostova, MD', 'Neurology', 'MD (Neurology), PhD', '+1 (555) 345-6789', 'Suite 415', 800.00, 'Specialist in clinical neurophysiology, headache disorders, and neuromuscular rehabilitation with research background at Johns Hopkins.', 'Tue - Fri, 10:00 AM - 04:00 PM'),
(3, 4, 'Dr. Marcus Vance, MBBS', 'Pediatrics', 'MBBS, DCH, FAAP', '+1 (555) 456-7890', 'Suite 108', 550.00, 'Dedicated Pediatric specialist passionate about neonatal care, developmental milestones, and childhood immunization programs.', 'Mon - Sat, 08:30 AM - 02:00 PM')
ON DUPLICATE KEY UPDATE `id`=`id`;

INSERT INTO `patients` (`id`, `user_id`, `full_name`, `dob`, `gender`, `phone`, `blood_group`, `address`, `emergency_contact`) VALUES
(1, 5, 'John Doe', '1990-05-14', 'Male', '+1 (555) 987-6543', 'O+', '442 Metro Boulevard, Suite 5B, New York, NY', 'Mary Doe (Wife) - +1 555-987-6544'),
(2, 6, 'Sarah Connor', '1995-11-22', 'Female', '+1 (555) 876-5432', 'A+', '120 Elm Wood Court, Los Angeles, CA', 'David Connor (Brother) - +1 555-876-5433')
ON DUPLICATE KEY UPDATE `id`=`id`;

INSERT INTO `appointments` (`id`, `patient_id`, `doctor_id`, `appointment_date`, `appointment_time`, `reason`, `status`) VALUES
(1, 1, 1, DATE_ADD(CURRENT_DATE, INTERVAL 2 DAY), '10:30:00', 'Routine heart checkup, occasional palpitations after aerobic exercise.', 'Confirmed'),
(2, 1, 2, DATE_ADD(CURRENT_DATE, INTERVAL 5 DAY), '14:00:00', 'Persistent tension headaches for the last 3 weeks.', 'Pending'),
(3, 2, 3, DATE_ADD(CURRENT_DATE, INTERVAL 1 DAY), '09:00:00', 'Annual pediatric wellness checkup and vaccine consultation.', 'Confirmed'),
(4, 2, 1, DATE_SUB(CURRENT_DATE, INTERVAL 4 DAY), '11:15:00', 'Blood pressure monitoring review.', 'Completed')
ON DUPLICATE KEY UPDATE `id`=`id`;

INSERT INTO `medical_records` (`id`, `patient_id`, `doctor_id`, `diagnosis`, `symptoms`, `test_results`, `notes`, `recorded_at`) VALUES
(1, 2, 1, 'Stage 1 Essential Hypertension', 'Mild morning occipital headache, fatigue upon exertion.', 'BP: 138/88 mmHg. Resting Pulse: 78 bpm. ECG: Normal Sinus Rhythm with no ischemic signs.', 'Advised low-sodium dietary regimen (DASH diet), 30 minutes daily brisk walk, and monitor systolic blood pressure.', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 4 DAY)),
(2, 1, 1, 'Mild Mitral Valve Prolapse (Asymptomatic)', 'Palpitations during heavy cardio sessions.', 'Echocardiogram: Trace MR, EF 62%. Normal left ventricular chamber dimensions.', 'Reassured patient; benign condition. Avoid excessive stimulant caffeine intake. Follow-up in 12 months.', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 20 DAY))
ON DUPLICATE KEY UPDATE `id`=`id`;

INSERT INTO `prescriptions` (`id`, `appointment_id`, `patient_id`, `doctor_id`, `medicines`, `dosage`, `instructions`, `prescribed_at`) VALUES
(1, 4, 2, 1, '1. Amlodipine 5mg\n2. Hydrochlorothiazide 12.5mg\n3. CoQ10 100mg supplement', '1 tablet daily morning after breakfast\n1 tablet once daily morning\n1 capsule after lunch', 'Take blood pressure readings every morning and record in the mobile portal. Return for checkup in 4 weeks.', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 4 DAY)),
(2, NULL, 1, 1, '1. Metoprolol Tartrate 25mg\n2. Magnesium Glycinate 200mg', 'Half tablet as needed for severe palpitations\n1 tablet at bedtime', 'Drink at least 2.5L water daily. Avoid energy drinks and excessive caffeine.', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 20 DAY))
ON DUPLICATE KEY UPDATE `id`=`id`;
