-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql113.infinityfree.com
-- Generation Time: Jun 25, 2025 at 10:07 AM
-- Server version: 10.6.22-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_38329700_students_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `regno` varchar(20) DEFAULT NULL,
  `nic` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `mobile` varchar(15) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `profile_picture` varchar(200) NOT NULL DEFAULT 'uploads/profile_pictures/default.jpg',
  `created_at` datetime DEFAULT current_timestamp(),
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `regno`, `nic`, `email`, `gender`, `mobile`, `password`, `status`, `profile_picture`, `created_at`, `last_login`) VALUES
(3, 'Mithun Chameera', 'gal/it/2324/f/0043', '200400211211', 'mithunchameera7@gmail.com', 'Male', '760099206', '$2y$10$U6E9uqdhneVtHvcy1eyTdeSabXSrpZAA/Gglu0wtFtiyU28SsjpZ2', 'rejected', 'uploads/profile_pictures/default.jpg', '2025-06-15 07:59:36', '2025-06-17 10:59:41');

-- --------------------------------------------------------

--
-- Table structure for table `sadmins`
--

CREATE TABLE `sadmins` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `nic` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `mobile` varchar(15) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `profile_picture` varchar(200) NOT NULL DEFAULT 'uploads/profile_pictures/default.jpg',
  `created_at` datetime DEFAULT current_timestamp(),
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sadmins`
--

INSERT INTO `sadmins` (`id`, `name`, `nic`, `email`, `mobile`, `password`, `status`, `profile_picture`, `created_at`, `last_login`) VALUES
(1, 'Malitha Tishamal', '20002202615', 'malithatishamal@gmail.com', '771000001', '$2y$10$wc.2njjqODl2guzQnvGmieCLFsJnHV/8x.zF90ONUQKxKWBrDEPHy', 'approved', 'uploads/profile_pictures/6818d0f1cf442-411001152_1557287805017611_3900716309730349802_n1.jpg', '2025-05-05 20:12:59', '2025-06-25 13:17:26');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `regno` varchar(20) DEFAULT NULL,
  `nic` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `address` text DEFAULT NULL,
  `nowstatus` varchar(50) DEFAULT NULL,
  `mobile` varchar(15) DEFAULT NULL,
  `mobile2` varchar(10) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'approved',
  `created_at` datetime DEFAULT current_timestamp(),
  `last_login` datetime DEFAULT NULL,
  `profile_picture` varchar(255) NOT NULL DEFAULT 'uploads/profile_pictures/default.jpg',
  `blog` varchar(200) NOT NULL,
  `facebook` varchar(200) NOT NULL,
  `linkedin` varchar(200) NOT NULL,
  `github` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `semester` varchar(50) DEFAULT NULL,
  `code` varchar(20) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `semester`, `code`, `name`, `description`) VALUES
(1, 'Semester I', 'HNDIT1012', 'Visual Application Programming', 'Core | GPA'),
(2, 'Semester I', 'HNDIT1022', 'Web Design', 'Core | GPA'),
(3, 'Semester I', 'HNDIT1032', 'Computer and Network Systems', 'Core | GPA'),
(4, 'Semester I', 'HNDIT1042', 'Information Management and Information Systems', 'Core | GPA'),
(5, 'Semester I', 'HNDIT1052', 'ICT Project (Individual)', 'Core | GPA'),
(6, 'Semester I', 'HNDIT1062', 'Communication Skills', 'Core | GPA'),
(7, 'Semester II', 'HNDIT2012', 'Fundamentals of Programming', 'Core | GPA'),
(8, 'Semester II', 'HNDIT2022', 'Software Development', 'Core | GPA'),
(9, 'Semester II', 'HNDIT2032', 'System Analysis and Design', 'Core | GPA'),
(10, 'Semester II', 'HNDIT2042', 'Data communication and Computer Networks', 'Core | GPA'),
(11, 'Semester II', 'HNDIT2052', 'Principles of User Interface Design', 'Core | GPA'),
(12, 'Semester II', 'HNDIT2062', 'ICT Project (Group)', 'Core | GPA'),
(13, 'Semester II', 'HNDIT2072', 'Technical Writing', 'Core | GPA'),
(14, 'Semester II', 'HNDIT2082', 'Human Value & Professional Ethics', 'Core | NGPA'),
(15, 'Semester III', 'HNDIT3012', 'Object Oriented Programming', 'Core | GPA'),
(16, 'Semester III', 'HNDIT3022', 'Web Programming', 'Core | GPA'),
(17, 'Semester III', 'HNDIT3032', 'Data Structures and Algorithms', 'Core | GPA'),
(18, 'Semester III', 'HNDIT3042', 'Database Management Systems', 'Core | GPA'),
(19, 'Semester III', 'HNDIT3052', 'Operating Systems', 'Core | GPA'),
(20, 'Semester III', 'HNDIT3062', 'Information and Computer Security', 'Core | GPA'),
(21, 'Semester III', 'HNDIT3072', 'Statistics for IT', 'Core | GPA'),
(22, 'Semester IV', 'HNDIT4012', 'Software Engineering', 'Core | GPA'),
(23, 'Semester IV', 'HNDIT4022', 'Software Quality Assurance', 'Core | GPA'),
(24, 'Semester IV', 'HNDIT4032', 'IT Project Management', 'Core | GPA'),
(25, 'Semester IV', 'HNDIT4042', 'Professional World', 'Core | GPA'),
(26, 'Semester IV', 'HNDIT4052', 'Programming Individual Project', 'Core | GPA'),
(27, 'Semester IV', 'HNDIT4212', 'Machine Learning', 'Elective | GPA'),
(28, 'Semester IV', 'HNDIT4222', 'Business Analysis Practice', 'Elective | GPA'),
(29, 'Semester IV', 'HNDIT4232', 'Enterprise Architecture', 'Elective | GPA'),
(30, 'Semester IV', 'HNDIT4242', 'Computer Services Management', 'Elective | GPA');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `regno` (`regno`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `sadmins`
--
ALTER TABLE `sadmins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `regno` (`regno`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sadmins`
--
ALTER TABLE `sadmins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
