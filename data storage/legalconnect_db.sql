-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 14, 2025 at 09:04 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `legalconnect_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_front` varchar(255) NOT NULL,
  `id_back` varchar(255) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `consulting` varchar(255) NOT NULL,
  `selected_date` date DEFAULT NULL,
  `selected_time` varchar(255) NOT NULL,
  `term_status` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `appointment_approval` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `id_front`, `id_back`, `fullname`, `address`, `phone`, `consulting`, `selected_date`, `selected_time`, `term_status`, `created_at`, `updated_at`, `appointment_approval`) VALUES
(1, '', '', 'gary fernan', 'Gayong Cordon Isabela', '996725326182', 'tax', NULL, '', '', '2025-06-27 12:18:50', '2025-06-27 12:18:50', '0'),
(2, '', '', 'Ichad', 'Aguinaldo Cordon, Isabela', '986278512', 'legal', NULL, '', '', '2025-06-27 17:56:16', '2025-06-27 17:56:16', '0'),
(3, '', '', 'Ichad', 'Aguinaldo Cordon, Isabela', '986278512', 'tax', NULL, '', '', '2025-06-27 21:31:30', '2025-06-27 21:31:30', '0'),
(4, '', '', 'Avenido, Arthur Benio', '3312 Sagat, Cordon, Isabela', '09916156687', 'business', NULL, '', '', '2025-06-30 19:40:13', '2025-06-30 19:40:13', '0'),
(5, '', '', 'Avenido, Arthur Benio', '3312 Sagat, Cordon, Isabela', '09916156687', 'business', NULL, '', '', '2025-06-30 21:02:49', '2025-06-30 21:02:49', '0'),
(6, '', '', 'Julie Anne Relatorres', '3312 Sagat, Cordon, Isabela', '09916156687', 'tax', NULL, '', '', '2025-06-30 21:31:58', '2025-06-30 21:31:58', '0'),
(7, '', '', 'Hermoso Hannah Ayala', '3312 GAYONG, CORDON, ISABELA', '09916156687', 'tax', NULL, '', '', '2025-06-30 21:33:13', '2025-06-30 21:33:13', '0'),
(8, 'ids/3hnIL44iSqtSR4N4w75JeHYyhakPIX7ViUyy8uMh.png', 'ids/kcJS1guS0xcOJdcs1ga17J0aXd8p6wYP9CKJnDyS.png', 'hana valdez', 'gayong cordon isabela', '098976785765', 'tax', '2025-05-28', '9-10 am', 'approved', '2025-07-08 16:47:55', '2025-07-08 16:47:55', 'pending'),
(9, 'ids/JUpLf8xVqT3lZJkL7vBNC2NmhpLzA5bLOTvycvvS.png', 'ids/4BsQMdIqfkB9LOz8JKhCc4llNZmvud0r0AkELCY0.png', 'hana valdez', 'gayong cordon isabela', '098976785765', 'tax', '2025-05-28', '9-10 am', 'approved', '2025-07-08 17:02:21', '2025-07-08 17:02:21', 'pending'),
(10, 'ids/ReaFdq94vFnxwbxfxHrNZYjfRwZmQaC3fErX0MBI.png', 'ids/FNtWK6uZpPV4Xfm6tHVR7vaMjB3nXW6Wx1N4Ipah.png', 'christian paul', 'gayong cordon isabela', '098967875765', 'legal', '2025-05-28', '11-12 pm', 'approved', '2025-07-08 17:04:48', '2025-07-08 17:04:48', 'pending'),
(11, 'ids/zh7g67CQ3U1qX7rxv8aTo40uaOJVXdxJdY2VXv2x.jpg', 'ids/CNcz5Ek2luiPbDE3ZyzJFdco6TJfu5WFFBCCF9Ln.jpg', 'christian videz', 'gayong cordon isabela', '09099879867', 'legal', '2025-05-28', '10-11 am', 'approved', '2025-07-08 17:23:11', '2025-07-08 17:23:11', 'pending'),
(12, 'ids/fN67GYHAi8kM4NNePjbObRrW75Uluj4a08BuvFRh.jpg', 'ids/3fXM6qiWut0WkNP8xKmdYTsl6O6RMxhFErsJKL4b.png', 'richard villegas', 'guribang diffun quirino', '0909909898787', 'business', '2025-07-01', '7-8 am', 'approved', '2025-07-08 17:36:56', '2025-07-08 17:36:56', 'pending'),
(13, 'ids/l62X2XJdNckl4Gw6X1fYLan7TVpSewSVUEdXN7jJ.jpg', 'ids/HYZjVc4nwtcge9KODSwnJxXdUbtqCQf8vd1OmZJO.png', 'richard cruz', 'guribang diffun quirino', '09099098987675', 'tax', '2025-07-01', '8-9 am', 'approved', '2025-07-08 17:47:14', '2025-07-08 17:47:14', 'pending'),
(14, 'ids/MBkMBhgRDbn8GUNv82IkM9vJWl2rO3mb9szwbYsk.png', 'ids/570P4FVQR9DBVA99mewPkLQj4GbdeAt3Oz8dSnA8.png', 'jerome a. cafirma', 'aguinaldo cordon isabela', '098786565', 'business', '2025-07-01', '10-11 am', 'approved', '2025-07-09 01:05:03', '2025-07-09 01:05:03', 'pending'),
(15, 'ids/bJgVhQMfjvDAVhwiKw6NhDhn0dGMwNhMT208JLED.png', 'ids/IoLxDD5PBV46J5n8lvhEOOTowUorO6ZabTW8mByU.png', 'jerome a. cafirma', 'guribang diffun quirino', '098976785765', 'tax', '2025-07-01', '11-12 pm', 'approved', '2025-07-13 23:02:29', '2025-07-13 23:02:29', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `appointment_slots`
--

CREATE TABLE `appointment_slots` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `time` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `booked` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `appointment_slots`
--

INSERT INTO `appointment_slots` (`id`, `date`, `time`, `created_at`, `updated_at`, `booked`) VALUES
(3, '2025-07-25', '7-8 pm', '2025-06-30 21:35:45', '2025-07-01 00:27:30', 1),
(4, '2025-06-02', '1-2 pm', '2025-06-30 21:36:51', '2025-07-03 17:16:44', 1),
(5, '2025-07-29', '1-2 pm', '2025-07-01 00:07:52', '2025-07-01 00:23:16', 1),
(6, '2025-05-28', '7-8 am', '2025-07-06 14:36:54', '2025-07-06 21:48:47', 1),
(7, '2025-05-28', '8-9 am', '2025-07-06 14:36:54', '2025-07-08 13:46:12', 1),
(8, '2025-05-28', '9-10 am', '2025-07-06 14:36:54', '2025-07-08 16:52:57', 1),
(9, '2025-05-28', '10-11 am', '2025-07-06 14:36:54', '2025-07-08 17:22:03', 1),
(10, '2025-05-28', '11-12 pm', '2025-07-06 14:36:54', '2025-07-08 17:03:48', 1),
(11, '2025-07-01', '7-8 am', '2025-07-08 17:28:58', '2025-07-08 17:32:24', 1),
(12, '2025-07-01', '8-9 am', '2025-07-08 17:28:58', '2025-07-08 17:46:55', 1),
(13, '2025-07-01', '9-10 am', '2025-07-08 17:28:58', '2025-07-08 17:28:58', 0),
(14, '2025-07-01', '10-11 am', '2025-07-08 17:28:58', '2025-07-09 01:04:41', 1),
(15, '2025-07-01', '11-12 pm', '2025-07-08 17:28:58', '2025-07-13 23:02:03', 1),
(16, '2025-07-01', '12-1 pm', '2025-07-08 17:28:58', '2025-07-08 17:28:58', 0);

-- --------------------------------------------------------

--
-- Table structure for table `available_times`
--

CREATE TABLE `available_times` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `time` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `client_tbl`
--

CREATE TABLE `client_tbl` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `client_tbl`
--

INSERT INTO `client_tbl` (`id`, `name`, `email`, `password`, `created_at`, `updated_at`) VALUES
(1, 'sampleuser', 'sampleuser@gmail.com', '$2y$12$zyJw4H0MaSVM1/Wf95J9OeG2wXng1b.G/JS2xOOiwk3RgxWJbp0dK', '2025-06-27 17:55:40', '2025-06-27 17:55:40'),
(2, 'Jerome A. Cafirma', 'jeromecafirma@email.com', '$2y$12$R581XL89/07PJUoXmZ4Z5up/kU2aWIhqQ.XhD0ogI.5w4z/AK56Ja', '2025-07-09 01:02:29', '2025-07-09 01:02:29'),
(3, 'Jenico A. Baldonado', 'jenico@gmail.com', '$2y$12$wG1Dw8vCkabznBqbEUJG/.5xVrQsnTii5AorVB6XpOe..rfDzwLSe', '2025-07-09 22:43:51', '2025-07-09 22:43:51');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `company` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(16, '0001_01_01_000000_create_users_table', 1),
(17, '0001_01_01_000001_create_cache_table', 1),
(18, '0001_01_01_000002_create_jobs_table', 1),
(19, '2025_05_19_103645_create_reviews_table', 1),
(20, '2025_05_19_111741_create_messages_table', 1),
(21, '2025_05_19_124821_create_appointments_table', 1),
(22, '2025_05_21_025536_create_available_times_table', 1),
(23, '2025_05_21_041206_create_appointment_slots_table', 1),
(24, '2025_05_21_051811_change_time_column_type_in_appointment_slots_table', 1),
(25, '2025_06_10_051722_create_client_tbl_table', 1),
(26, '2025_06_11_061421_add_role_to_users_table', 1),
(27, '2025_06_11_064331_add_role_to_users_table', 1),
(28, '2025_06_27_072429_create_termslogtbl_table', 1),
(29, '2025_07_01_055251_add_booked_to_appointment_slots', 2);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `review` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('0qMUIuyrcD6SvgZbZPFVwwRLNFPHuYx0HXnsufzy', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 'YTo4OntzOjY6Il90b2tlbiI7czo0MDoieTFLeE9mdnF6Z2JOWmIwOXpiWDhrVGlEWDRaN2lielpnSW5KanlMNCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6ODoiZnVsbG5hbWUiO3M6MTE6ImhhbmEgdmFsZGV6IjtzOjc6ImFkZHJlc3MiO3M6MjE6ImdheW9uZyBjb3Jkb24gaXNhYmVsYSI7czo1OiJwaG9uZSI7czoxMjoiMDk4OTc2Nzg1NzY1IjtzOjEwOiJjb25zdWx0aW5nIjtzOjM6InRheCI7czoxNToic3RhdHVzX2FwcHJvdmFsIjtzOjg6ImFwcHJvdmVkIjt9', 1752191544),
('a7VzL0MZaaWfGpzVbWjtdVVXosvM3NV5hGWLQqcP', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 'YTo4OntzOjY6Il90b2tlbiI7czo0MDoiUnlkQ1IxaHkyVlJRM0dIOXdrNmRLMG8zZ2lMSVpDSzJabU5RNHRFUiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjk6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC93ZWxjb21lIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo4OiJmdWxsbmFtZSI7czoxNzoiamVyb21lIGEuIGNhZmlybWEiO3M6NzoiYWRkcmVzcyI7czoyMzoiZ3VyaWJhbmcgZGlmZnVuIHF1aXJpbm8iO3M6NToicGhvbmUiO3M6MTI6IjA5ODk3Njc4NTc2NSI7czoxMDoiY29uc3VsdGluZyI7czozOiJ0YXgiO3M6MTU6InN0YXR1c19hcHByb3ZhbCI7czo4OiJhcHByb3ZlZCI7fQ==', 1752476584),
('AImVWlGoEzNRSGwP3bX9jpC7esLCsvbbZcmE764S', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiU0F5aU1nVnlCcWZJRnBXRndLWU9jaHhLQ21QWkJBcFVINFVXUkxxeiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjk6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC93ZWxjb21lIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1752132509),
('gvXhiUINAl9ifSiY8qSaRf8zRvEcZeNoGqdqY5ex', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiWlJJMGNVR0JacFdWZVlFSkluaUVKQXZBUGhtU3I0OEp6QTlCRVZ2VSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzY6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbmRhc2hib2FyZCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==', 1752296404);

-- --------------------------------------------------------

--
-- Table structure for table `termslogtbl`
--

CREATE TABLE `termslogtbl` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `termslogtbl`
--

INSERT INTO `termslogtbl` (`id`, `name`, `status`, `created_at`, `updated_at`) VALUES
(1, 'sampleuser', 'approved', '2025-06-27 17:56:07', '2025-06-27 17:56:07'),
(2, 'sampleuser', 'approved', '2025-06-27 21:31:22', '2025-06-27 21:31:22'),
(3, 'sampleuser', 'approved', '2025-06-30 19:40:03', '2025-06-30 19:40:03'),
(4, 'sampleuser', 'approved', '2025-06-30 23:38:40', '2025-06-30 23:38:40'),
(5, 'sampleuser', 'approved', '2025-07-01 00:05:11', '2025-07-01 00:05:11'),
(6, 'sampleuser', 'approved', '2025-07-01 00:08:43', '2025-07-01 00:08:43'),
(7, 'sampleuser', 'approved', '2025-07-03 17:15:42', '2025-07-03 17:15:42'),
(8, 'sampleuser', 'approved', '2025-07-04 14:31:31', '2025-07-04 14:31:31'),
(9, 'sampleuser', 'approved', '2025-07-06 14:25:05', '2025-07-06 14:25:05'),
(10, 'sampleuser', 'approved', '2025-07-06 14:37:42', '2025-07-06 14:37:42'),
(11, 'sampleuser', 'approved', '2025-07-06 16:23:36', '2025-07-06 16:23:36'),
(12, 'sampleuser', 'approved', '2025-07-06 21:27:44', '2025-07-06 21:27:44'),
(13, 'sampleuser', 'approved', '2025-07-08 13:34:54', '2025-07-08 13:34:54'),
(14, 'sampleuser', 'approved', '2025-07-08 17:03:13', '2025-07-08 17:03:13'),
(15, 'sampleuser', 'approved', '2025-07-08 17:21:35', '2025-07-08 17:21:35'),
(16, 'sampleuser', 'approved', '2025-07-08 17:31:23', '2025-07-08 17:31:23'),
(17, 'sampleuser', 'approved', '2025-07-08 17:46:23', '2025-07-08 17:46:23'),
(18, 'Jerome A. Cafirma', 'approved', '2025-07-09 01:03:55', '2025-07-09 01:03:55'),
(19, 'sampleuser', 'approved', '2025-07-09 22:56:41', '2025-07-09 22:56:41'),
(20, 'Jenico A. Baldonado', 'approved', '2025-07-10 15:50:44', '2025-07-10 15:50:44'),
(21, 'Jenico A. Baldonado', 'approved', '2025-07-13 23:01:39', '2025-07-13 23:01:39');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'client'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `role`) VALUES
(1, 'admin', 'admin', 'admin@gmail.com', NULL, '$2y$12$dwR.WnD3paseKDWnmkXciezEhSLzlD6PNCvdbW846MWuNQbmnwvPG', NULL, '2025-06-27 17:42:30', '2025-06-27 17:42:30', 'client'),
(2, 'superadmin', 'superadmin', 'superadmin@gmail.com', NULL, '$2y$12$goj1vijINPG4uRPkMDstoejxr.TyLB0APgwPlpAG0.bgKPNnFu0v2', NULL, '2025-06-27 17:49:43', '2025-06-27 17:49:43', 'client'),
(3, 'sampleuser', 'sampleuser', 'sampleuser@gmail.com', NULL, '$2y$12$7Mf4EObn7MUNfJq8NthG4Ox9Q9LVxBrvI51rs1aWeQybnaNwroKui', NULL, '2025-06-27 17:51:56', '2025-06-27 17:51:56', 'client');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `appointment_slots`
--
ALTER TABLE `appointment_slots`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `available_times`
--
ALTER TABLE `available_times`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `client_tbl`
--
ALTER TABLE `client_tbl`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `client_tbl_email_unique` (`email`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `termslogtbl`
--
ALTER TABLE `termslogtbl`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `appointment_slots`
--
ALTER TABLE `appointment_slots`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `available_times`
--
ALTER TABLE `available_times`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `client_tbl`
--
ALTER TABLE `client_tbl`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `termslogtbl`
--
ALTER TABLE `termslogtbl`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
