-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: 29 أبريل 2025 الساعة 00:20
-- إصدار الخادم: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `utas_platform`
--

-- --------------------------------------------------------

--
-- بنية الجدول `conversations`
--

CREATE TABLE `conversations` (
  `id` int(11) NOT NULL,
  `user1_id` int(11) NOT NULL,
  `user2_id` int(11) NOT NULL,
  `course_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `course_code` varchar(20) NOT NULL,
  `course_name` varchar(100) NOT NULL,
  `specialization` enum('IT','Engineering') NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `courses`
--

INSERT INTO `courses` (`id`, `teacher_id`, `course_code`, `course_name`, `specialization`, `description`, `created_at`) VALUES
(1, 0, 'IT101', 'مقدمة في البرمجة', 'IT', 'مادة تمهيدية تتعلم فيها أساسيات البرمجة باستخدام لغة Python', '2025-04-23 18:08:44'),
(2, 0, 'IT102', 'قواعد البيانات', 'IT', 'مادة تتعلم فيها تصميم وإدارة قواعد البيانات باستخدام SQL', '2025-04-23 18:08:44'),
(3, 0, 'ENG101', 'ميكانيكا المواد', 'Engineering', 'مادة أساسية في الهندسة الميكانيكية', '2025-04-23 18:08:44');

-- --------------------------------------------------------

--
-- بنية الجدول `course_teachers`
--

CREATE TABLE `course_teachers` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `teacher_name` varchar(100) NOT NULL,
  `teacher_email` varchar(100) NOT NULL,
  `teacher_phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `course_teachers`
--

INSERT INTO `course_teachers` (`id`, `course_id`, `teacher_name`, `teacher_email`, `teacher_phone`, `created_at`) VALUES
(1, 1, 'د. أحمد العبري', 'ahmed.alabri@utas.edu.om', '98765432', '2025-04-23 18:08:44'),
(2, 2, 'د. سمية البلوشية', 'sumaya.albalushi@utas.edu.om', '98765433', '2025-04-23 18:08:44'),
(3, 3, 'د. محمد الكندي', 'mohammed.alkindi@utas.edu.om', '98765434', '2025-04-23 18:08:44');

-- --------------------------------------------------------

--
-- بنية الجدول `materials`
--

CREATE TABLE `materials` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` enum('pdf','doc','ppt','zip') NOT NULL,
  `specialization` enum('IT','Engineering') NOT NULL,
  `course_name` varchar(100) NOT NULL,
  `uploaded_by` int(11) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `download_count` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `teacher_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `related_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `is_read`, `related_url`, `created_at`) VALUES
(1, 3, 'طلب درس جديد', 'لديك طلب درس جديد من الطالب Rawan', 0, 'tutor_requests.php', '2025-04-23 19:10:58'),
(2, 4, 'تم قبول طلب الدرس', 'تم قبول طلب الدرس الخاص بك من قبل المدرس خالد سعيد', 0, 'my_requests.php', '2025-04-23 19:30:21'),
(3, 3, 'طلب درس جديد', 'لديك طلب درس جديد من الطالب ALI', 0, 'tutor_requests.php', '2025-04-24 12:45:53'),
(4, 6, 'تم رفض طلب الدرس', 'تم رفض طلب الدرس الخاص بك من قبل المدرس خالد سعيد بسبب: لا يوجد وقت', 0, 'my_requests.php', '2025-04-24 12:47:02'),
(5, 12, 'New Tutoring Request', 'You have a new tutoring request from student خالد سعيد for 10$', 0, 'tutor_requests.php', '2025-04-28 20:34:42');

-- --------------------------------------------------------

--
-- بنية الجدول `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'OMR',
  `payment_method` varchar(50) NOT NULL,
  `status` enum('pending','paid','failed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `student_id` int(11) DEFAULT NULL,
  `tutor_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `payments`
--

INSERT INTO `payments` (`id`, `request_id`, `amount`, `currency`, `payment_method`, `status`, `created_at`, `student_id`, `tutor_id`) VALUES
(1, 8, 10.00, 'USD', 'paypal', 'pending', '2025-04-28 20:34:42', 3, 12);

-- --------------------------------------------------------

--
-- بنية الجدول `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(128) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` text NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `teachers`
--

CREATE TABLE `teachers` (
  `user_id` int(11) NOT NULL,
  `department` varchar(50) DEFAULT NULL,
  `position` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `teachers`
--

INSERT INTO `teachers` (`user_id`, `department`, `position`) VALUES
(1, 'Computer Science', 'Assistant Professor'),
(3, 'Engineering', 'Lecturer');

-- --------------------------------------------------------

--
-- بنية الجدول `teacher_materials`
--

CREATE TABLE `teacher_materials` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `course_id` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `tutoring_requests`
--

CREATE TABLE `tutoring_requests` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `tutor_id` int(11) NOT NULL,
  `course_name` varchar(100) NOT NULL,
  `session_type` enum('online','in_person') NOT NULL,
  `meeting_location` varchar(255) DEFAULT NULL,
  `meeting_time` datetime NOT NULL,
  `duration` int(11) NOT NULL COMMENT 'Duration in minutes',
  `status` enum('pending','accepted','rejected','completed','cancelled') DEFAULT 'pending',
  `student_notes` text DEFAULT NULL,
  `tutor_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `tutoring_requests`
--

INSERT INTO `tutoring_requests` (`id`, `student_id`, `tutor_id`, `course_name`, `session_type`, `meeting_location`, `meeting_time`, `duration`, `status`, `student_notes`, `tutor_notes`, `created_at`, `updated_at`) VALUES
(1, 4, 3, 'تقنيه معلومات', 'online', 'Online', '2025-04-15 21:10:00', 60, 'completed', '', '', '2025-04-23 19:10:58', '2025-04-23 19:30:29'),
(2, 6, 3, 'تقنيه معلومات', 'online', 'Online', '2025-04-17 14:45:00', 60, 'rejected', '', 'لا يوجد وقت', '2025-04-24 12:45:53', '2025-04-24 12:47:02'),
(3, 3, 12, 'it', 'online', 'Online', '2025-04-30 23:19:00', 60, 'pending', '', NULL, '2025-04-28 20:27:10', '2025-04-28 20:27:10'),
(8, 3, 12, 'it', 'online', 'Online', '2025-04-30 23:19:00', 60, 'pending', '', NULL, '2025-04-28 20:34:42', '2025-04-28 20:34:42');

-- --------------------------------------------------------

--
-- بنية الجدول `tutors`
--

CREATE TABLE `tutors` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `bio` text DEFAULT NULL,
  `hourly_rate` decimal(6,2) NOT NULL DEFAULT 0.00,
  `available_online` tinyint(1) DEFAULT 1,
  `available_in_person` tinyint(1) DEFAULT 1,
  `courses_taught` varchar(255) NOT NULL,
  `rating` decimal(2,1) DEFAULT 0.0,
  `total_sessions` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `tutors`
--

INSERT INTO `tutors` (`id`, `user_id`, `bio`, `hourly_rate`, `available_online`, `available_in_person`, `courses_taught`, `rating`, `total_sessions`, `created_at`, `updated_at`) VALUES
(1, 1, 'أنا طالب في السنة الثالثة تخصص IT، متخصص في برمجة Python ولدي خبرة في تدريس المبتدئين', 10.00, 1, 1, 'IT101, IT102', 0.0, 0, '2025-04-23 18:08:44', '2025-04-23 18:08:44'),
(2, 3, 'طالب متميز في تخصص IT، حاصل على معدل تراكمي 3.8، أستطيع مساعدتك في جميع مواد البرمجة', 12.00, 1, 1, 'IT101, IT102, IT203', 0.0, 0, '2025-04-23 18:08:44', '2025-04-23 18:08:44');

-- --------------------------------------------------------

--
-- بنية الجدول `tutor_profiles`
--

CREATE TABLE `tutor_profiles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `course_name` varchar(100) NOT NULL,
  `course_id` varchar(50) NOT NULL,
  `bank_account` varchar(50) NOT NULL,
  `teaching_method` enum('online','in_person','both') NOT NULL,
  `available_days` varchar(100) NOT NULL,
  `available_hours` varchar(50) NOT NULL,
  `is_approved` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `online_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `in_person_price` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `tutor_profiles`
--

INSERT INTO `tutor_profiles` (`id`, `user_id`, `course_name`, `course_id`, `bank_account`, `teaching_method`, `available_days`, `available_hours`, `is_approved`, `created_at`, `online_price`, `in_person_price`) VALUES
(1, 12, 'it', '1154', '3345567778999', 'online', 'Sunday,Monday,Tuesday,Saturday', '9:00-5:00', 0, '2025-04-28 15:34:00', 0.00, 0.00);

-- --------------------------------------------------------

--
-- بنية الجدول `uploaded_files`
--

CREATE TABLE `uploaded_files` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_size` int(11) NOT NULL COMMENT 'Size in bytes',
  `file_type` varchar(50) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `university_id` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `specialization` enum('IT','Engineering') NOT NULL,
  `phone` varchar(20) NOT NULL,
  `gpa` decimal(3,2) DEFAULT NULL,
  `is_tutor` tinyint(1) DEFAULT 0,
  `profile_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `user_type` enum('student','teacher') NOT NULL DEFAULT 'student'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `users`
--

INSERT INTO `users` (`id`, `university_id`, `email`, `password`, `full_name`, `specialization`, `phone`, `gpa`, `is_tutor`, `profile_image`, `created_at`, `updated_at`, `user_type`) VALUES
(1, '202010001', 'student1@utas.edu.om', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'أحمد محمد', 'IT', '91234567', 3.50, 1, NULL, '2025-04-23 18:08:44', '2025-04-23 18:08:44', 'student'),
(2, '202010002', 'student2@utas.edu.om', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'سارة خالد', 'Engineering', '92345678', 3.20, 0, NULL, '2025-04-23 18:08:44', '2025-04-23 18:08:44', 'student'),
(3, '202010003', 'student3@utas.edu.om', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'خالد سعيد', 'IT', '93456789', 3.80, 1, NULL, '2025-04-23 18:08:44', '2025-04-23 18:08:44', 'student'),
(4, '12345', 'Rawan@gmail.com', '$2y$10$rn2XX8FzWYuEZHXsMqdNk.2EQSxHujGKs5K.OELvWbN3OM.gtg6xa', 'Rawan', 'IT', '01091728359', 3.50, 1, NULL, '2025-04-23 19:08:47', '2025-04-23 19:08:47', 'student'),
(5, '111234', 'Ahmed@utas.edu.om', '$2y$10$RLrL8SZXzNFgNif6RAzoN.8OyT30Pz9S/1PJZ0MLu1uM04YR4KSRy', 'Ahmed', 'Engineering', '968222344', 3.00, 0, NULL, '2025-04-24 12:40:54', '2025-04-24 12:40:54', 'student'),
(6, '111222', 'Ali@utas.edu.om', '$2y$10$vAwtevStw8dmWFTcNdFpZeLnO8QcLpcNZcVyHSSwF44xtRxATh/fW', 'ALI', 'IT', '96899922', 2.90, 0, NULL, '2025-04-24 12:45:01', '2025-04-24 12:45:01', 'student'),
(7, '112333', 'tech@gmail.com', '$2y$10$yUilH//fOMhHZVxikGippeMpHDnZh9LHyE1b/tICj1MKE/egAhjBu', 'tech', 'IT', '1122222233', 0.00, 0, NULL, '2025-04-26 16:30:39', '2025-04-26 16:30:39', 'student'),
(8, '22333', 'teacher@gmail.com', '$2y$10$4QxhWQnQ6RpVAWP7yOFHVOV/03R9faX/DicMxLGP.gW.YcG7dTfuW', 'teacher', 'IT', '222222222', NULL, 0, NULL, '2025-04-26 16:49:09', '2025-04-26 16:49:09', 'teacher'),
(9, '1234567', 'Ahmed@gmail.com', '$2y$10$X0VVM/rzg3tAHCiopi8b4uifXJyXiTz6xxrJsAvmyPf.6Q289x5re', 'ahmed', 'Engineering', '333333334', NULL, 0, NULL, '2025-04-26 17:23:38', '2025-04-26 17:23:38', 'teacher'),
(10, '987654', 'teacher@utas.edu.om', '$2y$10$Evhk2sg1u816mLj7NDozSeRLUKFxYW2eOL.bJu4cgAn9EUdk3VHb.', 'teacher 1', 'Engineering', '2222223', NULL, 0, NULL, '2025-04-26 19:39:04', '2025-04-26 19:39:04', 'teacher'),
(11, '155566', 'stu@utas.edu.om', '$2y$10$If0lvERiZlivyOOniNTh0OGzBXI0Ongc8mFTYzR/LOsLudiFPwOq2', 'stu', 'Engineering', '9998009', 3.90, 1, NULL, '2025-04-28 15:26:56', '2025-04-28 15:26:56', 'student'),
(12, '666555', 'stud@gmail.com', '$2y$10$QaJY8CC8eIzzI7Drdw4PnuU08d10Zh3exMvtR4HaABmvs6lRG0cxK', 'stud', 'IT', '33344', 3.90, 1, NULL, '2025-04-28 15:34:00', '2025-04-28 15:34:00', 'student'),
(13, '333444', 'amany@utas.edu.om', '$2y$10$HyVcDlmMy6jwbLik5iwHL.8Mf7WOkTw7NCYepx.fV1TG0XmLFUMz6', 'amany', 'Engineering', '3332222', 3.90, 0, NULL, '2025-04-28 19:42:51', '2025-04-28 19:42:51', 'student');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user1_id` (`user1_id`),
  ADD KEY `user2_id` (`user2_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `course_teachers`
--
ALTER TABLE `course_teachers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `materials`
--
ALTER TABLE `materials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `conversation_id` (`conversation_id`),
  ADD KEY `sender_id` (`sender_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `request_id` (`request_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `teacher_materials`
--
ALTER TABLE `teacher_materials`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tutoring_requests`
--
ALTER TABLE `tutoring_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `tutor_id` (`tutor_id`);

--
-- Indexes for table `tutors`
--
ALTER TABLE `tutors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tutor_profiles`
--
ALTER TABLE `tutor_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `uploaded_files`
--
ALTER TABLE `uploaded_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `university_id` (`university_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `course_teachers`
--
ALTER TABLE `course_teachers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `materials`
--
ALTER TABLE `materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `teacher_materials`
--
ALTER TABLE `teacher_materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tutoring_requests`
--
ALTER TABLE `tutoring_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tutors`
--
ALTER TABLE `tutors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tutor_profiles`
--
ALTER TABLE `tutor_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `uploaded_files`
--
ALTER TABLE `uploaded_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- قيود الجداول المُلقاة.
--

--
-- قيود الجداول `conversations`
--
ALTER TABLE `conversations`
  ADD CONSTRAINT `conversations_ibfk_1` FOREIGN KEY (`user1_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `conversations_ibfk_2` FOREIGN KEY (`user2_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `course_teachers`
--
ALTER TABLE `course_teachers`
  ADD CONSTRAINT `course_teachers_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `materials`
--
ALTER TABLE `materials`
  ADD CONSTRAINT `materials_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `tutoring_requests` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `teachers`
--
ALTER TABLE `teachers`
  ADD CONSTRAINT `teachers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- قيود الجداول `tutoring_requests`
--
ALTER TABLE `tutoring_requests`
  ADD CONSTRAINT `tutoring_requests_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tutoring_requests_ibfk_2` FOREIGN KEY (`tutor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `tutors`
--
ALTER TABLE `tutors`
  ADD CONSTRAINT `tutors_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `tutor_profiles`
--
ALTER TABLE `tutor_profiles`
  ADD CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- قيود الجداول `uploaded_files`
--
ALTER TABLE `uploaded_files`
  ADD CONSTRAINT `uploaded_files_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
