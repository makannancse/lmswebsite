-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jun 09, 2026 at 02:19 PM
-- Server version: 8.4.7
-- PHP Version: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `learnwise`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
CREATE TABLE IF NOT EXISTS `admins` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=335 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `email`, `password`, `created_at`) VALUES
(1, 'admin@learnwise.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2026-05-02 08:36:25');

-- --------------------------------------------------------

--
-- Table structure for table `compliance_rules`
--

DROP TABLE IF EXISTS `compliance_rules`;
CREATE TABLE IF NOT EXISTS `compliance_rules` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `penalty` varchar(255) DEFAULT NULL,
  `sort_order` int DEFAULT '0',
  `status` enum('active','inactive') DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `compliance_rules`
--

INSERT INTO `compliance_rules` (`id`, `title`, `content`, `icon`, `penalty`, `sort_order`, `status`) VALUES
(3, 'Punctuality', 'Join within 3 minutes of scheduled start time\nCommunicate delays to administration promptly', 'bi-alarm', '₹50 penalty after grace period', 3, 'active'),
(4, 'Late Join by Teacher', 'Extend class duration accordingly', '⏳', '', 4, 'active'),
(5, 'Important Discussions', 'Keep the Director informed of significant student or parent concerns\nEscalate issues that affect learning outcomes or safety', 'bi-megaphone', '', 5, 'active'),
(6, 'Notice Period', 'Provide minimum 1 month notice before leaving the platform\nComplete all pending classes and handover documentation', 'bi-file-earmark-text', '', 6, 'active'),
(7, 'Student No Show', 'Wait 3 minutes for the student to join\nSend WhatsApp message at the 4th minute\nCall the parent at the 6th minute\nEnd session after 15 minutes if unresolved', 'bi-person-x', '', 1, 'active'),
(8, 'Teacher No Show', 'Cancel at least 1 hour before the scheduled class\nInform administration and the family immediately\nDocument the reason in class records', 'bi-calendar-x', '₹250 penalty if missed', 2, 'active'),
(9, 'Late Join', 'Extend class duration to compensate for teacher late arrival\nEnsure students receive full allotted learning time', 'bi-hourglass-split', '', 4, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

DROP TABLE IF EXISTS `courses`;
CREATE TABLE IF NOT EXISTS `courses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `category` varchar(100) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `sort_order` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `title`, `description`, `category`, `image`, `status`, `created_at`, `sort_order`, `updated_at`) VALUES
(1, 'Full Stack Coding Bootcamp', 'Real-time coding sessions with project support.', 'coding', '', 'active', '2026-05-10 06:45:04', 1, '2026-05-10 06:45:04'),
(2, 'Advanced Mathematics', 'Complete coverage for school and competition preparation.', 'math', '', 'active', '2026-05-10 06:45:04', 2, '2026-05-10 06:45:04'),
(3, 'STEM Science Lab', 'Live experiments, concepts, and guided problem solving.', 'science', '', 'active', '2026-05-10 06:45:04', 3, '2026-05-10 06:45:04'),
(4, 'English Communication', 'Interactive speaking and writing practice.', 'languages', '', 'active', '2026-05-10 06:45:04', 4, '2026-05-10 06:45:04'),
(5, 'Dance Class', 'Join our dynamic dance classes and master new moves with expert guidance. Perfect for beginners and advanced learners looking to grow their passion for dance.', 'Arts', '', 'active', '2026-06-09 14:06:47', 0, '2026-06-09 14:06:47');

-- --------------------------------------------------------

--
-- Table structure for table `homepage_sections`
--

DROP TABLE IF EXISTS `homepage_sections`;
CREATE TABLE IF NOT EXISTS `homepage_sections` (
  `id` int NOT NULL AUTO_INCREMENT,
  `section_key` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `content` text,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `section_key` (`section_key`)
) ENGINE=InnoDB AUTO_INCREMENT=352 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `homepage_sections`
--

INSERT INTO `homepage_sections` (`id`, `section_key`, `title`, `subtitle`, `content`, `image`, `status`, `updated_at`) VALUES
(1, 'hero', 'Transform Your Learning Experience', 'Smart class scheduling, live learning, and performance tracking in one platform.', 'Enroll Now', '', 'active', '2026-05-02 08:36:25'),
(2, 'features', 'Built for modern online education', 'Manage classes, homework, reports, payments, and global learners from one dashboard.', 'Google Meet Integration|Connect classes with unique meeting links\\nHomework & Assignments|Track submissions easily\\nStudent Reports|Share performance insights\\nINR Payment System|Handle payments in Indian Rupees\\nMulti-Timezone Support|Schedule for students worldwide\\nRecording Access|Share recorded lessons after class', '', 'inactive', '2026-05-02 08:49:30'),
(3, 'cta', 'Start Your Learning Journey Today', 'Choose LearnWise to streamline live lessons, homework, and reporting in one modern platform.', 'Enroll Now', '', 'active', '2026-05-02 08:36:25');

-- --------------------------------------------------------

--
-- Table structure for table `leads`
--

DROP TABLE IF EXISTS `leads`;
CREATE TABLE IF NOT EXISTS `leads` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(40) NOT NULL,
  `message` text,
  `source` varchar(60) DEFAULT 'website',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `parent_name` varchar(120) DEFAULT NULL,
  `student_name` varchar(120) DEFAULT NULL,
  `course` varchar(150) DEFAULT NULL,
  `status` enum('new','contacted','converted','closed') NOT NULL DEFAULT 'new',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `menus`
--

DROP TABLE IF EXISTS `menus`;
CREATE TABLE IF NOT EXISTS `menus` (
  `id` int NOT NULL AUTO_INCREMENT,
  `menu_name` varchar(150) NOT NULL,
  `menu_link` varchar(255) NOT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `menus`
--

INSERT INTO `menus` (`id`, `menu_name`, `menu_link`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Home', 'index.php', 1, 'active', '2026-05-10 06:45:04', '2026-05-10 06:45:04'),
(2, 'Courses', 'courses.php', 2, 'active', '2026-05-10 06:45:04', '2026-05-10 06:45:04'),
(3, 'Teachers', 'teachers.php', 3, 'active', '2026-05-10 06:45:04', '2026-05-10 06:45:04'),
(4, 'Teaching Standards', 'standards.php', 4, 'active', '2026-05-10 06:45:04', '2026-05-10 06:45:04'),
(5, 'About', 'about.php', 5, 'active', '2026-05-10 06:45:04', '2026-05-10 06:45:04'),
(6, 'FAQ', 'faq.php', 6, 'active', '2026-05-10 06:45:04', '2026-05-10 06:45:04'),
(7, 'Contact', 'contact.php', 7, 'active', '2026-05-10 06:45:04', '2026-05-10 06:45:04'),
(8, 'Enroll Now', 'enroll.php', 8, 'active', '2026-06-09 11:48:12', '2026-06-09 11:48:12');

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

DROP TABLE IF EXISTS `pages`;
CREATE TABLE IF NOT EXISTS `pages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `page_name` varchar(100) NOT NULL,
  `page_title` varchar(255) NOT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text,
  `og_image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `page_name` (`page_name`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pages`
--

INSERT INTO `pages` (`id`, `page_name`, `page_title`, `meta_title`, `meta_description`, `og_image`, `status`, `created_at`, `updated_at`) VALUES
(1, 'home', 'Home', 'LearnWise | Premium Learning for Modern Families', 'Explore the LearnWise learning platform, flexible programs, trusted educators, and immersive online class experiences.', 'https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=1200&q=80', 'active', '2026-05-10 06:45:04', '2026-05-10 06:45:04'),
(2, 'about', 'About', 'About LearnWise', 'Learn why LearnWise was created and how our online-first teaching model supports students and parents.', '', 'active', '2026-05-10 06:45:04', '2026-05-10 06:45:04'),
(3, 'courses', 'Courses', 'Courses | LearnWise', 'Browse LearnWise course categories, enrichment tracks, and academic support programs.', '', 'active', '2026-05-10 06:45:04', '2026-05-10 06:45:04'),
(4, 'teachers', 'Teachers', 'Teachers | LearnWise', 'Meet the LearnWise teachers behind our interactive, high-impact online classes.', '', 'active', '2026-05-10 06:45:04', '2026-05-10 06:45:04'),
(5, 'faq', 'FAQ', 'FAQ | LearnWise', 'Answers to common questions about LearnWise classes, scheduling, recordings, and support.', '', 'active', '2026-05-10 06:45:04', '2026-05-10 06:45:04'),
(6, 'contact', 'Contact', 'Contact LearnWise', 'Talk with LearnWise about demos, admissions, family support, and partnerships.', '', 'active', '2026-05-10 06:45:04', '2026-05-10 06:45:04'),
(7, 'standards', 'Teaching Standards', 'Teaching Standards | LearnWise', 'Explore LearnWise teaching standards, educator best practices, and classroom compliance protocols.', '', 'active', '2026-05-10 06:45:04', '2026-05-10 06:45:04'),
(8, 'enroll', 'Enroll Now', 'Enroll Now | LearnWise', 'Start your child\'s learning journey with LearnWise. Book a free trial or enroll in live online classes today.', '', 'active', '2026-06-09 11:48:12', '2026-06-09 11:48:12'),
(9, 'privacy', 'Privacy Policy', 'Privacy Policy | LearnWise', 'Learn how LearnWise protects student and family data across our online learning platform.', '', 'active', '2026-06-09 11:48:12', '2026-06-09 11:48:12'),
(10, 'terms', 'Terms & Conditions', 'Terms & Conditions | LearnWise', 'Read the terms and conditions for using LearnWise online classes, enrollment, and platform services.', '', 'active', '2026-06-09 11:48:12', '2026-06-09 11:48:12'),
(11, 'teacher-registration', 'Teacher Registration', 'Teacher Registration | LearnWise', 'Apply to teach with LearnWise. Join our community of qualified educators delivering premium online classes.', '', 'active', '2026-06-09 11:48:12', '2026-06-09 11:48:12'),
(12, 'student-registration', 'Student Registration', 'Student Registration | LearnWise', 'Register as a student with LearnWise and access personalized live classes, homework support, and progress tracking.', '', 'active', '2026-06-09 11:48:12', '2026-06-09 11:48:12');

-- --------------------------------------------------------

--
-- Table structure for table `page_sections`
--

DROP TABLE IF EXISTS `page_sections`;
CREATE TABLE IF NOT EXISTS `page_sections` (
  `id` int NOT NULL AUTO_INCREMENT,
  `page_name` varchar(100) NOT NULL,
  `section_type` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `content` text,
  `image` varchar(255) DEFAULT NULL,
  `button_text` varchar(100) DEFAULT NULL,
  `button_link` varchar(255) DEFAULT NULL,
  `sort_order` int DEFAULT '0',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `page_id` int DEFAULT NULL,
  `section_key` varchar(100) DEFAULT NULL,
  `section_title` varchar(255) DEFAULT NULL,
  `section_subtitle` text,
  `section_content` longtext,
  `section_image` varchar(255) DEFAULT NULL,
  `section_settings` longtext,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=83 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `page_sections`
--

INSERT INTO `page_sections` (`id`, `page_name`, `section_type`, `title`, `subtitle`, `content`, `image`, `button_text`, `button_link`, `sort_order`, `status`, `created_at`, `page_id`, `section_key`, `section_title`, `section_subtitle`, `section_content`, `section_image`, `section_settings`, `updated_at`) VALUES
(33, 'home', 'hero', 'Personalized Learning for Your Child\'s Success', 'Live and self-paced online classes with expert tutors that support core learning, enrichment, and real-world skills. We match your child with the perfect tutor.', '', 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=1200&q=80', 'Book Free Demo', '#lead-form', 1, 'active', '2026-05-10 01:59:57', 1, 'hero', 'Personalized Learning for Your Child\'s Success', 'Live and self-paced online classes with expert tutors that support core learning, enrichment, and real-world skills. We match your child with the perfect tutor.', '', 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=1200&q=80', NULL, '2026-05-10 06:45:04'),
(34, 'home', 'curriculum', 'Supporting All Major Education Boards', 'Whether your child follows a local or international curriculum, SmartEdWise connects them with expert tutors who know the syllabus inside out.', 'American Curriculum|Common Core, AP, and state-specific standards for K–12 learners across the US.\r\nAustralian Curriculum|Aligned with ACARA standards across all key learning areas and year levels.\r\nCanadian Curriculum|Provincial curricula support from Ontario, BC, Alberta, and more.\r\nGlobal & Regional Boards|IB, CBSE, ICSE, UAE MOE, Singapore, and British GCSE/A-Level preparation.\r\nCo-Curriculars & Future Skills|Coding, AI, chess, robotics, creative writing, and financial literacy.', '', '', '', 2, 'inactive', '2026-05-10 01:59:57', 1, 'curriculum', 'Supporting All Major Education Boards', 'Whether your child follows a local or international curriculum, SmartEdWise connects them with expert tutors who know the syllabus inside out.', 'American Curriculum|Common Core, AP, and state-specific standards for K–12 learners across the US.\r\nAustralian Curriculum|Aligned with ACARA standards across all key learning areas and year levels.\r\nCanadian Curriculum|Provincial curricula support from Ontario, BC, Alberta, and more.\r\nGlobal & Regional Boards|IB, CBSE, ICSE, UAE MOE, Singapore, and British GCSE/A-Level preparation.\r\nCo-Curriculars & Future Skills|Coding, AI, chess, robotics, creative writing, and financial literacy.', '', NULL, '2026-06-09 11:50:07'),
(35, 'home', 'hero', 'A Personal Vision', 'Hear from the force behind LearnWise about our commitment to your child\'s future.', '\"I started LearnWise with one clear belief—students deserve more value than what most education offers today. We deeply respect the time you invest and every single penny you spend. And we carry a strong promise—to deliver 10x value in return through everything we do. This is not about promises alone. It\'s about consistently delivering real quality, with honesty and global standards at the core\"\r\n\r\nLearnWise\r\nRidhima Gupta\r\nFounder & CEO', 'https://images.unsplash.com/photo-1494790108755-2616b612b786?auto=format&fit=crop&w=400&q=80', '', '', 3, 'inactive', '2026-05-10 01:59:57', 1, 'hero', 'A Personal Vision', 'Hear from the force behind LearnWise about our commitment to your child\'s future.', '\"I started LearnWise with one clear belief—students deserve more value than what most education offers today. We deeply respect the time you invest and every single penny you spend. And we carry a strong promise—to deliver 10x value in return through everything we do. This is not about promises alone. It\'s about consistently delivering real quality, with honesty and global standards at the core\"\r\n\r\nLearnWise\r\nNarmadha Suresh\r\nFounder & CEO', 'http://localhost/learnwise/uploads/images/DDS.JPG', '', '2026-06-09 11:50:17'),
(36, 'home', 'how_it_works', 'How LearnWise Works', 'Getting started is simple. We handle the hard part so you can focus on what matters — your child\'s growth.', 'Tell Us About Your Child|Share your child\'s age, subjects, learning style, and goals through a quick questionnaire.\r\nWe Match the Perfect Tutor|Our smart matching algorithm pairs your child with a vetted expert tutor tailored to their needs.\r\nFree Demo Lecture|Your child attends a free introductory session — no commitment, no credit card required.\r\nStart Learning & Growing|Continue with a plan that fits your family. Track progress with detailed reports and parent dashboards.', 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=1200&q=80', '', '', 4, 'inactive', '2026-05-10 01:59:57', 1, 'how_it_works', 'How LearnWise Works', 'Getting started is simple. We handle the hard part so you can focus on what matters — your child\'s growth.', 'Tell Us About Your Child|Share your child\'s age, subjects, learning style, and goals through a quick questionnaire.\r\nWe Match the Perfect Tutor|Our smart matching algorithm pairs your child with a vetted expert tutor tailored to their needs.\r\nFree Demo Lecture|Your child attends a free introductory session — no commitment, no credit card required.\r\nStart Learning & Growing|Continue with a plan that fits your family. Track progress with detailed reports and parent dashboards.', 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=1200&q=80', NULL, '2026-06-09 11:50:07'),
(37, 'home', 'free_demo', 'Your First Demo Lecture is Completely Free', 'Experience LearnWise with zero risk. Your child gets a full introductory session with their matched tutor — absolutely free.', 'Live 1-on-1 session with an expert tutor|Personalized to your child\'s learning level|No obligation to continue — ever|Full parent feedback report after the session', '', 'Book Your Free Demo', '#lead-form', 5, 'inactive', '2026-05-10 01:59:57', 1, 'free_demo', 'Your First Demo Lecture is Completely Free', 'Experience LearnWise with zero risk. Your child gets a full introductory session with their matched tutor — absolutely free.', 'Live 1-on-1 session with an expert tutor|Personalized to your child\'s learning level|No obligation to continue — ever|Full parent feedback report after the session', '', NULL, '2026-06-09 11:50:07'),
(39, 'home', 'testimonials', 'Why Parents Trust Us', 'See real results from real students and parents who have experienced the LearnWise difference', 'Student Success Story|Watch how our students improved their grades|https://images.unsplash.com/photo-1544717297-fa95b6ee9643?auto=format&fit=crop&w=400&q=80\r\nParent Testimonial|Hear from parents about their experience|https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=400&q=80\r\nParent Review|Outstanding improvement in math scores|https://images.unsplash.com/photo-1438761681033-6461ffad8d80?auto=format&fit=crop&w=400&q=80', '', 'Success Stories', 'success-stories.php', 7, 'active', '2026-05-10 01:59:57', 1, 'testimonials', 'Why Parents Trust Us', 'See real results from real students and parents who have experienced the LearnWise difference', 'Student Success Story|Watch how our students improved their grades|https://images.unsplash.com/photo-1544717297-fa95b6ee9643?auto=format&fit=crop&w=400&q=80\r\nParent Testimonial|Hear from parents about their experience|https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=400&q=80\r\nParent Review|Outstanding improvement in math scores|https://images.unsplash.com/photo-1438761681033-6461ffad8d80?auto=format&fit=crop&w=400&q=80', '', NULL, '2026-05-10 06:45:04'),
(40, 'home', 'stats', 'Trusted by Thousands', '', '5,000+|Happy Parents Trust us with their children\r\n5★|Average Rating Across multiple platforms\r\n100%|Success Rate Students see improvement', '', '', '', 8, 'inactive', '2026-05-10 01:59:57', 1, 'stats', 'Trusted by Thousands', '', '5,000+|Happy Parents Trust us with their children\r\n5★|Average Rating Across multiple platforms\r\n100%|Success Rate Students see improvement', '', NULL, '2026-06-09 11:50:07'),
(41, 'home', 'cta', 'Ready to unlock your child\'s potential?', 'Book a free demo lecture today. No credit card, no commitment — just great learning.', '', '', 'Get Started — It\'s Free', '#lead-form', 9, 'inactive', '2026-05-10 01:59:57', 1, 'cta', 'Ready to unlock your child\'s potential?', 'Book a free demo lecture today. No credit card, no commitment — just great learning.', '', '', NULL, '2026-06-09 11:50:07'),
(42, 'home', 'teaching_standards', 'Teaching Standards', 'See the professionalism, care, and teaching consistency every LearnWise educator is expected to deliver.', 'Explore our premium framework for lesson quality, parent communication, classroom discipline, and learner-first online instruction.', '', 'Explore Standards', 'standards.php', 10, 'inactive', '2026-05-10 02:26:29', 1, 'teaching_standards', 'Teaching Standards', 'See the professionalism, care, and teaching consistency every LearnWise educator is expected to deliver.', 'Explore our premium framework for lesson quality, parent communication, classroom discipline, and learner-first online instruction.', '', NULL, '2026-06-09 11:50:07'),
(43, 'standards', 'hero', 'Online Teaching Standards & Best Practices', 'Delivering high-quality learning experiences with professionalism, care, and impact.', 'A premium framework that helps LearnWise educators create trusted, engaging, and high-performing online classrooms.', 'https://images.unsplash.com/photo-1513258496099-48168024aec0?auto=format&fit=crop&w=900&q=80', 'Join LearnWise', 'contact.php', 1, 'active', '2026-05-10 02:26:29', 7, 'hero', 'Online Teaching Standards & Best Practices', 'Delivering high-quality learning experiences with professionalism, care, and impact.', 'A premium framework that helps LearnWise educators create trusted, engaging, and high-performing online classrooms.', 'https://images.unsplash.com/photo-1513258496099-48168024aec0?auto=format&fit=crop&w=900&q=80', NULL, '2026-05-10 06:45:04'),
(44, 'standards', 'mission', 'Mission', 'Deliver consistent, high-quality learning experiences with professionalism, care, and impact.', 'Create trusted online classrooms where every interaction reflects warmth, clarity, accountability, and learner-centered teaching.', '', 'Join LearnWise', 'contact.php', 2, 'active', '2026-05-10 02:26:29', 7, 'mission', 'Mission', 'Deliver consistent, high-quality learning experiences with professionalism, care, and impact.', 'Create trusted online classrooms where every interaction reflects warmth, clarity, accountability, and learner-centered teaching.', '', NULL, '2026-05-10 06:45:04'),
(45, 'standards', 'compliance_intro', 'Class Compliance & Protocols', 'Clear rules, predictable escalation steps, and transparent accountability keep every class experience dependable for families and educators.', 'Structured protocols help LearnWise protect classroom quality, communication standards, and punctual service delivery.', '', '', '', 3, 'active', '2026-05-10 02:26:29', 7, 'compliance_intro', 'Class Compliance & Protocols', 'Clear rules, predictable escalation steps, and transparent accountability keep every class experience dependable for families and educators.', 'Structured protocols help LearnWise protect classroom quality, communication standards, and punctual service delivery.', '', NULL, '2026-05-10 06:45:04'),
(46, 'standards', 'cta', 'Become a Professional LearnWise Educator', 'Join a teaching community built around clarity, care, and premium online learning standards.', '', '', 'Apply Now', 'contact.php', 4, 'active', '2026-05-10 02:26:29', 7, 'cta', 'Become a Professional LearnWise Educator', 'Join a teaching community built around clarity, care, and premium online learning standards.', '', '', NULL, '2026-05-10 06:45:04'),
(47, '', 'feature_grid', '', NULL, NULL, NULL, NULL, NULL, 2, 'inactive', '2026-05-10 06:45:04', 1, 'features', 'Everything families expect from a modern learning brand', 'Each card below can be edited inside the CMS by updating this single section.', 'Flexible Scheduling|Classes that match school, timezone, and family routines.|bi-calendar2-week\nProgress Visibility|Parents stay informed with clear updates and milestone tracking.|bi-graph-up-arrow\nQualified Educators|Expert tutors, strong communication, and learner-first methods.|bi-mortarboard\nGlobal Learning|Support for international boards, enrichment tracks, and future-ready skills.|bi-globe2', '', '{\"columns\":4}', '2026-06-09 11:49:13'),
(48, '', 'videos', '', NULL, NULL, NULL, NULL, NULL, 3, 'inactive', '2026-05-10 06:45:04', 1, 'studying_with_learnwise', 'Studying with LearnWise', 'Showcase real sample classes with thumbnails, descriptions, and either hosted videos or external links.', '', '', '{\"source\":\"sample_videos\"}', '2026-06-09 11:49:13'),
(49, '', 'feature_grid', '', NULL, NULL, NULL, NULL, NULL, 4, 'inactive', '2026-05-10 06:45:04', 1, 'why_parents_trust_us', 'Why parents trust us', 'Fast communication, meaningful progress, and classes that feel thoughtful rather than generic.', 'Responsive Support|Families get quick answers and real guidance, not ticket chaos.|bi-chat-heart\nClear Reporting|Progress updates help parents understand effort, outcomes, and next steps.|bi-journal-check\nChild-Centered Matching|We align students with tutors who fit both academic needs and learning style.|bi-stars', '', '{\"columns\":3}', '2026-06-09 11:49:13'),
(50, '', 'faq', '', NULL, NULL, NULL, NULL, NULL, 5, 'active', '2026-05-10 06:45:04', 1, 'faq', 'Frequently asked questions', 'This section can be disabled any time from the admin panel.', 'How are classes conducted?|Students join live online sessions with a teacher and receive follow-up support when needed.\nCan I request a demo first?|Yes. Families can book a free demo session before choosing a program.\nDo you support different subjects?|Yes. LearnWise can showcase academics, language learning, coding, and enrichment tracks.', '', '', '2026-05-10 06:45:04'),
(51, '', 'cta_banner', '', NULL, NULL, NULL, NULL, NULL, 6, 'active', '2026-05-10 06:45:04', 1, 'cta_banner', 'Ready to launch a smarter learning journey?', 'Talk to LearnWise and book a free demo session for your child.', 'Book Free Demo|#lead-form', '', '{\"button_style\":\"light\"}', '2026-05-10 06:45:04'),
(52, '', 'lead_form', '', NULL, NULL, NULL, NULL, NULL, 7, 'active', '2026-05-10 06:45:04', 1, 'lead_form', 'Book your free demo lecture', 'Use this form to collect leads directly from the homepage.', '100% Free Demo\nNo Credit Card Required\nParent-Friendly Support', '', '{\"source\":\"Home Page\"}', '2026-05-10 06:45:04'),
(53, '', 'hero', '', NULL, NULL, NULL, NULL, NULL, 1, 'active', '2026-05-10 06:45:04', 2, 'about_hero', 'LearnWise was built to make online learning feel more human', 'We combine premium presentation, smart operations, and warm parent communication in one scalable website experience.', 'Talk to Our Team|contact.php\nSee Courses|courses.php', 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=1200&q=80', '{\"badge\":\"About LearnWise\"}', '2026-05-10 06:45:04'),
(54, '', 'rich_text', '', NULL, NULL, NULL, NULL, NULL, 2, 'active', '2026-05-10 06:45:04', 2, 'about_story', 'Our story', 'A lightweight CMS should never feel lightweight to the families using it.', 'LearnWise was designed for education businesses that want complete control over their marketing website without the overhead of a bloated CMS.\n\nEvery page, section, menu item, and homepage block can be managed from the admin panel so your team can publish updates fast.', '', '', '2026-05-10 06:45:04'),
(55, '', 'feature_grid', '', NULL, NULL, NULL, NULL, NULL, 3, 'active', '2026-05-10 06:45:04', 2, 'about_values', 'What we value', 'Consistency, clarity, and learning experiences that families can genuinely trust.', 'Thoughtful Support|Every inquiry matters, and every family should feel guided.|bi-heart\nBeautiful Simplicity|Clean interfaces reduce friction for both admins and visitors.|bi-window\nScalable Content|New sections, new pages, and new campaigns should be easy to launch.|bi-layers', '', '{\"columns\":3}', '2026-05-10 06:45:04'),
(56, '', 'hero', '', NULL, NULL, NULL, NULL, NULL, 1, 'active', '2026-05-10 06:45:04', 3, 'courses_hero', 'Programs designed for modern learners', 'Your admin can manage the course list separately while still controlling the page layout through sections.', 'Enroll Now|contact.php', 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=1200&q=80', '{\"badge\":\"Courses\"}', '2026-05-10 06:45:04'),
(57, '', 'courses_grid', '', NULL, NULL, NULL, NULL, NULL, 2, 'active', '2026-05-10 06:45:04', 3, 'courses_grid', 'Explore course categories', 'Active courses below are loaded directly from the database.', '', '', '', '2026-05-10 06:45:04'),
(58, '', 'cta_banner', '', NULL, NULL, NULL, NULL, NULL, 3, 'active', '2026-05-10 06:45:04', 3, 'courses_cta', 'Need help choosing the right program?', 'We can recommend a learning path based on grade, subject, and goals.', 'Contact Sales|contact.php', '', '', '2026-05-10 06:45:04'),
(59, '', 'hero', '', NULL, NULL, NULL, NULL, NULL, 1, 'active', '2026-05-10 06:45:04', 4, 'teachers_hero', 'Meet the teachers behind the experience', 'Showcase your educators with profiles, experience, and student impact metrics.', 'Book a Demo|contact.php', 'https://images.unsplash.com/photo-1544717305-2782549b5136?auto=format&fit=crop&w=1200&q=80', '{\"badge\":\"Our Faculty\"}', '2026-05-10 06:45:04'),
(60, '', 'teachers_grid', '', NULL, NULL, NULL, NULL, NULL, 2, 'active', '2026-05-10 06:45:04', 4, 'teachers_grid', 'Our expert teachers', 'Only active teacher profiles are shown here.', '', '', '', '2026-05-10 06:45:04'),
(61, '', 'hero', '', NULL, NULL, NULL, NULL, NULL, 1, 'active', '2026-05-10 06:45:04', 5, 'faq_hero', 'Questions families usually ask first', 'The FAQ page is fully manageable through the sections system too.', 'Need More Help?|contact.php', '', '{\"badge\":\"FAQ\"}', '2026-05-10 06:45:04'),
(62, '', 'faq', '', NULL, NULL, NULL, NULL, NULL, 2, 'active', '2026-05-10 06:45:04', 5, 'faq_items', 'Frequently asked questions', '', 'How do demo classes work?|Families can request a free demo and meet the instructor before committing.\nCan videos be uploaded in the CMS?|Yes. Admins can upload thumbnails and MP4 files or use external URLs.\nCan sections be hidden without deleting them?|Yes. Set the section status to inactive and it will disappear from the frontend.', '', '', '2026-05-10 06:45:04'),
(63, '', 'hero', '', NULL, NULL, NULL, NULL, NULL, 1, 'active', '2026-05-10 06:45:04', 6, 'contact_hero', 'Talk to LearnWise', 'Use this page for demos, enrollment help, and partnership conversations.', 'WhatsApp|dynamic\nEmail Us|mailto:hello@learnwise.com', '', '{\"badge\":\"Contact\"}', '2026-05-10 06:45:04'),
(64, '', 'contact_form', '', NULL, NULL, NULL, NULL, NULL, 2, 'active', '2026-05-10 06:45:04', 6, 'contact_form', 'Send us your details', 'We will get back to you with the right next step.', '', '', '{\"source\":\"Contact Page\"}', '2026-05-10 06:45:04'),
(65, '', 'hero', '', NULL, NULL, NULL, NULL, NULL, 1, 'active', '2026-05-10 06:45:04', 7, 'standards_hero', 'Online Teaching Standards & Best Practices', 'Delivering high-quality learning experiences with professionalism, care, and impact.', 'A premium framework that helps LearnWise educators create trusted, engaging, and high-performing online classrooms.', 'https://images.unsplash.com/photo-1513258496099-48168024aec0?auto=format&fit=crop&w=900&q=80', '{\"badge\":\"Premium Teaching Framework\",\"button_text\":\"Join LearnWise\",\"button_link\":\"contact.php\"}', '2026-05-10 06:45:04'),
(66, '', 'feature_grid', '', NULL, NULL, NULL, NULL, NULL, 2, 'active', '2026-06-09 11:48:12', 1, 'why_learnwise', 'Why families choose LearnWise', 'A complete learning experience built around your child\'s goals, pace, and potential.', 'Personalized Learning|Custom lesson plans tailored to each student\'s strengths and growth areas.|bi-person-check\nQualified Teachers|Experienced educators vetted for subject expertise and classroom excellence.|bi-mortarboard\nGoogle Meet Classes|Secure, interactive live sessions with screen sharing and real-time engagement.|bi-camera-video\nProgress Tracking|Clear dashboards and milestone updates so families always know how learning is going.|bi-graph-up-arrow\nHomework Management|Structured assignments with timely feedback to reinforce every lesson.|bi-journal-text\nParent Reports|Regular performance summaries with actionable insights and next-step guidance.|bi-envelope-paper\nFlexible Scheduling|Classes that fit school routines, time zones, and family calendars.|bi-calendar2-week\nRecorded Sessions|Missed a class? Access recordings to review concepts anytime.|bi-play-circle', '', '{\"columns\":4,\"kicker\":\"Why LearnWise\"}', '2026-06-09 11:48:12'),
(67, '', 'courses_grid', '', NULL, NULL, NULL, NULL, NULL, 3, 'active', '2026-06-09 11:48:12', 1, 'course_categories', 'Explore our course categories', 'From core academics to creative skills and exam preparation — find the right path for your child.', '', '', '{\"kicker\":\"Programs\",\"cta_label\":\"Enroll Now\",\"cta_link\":\"enroll.php\"}', '2026-06-09 11:48:12'),
(68, '', 'trust', '', NULL, NULL, NULL, NULL, NULL, 4, 'active', '2026-06-09 11:48:12', 1, 'parent_trust_indicators', 'Trusted by families who value quality education', 'LearnWise combines experienced educators, transparent communication, and measurable outcomes.', '4.9/5|Average parent satisfaction|bi-star-fill\n2,500+|Students learning with us|bi-people-fill\n150+|Qualified teachers|bi-mortarboard-fill\n100%|Safe online environment|bi-shield-check', '', '{\"kicker\":\"Parent Trust\"}', '2026-06-09 11:48:12'),
(69, '', 'feature_grid', '', NULL, NULL, NULL, NULL, NULL, 5, 'active', '2026-06-09 11:48:12', 1, 'parent_trust', 'Why parents trust LearnWise', 'We partner with families through every step of the learning journey with clarity, care, and consistency.', 'Experienced Teachers|Every educator is selected for subject mastery, communication, and student rapport.|bi-award\nPersonalized Learning Plans|Programs adapt to each child\'s pace, goals, and learning style.|bi-sliders\nContinuous Progress Tracking|Regular assessments and milestone reviews keep learning on track.|bi-bar-chart-line\nTransparent Communication|Quick responses via WhatsApp, email, and scheduled parent check-ins.|bi-chat-dots\nPerformance Reports|Detailed reports highlight strengths, gaps, and recommended next steps.|bi-file-earmark-text\nSafe Online Environment|Secure Google Meet classrooms with professional teaching standards.|bi-shield-lock', '', '{\"columns\":3,\"kicker\":\"Parent Trust\",\"surface\":\"section-muted\"}', '2026-06-09 11:48:12'),
(70, '', 'feature_grid', '', NULL, NULL, NULL, NULL, NULL, 6, 'active', '2026-06-09 11:48:12', 1, 'studying_features', 'Studying with LearnWise', 'An engaging, structured learning experience designed to help students thrive online.', 'Interactive Classes|Live sessions with polls, discussions, and hands-on problem solving.|bi-lightning-charge\nHomework Assignments|Practice tasks with teacher feedback to reinforce every concept.|bi-journal-check\nPerformance Tracking|Students and parents can monitor progress against clear learning goals.|bi-speedometer2\nGoogle Meet Learning|High-quality video classes with screen sharing and collaborative tools.|bi-camera-video\nClass Recordings|Review sessions anytime to strengthen understanding.|bi-play-btn\nOne-on-One Attention|Small groups and individual focus when students need extra support.|bi-person-hearts', '', '{\"columns\":3,\"kicker\":\"Student Experience\"}', '2026-06-09 11:48:12'),
(71, '', 'videos', '', NULL, NULL, NULL, NULL, NULL, 7, 'active', '2026-06-09 11:48:12', 1, 'studying_videos', 'See learning in action', 'Preview sample classes managed directly from your CMS video library.', '', '', '{\"source\":\"sample_videos\",\"kicker\":\"Class Previews\"}', '2026-06-09 11:48:12'),
(72, '', 'teachers_grid', '', NULL, NULL, NULL, NULL, NULL, 9, 'active', '2026-06-09 11:48:12', 1, 'teachers_showcase', 'Meet our expert teachers', 'Passionate educators committed to helping every student succeed.', '', '', '{\"kicker\":\"Our Faculty\",\"surface\":\"section-muted\"}', '2026-06-09 11:48:12'),
(73, '', 'hero', '', NULL, NULL, NULL, NULL, NULL, 1, 'active', '2026-06-09 11:48:12', 8, 'enroll_hero', 'Enroll your child with confidence', 'Choose a program, book a free trial, or speak with our team to find the perfect learning path.', 'Book Free Trial|#enroll-form\nContact Us|contact.php', 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=1200&q=80', '{\"badge\":\"Enroll Now\"}', '2026-06-09 11:48:12'),
(74, '', 'lead_form', '', NULL, NULL, NULL, NULL, NULL, 2, 'active', '2026-06-09 11:48:12', 8, 'enroll-form', 'Complete your enrollment request', 'Share your details and our academic team will guide you through the next steps.', 'Free trial class included\nFlexible scheduling options\nDedicated academic advisor', '', '{\"source\":\"Enroll Page\"}', '2026-06-09 11:48:12'),
(75, '', 'hero', '', NULL, NULL, NULL, NULL, NULL, 1, 'active', '2026-06-09 11:48:12', 9, 'privacy_hero', 'Privacy Policy', 'How LearnWise collects, uses, and protects your family\'s information.', '', '', '{\"badge\":\"Legal\"}', '2026-06-09 11:48:12'),
(76, '', 'rich_text', '', NULL, NULL, NULL, NULL, NULL, 2, 'active', '2026-06-09 11:48:12', 9, 'privacy_content', 'Your privacy matters', '', 'LearnWise is committed to protecting the privacy of students, parents, and educators who use our platform.\n\nWe collect only the information necessary to deliver classes, communicate with families, and improve our services. This may include names, contact details, academic preferences, and class participation data.\n\nWe do not sell personal information to third parties. Data is stored securely and accessed only by authorized team members who need it to support your learning experience.\n\nClass sessions may be recorded for educational purposes with prior consent. Recordings are shared only with enrolled students and their parents or guardians.\n\nYou may request access to, correction of, or deletion of your personal data by contacting us at the email address listed on our Contact page.\n\nThis policy may be updated periodically. Continued use of LearnWise services constitutes acceptance of the current policy.', '', '', '2026-06-09 11:48:12'),
(77, '', 'hero', '', NULL, NULL, NULL, NULL, NULL, 1, 'active', '2026-06-09 11:48:12', 10, 'terms_hero', 'Terms & Conditions', 'Please read these terms carefully before using LearnWise services.', '', '', '{\"badge\":\"Legal\"}', '2026-06-09 11:48:12'),
(78, '', 'rich_text', '', NULL, NULL, NULL, NULL, NULL, 2, 'active', '2026-06-09 11:48:12', 10, 'terms_content', 'Terms of use', '', 'By accessing LearnWise classes, website, or enrollment services, you agree to these Terms & Conditions.\n\nEnrollment confirms acceptance of class schedules, fee structures, and communication policies shared during onboarding.\n\nStudents and parents agree to maintain respectful conduct during live sessions and follow teacher guidance for a productive learning environment.\n\nMissed classes, rescheduling, and refund policies are communicated at enrollment and may vary by program.\n\nLearnWise reserves the right to update programs, pricing, and platform features with reasonable notice to enrolled families.\n\nTeachers and staff are expected to follow LearnWise teaching standards and compliance protocols at all times.\n\nFor questions about these terms, please contact our support team through the Contact page.', '', '', '2026-06-09 11:48:12'),
(79, '', 'hero', '', NULL, NULL, NULL, NULL, NULL, 1, 'active', '2026-06-09 11:48:12', 11, 'teacher_reg_hero', 'Teach with LearnWise', 'Join a community of educators delivering premium online learning experiences.', 'View Standards|standards.php', 'https://images.unsplash.com/photo-1544717305-2782549b5136?auto=format&fit=crop&w=1200&q=80', '{\"badge\":\"Teacher Registration\"}', '2026-06-09 11:48:12'),
(80, '', 'lead_form', '', NULL, NULL, NULL, NULL, NULL, 2, 'active', '2026-06-09 11:48:12', 11, 'teacher_reg_form', 'Apply to become a LearnWise teacher', 'Share your experience and subject expertise. Our team will review your application and reach out.', 'Competitive compensation\nFlexible online teaching\nProfessional development support', '', '{\"source\":\"Teacher Registration\"}', '2026-06-09 11:48:12'),
(81, '', 'hero', '', NULL, NULL, NULL, NULL, NULL, 1, 'active', '2026-06-09 11:48:12', 12, 'student_reg_hero', 'Student registration', 'Register to access live classes, homework support, and progress tracking with LearnWise.', 'View Courses|courses.php', 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=1200&q=80', '{\"badge\":\"Student Registration\"}', '2026-06-09 11:48:12'),
(82, '', 'lead_form', '', NULL, NULL, NULL, NULL, NULL, 2, 'active', '2026-06-09 11:48:12', 12, 'student_reg_form', 'Create your student profile', 'A parent or guardian should complete this form to begin the registration process.', 'Free trial available\nAll major subjects covered\nParent progress reports included', '', '{\"source\":\"Student Registration\"}', '2026-06-09 11:48:12');

-- --------------------------------------------------------

--
-- Table structure for table `sample_videos`
--

DROP TABLE IF EXISTS `sample_videos`;
CREATE TABLE IF NOT EXISTS `sample_videos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `thumbnail` varchar(255) DEFAULT NULL,
  `video_file` varchar(255) DEFAULT NULL,
  `video_url` varchar(255) DEFAULT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `sample_videos`
--

INSERT INTO `sample_videos` (`id`, `title`, `description`, `thumbnail`, `video_file`, `video_url`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Live Class Walkthrough', 'A quick look at how a LearnWise sample class feels for students and parents.', 'https://img.youtube.com/vi/xcm0N9oQia4/hqdefault.jpg', '', 'https://www.youtube.com/watch?v=xcm0N9oQia4', 1, 'active', '2026-05-10 06:45:04', '2026-05-10 06:45:04'),
(2, 'Competition Session', 'A high-energy class moment designed to keep learners engaged.', 'https://img.youtube.com/vi/g-x-aBLFR3k/hqdefault.jpg', '', 'https://www.youtube.com/watch?v=g-x-aBLFR3k', 2, 'active', '2026-05-10 06:45:04', '2026-05-10 06:45:04'),
(3, 'Language Class Preview', 'A sample from one of our interactive language learning sessions.', 'https://img.youtube.com/vi/6TuiKjeQkBU/hqdefault.jpg', '', 'https://www.youtube.com/watch?v=6TuiKjeQkBU', 3, 'active', '2026-05-10 06:45:04', '2026-05-10 06:45:04');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `key_name` varchar(100) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key_name` (`key_name`)
) ENGINE=InnoDB AUTO_INCREMENT=560 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `key_name`, `value`) VALUES
(1, 'phone', '+91 98765 43210'),
(2, 'email', 'hello@learnwise.com'),
(3, 'address', '86 EdTech Lane, Mumbai, India'),
(4, 'site_logo', 'uploads/logo/logo.png'),
(163, 'whatsapp', '+91 98765 43210');

-- --------------------------------------------------------

--
-- Table structure for table `standards_sections`
--

DROP TABLE IF EXISTS `standards_sections`;
CREATE TABLE IF NOT EXISTS `standards_sections` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `sort_order` int DEFAULT '0',
  `status` enum('active','inactive') DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `standards_sections`
--

INSERT INTO `standards_sections` (`id`, `title`, `content`, `icon`, `sort_order`, `status`) VALUES
(1, 'Student & Parent Relationships', 'Build strong rapport through friendliness, respect, and confident, solution-oriented interactions.', 'bi-people', 1, 'active'),
(2, 'Lesson Delivery', 'Be well prepared, use simple examples, and adapt teaching pace to suit every learner.', 'bi-journal-richtext', 2, 'active'),
(3, 'Student Engagement', 'Encourage participation through active listening, age-appropriate interaction, and flexible teaching methods.', 'bi-lightning-charge', 3, 'active'),
(4, 'Professional Discipline', 'Maintain punctuality, manage time effectively, and follow structured protocols for attendance and delays.', 'bi-shield-check', 4, 'active'),
(5, 'Communication Protocols', 'Stay responsive and transparent with students, parents, and the LearnWise administration team.', 'bi-chat-dots', 5, 'active'),
(6, 'Accountability', 'Track lessons, maintain accurate records, and follow up on student progress consistently.', 'bi-clipboard-data', 6, 'active'),
(7, 'Background', 'Maintain a clean, distraction-free LearnWise background with proper lighting and visibility.', '🧩', 7, 'active'),
(8, 'Punctuality', 'Join classes before the scheduled start time and be fully prepared when students arrive.', 'bi-clock-history', 8, 'active'),
(9, 'Avoid No-Show', 'Inform in advance in case of absence. No-shows affect performance evaluation.', '🚫', 9, 'active'),
(10, 'Notice Period', 'Follow notice guidelines when changing availability or concluding teaching assignments.', 'bi-calendar-event', 10, 'active'),
(11, 'Dress Code', 'Maintain a professional appearance appropriate for live online teaching with students and parents.', 'bi-person-badge', 11, 'active'),
(12, 'Background Standards', 'Use a clean, professional, and distraction-free teaching environment for every session.', 'bi-camera-video', 7, 'active'),
(13, 'Avoid No-Shows', 'Inform administration in advance if you cannot attend a scheduled class.', 'bi-exclamation-triangle', 9, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

DROP TABLE IF EXISTS `teachers`;
CREATE TABLE IF NOT EXISTS `teachers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `experience` varchar(50) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `experience_years` int NOT NULL DEFAULT '0',
  `students_count` int NOT NULL DEFAULT '0',
  `bio` text,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `qualifications` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`id`, `name`, `subject`, `experience`, `image`, `status`, `created_at`, `experience_years`, `students_count`, `bio`, `updated_at`, `qualifications`) VALUES
(1, 'Ananya Singh', 'Mathematics', '8 years', '', 'active', '2026-05-02 09:44:28', 0, 0, NULL, '2026-05-10 06:45:04', NULL),
(2, 'Rahul Verma', 'Coding', '6 years', '', 'active', '2026-05-02 09:44:28', 0, 0, NULL, '2026-05-10 06:45:04', NULL),
(3, 'Priya Joshi', 'Science', '7 years', '', 'active', '2026-05-02 09:44:28', 0, 0, NULL, '2026-05-10 06:45:04', NULL),
(4, 'Vikram Kumar', 'Languages', '5 years', '', 'active', '2026-05-02 09:44:28', 0, 0, NULL, '2026-05-10 06:45:04', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `website_settings`
--

DROP TABLE IF EXISTS `website_settings`;
CREATE TABLE IF NOT EXISTS `website_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(150) NOT NULL,
  `setting_value` longtext,
  `setting_type` varchar(50) NOT NULL DEFAULT 'text',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=1209 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `website_settings`
--

INSERT INTO `website_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `updated_at`) VALUES
(1, 'site_phone', '+91 79962 05203', 'text', '2026-06-09 12:52:55'),
(2, 'site_email', 'narmadha@edulearnwise.com', 'text', '2026-06-09 12:52:55'),
(3, 'address', '86 EdTech Lane, Mumbai, India', 'textarea', '2026-06-09 12:52:55'),
(4, 'whatsapp_number', '+91 79962 05203', 'text', '2026-06-09 12:52:55'),
(5, 'logo', 'uploads/logo/logo.png', 'image', '2026-06-09 12:30:13'),
(10, 'footer_text', 'LearnWise helps families access premium online classes, clear progress tracking, and trusted educators in one beautifully simple experience.', 'textarea', '2026-06-09 12:52:55'),
(12, 'social_links', '[{\"label\":\"Instagram\",\"url\":\"https://instagram.com\"},{\"label\":\"LinkedIn\",\"url\":\"https://linkedin.com\"},{\"label\":\"YouTube\",\"url\":\"https://youtube.com\"}]', 'json', '2026-06-09 12:52:55'),
(13, 'site_name', 'LearnWise', 'text', '2026-06-09 12:52:55'),
(14, 'site_tagline', 'A flexible learning website powered by your own CMS.', 'text', '2026-06-09 12:52:55'),
(470, 'site_logo', 'uploads/logo/logo.png', 'image', '2026-06-09 12:30:13'),
(846, 'favicon', '', 'image', '2026-06-09 11:48:12'),
(847, 'nav_cta_text', 'Book Free Trial', 'text', '2026-06-09 12:52:55'),
(848, 'nav_cta_link', '#lead-form', 'text', '2026-06-09 12:52:55'),
(849, 'footer_legal_privacy', 'privacy.php', 'text', '2026-06-09 12:52:55'),
(850, 'footer_legal_terms', 'terms.php', 'text', '2026-06-09 12:52:55'),
(1158, 'legacy_settings_migrated', '1', 'text', '2026-06-09 12:30:13'),
(1159, 'admin_notification_email', 'narmadha@edulearnwise.com', 'text', '2026-06-09 12:52:55'),
(1160, 'smtp_host', 'smtp.gmail.com', 'text', '2026-06-09 12:52:55'),
(1161, 'smtp_port', '587', 'text', '2026-06-09 12:52:55'),
(1162, 'smtp_username', 'narmadha@edulearnwise.com', 'text', '2026-06-09 12:52:55'),
(1163, 'smtp_password', 'kxrkvprysryfwzse', 'text', '2026-06-09 12:52:55'),
(1164, 'smtp_encryption', 'tls', 'text', '2026-06-09 12:52:55'),
(1165, 'smtp_from_email', 'narmadha@edulearnwise.com', 'text', '2026-06-09 12:52:55'),
(1166, 'smtp_from_name', 'LearnWise Website', 'text', '2026-06-09 12:52:55');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
