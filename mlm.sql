-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 20, 2025 at 01:47 PM
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
-- Database: `mlm`
--

-- --------------------------------------------------------

--
-- Table structure for table `badges`
--

CREATE TABLE `badges` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `level_required` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `badges`
--

INSERT INTO `badges` (`id`, `name`, `description`, `image`, `level_required`, `created_at`) VALUES
(1, 'Starter', 'Welcome to our team!', 'badge1.png', 0, '2025-04-19 15:49:49'),
(2, 'Bronze', 'Achieved Level 1', 'badge2.png', 1, '2025-04-19 15:49:49'),
(3, 'Silver', 'Achieved Level 2', 'badge3.png', 2, '2025-04-19 15:49:49'),
(4, 'Gold', 'Achieved Level 3', 'badge4.png', 3, '2025-04-19 15:49:49'),
(5, 'Platinum', 'Achieved Level 4', 'badge5.png', 4, '2025-04-19 15:49:49'),
(6, 'Diamond', 'Achieved Level 5', 'badge6.png', 5, '2025-04-19 15:49:49');

-- --------------------------------------------------------

--
-- Table structure for table `commissions`
--

CREATE TABLE `commissions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `level` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `percentage` decimal(5,2) NOT NULL,
  `from_member` int(11) NOT NULL,
  `order_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','paid') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `paid_at` timestamp NULL DEFAULT NULL,
  `payout_item_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `commission_levels`
--

CREATE TABLE `commission_levels` (
  `id` int(11) NOT NULL,
  `level` int(11) NOT NULL,
  `percentage` decimal(5,2) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `commission_levels`
--

INSERT INTO `commission_levels` (`id`, `level`, `percentage`, `description`, `created_at`, `updated_at`) VALUES
(1, 1, 10.00, 'Direct referrals', '2025-04-19 15:49:49', '2025-04-19 15:49:49'),
(2, 2, 5.00, 'Second level team', '2025-04-19 15:49:49', '2025-04-19 15:49:49'),
(3, 3, 3.00, 'Third level team', '2025-04-19 15:49:49', '2025-04-19 15:49:49'),
(4, 4, 2.00, 'Fourth level team', '2025-04-19 15:49:49', '2025-04-19 15:49:49'),
(5, 5, 1.00, 'Fifth level team', '2025-04-19 15:49:49', '2025-04-19 15:49:49');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `shipping_address` text DEFAULT NULL,
  `billing_address` text DEFAULT NULL,
  `payment_status` enum('pending','completed','failed','refunded') DEFAULT 'pending',
  `shipping_method_id` int(11) NOT NULL,
  `payment_method` enum('razorpay','bank_transfer','cod') DEFAULT 'razorpay',
  `payment_id` varchar(255) DEFAULT NULL,
  `tracking_number` varchar(100) DEFAULT NULL,
  `shipping_status` enum('pending','processing','shipped','delivered','returned') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `product_id`, `quantity`, `total_amount`, `shipping_address`, `billing_address`, `payment_status`, `shipping_method_id`, `payment_method`, `payment_id`, `tracking_number`, `shipping_status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 1, 799.00, 'Dhaura Tanda\r\nDarzi Chowk', 'Dhaura Tanda\r\nDarzi Chowk', 'pending', 0, 'razorpay', 'ORD-680439C14507D', 'MLM-0983JKJDG', 'returned', NULL, '2025-04-20 00:03:13', '2025-04-20 09:53:38'),
(2, 2, 1, 1, 799.00, 'Dhaura Tanda\r\nDarzi Chowk', 'Dhaura Tanda\r\nDarzi Chowk', 'failed', 0, 'razorpay', 'ORD-68043A500FE03', NULL, 'pending', NULL, '2025-04-20 00:05:36', '2025-04-20 00:38:37'),
(3, 2, 1, 1, 799.00, 'Dhaura Tanda\r\nDarzi Chowk', 'Dhaura Tanda\r\nDarzi Chowk', 'pending', 0, 'razorpay', 'ORD-680442B555863', NULL, 'pending', NULL, '2025-04-20 00:41:25', '2025-04-20 00:41:25'),
(4, 2, 1, 1, 799.00, 'Dhaura Tanda\r\nDarzi Chowk', 'Dhaura Tanda\r\nDarzi Chowk', 'pending', 3, 'razorpay', 'RP_68044deb01484', NULL, 'shipped', '', '2025-04-20 01:29:14', '2025-04-20 09:48:37'),
(5, 2, 1, 1, 849.00, 'Dhaura Tanda\r\nDarzi Chowk', 'Dhaura Tanda\r\nDarzi Chowk', 'pending', 1, 'cod', NULL, NULL, 'pending', '', '2025-04-20 01:37:37', '2025-04-20 01:37:37'),
(6, 2, 1, 1, 799.00, 'Dhaura Tanda\r\nDarzi Chowk', 'Dhaura Tanda\r\nDarzi Chowk', 'pending', 3, 'cod', NULL, NULL, 'pending', '', '2025-04-20 01:41:44', '2025-04-20 01:41:44');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(10) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_status_history`
--

CREATE TABLE `order_status_history` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `status_type` enum('payment','shipping') DEFAULT NULL,
  `old_status` varchar(50) DEFAULT NULL,
  `new_status` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `changed_by` int(11) DEFAULT NULL COMMENT 'User ID who changed the status',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_status_history`
--

INSERT INTO `order_status_history` (`id`, `order_id`, `status_type`, `old_status`, `new_status`, `notes`, `changed_by`, `created_at`) VALUES
(1, 2, 'payment', 'pending', 'failed', 'Not Done', 1, '2025-04-20 00:38:37'),
(2, 1, 'shipping', 'pending', 'shipped', 'Tracking number: MLM-0983JKJDG', 1, '2025-04-20 00:39:16'),
(5, 4, 'shipping', 'pending', 'returned', '', 2, '2025-04-20 09:45:32'),
(6, 4, 'shipping', 'returned', 'delivered', '', 2, '2025-04-20 09:48:25'),
(7, 4, 'shipping', 'delivered', 'shipped', '', 2, '2025-04-20 09:48:37'),
(8, 1, 'shipping', 'shipped', 'returned', '', 2, '2025-04-20 09:53:38');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `amount` int(10) NOT NULL,
  `status` varchar(10) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payouts`
--

CREATE TABLE `payouts` (
  `id` int(11) NOT NULL,
  `period` enum('weekly','monthly','custom') NOT NULL,
  `from_date` date DEFAULT NULL,
  `to_date` date DEFAULT NULL,
  `min_amount` decimal(10,2) NOT NULL,
  `distributor_count` int(11) NOT NULL DEFAULT 0,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','processing','completed','failed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `processed_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payout_items`
--

CREATE TABLE `payout_items` (
  `id` int(11) NOT NULL,
  `payout_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `commission_count` int(11) NOT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `account_number` varchar(50) DEFAULT NULL,
  `ifsc_code` varchar(20) DEFAULT NULL,
  `status` enum('pending','paid','failed') DEFAULT 'pending',
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `stock` int(11) NOT NULL,
  `rating` decimal(2,1) DEFAULT 0.0,
  `review_count` int(11) DEFAULT 0,
  `meta_title` varchar(100) DEFAULT NULL,
  `meta_description` varchar(160) DEFAULT NULL,
  `meta_keywords` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `image`, `stock`, `rating`, `review_count`, `meta_title`, `meta_description`, `meta_keywords`, `created_at`, `updated_at`) VALUES
(1, 'Premium Health Supplement', 'Our flagship health product with all-natural ingredients for optimal wellness.', 799.00, '61zrojhfnTL._AC_UF1000_1000_QL80_-removebg-preview.png', 100, 0.0, 0, NULL, NULL, NULL, '2025-04-19 15:49:49', '2025-04-19 17:50:13');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `created_at`, `updated_at`) VALUES
(1, 'site_name', 'HealthPlus MLM', '2025-04-19 15:49:49', '2025-04-19 15:49:49'),
(2, 'site_email', 'info@healthplusmlm.com', '2025-04-19 15:49:49', '2025-04-19 15:49:49'),
(3, 'razorpay_key_id', 'your_razorpay_key_id', '2025-04-19 15:49:49', '2025-04-19 15:49:49'),
(4, 'razorpay_key_secret', 'your_razorpay_key_secret', '2025-04-19 15:49:49', '2025-04-19 15:49:49'),
(5, 'min_payout_amount', '600.00', '2025-04-19 15:49:49', '2025-04-19 17:12:41'),
(6, 'last_cron_run', NULL, '2025-04-19 15:49:49', '2025-04-19 15:49:49');

-- --------------------------------------------------------

--
-- Table structure for table `shipping_methods`
--

CREATE TABLE `shipping_methods` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `cost` decimal(10,2) NOT NULL,
  `estimated_days` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shipping_methods`
--

INSERT INTO `shipping_methods` (`id`, `name`, `description`, `cost`, `estimated_days`, `is_active`, `created_at`) VALUES
(1, 'Standard Shipping', 'Delivery within 5-7 business days', 50.00, '5-7 days', 1, '2025-04-19 22:48:01'),
(2, 'Express Shipping', 'Delivery within 2-3 business days', 120.00, '2-3 days', 1, '2025-04-19 22:48:01'),
(3, 'Free Shipping', 'Free delivery within 10-14 business days', 0.00, '10-14 days', 1, '2025-04-19 22:48:01');

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `rating` int(11) NOT NULL,
  `comment` text NOT NULL,
  `approved` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `training_modules`
--

CREATE TABLE `training_modules` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `training_modules`
--

INSERT INTO `training_modules` (`id`, `title`, `description`, `sort_order`, `created_at`) VALUES
(1, 'Getting Started', 'Learn the basics of our MLM system and how to get started', 1, '2025-04-19 16:09:52'),
(2, 'Product Knowledge', 'Become an expert on our health product', 2, '2025-04-19 16:09:52'),
(3, 'Sales Techniques', 'Learn proven methods to sell our product', 3, '2025-04-19 16:09:52'),
(4, 'Team Building', 'How to recruit and lead your team', 4, '2025-04-19 16:09:52');

-- --------------------------------------------------------

--
-- Table structure for table `training_videos`
--

CREATE TABLE `training_videos` (
  `id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `video_url` varchar(255) NOT NULL,
  `duration` int(11) NOT NULL COMMENT 'Duration in seconds',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `training_videos`
--

INSERT INTO `training_videos` (`id`, `module_id`, `title`, `description`, `video_url`, `duration`, `sort_order`, `created_at`) VALUES
(1, 1, 'Welcome to Our Team', 'Introduction to the company and opportunity', 'https://youtu.be/1', 322, 1, '2025-04-19 16:09:52'),
(2, 1, 'Understanding the Compensation Plan', 'How you earn money in our system', 'https://youtu.be/2', 765, 2, '2025-04-19 16:09:52'),
(3, 1, 'Your First 30 Days', 'Action plan for new distributors', 'https://youtu.be/3', 516, 3, '2025-04-19 16:09:52'),
(4, 2, 'Product Ingredients and Benefits', 'Detailed product breakdown', 'https://youtu.be/4', 910, 1, '2025-04-19 16:09:52'),
(5, 2, 'How to Demonstrate the Product', 'Effective product demonstration techniques', 'https://youtu.be/5', 625, 2, '2025-04-19 16:09:52'),
(6, 2, 'Answering Common Questions', 'How to handle customer questions', 'https://youtu.be/6', 465, 3, '2025-04-19 16:09:52'),
(7, 3, 'The Power of Storytelling', 'Using stories to sell effectively', 'https://youtu.be/7', 555, 1, '2025-04-19 16:09:52'),
(8, 3, 'Overcoming Objections', 'How to handle customer objections', 'https://youtu.be/8', 690, 2, '2025-04-19 16:09:52'),
(9, 3, 'Closing the Sale', 'Techniques to finalize the sale', 'https://youtu.be/9', 410, 3, '2025-04-19 16:09:52'),
(10, 4, 'Finding Potential Team Members', 'Where to find new team members', 'https://youtu.be/10', 860, 1, '2025-04-19 16:09:52'),
(11, 4, 'Effective Team Communication', 'How to communicate with your team', 'https://youtu.be/11', 525, 2, '2025-04-19 16:09:52'),
(12, 4, 'Motivating Your Team', 'Keeping your team engaged and motivated', 'https://youtu.be/12', 435, 3, '2025-04-19 16:09:52');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `sponsor_id` int(11) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `position` enum('left','right') DEFAULT NULL,
  `level` int(11) DEFAULT 0,
  `active` tinyint(1) DEFAULT 1,
  `is_admin` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `bank_name` varchar(100) DEFAULT NULL,
  `account_number` varchar(50) DEFAULT NULL,
  `ifsc_code` varchar(20) DEFAULT NULL,
  `pan_number` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `full_name`, `phone`, `address`, `image`, `sponsor_id`, `parent_id`, `position`, `level`, `active`, `is_admin`, `created_at`, `updated_at`, `bank_name`, `account_number`, `ifsc_code`, `pan_number`) VALUES
(1, 'admin', '$2y$10$r6hd.eXrHqFW1PTyGob5YOkPs8VoEu7EPH6IWfI.dxWA9nMNfRWhC', 'admin@example.com', 'Admin User', NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, 1, '2025-04-19 15:49:50', '2025-04-19 16:52:11', NULL, NULL, NULL, NULL),
(2, 'moin', '$2y$10$xoR0G9AfQO6G3muXEal6HeRH3jzJYZ6m.uhnUHX8l01ZPfa6tslEG', 'mohdmoin@gmail.com', 'Mohd Moin', '9090989890', 'Dhaura Tanda\r\nDarzi Chowk', NULL, 1, NULL, NULL, 0, 1, 0, '2025-04-19 19:00:56', '2025-04-19 19:00:56', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_badges`
--

CREATE TABLE `user_badges` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `badge_id` int(11) NOT NULL,
  `earned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_badges`
--

INSERT INTO `user_badges` (`id`, `user_id`, `badge_id`, `earned_at`) VALUES
(1, 2, 1, '2025-04-19 19:00:56');

-- --------------------------------------------------------

--
-- Table structure for table `user_training`
--

CREATE TABLE `user_training` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `video_id` int(11) NOT NULL,
  `completed` tinyint(1) DEFAULT 0,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `badges`
--
ALTER TABLE `badges`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `commissions`
--
ALTER TABLE `commissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `from_member` (`from_member`),
  ADD KEY `payout_item_id` (`payout_item_id`);

--
-- Indexes for table `commission_levels`
--
ALTER TABLE `commission_levels`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `level` (`level`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `changed_by` (`changed_by`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `token` (`token`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payouts`
--
ALTER TABLE `payouts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payout_items`
--
ALTER TABLE `payout_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payout_id` (`payout_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `shipping_methods`
--
ALTER TABLE `shipping_methods`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `training_modules`
--
ALTER TABLE `training_modules`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `training_videos`
--
ALTER TABLE `training_videos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `module_id` (`module_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `sponsor_id` (`sponsor_id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `user_badges`
--
ALTER TABLE `user_badges`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `badge_id` (`badge_id`);

--
-- Indexes for table `user_training`
--
ALTER TABLE `user_training`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`video_id`),
  ADD KEY `video_id` (`video_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `badges`
--
ALTER TABLE `badges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `commissions`
--
ALTER TABLE `commissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `commission_levels`
--
ALTER TABLE `commission_levels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_status_history`
--
ALTER TABLE `order_status_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payouts`
--
ALTER TABLE `payouts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payout_items`
--
ALTER TABLE `payout_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `shipping_methods`
--
ALTER TABLE `shipping_methods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `training_modules`
--
ALTER TABLE `training_modules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `training_videos`
--
ALTER TABLE `training_videos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_badges`
--
ALTER TABLE `user_badges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_training`
--
ALTER TABLE `user_training`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `commissions`
--
ALTER TABLE `commissions`
  ADD CONSTRAINT `commissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `commissions_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `commissions_ibfk_3` FOREIGN KEY (`from_member`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `commissions_ibfk_4` FOREIGN KEY (`payout_item_id`) REFERENCES `payout_items` (`id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD CONSTRAINT `order_status_history_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_status_history_ibfk_2` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `payout_items`
--
ALTER TABLE `payout_items`
  ADD CONSTRAINT `payout_items_ibfk_1` FOREIGN KEY (`payout_id`) REFERENCES `payouts` (`id`),
  ADD CONSTRAINT `payout_items_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `training_videos`
--
ALTER TABLE `training_videos`
  ADD CONSTRAINT `training_videos_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `training_modules` (`id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`sponsor_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `user_badges`
--
ALTER TABLE `user_badges`
  ADD CONSTRAINT `user_badges_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `user_badges_ibfk_2` FOREIGN KEY (`badge_id`) REFERENCES `badges` (`id`);

--
-- Constraints for table `user_training`
--
ALTER TABLE `user_training`
  ADD CONSTRAINT `user_training_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `user_training_ibfk_2` FOREIGN KEY (`video_id`) REFERENCES `training_videos` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
