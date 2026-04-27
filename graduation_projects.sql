-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 18, 2026 at 11:32 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `graduation_projects`
--

CREATE DATABASE IF NOT EXISTS graduation_projects;
USE graduation_projects;

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

-- تسجيل دخول الإدارة الافتراضي: اسم المستخدم admin — كلمة المرور ١٢٣٤ (أرقام عربية يونيكود)
INSERT INTO `admins` (`id`, `username`, `password`) VALUES
(1, 'admin', '$2y$12$A0WscPn4FxKtYh380dY.IOcARz0fr4DV0U8a4U6/DWjuu/JK1pt7K');

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL,
  `sender_name` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_pic` varchar(255) DEFAULT 'default_avatar.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `department` varchar(100) NOT NULL,
  `grad_year` int(11) NOT NULL,
  `tech_stack` varchar(255) NOT NULL,
  `owner_linkedin` varchar(512) DEFAULT NULL,
  `project_poster` varchar(255) DEFAULT NULL,
  `project_poster_pdf` varchar(255) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT 'default.jpg',
  `pdf_file` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- بيانات تجريبية: مشاريع تخرج مع صور (روابط picsum.photos — seed مختلف لكل id)
--

INSERT INTO `projects` (`id`, `title`, `description`, `department`, `grad_year`, `tech_stack`, `owner_linkedin`, `project_poster`, `project_poster_pdf`, `image_url`, `pdf_file`) VALUES
(1, 'منصة إدارة مشاريع التخرج الجامعية', 'منصة ويب عربية لعرض ومشاركة مشاريع التخرج مع بحث متقدم وتصفية حسب القسم وسنة التخرج ورفع ملفات توضيحية.', 'علوم الحاسب والمعلومات', 2026, 'PHP, MySQL, HTML, CSS, JavaScript', NULL, NULL, NULL, 'https://picsum.photos/seed/gradproj1/800/450', NULL),
(2, 'تطبيق إرشاد أكاديمي بالذكاء الاصطناعي', 'واجهة تساعد الطالب على تلخيص المقررات واقتراح خطة مراجعة حسب الجدول الدراسي مع تذكير بالمواعيد.', 'نظم المعلومات', 2025, 'Python, FastAPI, PostgreSQL, REST', NULL, NULL, NULL, 'https://picsum.photos/seed/gradproj2/800/450', NULL),
(3, 'نظام مراقبة استهلاك الطاقة في المختبرات', 'لوحة تحكم لقراءة أجهزة استشعار وعرض الاستهلاك اليومي وتنبيهات عند تجاوز العتبة.', 'هندسة الحاسب', 2025, 'C++, MQTT, Node.js, Chart.js', NULL, NULL, NULL, 'https://picsum.photos/seed/gradproj3/800/450', NULL),
(4, 'منصة تعلم تفاعلية للبرمجة للمبتدئين', 'دروس قصيرة تمارين فورية وتتبع تقدم المتعلم مع شهادات إتمام بسيطة.', 'علوم الحاسب والمعلومات', 2024, 'React, Firebase, TypeScript', NULL, NULL, NULL, 'https://picsum.photos/seed/gradproj4/800/450', NULL),
(5, 'نظام حجز المواعيد للإرشاد الأكاديمي', 'يسمح للطالب بحجز موعد مع المرشد الأكاديمي وإدارة الجدول من لوحة المرشد.', 'نظم المعلومات', 2026, 'PHP, MySQL, FullCalendar', NULL, NULL, NULL, 'https://picsum.photos/seed/gradproj5/800/450', NULL),
(6, 'تطبيق مكتبة رقمية للمقررات', 'رفع ملخصات وملفات PDF مع تصنيف حسب المقرر والبحث النصي داخل العناوين.', 'علوم الحاسب والمعلومات', 2025, 'Laravel, MySQL, Vue.js', NULL, NULL, NULL, 'https://picsum.photos/seed/gradproj6/800/450', NULL),
(7, 'موقع تعريفي لقسم علوم الحاسب', 'صفحات عن الرؤية والتخصصات وروابط للمشاريع المميزة ونموذج تواصل.', 'علوم الحاسب والمعلومات', 2024, 'HTML, CSS, JavaScript', NULL, NULL, NULL, 'https://picsum.photos/seed/gradproj7/800/450', NULL),
(8, 'نظام إدارة فعاليات الجامعة', 'تسجيل الحضور، الجداول الزمنية، وإشعارات للمسجلين قبل الفعالية.', 'نظم المعلومات', 2026, 'PHP, MySQL, Bootstrap', NULL, NULL, NULL, 'https://picsum.photos/seed/gradproj8/800/450', NULL),
(9, 'تطبيق تتبع عادات الدراسة', 'مؤقت بومودورو وإحصائيات أسبوعية وتذكيرات لطيفة لزيادة التركيز.', 'هندسة البرمجيات', 2025, 'Flutter, Dart, SQLite', NULL, NULL, NULL, 'https://picsum.photos/seed/gradproj9/800/450', NULL),
(10, 'بوابة تقديم طلبات مشاريع التخرج', 'نموذج إلكتروني لرفع الفكرة والمشرف مع حالات الموافقة من الإدارة.', 'علوم الحاسب والمعلومات', 2026, 'PHP, MySQL, Alpine.js', NULL, NULL, NULL, 'https://picsum.photos/seed/gradproj10/800/450', NULL),
(11, 'نظام إدارة مخزون مختبر الحاسب', 'تسجيل الأجهزة والإعارات والصيانة مع تقارير جرد شهرية.', 'هندسة الحاسب', 2024, 'PHP, MySQL', NULL, NULL, NULL, 'https://picsum.photos/seed/gradproj11/800/450', NULL),
(12, 'منصة نقاش جماعي لمقرر مشروع التخرج', 'منتدى بسيط للمجموعات مع مرفقات وإشعارات عند رد المشرف.', 'هندسة البرمجيات', 2025, 'PHP, MySQL, JavaScript', NULL, NULL, NULL, 'https://picsum.photos/seed/gradproj12/800/450', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `bio` text DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_pic` varchar(255) DEFAULT 'default_avatar.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
