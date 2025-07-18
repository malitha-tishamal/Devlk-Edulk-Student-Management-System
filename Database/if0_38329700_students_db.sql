-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql113.infinityfree.com
-- Generation Time: Jul 18, 2025 at 01:23 AM
-- Server version: 11.4.7-MariaDB
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
(3, 'Mithun Chameera', 'gal/it/2324/f/0043', '200400211211', 'mithunchameera7@gmail.com', 'Male', '760099206', '$2y$10$U6E9uqdhneVtHvcy1eyTdeSabXSrpZAA/Gglu0wtFtiyU28SsjpZ2', 'approved', 'uploads/profile_pictures/default.jpg', '2025-06-15 07:59:36', '2025-07-02 21:00:59'),
(4, 'H.P.Bhashitha sandaruwan', 'GAL/IT/2324/F/0048', '200111900818', 'admin.hpbashitha@gmail.com', 'Male', '766251286', '$2y$10$6U/KHjL5pMEON5Yqkz8oa.1klAuI8ibREakAtECPVCCOgg0TmQGcK', 'approved', 'uploads/profile_pictures/default.jpg', '2025-06-29 06:47:30', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `lectures`
--

CREATE TABLE `lectures` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `nic` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` enum('approved','pending','disabled') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `last_login` datetime DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT 'uploads/profile_pictures/default.png',
  `blog` varchar(255) DEFAULT NULL,
  `facebook` varchar(255) DEFAULT NULL,
  `linkedin` varchar(255) DEFAULT NULL,
  `github` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lectures_assignment`
--

CREATE TABLE `lectures_assignment` (
  `id` int(11) NOT NULL,
  `lecturer_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `assigned_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
(1, 'Malitha Tishamal', '20002202615', 'malithatishamal@gmail.com', '771000001', '$2y$10$wc.2njjqODl2guzQnvGmieCLFsJnHV/8x.zF90ONUQKxKWBrDEPHy', 'approved', 'uploads/profile_pictures/6818d0f1cf442-411001152_1557287805017611_3900716309730349802_n1.jpg', '2025-05-05 20:12:59', '2025-07-18 09:26:32');

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

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `name`, `regno`, `nic`, `email`, `gender`, `address`, `nowstatus`, `mobile`, `mobile2`, `password`, `status`, `created_at`, `last_login`, `profile_picture`, `blog`, `facebook`, `linkedin`, `github`) VALUES
(4, 'H.P.Bhashitha sandaruwan', 'GAL/IT/2324/F/0048', '200111900818', 'hpbashitha@gmail.com', 'Male', 'PITAWATHTHA KANATHTHAHENA  RANSEGODA WEST RANSEGODA', 'Bord', '766251286', '778351286', '$2y$10$1PL4L.AkzlR5mP3.Qgu4XevUxwSyWEVQ1rvgmzm/ecd7KjXSHcxqG', 'approved', '2025-06-29 03:34:36', '2025-06-29 16:06:39', 'uploads/profile_pictures/default.jpg', '', '', '', ''),
(5, 'test', 'TEST', '200202202615', 'user@gmail.com', 'Male', 'test', 'Home', '763279285', '771295976', '$2y$10$2ajMdZ6zXKtD5k5zqFh3Muv5ghHXpJyhKueQE.Xt1wyDzLzDX3L4q', 'approved', '2025-06-29 20:47:34', '2025-07-17 23:15:38', 'uploads/profile_pictures/default.jpg', '', '', '', ''),
(7, 'S.S.Godakanda', 'GAL/IT2324/F/219', '200364412130', 'gsandanila@gmail.com', 'Female', 'Sarasavi Kurunduwatta Wanduramba', 'Home', '781852852', '703444188', '$2y$10$73Oi6UAeWg41BYUdfw9d7u609Xr639eHLG3nnc0YxbOdlIJKJhu72', 'rejected', '2025-07-02 08:07:08', NULL, 'uploads/profile_pictures/default.jpg', '', '', '', ''),
(8, 'Thimira Savinda', 'GAL/IT/2324/F/216', '200412701219', 'thimirapost116@gmail.com', 'Male', 'Malwachchigoda, Meegahatenna', 'Bord', '787842415', '712118983', '$2y$10$oUdPcfgn/3vqp2YxZUQFXe9x//OTm/GOG9uhsa.pjNTf4.WT641/e', 'rejected', '2025-07-02 18:23:13', '2025-07-03 06:53:51', 'uploads/profile_pictures/default.jpg', '', '', '', ''),
(9, 'I.G.N.Sewwandi', 'GAL/IT/2324/F/0018', '200350302032', 'nadeeshasewwandi859@gmail.com', 'Female', 'Iluppellagewaththa,Kirimetimulla,Thelijjawila', 'Bord', '752462899', '704944170', '$2y$10$t3TlglzJ6T4h3XXrlLXLQu3L5foKoaU8lDwln0LU/CK0FHX8JrsWO', 'rejected', '2025-07-04 06:49:32', '2025-07-04 19:20:20', 'uploads/profile_pictures/default.jpg', '', '', '', ''),
(10, 'Dumindu Damsara', 'GAL/IT/2324/F/0050', '200105602878', 'dumindudamsara60@gmail.com', 'Male', '61 Rathna Gangodahena Bopagoda Akuressa', 'Bord', '721712468', '703146434', '$2y$10$rx.sg5KOpXUjdQNykmu.D.c2n9/OmcJXoosd2kgWbT7j1krDoc17.', 'rejected', '2025-07-07 00:42:59', '2025-07-07 13:13:27', 'uploads/profile_pictures/686b7df08467c-WhatsApp Image 2025-07-07 at 13.23.53.jpeg', '', 'https://web.facebook.com/dumindu.sirijayalathjothirathna.9', 'https://www.linkedin.com/in/dumindu-damsara-0049ab246/', 'https://github.com/dumindu2041329'),
(11, 'Subhashi Jayawardhana', 'GAL/IT/2324/F/0109', '200380813870', 'jayawardhanasubhashi@gmail.com', 'Female', 'Kapila Iron Works,Udugama Road, Ginigalgodalla,Thalgampala', 'Home', '753240927', '787854336', '$2y$10$Um1dprESlrTtGfn8KEF2RulnGRHN5ONDI.JK9DFA71aVmKEFeaTOW', 'rejected', '2025-07-11 03:12:28', NULL, 'uploads/profile_pictures/default.jpg', '', '', '', ''),
(12, 'Enushka', 'GAL/IT/2324/F/0041', '200311300749', 'enushkagunarathna2@gmail.com', 'Male', 'Hikkaduwa', 'Home', '716538925', '776599023', '$2y$10$FqPvh4DTUgRy5mtWgPXUZ.ejTVOwmd/cngJP3kfo2X/Gm8xysyYIW', 'rejected', '2025-07-17 01:12:34', '2025-07-17 13:43:34', 'uploads/profile_pictures/default.jpg', '', '', '', '');

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

-- --------------------------------------------------------

--
-- Table structure for table `tuition_files`
--

CREATE TABLE `tuition_files` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `category` varchar(100) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `status` enum('active','disabled') DEFAULT 'active',
  `uploaded_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tuition_files`
--

INSERT INTO `tuition_files` (`id`, `user_id`, `title`, `subject_id`, `category`, `filename`, `status`, `uploaded_at`) VALUES
(2, 0, 'Communication Skills Paper 2021', 6, 'Pass Papers', '1751207044_68614c84f41d6_CommunicationSkills-JUMAIL-0039.pdf', 'active', '2025-06-29 14:24:05'),
(3, 0, 'Computer and Network System Paper 2021', 3, 'Pass Papers', '1751207045_68614c8500718_ComputerandNetworkSystem-JUMAIL-0039.pdf', 'active', '2025-06-29 14:24:05'),
(4, 0, 'Computer and Network System Marking 2021', 3, 'Pass Papers', '1751207045_68614c8501063_HNDIT1032ComputerNetworkScheme.pdf', 'active', '2025-06-29 14:24:05'),
(5, 0, 'Information Management and Information System Paper 2021', 4, 'Pass Papers', '1751207045_68614c850175d_InformationManagementandInformationSystem-JUMAIL-0039.pdf', 'active', '2025-06-29 14:24:05'),
(6, 0, 'Information Management and Information System Marking 2021', 4, 'Pass Papers', '1751207045_68614c850226b_HNDIT1042InformationManagementandInformationSystems-Answer-1styear2021.pdf', 'active', '2025-06-29 14:24:05'),
(7, 0, 'Visual Application Programming Paper 2021', 1, 'Pass Papers', '1751207045_68614c8502ad7_VisualApplicationProgramming-JUMAIL-0039.pdf', 'active', '2025-06-29 14:24:05'),
(8, 0, 'Visual Application Programming Marking 2021', 1, 'Pass Papers', '1751207045_68614c850319f_HNDIT1012VisualScheme.pdf', 'active', '2025-06-29 14:24:05'),
(9, 0, 'Web Design Paper 2021', 2, 'Pass Papers', '1751207045_68614c85037e3_WebDesign-JUMAIL-0039.pdf', 'active', '2025-06-29 14:24:05'),
(10, 0, 'Web Design Marking 2021', 2, 'Pass Papers', '1751207045_68614c8503d25_HNDIT1022WebDesignScheme-new.pdf', 'active', '2025-06-29 14:24:05'),
(11, 0, 'Paper 2022', 6, 'Pass Papers', '1751208012_6861504c10451_2022-communicationskills.pdf', 'active', '2025-06-29 14:40:12'),
(12, 0, 'Paper 2022', 3, 'Pass Papers', '1751208012_6861504c532b1_2022-CNS.pdf', 'active', '2025-06-29 14:40:12'),
(13, 0, 'Paper 2022', 4, 'Pass Papers', '1751208012_6861504c743df_2022-MIS.pdf', 'active', '2025-06-29 14:40:12'),
(14, 0, 'Paper 2022', 1, 'Pass Papers', '1751208012_6861504ca11e9_2022-visual.pdf', 'active', '2025-06-29 14:40:12'),
(15, 0, 'Paper 2022', 2, 'Pass Papers', '1751208012_6861504cdfc07_2022-webdesign.pdf', 'active', '2025-06-29 14:40:13'),
(16, 0, 'Marking 2022', 6, 'Pass Papers', '1751208154_686150dade23f_2022-HNDIT1062-Marking-CommunicationSkill.pdf', 'active', '2025-06-29 14:42:34'),
(17, 0, 'Marking 2022', 3, 'Pass Papers', '1751208154_686150dade935_IT1032MarkingScheme2022-ComputerNetwork.pdf', 'active', '2025-06-29 14:42:34'),
(18, 0, 'Marking 2022', 4, 'Pass Papers', '1751208154_686150dadf020_.2024.01.10E-HNDIT1042_1styear_2022_InformationManagementandInformationSystems_MarkingScheme.pdf', 'active', '2025-06-29 14:42:34'),
(19, 0, 'Marking 2022', 1, 'Pass Papers', '1751208154_686150dadf577_HNDIT1012-Visual_2022_MarkingScheme.pdf', 'active', '2025-06-29 14:42:34'),
(20, 0, 'Marking 2022', 2, 'Pass Papers', '1751208154_686150dadfa3b_WebDesignMarkingScheme-2022.pdf', 'active', '2025-06-29 14:42:34'),
(21, 0, 'Paper 2021', 10, 'Pass Papers', '1751208299_6861516b111be_DataCommunicationandComputerNetworks-JUMAIL-00391.pdf', 'active', '2025-06-29 14:44:59'),
(22, 0, 'Paper 2021', 7, 'Pass Papers', '1751208299_6861516b118ba_FundamentalsofProgramming-JUMAIL-0039.pdf', 'active', '2025-06-29 14:44:59'),
(23, 0, 'Paper 2021', 11, 'Pass Papers', '1751208299_6861516b11ef9_PrincipelsofUserInterfaceDesign-JUMAIL-0039.pdf', 'active', '2025-06-29 14:44:59'),
(24, 0, 'Paper 2021', 8, 'Pass Papers', '1751208299_6861516b12595_Softwaredevelopment-JUMAIL-0039.pdf', 'active', '2025-06-29 14:44:59'),
(25, 0, 'Paper 2021', 9, 'Pass Papers', '1751208299_6861516b12cfa_SystemAnalysisandDesign-JUMAIL-0039.pdf', 'active', '2025-06-29 14:44:59'),
(26, 0, 'Paper 2021', 13, 'Pass Papers', '1751208299_6861516b13372_TechnicalWriting-JUMAIL-0039.pdf', 'active', '2025-06-29 14:44:59'),
(27, 0, 'Marking 2021', 10, 'Pass Papers', '1751208520_68615248d6dda_HNDIT2042-2021DataCommunicationandNetwork.pdf', 'active', '2025-06-29 14:48:40'),
(28, 0, 'Marking 2021', 7, 'Pass Papers', '1751208520_68615248d74fb_HNDIT2012fundamentalsofprogrammingMarkingScheme.pdf', 'active', '2025-06-29 14:48:40'),
(29, 0, 'Marking 2021', 11, 'Pass Papers', '1751208520_68615248d7b7f_HNDIT2052PrinciplesofUID_2021-Answer.pdf', 'active', '2025-06-29 14:48:40'),
(30, 0, 'Marking 2021', 8, 'Pass Papers', '1751208520_68615248d80a7_HNDIT2022-SoftwareDevelopmentMarkingScheme2021.pdf', 'active', '2025-06-29 14:48:40'),
(31, 0, 'Marking 2021', 9, 'Pass Papers', '1751208520_68615248d8972_HNDIT2032-SADMarkingScheme.pdf', 'active', '2025-06-29 14:48:40'),
(32, 0, 'Marking 2021', 13, 'Pass Papers', '1751208520_68615248ed527_HNDIT2072-TechnicalWriting.pdf', 'active', '2025-06-29 14:48:40'),
(33, 0, 'Paper 2022', 10, 'Pass Papers', '1751208886_686153b6c12d6_2022-DCN.pdf', 'active', '2025-06-29 14:54:46'),
(34, 0, 'Paper 2022', 7, 'Pass Papers', '1751208886_686153b6e77bf_2022-FoP.pdf', 'active', '2025-06-29 14:54:47'),
(35, 0, 'Paper 2022', 11, 'Pass Papers', '1751208887_686153b71a6fe_2022-UI.pdf', 'active', '2025-06-29 14:54:47'),
(36, 0, 'Paper 2022', 8, 'Pass Papers', '1751208887_686153b74f6f9_2022-SD.pdf', 'active', '2025-06-29 14:54:47'),
(37, 0, 'Paper 2022', 9, 'Pass Papers', '1751208887_686153b785d1c_2022-SAD.pdf', 'active', '2025-06-29 14:54:47'),
(38, 0, 'Paper 2022', 13, 'Pass Papers', '1751208887_686153b7d20d4_2022-technicalwriting.pdf', 'active', '2025-06-29 14:54:48'),
(39, 0, 'Marking 2022', 10, 'Pass Papers', '1751209056_68615460a54e7_HNDIT2042-2022-DCN.pdf', 'active', '2025-06-29 14:57:36'),
(40, 0, 'Marking 2022', 7, 'Pass Papers', '1751209056_68615460a5c6f_HNDIT2012MarkingSchemeFundamentalofProgramming.pdf', 'active', '2025-06-29 14:57:36'),
(41, 0, 'Marking 2022', 11, 'Pass Papers', '1751209056_68615460a6159_HNDIT2052_PUIDanswerscript2022sem2.pdf', 'active', '2025-06-29 14:57:36'),
(42, 0, 'Marking 2022', 8, 'Pass Papers', '1751209056_68615460a67fd_HNDIT2022-SoftwareDevelopment-2022-MarkingScheme.pdf', 'active', '2025-06-29 14:57:36'),
(43, 0, 'Marking 2022', 9, 'Pass Papers', '1751209056_68615460a6d3f_HNDIT2032SAD-Answer.pdf', 'active', '2025-06-29 14:57:36'),
(44, 0, 'Week 1', 1, 'Notes', '1751251447_6861f9f7edb42_W1.pptx', 'active', '2025-06-30 02:44:07'),
(45, 0, 'Week 2', 1, 'Notes', '1751251447_6861f9f7ef16b_W2.pptx', 'active', '2025-06-30 02:44:08'),
(46, 0, 'Week 3', 1, 'Notes', '1751251448_6861f9f80d372_W3.pptx', 'active', '2025-06-30 02:44:08'),
(47, 0, 'Week 4', 1, 'Notes', '1751251448_6861f9f82cac9_W4.pptx', 'active', '2025-06-30 02:44:08'),
(48, 0, 'Week 5', 1, 'Notes', '1751251448_6861f9f830077_W5.pptx', 'active', '2025-06-30 02:44:08'),
(49, 0, 'Week 6', 1, 'Notes', '1751251448_6861f9f83520e_W6.pptx', 'active', '2025-06-30 02:44:08'),
(50, 0, 'Week 7', 1, 'Notes', '1751251448_6861f9f83c75d_W7.pptx', 'active', '2025-06-30 02:44:08'),
(51, 0, 'Week 8', 1, 'Notes', '1751251448_6861f9f847b45_W8.pptx', 'active', '2025-06-30 02:44:08'),
(52, 0, 'Week 9', 1, 'Notes', '1751251448_6861f9f84d080_W9.pptx', 'active', '2025-06-30 02:44:08'),
(53, 0, 'Week 10', 1, 'Notes', '1751251448_6861f9f852267_W10.pptx', 'active', '2025-06-30 02:44:08'),
(54, 0, 'Lab sheet 1', 1, 'Notes', '1751251448_6861f9f857c67_Labsheet1.docx', 'active', '2025-06-30 02:44:08'),
(55, 0, 'Lab sheet 1', 1, 'Notes', '1751251448_6861f9f894344_LabSheet2.docx', 'active', '2025-06-30 02:44:08'),
(56, 0, 'BMI Calculator', 1, 'Notes', '1751251448_6861f9f89eb6e_BMICalculatorApplication.pdf', 'active', '2025-06-30 02:44:08'),
(57, 0, 'VAP Pos System', 1, 'Notes', '1751251448_6861f9f8a3530_VAPPOSTutorial.pdf', 'active', '2025-06-30 02:44:08'),
(58, 0, 'Week 1', 2, 'Notes', '1751253097_68620069b8764_HNDIT1022Week01Theory.pptx', 'active', '2025-06-30 03:11:38'),
(59, 0, 'Week 2', 2, 'Notes', '1751253098_6862006a24c94_HNDIT1022Week02Theory.pptx', 'active', '2025-06-30 03:11:38'),
(60, 0, 'Week 3-1', 2, 'Notes', '1751253098_6862006a3e29f_HNDIT1022Week03Part1Theory.pptx', 'active', '2025-06-30 03:11:38'),
(61, 0, 'Week 3-2', 2, 'Notes', '1751253098_6862006a5b610_HNDIT1022Week03Part2Theory.pptx', 'active', '2025-06-30 03:11:38'),
(62, 0, 'Week 4', 2, 'Notes', '1751253098_6862006a6876c_HNDIT1022Week04Theory.pptx', 'active', '2025-06-30 03:11:38'),
(63, 0, 'Week 5', 2, 'Notes', '1751253098_6862006a900b9_HNDIT1022Week05Theory.pptx', 'active', '2025-06-30 03:11:38'),
(64, 0, 'Week 6', 2, 'Notes', '1751253098_6862006aa387e_HNDIT1022Week06Theory.pptx', 'active', '2025-06-30 03:11:38'),
(65, 0, 'Week 7', 2, 'Notes', '1751253098_6862006ab1052_HNDIT1022Week07Theory.pptx', 'active', '2025-06-30 03:11:38'),
(66, 0, 'Week 8.9.10', 2, 'Notes', '1751253098_6862006abca86_HNDIT1022Week080910Theory.pptx', 'active', '2025-06-30 03:11:38'),
(67, 0, 'Week 13', 2, 'Notes', '1751253098_6862006ad6563_HNDIT1022Week13Theory.pptx', 'active', '2025-06-30 03:11:38'),
(68, 0, 'labsheet1', 2, 'Notes', '1751253098_6862006ade2be_HNDIT1022labsheet01.docx', 'active', '2025-06-30 03:11:38'),
(69, 0, 'labsheet2', 2, 'Notes', '1751253098_6862006ae5f1d_HNDIT1022labsheet02.docx', 'active', '2025-06-30 03:11:39'),
(70, 0, 'labsheet4', 2, 'Notes', '1751253099_6862006b3a2a9_HNDIT1022labsheet04.docx', 'active', '2025-06-30 03:11:39'),
(71, 0, 'labsheet5', 2, 'Notes', '1751253099_6862006b41e11_HNDIT1022labsheet05.docx', 'active', '2025-06-30 03:11:39'),
(72, 0, 'labsheet7', 2, 'Notes', '1751253099_6862006b49a90_HNDIT1022labsheet07.docx', 'active', '2025-06-30 03:11:39'),
(73, 0, 'labsheet13', 2, 'Notes', '1751253099_6862006b51902_HNDIT1022labsheet13.docx', 'active', '2025-06-30 03:11:39'),
(74, 0, 'practicle quick rev', 2, 'Notes', '1751253099_6862006b59953_HNDIT1022PracticalQuickRevision.docx', 'active', '2025-06-30 03:11:39'),
(75, 0, 'Week 1', 3, 'Notes', '1751254259_686204f3cb1c5_IT1032-Week01.pptx', 'active', '2025-06-30 03:31:00'),
(76, 0, 'Week 2', 3, 'Notes', '1751254260_686204f4017b5_IT1032-Week02.pptx', 'active', '2025-06-30 03:31:00'),
(77, 0, 'Week 3', 3, 'Notes', '1751254260_686204f41eb3a_IT1032-Week03.pptx', 'active', '2025-06-30 03:31:00'),
(78, 0, 'Week 4', 3, 'Notes', '1751254260_686204f4323d3_IT1032-Week04.pptx', 'active', '2025-06-30 03:31:00'),
(79, 0, 'Week 5', 3, 'Notes', '1751254260_686204f43ff38_IT1032-Week05.pptx', 'active', '2025-06-30 03:31:00'),
(80, 0, 'Week 6', 3, 'Notes', '1751254260_686204f4632a9_IT1032-Week06.pptx', 'active', '2025-06-30 03:31:00'),
(81, 0, 'Week 7', 3, 'Notes', '1751254260_686204f4ad822_IT1032-Week07.pptx', 'active', '2025-06-30 03:31:00'),
(82, 0, 'Week 8', 3, 'Notes', '1751254260_686204f4bd5f3_IT1032-Week08.pptx', 'active', '2025-06-30 03:31:00'),
(83, 0, 'Week 9', 3, 'Notes', '1751254260_686204f4d4594_IT1032-Week09.pptx', 'active', '2025-06-30 03:31:00'),
(84, 0, 'Week 10.11', 3, 'Notes', '1751254260_686204f4f39f1_IT1032-Week10_11.pptx', 'active', '2025-06-30 03:31:01'),
(85, 0, 'Week 12', 3, 'Notes', '1751254261_686204f5343a9_IT1032-Week12.pptx', 'active', '2025-06-30 03:31:01'),
(86, 0, 'Week 13', 3, 'Notes', '1751254261_686204f553793_IT1032-Week13.pptx', 'active', '2025-06-30 03:31:01'),
(87, 0, 'Week 14', 3, 'Notes', '1751254261_686204f5a9806_IT1032-Week14.pptx', 'active', '2025-06-30 03:31:01'),
(88, 0, 'Tutorial 01', 3, 'Notes', '1751254261_686204f5de28e_IT1032-Tutorial01.pdf', 'active', '2025-06-30 03:31:01'),
(89, 0, 'Tutorial 02', 3, 'Notes', '1751254261_686204f5dffcc_IT1032-Tutorial02.pdf', 'active', '2025-06-30 03:31:01'),
(90, 0, 'Tutorial 03', 3, 'Notes', '1751254261_686204f5e3a9b_IT1032-Tutorial03.pdf', 'active', '2025-06-30 03:31:01'),
(91, 0, 'Tutorial 09', 3, 'Notes', '1751254261_686204f5e711a_Tutorial09.pdf', 'active', '2025-06-30 03:31:01'),
(92, 0, 'Tutorial 12', 3, 'Notes', '1751254261_686204f5eb36d_IT1032-Tutorial12.pdf', 'active', '2025-06-30 03:31:01'),
(93, 0, 'Model Paper', 3, 'Notes', '1751254261_686204f5ef9e8_IT1032-ModelPaper.pdf', 'active', '2025-06-30 03:31:01'),
(94, 0, 'Week 1', 4, 'Notes', '1751255293_686208fd01c4d_MISweek1.pptx', 'active', '2025-06-30 03:48:13'),
(95, 0, 'Week 2', 4, 'Notes', '1751255293_686208fd3a356_MISweek2.pdf', 'active', '2025-06-30 03:48:13'),
(96, 0, 'Week 3', 4, 'Notes', '1751255293_686208fd4d954_week3.pdf', 'active', '2025-06-30 03:48:13'),
(97, 0, 'Week 4-1', 4, 'Notes', '1751255293_686208fd5f567_MISweek4.pdf', 'active', '2025-06-30 03:48:13'),
(98, 0, 'Week 4-2', 4, 'Notes', '1751255293_686208fd64fb3_MISweek4-1.pdf', 'active', '2025-06-30 03:48:13'),
(99, 0, 'Week 5', 4, 'Notes', '1751255293_686208fd6d124_MISweek5.pdf', 'active', '2025-06-30 03:48:13'),
(100, 0, 'Week 6', 4, 'Notes', '1751255293_686208fd8e23c_MISweek6.pdf', 'active', '2025-06-30 03:48:13'),
(101, 0, 'Week 7', 4, 'Notes', '1751255293_686208fda393d_MISweek7.pdf', 'active', '2025-06-30 03:48:13'),
(102, 0, 'Week 8', 4, 'Notes', '1751255293_686208fdb5307_MIS8-newAutosaved.pdf', 'active', '2025-06-30 03:48:13'),
(103, 0, 'Week 9', 4, 'Notes', '1751255293_686208fdc8b70_MIS9.pdf', 'active', '2025-06-30 03:48:13'),
(104, 0, 'Week 10', 4, 'Notes', '1751255293_686208fdd2a96_MIS10.pdf', 'active', '2025-06-30 03:48:13'),
(105, 0, 'week 1 activity 1', 4, 'Notes', '1751255293_686208fddf659_week1activity.docx', 'active', '2025-06-30 03:48:13'),
(106, 0, 'week 1 activity 2', 4, 'Notes', '1751255293_686208fddfb9a_week1activity2.docx', 'active', '2025-06-30 03:48:13'),
(107, 0, 'activity 3', 4, 'Notes', '1751255293_686208fdf3a70_Activity3.pdf', 'active', '2025-06-30 03:48:13'),
(108, 0, 'Paper 2023', 6, 'Pass Papers', '1752077219_686e93a3cc9be_edulk.hndit.1062.com.skills.2023.pdf', 'active', '2025-07-09 16:06:59'),
(109, 0, 'Paper 2023', 3, 'Pass Papers', '1752077219_686e93a3ce391_edulk.hndit.1042.cns.2023.pdf', 'active', '2025-07-09 16:06:59'),
(110, 0, 'Paper 2023', 1, 'Pass Papers', '1752077219_686e93a3e7376_edulk.hndit.1012.vap.2023.pdf', 'active', '2025-07-09 16:07:00'),
(111, 0, 'Paper 2023', 2, 'Pass Papers', '1752077220_686e93a416476_edulk.hndit.1022.web.design.2023.pdf', 'active', '2025-07-09 16:07:00'),
(112, 0, 'Paper 2023', 4, 'Pass Papers', '1752077220_686e93a43f485_edulk.hndit.1042.ims.2023.pdf', 'active', '2025-07-09 16:07:00'),
(113, 0, 'Paper 2023', 11, 'Pass Papers', '1752086273_686eb70174f72_Edulk.hndit2052.ui.2023.pdf', 'active', '2025-07-09 18:37:53'),
(114, 0, 'Paper 2023', 9, 'Pass Papers', '1752086273_686eb70176b12_Edulk-hndit2052.SAD.2023.pdf', 'active', '2025-07-09 18:37:53'),
(115, 0, 'Assignment', 10, 'Pass Papers', '1752086273_686eb701776a3_ASS-DCCN.docx', 'active', '2025-07-09 18:37:53'),
(116, 0, 'Assignment', 9, 'Pass Papers', '1752086273_686eb70177ab2_IMG_20250709_173106.jpg', 'active', '2025-07-09 18:37:53'),
(117, 0, 'Assignment', 3, 'Pass Papers', '1752086377_686eb7690d161_SLIATE-Copy.docx', 'active', '2025-07-09 18:39:37'),
(118, 0, 'Assignment', 2, 'Pass Papers', '1752086377_686eb7690d734_edulk.web.design.assignment.2023.pdf', 'active', '2025-07-09 18:39:37'),
(119, 0, 'Assignment 2023', 1, 'Pass Papers', '1752141169_686f8d71a890b_1752141098669.jpg', 'active', '2025-07-10 09:52:49');

-- --------------------------------------------------------

--
-- Table structure for table `user_logs`
--

CREATE TABLE `user_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `device_type` varchar(50) DEFAULT NULL,
  `os` varchar(50) DEFAULT NULL,
  `browser` varchar(50) DEFAULT NULL,
  `language` varchar(100) DEFAULT NULL,
  `referrer` text DEFAULT NULL,
  `current_url` text DEFAULT NULL,
  `session_id` varchar(128) DEFAULT NULL,
  `accessed_at` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `user_logs`
--

INSERT INTO `user_logs` (`id`, `user_id`, `ip_address`, `user_agent`, `device_type`, `os`, `browser`, `language`, `referrer`, `current_url`, `session_id`, `accessed_at`) VALUES
(1, NULL, '101.2.191.75', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Mobile Safari/537.36', 'Mobile', 'Linux', 'Chrome', 'en-GB,en-US;q=0.9,en;q=0.8', 'https://edulk.42web.io/index.php?i=1', '/guest_view.php?semester=Semester+I&subject=', '18f70c31a2217392645558d01ba6c5f7', '2025-07-10 22:46:25'),
(2, NULL, '101.2.191.75', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Mobile Safari/537.36', 'Mobile', 'Linux', 'Chrome', 'en-GB,en-US;q=0.9,en;q=0.8', 'https://edulk.42web.io/guest_view.php?semester=Semester+I&subject=', '/guest_view.php?semester=Semester+I&subject=6', '18f70c31a2217392645558d01ba6c5f7', '2025-07-10 22:47:10'),
(3, NULL, '101.2.191.75', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Mobile Safari/537.36', 'Mobile', 'Linux', 'Chrome', 'en-GB,en-US;q=0.9,en;q=0.8', 'https://edulk.42web.io/guest_view.php?semester=Semester+I&subject=6', '/guest_view.php?semester=Semester+I&subject=', '18f70c31a2217392645558d01ba6c5f7', '2025-07-10 22:47:14'),
(4, NULL, '101.2.191.75', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Mobile Safari/537.36', 'Mobile', 'Linux', 'Chrome', 'en-GB,en-US;q=0.9,en;q=0.8', 'https://edulk.42web.io/guest_view.php?semester=Semester+I&subject=', '/guest_view.php?semester=Semester+I&subject=5', '18f70c31a2217392645558d01ba6c5f7', '2025-07-10 22:47:16'),
(5, NULL, '64.233.173.35', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)', 'Mobile', 'Linux', 'Chrome', 'Unknown', 'Direct', '/guest_view.php?semester=Semester+I&subject=6', 'e203a4dc3c4704d1e7d3d19b92908106', '2025-07-10 22:47:16'),
(6, NULL, '66.102.6.129', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)', 'Mobile', 'Linux', 'Chrome', 'en-US', 'Direct', '/guest_view.php?semester=Semester+I&subject=6', '0f627ce8f0708c70d1c824261fafd3e9', '2025-07-10 22:47:17'),
(7, NULL, '66.102.6.128', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)', 'Mobile', 'Linux', 'Chrome', 'en-US', 'Direct', '/guest_view.php?semester=Semester+I&subject=6', 'c8be0b581dedf3280b2f94e7e5e5e897', '2025-07-10 22:47:17'),
(8, NULL, '101.2.191.75', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Mobile Safari/537.36', 'Mobile', 'Linux', 'Chrome', 'en-GB,en-US;q=0.9,en;q=0.8', 'https://edulk.42web.io/guest_view.php?semester=Semester+I&subject=5', '/guest_view.php?semester=Semester+I&subject=', '18f70c31a2217392645558d01ba6c5f7', '2025-07-10 22:47:19'),
(9, NULL, '101.2.191.75', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Mobile Safari/537.36', 'Mobile', 'Linux', 'Chrome', 'en-GB,en-US;q=0.9,en;q=0.8', 'https://edulk.42web.io/guest_view.php?semester=Semester+I&subject=', '/guest_view.php?semester=Semester+I&subject=1', '18f70c31a2217392645558d01ba6c5f7', '2025-07-10 22:47:21'),
(10, NULL, '112.134.104.213', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'Desktop', 'Windows 10', 'Chrome', 'en-US,en;q=0.9', 'https://edulk.42web.io/index.php?msg=Guest%20session%20expired.%20Please%20login.', '/guest_view.php?semester=Semester+I&subject=', '84ceea5f56d9e4bb18c36045a4829a68', '2025-07-10 22:58:33'),
(11, NULL, '175.157.191.233', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'Desktop', 'Windows 10', 'Chrome', 'en-US,en;q=0.9,si;q=0.8', 'https://edulk.42web.io/index.php', '/guest_view.php?semester=Semester+I&subject=', 'efb2beee9d83dc5c9748ed32d10f9c5a', '2025-07-11 02:02:01'),
(12, NULL, '175.157.103.169', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_4 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.7 Mobile/15E148 Safari/604.1', 'Mobile', 'Mac OS X', 'Safari', 'en-US,en;q=0.9', 'https://edulk.42web.io/index.php?i=1', '/guest_view.php?semester=Semester+I&subject=', '63573889306569adf8c1409ab09c2255', '2025-07-11 05:48:46'),
(13, NULL, '45.121.90.253', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.6312.118 Mobile Safari/537.36 XiaoMi/MiuiBrowser/14.37.0-gn', 'Mobile', 'Linux', 'Chrome', 'en-GB,en-US;q=0.9,en;q=0.8', 'https://edulk.42web.io/index.php', '/guest_view.php?semester=Semester+I&subject=', '64bbad861e5de5ad2fa88609e380b706', '2025-07-11 05:49:07'),
(14, NULL, '175.157.103.169', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_4 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.7 Mobile/15E148 Safari/604.1', 'Mobile', 'Mac OS X', 'Safari', 'en-US,en;q=0.9', 'https://edulk.42web.io/guest_view.php?semester=Semester+I&subject=', '/guest_view.php?semester=Semester+II&subject=', '63573889306569adf8c1409ab09c2255', '2025-07-11 05:49:08'),
(15, NULL, '45.121.90.253', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.6312.118 Mobile Safari/537.36 XiaoMi/MiuiBrowser/14.37.0-gn', 'Mobile', 'Linux', 'Chrome', 'en-GB,en-US;q=0.9,en;q=0.8', 'https://edulk.42web.io/guest_view.php?semester=Semester+I&subject=', '/guest_view.php?semester=Semester+II&subject=', '64bbad861e5de5ad2fa88609e380b706', '2025-07-11 05:50:06'),
(16, NULL, '45.121.90.253', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.6312.118 Mobile Safari/537.36 XiaoMi/MiuiBrowser/14.37.0-gn', 'Mobile', 'Linux', 'Chrome', 'en-GB,en-US;q=0.9,en;q=0.8', 'https://edulk.42web.io/guest_view.php?semester=Semester+II&subject=', '/guest_view.php?semester=Semester+III&subject=', '64bbad861e5de5ad2fa88609e380b706', '2025-07-11 05:50:10'),
(17, NULL, '223.224.31.213', 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_4_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.4 Mobile/15E148 Safari/604.1', 'Mobile', 'Mac OS X', 'Safari', 'en-GB,en;q=0.9', 'https://edulk.42web.io/index.php', '/guest_view.php?semester=Semester+I&subject=', 'ca0a342468a71195ce0d8baedd273423', '2025-07-11 06:12:44'),
(18, NULL, '223.224.31.213', 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_4_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.4 Mobile/15E148 Safari/604.1', 'Mobile', 'Mac OS X', 'Safari', 'en-GB,en;q=0.9', 'https://edulk.42web.io/guest_view.php?semester=Semester+I&subject=', '/guest_view.php?semester=Semester+II&subject=', 'ca0a342468a71195ce0d8baedd273423', '2025-07-11 06:13:19'),
(19, NULL, '223.224.31.213', 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_4_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.4 Mobile/15E148 Safari/604.1', 'Mobile', 'Mac OS X', 'Safari', 'en-GB,en;q=0.9', 'https://edulk.42web.io/guest_view.php?semester=Semester+II&subject=', '/guest_view.php?semester=Semester+II&subject=8', 'ca0a342468a71195ce0d8baedd273423', '2025-07-11 06:13:33'),
(20, NULL, '223.224.31.213', 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_4_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.4 Mobile/15E148 Safari/604.1', 'Mobile', 'Mac OS X', 'Safari', 'en-GB,en;q=0.9', 'https://edulk.42web.io/guest_view.php?semester=Semester+I&subject=', '/guest_view.php?semester=Semester+II&subject=', 'ca0a342468a71195ce0d8baedd273423', '2025-07-11 06:14:14'),
(21, NULL, '223.224.31.213', 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_4_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.4 Mobile/15E148 Safari/604.1', 'Mobile', 'Mac OS X', 'Safari', 'en-GB,en;q=0.9', 'https://edulk.42web.io/index.php', '/guest_view.php?semester=Semester+I&subject=', 'ca0a342468a71195ce0d8baedd273423', '2025-07-11 06:14:15'),
(22, NULL, '111.223.189.119', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/25.0 Chrome/121.0.0.0 Mobile Safari/537.36', 'Mobile', 'Linux', 'Chrome', 'en-GB,en-US;q=0.9,en;q=0.8', 'https://edulk.42web.io/index.php?i=1', '/guest_view.php?semester=Semester+I&subject=', 'ac003daf29382c090776ddd8c2d28ddb', '2025-07-11 15:40:57'),
(23, NULL, '111.223.189.119', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/25.0 Chrome/121.0.0.0 Mobile Safari/537.36', 'Mobile', 'Linux', 'Chrome', 'en-GB,en-US;q=0.9,en;q=0.8', 'https://edulk.42web.io/guest_view.php?semester=Semester+I&subject=', '/guest_view.php?semester=Semester+II&subject=', 'ac003daf29382c090776ddd8c2d28ddb', '2025-07-11 15:41:25'),
(24, NULL, '43.250.243.254', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.6 Mobile/15E148 Safari/604.1', 'Mobile', 'Mac OS X', 'Safari', 'en-GB,en;q=0.9', 'https://edulk.42web.io/index.php?i=1', '/guest_view.php?semester=Semester+I&subject=', 'a8134de541fc73ed7d27f31cba3ea3d0', '2025-07-12 03:23:37'),
(25, NULL, '43.250.243.254', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.6 Mobile/15E148 Safari/604.1', 'Mobile', 'Mac OS X', 'Safari', 'en-GB,en;q=0.9', 'https://edulk.42web.io/guest_view.php?semester=Semester+I&subject=', '/guest_view.php?semester=Semester+II&subject=', 'a8134de541fc73ed7d27f31cba3ea3d0', '2025-07-12 03:24:05'),
(26, NULL, '43.250.243.254', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.6 Mobile/15E148 Safari/604.1', 'Mobile', 'Mac OS X', 'Safari', 'en-GB,en;q=0.9', 'https://edulk.42web.io/guest_view.php?semester=Semester+II&subject=', '/guest_view.php?semester=Semester+II&subject=7', 'a8134de541fc73ed7d27f31cba3ea3d0', '2025-07-12 03:24:13'),
(27, NULL, '43.250.243.254', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.6 Mobile/15E148 Safari/604.1', 'Mobile', 'Mac OS X', 'Safari', 'en-GB,en;q=0.9', 'https://edulk.42web.io/guest_view.php?semester=Semester+I&subject=', '/guest_view.php?semester=Semester+II&subject=', 'a8134de541fc73ed7d27f31cba3ea3d0', '2025-07-12 03:24:28'),
(28, NULL, '203.189.185.84', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36 Edg/134.0.0.0', 'Desktop', 'Windows 10', 'Chrome', 'en-US,en;q=0.9', 'https://edulk.42web.io/index.php?i=1', '/guest_view.php?semester=Semester+I&subject=', '4abd6191c44c925fbc262fb432dd45cf', '2025-07-15 00:15:19'),
(29, NULL, '203.189.185.84', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36 Edg/134.0.0.0', 'Desktop', 'Windows 10', 'Chrome', 'en-US,en;q=0.9', 'https://edulk.42web.io/guest_view.php?semester=Semester+I&subject=', '/guest_view.php?semester=Semester+II&subject=', '4abd6191c44c925fbc262fb432dd45cf', '2025-07-15 00:15:27'),
(30, NULL, '203.189.185.84', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36 Edg/134.0.0.0', 'Desktop', 'Windows 10', 'Chrome', 'en-US,en;q=0.9', 'https://edulk.42web.io/guest_view.php?semester=Semester+II&subject=', '/guest_view.php?semester=Semester+II&subject=7', '4abd6191c44c925fbc262fb432dd45cf', '2025-07-15 00:15:29'),
(31, NULL, '203.189.185.84', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36 Edg/134.0.0.0', 'Desktop', 'Windows 10', 'Chrome', 'en-US,en;q=0.9', 'https://edulk.42web.io/guest_view.php?semester=Semester+II&subject=7', '/guest_view.php?semester=Semester+II&subject=', '4abd6191c44c925fbc262fb432dd45cf', '2025-07-15 00:15:37'),
(32, NULL, '203.189.189.169', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.6 Mobile/15E148 Safari/604.1', 'Mobile', 'Mac OS X', 'Safari', 'en-GB,en;q=0.9', 'https://edulk.42web.io/index.php?i=1', '/guest_view.php?semester=Semester+I&subject=', '2ff887dde0ebaac1c19906a41189f3ca', '2025-07-17 04:09:03'),
(33, NULL, '203.189.189.169', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.6 Mobile/15E148 Safari/604.1', 'Mobile', 'Mac OS X', 'Safari', 'en-GB,en;q=0.9', 'https://edulk.42web.io/guest_view.php?semester=Semester+I&subject=', '/guest_view.php?semester=Semester+II&subject=', '2ff887dde0ebaac1c19906a41189f3ca', '2025-07-17 04:09:11'),
(34, 12, '203.189.189.169', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.6 Mobile/15E148 Safari/604.1', 'Mobile', 'Mac OS X', 'Safari', 'en-GB,en;q=0.9', 'https://edulk.42web.io/index.php', '/guest_view.php?semester=Semester+I&subject=', '2ff887dde0ebaac1c19906a41189f3ca', '2025-07-17 04:15:09'),
(35, 12, '203.189.189.169', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.6 Mobile/15E148 Safari/604.1', 'Mobile', 'Mac OS X', 'Safari', 'en-GB,en;q=0.9', 'https://edulk.42web.io/index.php', '/guest_view.php?semester=Semester+I&subject=', '2ff887dde0ebaac1c19906a41189f3ca', '2025-07-17 04:15:34'),
(36, 12, '203.189.189.169', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_8_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.6 Mobile/15E148 Safari/604.1', 'Mobile', 'Mac OS X', 'Safari', 'en-GB,en;q=0.9', 'https://edulk.42web.io/guest_view.php?semester=Semester+I&subject=', '/guest_view.php?semester=Semester+II&subject=', '2ff887dde0ebaac1c19906a41189f3ca', '2025-07-17 04:15:38');

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
-- Indexes for table `lectures`
--
ALTER TABLE `lectures`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nic` (`nic`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `lectures_assignment`
--
ALTER TABLE `lectures_assignment`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lecturer_id` (`lecturer_id`,`subject_id`),
  ADD KEY `subject_id` (`subject_id`);

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
-- Indexes for table `tuition_files`
--
ALTER TABLE `tuition_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_subject` (`subject_id`);

--
-- Indexes for table `user_logs`
--
ALTER TABLE `user_logs`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `lectures`
--
ALTER TABLE `lectures`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lectures_assignment`
--
ALTER TABLE `lectures_assignment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sadmins`
--
ALTER TABLE `sadmins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `tuition_files`
--
ALTER TABLE `tuition_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=121;

--
-- AUTO_INCREMENT for table `user_logs`
--
ALTER TABLE `user_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
