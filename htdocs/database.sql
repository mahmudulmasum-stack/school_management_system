-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: sql310.infinityfree.com
-- Generation Time: Jun 27, 2026 at 12:00 AM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- ========================================================
-- ডেটাবেস তৈরি করুন (যদি না থাকে)
-- ========================================================

CREATE DATABASE IF NOT EXISTS `if0_42282183_school_management`;
USE `if0_42282183_school_management`;

-- ========================================================
-- সব টেবিল তৈরি করুন (DROP TABLE IF EXISTS দিয়ে)
-- ========================================================

-- Table: users
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','teacher','staff') DEFAULT 'staff',
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  `nfc_tag` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `nfc_tag` (`nfc_tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: school_info
DROP TABLE IF EXISTS `school_info`;
CREATE TABLE `school_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_name` varchar(200) NOT NULL,
  `school_logo` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `phone2` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `website` varchar(100) DEFAULT NULL,
  `established_year` year(4) DEFAULT NULL,
  `eiin` varchar(20) DEFAULT NULL,
  `about` text DEFAULT NULL,
  `mission` text DEFAULT NULL,
  `vision` text DEFAULT NULL,
  `facebook` varchar(255) DEFAULT NULL,
  `youtube` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: students
DROP TABLE IF EXISTS `students`;
CREATE TABLE `students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` varchar(20) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `father_name` varchar(100) DEFAULT NULL,
  `mother_name` varchar(100) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `guardian_phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `class` varchar(20) NOT NULL,
  `section` varchar(20) DEFAULT NULL,
  `roll` varchar(20) NOT NULL,
  `group_name` varchar(50) DEFAULT NULL,
  `blood_group` varchar(10) DEFAULT NULL,
  `card_id` varchar(50) NOT NULL,
  `photo_path` varchar(255) DEFAULT 'uploads/default.jpg',
  `admission_date` date DEFAULT NULL,
  `status` enum('active','inactive','graduated') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `card_id` (`card_id`),
  UNIQUE KEY `roll` (`roll`),
  KEY `student_id` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: teachers
DROP TABLE IF EXISTS `teachers`;
CREATE TABLE `teachers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `teacher_id` varchar(20) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `father_name` varchar(100) DEFAULT NULL,
  `mother_name` varchar(100) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `qualification` varchar(255) DEFAULT NULL,
  `designation` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `blood_group` varchar(10) DEFAULT NULL,
  `card_id` varchar(50) NOT NULL,
  `photo_path` varchar(255) DEFAULT 'uploads/default.jpg',
  `joining_date` date DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive','resigned') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `card_id` (`card_id`),
  KEY `teacher_id` (`teacher_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: staff
DROP TABLE IF EXISTS `staff`;
CREATE TABLE `staff` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` varchar(20) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `designation` varchar(100) NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `blood_group` varchar(10) DEFAULT NULL,
  `card_id` varchar(50) NOT NULL,
  `photo_path` varchar(255) DEFAULT 'uploads/default.jpg',
  `joining_date` date DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `status` enum('active','inactive','resigned') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `card_id` (`card_id`),
  KEY `staff_id` (`staff_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: attendance
DROP TABLE IF EXISTS `attendance`;
CREATE TABLE `attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `time_in` time DEFAULT NULL,
  `time_out` time DEFAULT NULL,
  `status` enum('present','absent','late','half_day') NOT NULL DEFAULT 'present',
  `nfc_tag` varchar(50) DEFAULT NULL,
  `nfc_scanned` tinyint(1) DEFAULT 0,
  `scanned_by` int(11) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `marked_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `nfc_tag` (`nfc_tag`),
  KEY `date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: teacher_attendance
DROP TABLE IF EXISTS `teacher_attendance`;
CREATE TABLE `teacher_attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `teacher_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `time_in` time DEFAULT NULL,
  `time_out` time DEFAULT NULL,
  `status` enum('present','absent','late','half_day') NOT NULL DEFAULT 'present',
  `nfc_tag` varchar(50) DEFAULT NULL,
  `nfc_scanned` tinyint(1) DEFAULT 0,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `teacher_id` (`teacher_id`),
  KEY `nfc_tag` (`nfc_tag`),
  KEY `date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: staff_attendance
DROP TABLE IF EXISTS `staff_attendance`;
CREATE TABLE `staff_attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `time_in` time DEFAULT NULL,
  `time_out` time DEFAULT NULL,
  `status` enum('present','absent','late','half_day') NOT NULL DEFAULT 'present',
  `nfc_tag` varchar(50) DEFAULT NULL,
  `nfc_scanned` tinyint(1) DEFAULT 0,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `staff_id` (`staff_id`),
  KEY `nfc_tag` (`nfc_tag`),
  KEY `date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: off_days
DROP TABLE IF EXISTS `off_days`;
CREATE TABLE `off_days` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `is_holiday` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: subjects
DROP TABLE IF EXISTS `subjects`;
CREATE TABLE `subjects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject_code` varchar(20) NOT NULL,
  `subject_name` varchar(100) NOT NULL,
  `class` varchar(20) NOT NULL,
  `section` varchar(20) DEFAULT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `full_mark` int(11) DEFAULT 100,
  `pass_mark` int(11) DEFAULT 33,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `teacher_id` (`teacher_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: marks
DROP TABLE IF EXISTS `marks`;
CREATE TABLE `marks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `exam_type` enum('class_test','assignment','mid_term','final_term','practical') NOT NULL,
  `marks_obtained` decimal(5,2) DEFAULT NULL,
  `total_marks` decimal(5,2) DEFAULT NULL,
  `grade` varchar(5) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `exam_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `subject_id` (`subject_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: results
DROP TABLE IF EXISTS `results`;
CREATE TABLE `results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `class` varchar(20) NOT NULL,
  `section` varchar(20) DEFAULT NULL,
  `exam_type` varchar(50) NOT NULL,
  `exam_year` year(4) NOT NULL,
  `total_marks` decimal(8,2) DEFAULT NULL,
  `obtained_marks` decimal(8,2) DEFAULT NULL,
  `percentage` decimal(5,2) DEFAULT NULL,
  `grade` varchar(5) DEFAULT NULL,
  `gpa` decimal(3,2) DEFAULT NULL,
  `position` int(11) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: library_books
DROP TABLE IF EXISTS `library_books`;
CREATE TABLE `library_books` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `book_id` varchar(20) NOT NULL,
  `book_name` varchar(200) NOT NULL,
  `author` varchar(100) DEFAULT NULL,
  `publisher` varchar(100) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `isbn` varchar(20) DEFAULT NULL,
  `quantity` int(11) DEFAULT 0,
  `available` int(11) DEFAULT 0,
  `rack_no` varchar(20) DEFAULT NULL,
  `added_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `book_id` (`book_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: library_issued
DROP TABLE IF EXISTS `library_issued`;
CREATE TABLE `library_issued` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `book_id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `issue_date` date NOT NULL,
  `return_date` date NOT NULL,
  `actual_return_date` date DEFAULT NULL,
  `status` enum('issued','returned','overdue') DEFAULT 'issued',
  `fine_amount` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `book_id` (`book_id`),
  KEY `student_id` (`student_id`),
  KEY `teacher_id` (`teacher_id`),
  KEY `staff_id` (`staff_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: canteen_items
DROP TABLE IF EXISTS `canteen_items`;
CREATE TABLE `canteen_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_name` varchar(100) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `available` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: canteen_orders
DROP TABLE IF EXISTS `canteen_orders`;
CREATE TABLE `canteen_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) DEFAULT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `price` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `order_date` datetime NOT NULL,
  `status` enum('pending','preparing','ready','delivered','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `teacher_id` (`teacher_id`),
  KEY `staff_id` (`staff_id`),
  KEY `item_id` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: notices
DROP TABLE IF EXISTS `notices`;
CREATE TABLE `notices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `category` enum('academic','exam','event','general','holiday','emergency') DEFAULT 'general',
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expiry_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: class_routine
DROP TABLE IF EXISTS `class_routine`;
CREATE TABLE `class_routine` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `class` varchar(20) NOT NULL,
  `section` varchar(20) DEFAULT NULL,
  `day` enum('Saturday','Sunday','Monday','Tuesday','Wednesday','Thursday','Friday') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `subject_id` int(11) NOT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `room_no` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subject_id` (`subject_id`),
  KEY `teacher_id` (`teacher_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: exam_schedule
DROP TABLE IF EXISTS `exam_schedule`;
CREATE TABLE `exam_schedule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exam_name` varchar(100) NOT NULL,
  `class` varchar(20) NOT NULL,
  `section` varchar(20) DEFAULT NULL,
  `subject_id` int(11) NOT NULL,
  `exam_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `full_marks` int(11) DEFAULT NULL,
  `room_no` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subject_id` (`subject_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: nfc_devices
DROP TABLE IF EXISTS `nfc_devices`;
CREATE TABLE `nfc_devices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `device_name` varchar(100) NOT NULL,
  `device_id` varchar(50) NOT NULL,
  `location` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive','offline') DEFAULT 'active',
  `last_sync` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `device_id` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- ডেটা ইনসার্ট করুন
-- ========================================================

-- Users (ডিফল্ট অ্যাডমিন: admin / password)
INSERT INTO `users` (`username`, `password`, `role`, `full_name`, `email`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'System Administrator', 'admin@school.edu.bd');

-- School Info
INSERT INTO `school_info` (`school_name`, `address`, `phone`, `email`, `website`, `established_year`, `eiin`, `about`, `mission`, `vision`) VALUES
('Sunshine School & College', '123, Main Road, Dhaka, Bangladesh', '+8801712345678', 'info@sunshine.edu.bd', 'www.sunshine.edu.bd', 2000, '123456', 'Welcome to Sunshine School & College. We are committed to providing quality education and developing future leaders.', 'To provide quality education and develop future leaders with strong moral values.', 'To be a center of excellence in education and character building.');

-- Students (15)
INSERT INTO `students` (`student_id`, `name`, `father_name`, `mother_name`, `date_of_birth`, `gender`, `address`, `phone`, `guardian_phone`, `email`, `class`, `section`, `roll`, `group_name`, `blood_group`, `card_id`, `photo_path`, `admission_date`, `status`) VALUES
('STU-2024-001', 'Md. Arif Hasan', 'Md. Abdur Rahim', 'Fatema Begum', '2010-05-15', 'Male', '23/B, Dhanmondi, Dhaka', '01712345678', '01912345678', 'arif.hasan@gmail.com', '10', 'A', '01', 'Science', 'O+', 'CARD-001', 'uploads/default.jpg', '2024-01-15', 'active'),
('STU-2024-002', 'Sadia Akter', 'Md. Kamal Hossain', 'Laila Akter', '2010-08-20', 'Female', '45, Mohammadpur, Dhaka', '01712345679', '01912345679', 'sadia.akter@gmail.com', '10', 'A', '02', 'Science', 'A+', 'CARD-002', 'uploads/default.jpg', '2024-01-15', 'active'),
('STU-2024-003', 'Rafiul Islam', 'Md. Jahangir Alam', 'Nasrin Sultana', '2010-03-10', 'Male', '12/A, Mirpur, Dhaka', '01712345680', '01912345680', 'rafiul.islam@gmail.com', '10', 'A', '03', 'Science', 'B+', 'CARD-003', 'uploads/default.jpg', '2024-01-15', 'active'),
('STU-2024-004', 'Nusrat Jahan', 'Md. Shamsul Haque', 'Rokeya Begum', '2010-11-25', 'Female', '78, Uttara, Dhaka', '01712345681', '01912345681', 'nusrat.jahan@gmail.com', '10', 'A', '04', 'Science', 'AB+', 'CARD-004', 'uploads/default.jpg', '2024-01-15', 'active'),
('STU-2024-005', 'Shahriar Kabir', 'Md. Abdul Mannan', 'Shirin Akhter', '2010-07-05', 'Male', '56, Gulshan, Dhaka', '01712345682', '01912345682', 'shahriar.kabir@gmail.com', '10', 'A', '05', 'Science', 'O-', 'CARD-005', 'uploads/default.jpg', '2024-01-15', 'active'),
('STU-2024-006', 'Tasnim Akhter', 'Md. Nurul Islam', 'Rahima Khatun', '2010-09-18', 'Female', '34, Banani, Dhaka', '01712345683', '01912345683', 'tasnim.akhter@gmail.com', '10', 'A', '06', 'Science', 'A-', 'CARD-006', 'uploads/default.jpg', '2024-01-15', 'active'),
('STU-2024-007', 'Mahmudul Hasan', 'Md. Abdul Kader', 'Ayesha Begum', '2010-02-28', 'Male', '90, Malibagh, Dhaka', '01712345684', '01912345684', 'mahmudul.hasan@gmail.com', '9', 'B', '07', 'Commerce', 'B-', 'CARD-007', 'uploads/default.jpg', '2024-01-20', 'active'),
('STU-2024-008', 'Saharika Sultana', 'Md. Anwar Hossain', 'Shahnaz Parvin', '2010-10-12', 'Female', '67, Jatrabari, Dhaka', '01712345685', '01912345685', 'saharika.sultana@gmail.com', '9', 'B', '08', 'Commerce', 'AB-', 'CARD-008', 'uploads/default.jpg', '2024-01-20', 'active'),
('STU-2024-009', 'Imran Khan', 'Md. Shahjahan', 'Mona Khan', '2010-04-03', 'Male', '123, Motijheel, Dhaka', '01712345686', '01912345686', 'imran.khan@gmail.com', '9', 'B', '09', 'Commerce', 'O+', 'CARD-009', 'uploads/default.jpg', '2024-01-20', 'active'),
('STU-2024-010', 'Sharmin Akhter', 'Md. Faruk Ahmed', 'Jesmin Akhter', '2010-06-30', 'Female', '45, Pallabi, Dhaka', '01712345687', '01912345687', 'sharmin.akhter@gmail.com', '9', 'B', '10', 'Commerce', 'A+', 'CARD-010', 'uploads/default.jpg', '2024-01-20', 'active'),
('STU-2024-011', 'Rakib Hasan', 'Md. Shafiullah', 'Rokeya Sultana', '2010-12-15', 'Male', '89, Rampura, Dhaka', '01712345688', '01912345688', 'rakib.hasan@gmail.com', '9', 'B', '11', 'Commerce', 'B+', 'CARD-011', 'uploads/default.jpg', '2024-01-20', 'active'),
('STU-2024-012', 'Sumaiya Akter', 'Md. Golam Mostafa', 'Khadizatul Kubra', '2010-01-22', 'Female', '56, Khilkhet, Dhaka', '01712345689', '01912345689', 'sumaiya.akter@gmail.com', '8', 'C', '12', 'Arts', 'AB+', 'CARD-012', 'uploads/default.jpg', '2024-02-01', 'active'),
('STU-2024-013', 'Fahim Ahmed', 'Md. Mozammel Haque', 'Taslima Begum', '2010-07-19', 'Male', '34, Shyamoli, Dhaka', '01712345690', '01912345690', 'fahim.ahmed@gmail.com', '8', 'C', '13', 'Arts', 'O-', 'CARD-013', 'uploads/default.jpg', '2024-02-01', 'active'),
('STU-2024-014', 'Nabila Islam', 'Md. Ruhul Amin', 'Salma Parvin', '2010-03-27', 'Female', '78, Kafrul, Dhaka', '01712345691', '01912345691', 'nabila.islam@gmail.com', '8', 'C', '14', 'Arts', 'A-', 'CARD-014', 'uploads/default.jpg', '2024-02-01', 'active'),
('STU-2024-015', 'Tanjim Hasan', 'Md. Abdul Aziz', 'Jahanara Khatun', '2010-09-08', 'Male', '12, Gabtoli, Dhaka', '01712345692', '01912345692', 'tanjim.hasan@gmail.com', '8', 'C', '15', 'Arts', 'B-', 'CARD-015', 'uploads/default.jpg', '2024-02-01', 'active');

-- Teachers (10)
INSERT INTO `teachers` (`teacher_id`, `name`, `father_name`, `mother_name`, `date_of_birth`, `gender`, `address`, `phone`, `email`, `qualification`, `designation`, `department`, `blood_group`, `card_id`, `photo_path`, `joining_date`, `salary`, `subject`, `status`) VALUES
('TCH-001', 'Prof. Md. Kamal Uddin', 'Late Md. Nurul Islam', 'Mrs. Ayesha Begum', '1980-05-10', 'Male', 'House#12, Road#03, Dhanmondi, Dhaka', '01711111111', 'kamal.uddin@school.edu.bd', 'PhD in Physics', 'Professor', 'Science', 'A+', 'CARD-T001', 'uploads/default.jpg', '2010-01-01', 85000.00, 'Physics', 'active'),
('TCH-002', 'Dr. Tahmina Akhter', 'Md. Abdus Salam', 'Mrs. Nurun Nahar', '1982-08-15', 'Female', 'House#45, Road#12, Mohammadpur, Dhaka', '01711111112', 'tahmina.akhter@school.edu.bd', 'PhD in Chemistry', 'Professor', 'Science', 'B+', 'CARD-T002', 'uploads/default.jpg', '2010-01-01', 82000.00, 'Chemistry', 'active'),
('TCH-003', 'Prof. Shahidul Islam', 'Late Md. Abdur Rashid', 'Mrs. Hasina Begum', '1978-12-20', 'Male', 'House#78, Road#05, Mirpur, Dhaka', '01711111113', 'shahidul.islam@school.edu.bd', 'PhD in English', 'Professor', 'Arts', 'O+', 'CARD-T003', 'uploads/default.jpg', '2010-06-01', 80000.00, 'English', 'active'),
('TCH-004', 'Mrs. Sharmin Sultana', 'Md. Shahjahan', 'Mrs. Rokeya Begum', '1985-03-25', 'Female', 'House#34, Road#08, Uttara, Dhaka', '01711111114', 'sharmin.sultana@school.edu.bd', 'M.Sc in Mathematics', 'Associate Professor', 'Science', 'AB+', 'CARD-T004', 'uploads/default.jpg', '2012-07-01', 72000.00, 'Mathematics', 'active'),
('TCH-005', 'Mr. Abdul Karim', 'Md. Abdul Haque', 'Mrs. Fatema Begum', '1975-09-05', 'Male', 'House#56, Road#02, Gulshan, Dhaka', '01711111115', 'abdul.karim@school.edu.bd', 'MA in History', 'Senior Teacher', 'Arts', 'A-', 'CARD-T005', 'uploads/default.jpg', '2011-09-01', 68000.00, 'History', 'active'),
('TCH-006', 'Dr. Rukhsana Akhter', 'Md. Nurul Amin', 'Mrs. Salma Parvin', '1983-06-30', 'Female', 'House#90, Road#10, Banani, Dhaka', '01711111116', 'rukhsana.akhter@school.edu.bd', 'PhD in Botany', 'Associate Professor', 'Science', 'B-', 'CARD-T006', 'uploads/default.jpg', '2013-01-01', 75000.00, 'Biology', 'active'),
('TCH-007', 'Mr. Mahbub Alam', 'Late Md. Rahman', 'Mrs. Nazma Khatun', '1979-11-15', 'Male', 'House#23, Road#07, Malibagh, Dhaka', '01711111117', 'mahbub.alam@school.edu.bd', 'M.A in Bengali', 'Senior Teacher', 'Arts', 'O-', 'CARD-T007', 'uploads/default.jpg', '2012-07-01', 65000.00, 'Bengali', 'active'),
('TCH-008', 'Mrs. Farhana Kabir', 'Md. Kabir Hossain', 'Mrs. Nasrin Sultana', '1987-02-28', 'Female', 'House#67, Road#09, Jatrabari, Dhaka', '01711111118', 'farhana.kabir@school.edu.bd', 'M.Sc in Physics', 'Assistant Professor', 'Science', 'AB-', 'CARD-T008', 'uploads/default.jpg', '2014-03-01', 70000.00, 'Physics', 'active'),
('TCH-009', 'Mr. Saidur Rahman', 'Md. Abdur Rahim', 'Mrs. Sufia Begum', '1976-04-18', 'Male', 'House#12, Road#15, Motijheel, Dhaka', '01711111119', 'saidur.rahman@school.edu.bd', 'MA in Islamic Studies', 'Senior Teacher', 'Arts', 'A+', 'CARD-T009', 'uploads/default.jpg', '2011-01-01', 63000.00, 'Islamic Studies', 'active'),
('TCH-010', 'Dr. Sajeda Akhter', 'Md. Anowar Hossain', 'Mrs. Bilkis Begum', '1984-10-08', 'Female', 'House#45, Road#22, Pallabi, Dhaka', '01711111120', 'sajeda.akhter@school.edu.bd', 'PhD in Zoology', 'Professor', 'Science', 'B+', 'CARD-T010', 'uploads/default.jpg', '2010-07-01', 88000.00, 'Biology', 'active');

-- Staff (5)
INSERT INTO `staff` (`staff_id`, `name`, `designation`, `department`, `phone`, `email`, `address`, `blood_group`, `card_id`, `photo_path`, `joining_date`, `salary`, `status`) VALUES
('STF-001', 'Md. Abdul Bari', 'Administrative Officer', 'Administration', '01722222221', 'bari@school.edu.bd', 'House#15, Road#05, Dhanmondi, Dhaka', 'A+', 'CARD-S001', 'uploads/default.jpg', '2015-01-01', 45000.00, 'active'),
('STF-002', 'Mrs. Parvin Sultana', 'Accountant', 'Finance', '01722222222', 'parvin.sultana@school.edu.bd', 'House#45, Road#08, Mohammadpur, Dhaka', 'B+', 'CARD-S002', 'uploads/default.jpg', '2015-06-01', 42000.00, 'active'),
('STF-003', 'Mr. Jamil Hossain', 'Librarian', 'Library', '01722222223', 'jamil.hossain@school.edu.bd', 'House#78, Road#12, Mirpur, Dhaka', 'O+', 'CARD-S003', 'uploads/default.jpg', '2016-01-01', 40000.00, 'active'),
('STF-004', 'Mrs. Shahnaz Akhter', 'Assistant Accountant', 'Finance', '01722222224', 'shahnaz.akhter@school.edu.bd', 'House#34, Road#15, Uttara, Dhaka', 'AB+', 'CARD-S004', 'uploads/default.jpg', '2016-07-01', 38000.00, 'active'),
('STF-005', 'Mr. Anwar Hossain', 'Clerk', 'Administration', '01722222225', 'anwar.hossain@school.edu.bd', 'House#56, Road#10, Gulshan, Dhaka', 'A-', 'CARD-S005', 'uploads/default.jpg', '2017-01-01', 35000.00, 'active');

-- Off Days (সরকারি ছুটির দিন)
INSERT INTO `off_days` (`date`, `reason`, `is_holiday`) VALUES
('2026-01-01', 'নববর্ষ (ইংরেজি)', 1),
('2026-02-21', 'আন্তর্জাতিক মাতৃভাষা দিবস', 1),
('2026-03-26', 'স্বাধীনতা দিবস', 1),
('2026-04-14', 'বাংলা নববর্ষ (পহেলা বৈশাখ)', 1),
('2026-05-01', 'মে দিবস', 1),
('2026-08-15', 'জাতীয় শোক দিবস', 1),
('2026-12-16', 'বিজয় দিবস', 1),
('2026-12-25', 'বড়দিন', 1);

-- Canteen Items
INSERT INTO `canteen_items` (`item_name`, `category`, `price`, `available`) VALUES
('Chicken Roll', 'Fast Food', 80.00, 1),
('Beef Sandwich', 'Fast Food', 90.00, 1),
('Vegetable Pizza', 'Pizza', 120.00, 1),
('Chicken Pizza', 'Pizza', 150.00, 1),
('White Rice', 'Main Course', 50.00, 1),
('Chicken Curry', 'Main Course', 100.00, 1),
('Beef Curry', 'Main Course', 120.00, 1),
('Mixed Vegetables', 'Main Course', 60.00, 1),
('Lemon Juice', 'Drinks', 30.00, 1),
('Mango Juice', 'Drinks', 40.00, 1),
('Lassi', 'Drinks', 50.00, 1),
('Tea', 'Beverages', 20.00, 1),
('Coffee', 'Beverages', 30.00, 1),
('Cold Drinks', 'Beverages', 35.00, 1),
('Ice Cream', 'Desserts', 40.00, 1);

-- NFC Devices
INSERT INTO `nfc_devices` (`device_name`, `device_id`, `location`, `status`) VALUES
('Main Entrance NFC Reader', 'NFC-DEV-001', 'Main Gate', 'active'),
('Library NFC Reader', 'NFC-DEV-002', 'Library', 'active'),
('Canteen NFC Reader', 'NFC-DEV-003', 'Canteen', 'active');

-- Subjects (বিষয়সমূহ)
INSERT INTO `subjects` (`subject_code`, `subject_name`, `class`, `section`, `teacher_id`, `full_mark`, `pass_mark`) VALUES
('BAN-101', 'বাংলা', '10', 'A', 7, 100, 33),
('ENG-101', 'ইংরেজি', '10', 'A', 3, 100, 33),
('MATH-101', 'গণিত', '10', 'A', 4, 100, 33),
('SCI-101', 'বিজ্ঞান', '10', 'A', 1, 100, 33),
('SOC-101', 'সামাজিক বিজ্ঞান', '10', 'A', 5, 100, 33),
('REL-101', 'ইসলাম শিক্ষা', '10', 'A', 9, 100, 33),
('PHY-101', 'পদার্থবিজ্ঞান', '10', 'A', 8, 100, 33),
('CHE-101', 'রসায়ন', '10', 'A', 2, 100, 33),
('BIO-101', 'জীববিজ্ঞান', '10', 'A', 10, 100, 33);

-- Class Routine (শ্রেণি রুটিন)
INSERT INTO `class_routine` (`class`, `section`, `day`, `start_time`, `end_time`, `subject_id`, `teacher_id`, `room_no`) VALUES
('10', 'A', 'Saturday', '09:00:00', '09:45:00', 1, 7, '101'),
('10', 'A', 'Saturday', '09:45:00', '10:30:00', 2, 3, '101'),
('10', 'A', 'Saturday', '10:45:00', '11:30:00', 3, 4, '101'),
('10', 'A', 'Saturday', '11:30:00', '12:15:00', 4, 1, '102'),
('10', 'A', 'Sunday', '09:00:00', '09:45:00', 5, 5, '101'),
('10', 'A', 'Sunday', '09:45:00', '10:30:00', 6, 9, '101'),
('10', 'A', 'Sunday', '10:45:00', '11:30:00', 7, 8, '102'),
('10', 'A', 'Sunday', '11:30:00', '12:15:00', 8, 2, '102');

-- Exam Schedule (পরীক্ষার সময়সূচি)
INSERT INTO `exam_schedule` (`exam_name`, `class`, `section`, `subject_id`, `exam_date`, `start_time`, `end_time`, `full_marks`, `room_no`) VALUES
('Mid Term 2026', '10', 'A', 1, '2026-07-15', '09:00:00', '11:00:00', 100, '101'),
('Mid Term 2026', '10', 'A', 2, '2026-07-16', '09:00:00', '11:00:00', 100, '101'),
('Mid Term 2026', '10', 'A', 3, '2026-07-17', '09:00:00', '11:00:00', 100, '102'),
('Mid Term 2026', '10', 'A', 4, '2026-07-18', '09:00:00', '11:00:00', 100, '102');

-- Library Books (গ্রন্থাগারের বই)
INSERT INTO `library_books` (`book_id`, `book_name`, `author`, `publisher`, `category`, `isbn`, `quantity`, `available`, `rack_no`, `added_date`) VALUES
('BK-001', 'Advanced Physics', 'Dr. Md. Kamal Uddin', 'University Press', 'Science', '978-1-234567-01-2', 5, 4, 'RACK-A01', '2024-01-01'),
('BK-002', 'Organic Chemistry', 'Dr. Tahmina Akhter', 'Academic Publishers', 'Science', '978-1-234567-02-9', 4, 3, 'RACK-A02', '2024-01-01'),
('BK-003', 'English Grammar Guide', 'Prof. Shahidul Islam', 'Dhaka Publishers', 'Language', '978-1-234567-03-6', 6, 5, 'RACK-B01', '2024-01-01'),
('BK-004', 'Mathematics for High School', 'Mrs. Sharmin Sultana', 'Education Press', 'Mathematics', '978-1-234567-04-3', 5, 4, 'RACK-A03', '2024-01-01'),
('BK-005', 'History of Bangladesh', 'Mr. Abdul Karim', 'National Publications', 'History', '978-1-234567-05-0', 4, 3, 'RACK-B02', '2024-01-01');

-- Notices (নোটিশ)
INSERT INTO `notices` (`title`, `content`, `category`, `priority`, `created_by`, `expiry_date`) VALUES
('মধ্যবর্তী পরীক্ষার সময়সূচি', 'মধ্যবর্তী পরীক্ষা ১৫ই জুলাই থেকে শুরু হবে। বিস্তারিত সময়সূচি নোটিশ বোর্ডে দেখুন।', 'exam', 'high', 1, '2026-07-30'),
('বিদ্যালয় বন্ধের ঘোষণা', '২১শে ফেব্রুয়ারি আন্তর্জাতিক মাতৃভাষা দিবস উপলক্ষে বিদ্যালয় বন্ধ থাকবে।', 'holiday', 'high', 1, '2026-02-22'),
('নতুন শিক্ষার্থী ভর্তি', '২০২৬ শিক্ষাবর্ষে নতুন শিক্ষার্থী ভর্তি শুরু হয়েছে। আরও তথ্যের জন্য অফিসে যোগাযোগ করুন।', 'academic', 'medium', 1, '2026-12-31');

-- ========================================================
-- COMMIT
-- ========================================================

COMMIT;