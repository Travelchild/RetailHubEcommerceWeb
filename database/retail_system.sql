-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 30, 2026 at 02:16 PM
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
-- Database: `retail_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `product_id`, `quantity`, `created_at`, `updated_at`) VALUES
(2, 5, 1, 2, '2026-03-25 06:49:12', '2026-03-25 07:04:59'),
(3, 5, 2, 1, '2026-03-25 06:49:25', '2026-03-25 06:49:25'),
(5, 5, 4, 1, '2026-03-25 07:05:11', '2026-03-25 07:05:11'),
(26, 6, 15, 1, '2026-03-29 16:03:09', '2026-03-29 16:03:09'),
(28, 6, 16, 1, '2026-03-30 02:13:19', '2026-03-30 02:13:19');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `parent_id` int(10) UNSIGNED DEFAULT NULL,
  `sort_order` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `parent_id`, `sort_order`, `is_active`, `created_at`) VALUES
(1, 'Electronics', NULL, 0, 1, '2026-03-29 11:27:13'),
(80, 'Phones', 1, 0, 1, '2026-03-29 11:27:13'),
(81, 'Laptops', 1, 0, 1, '2026-03-29 11:27:13'),
(90, 'Kitchen', 1, 0, 1, '2026-03-29 11:27:13'),
(91, 'Arts & Crafts', NULL, 0, 1, '2026-03-29 12:05:30'),
(92, 'Beading & Jewelry Making', 91, 0, 1, '2026-03-29 12:13:31'),
(93, 'Fashion', NULL, 0, 1, '2026-03-29 18:34:26'),
(94, 'Skirt', 93, 0, 1, '2026-03-29 18:34:39'),
(95, 'Shoue', 93, 0, 1, '2026-03-29 19:38:31'),
(96, 'Furniture', NULL, 0, 1, '2026-03-29 19:39:34'),
(97, 'Door', 96, 0, 1, '2026-03-29 19:39:44'),
(98, 'Chair', 96, 0, 1, '2026-03-29 19:39:53');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `shipping_address` text DEFAULT NULL,
  `payment_method` varchar(80) DEFAULT NULL,
  `payment_gateway` varchar(50) NOT NULL DEFAULT 'cod',
  `payment_status` varchar(40) NOT NULL DEFAULT 'Pending',
  `payment_transaction_id` varchar(120) DEFAULT NULL,
  `order_status` enum('Pending','Processing','Shipped','Delivered') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `shipping_address`, `payment_method`, `payment_gateway`, `payment_status`, `payment_transaction_id`, `order_status`, `created_at`, `updated_at`) VALUES
(2, 1, 50000.00, 'Colombo', 'Visa / Mastercard ·••• 7457', 'card', 'Paid', 'TXN-93B0A9987B', 'Processing', '2026-03-25 04:30:39', '2026-03-25 04:30:39'),
(3, 5, 50000.00, '717/F/12 Polhna Road, Kohalwila, Kelaniya', 'Cash on delivery', 'cod', 'Pending (COD)', NULL, 'Processing', '2026-03-25 06:28:14', '2026-03-29 08:33:08'),
(4, 6, 508500.00, 'kelaniya', 'Cash on delivery', 'cod', 'Pending (COD)', NULL, 'Processing', '2026-03-26 06:37:05', '2026-03-27 06:41:47'),
(5, 6, 508500.00, 'kelaniya', 'Visa / Mastercard ·••• 1111', 'card', 'Paid', 'TXN-6B5C41CAE3', 'Delivered', '2026-03-27 06:06:38', '2026-03-27 07:00:27'),
(6, 1, 159000.00, 'Colombo', 'Cash on delivery', 'cod', 'Pending (COD)', NULL, 'Pending', '2026-03-29 13:37:18', '2026-03-29 13:37:18'),
(7, 1, 4000.00, 'Colombo', 'Cash on delivery', 'cod', 'Pending (COD)', NULL, 'Pending', '2026-03-29 14:02:30', '2026-03-29 14:02:30'),
(8, 6, 1013500.00, 'kelaniya', 'Cash on delivery', 'cod', 'Pending (COD)', NULL, 'Pending', '2026-03-29 14:03:06', '2026-03-29 14:03:06'),
(9, 6, 3000.00, 'kelaniya', 'Cash on delivery', 'cod', 'Pending (COD)', NULL, 'Pending', '2026-03-29 14:06:23', '2026-03-29 14:06:23');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `unit_price`, `subtotal`) VALUES
(2, 2, 1, 1, 50000.00, 50000.00),
(3, 3, 1, 1, 50000.00, 50000.00),
(4, 4, 1, 1, 50000.00, 50000.00),
(5, 4, 2, 1, 450000.00, 450000.00),
(6, 4, 3, 1, 8500.00, 8500.00),
(7, 5, 1, 1, 50000.00, 50000.00),
(8, 5, 2, 1, 450000.00, 450000.00),
(9, 5, 3, 1, 8500.00, 8500.00),
(10, 6, 1, 3, 50000.00, 150000.00),
(11, 6, 5, 5, 1000.00, 5000.00),
(12, 6, 6, 2, 2000.00, 4000.00),
(13, 7, 6, 2, 2000.00, 4000.00),
(14, 8, 1, 2, 50000.00, 100000.00),
(15, 8, 2, 2, 450000.00, 900000.00),
(16, 8, 3, 1, 8500.00, 8500.00),
(17, 8, 5, 1, 1000.00, 1000.00),
(18, 8, 6, 2, 2000.00, 4000.00),
(19, 9, 5, 1, 1000.00, 1000.00),
(20, 9, 6, 1, 2000.00, 2000.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(10) UNSIGNED DEFAULT NULL,
  `sub_category_id` int(10) UNSIGNED DEFAULT NULL,
  `name` varchar(180) NOT NULL,
  `brand` varchar(120) DEFAULT NULL,
  `sku` varchar(80) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock_qty` int(11) NOT NULL DEFAULT 0,
  `image_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `image_url_2` varchar(500) DEFAULT NULL,
  `image_url_3` varchar(500) DEFAULT NULL,
  `image_url_4` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `sub_category_id`, `name`, `brand`, `sku`, `description`, `price`, `stock_qty`, `image_url`, `is_active`, `created_at`, `updated_at`, `image_url_2`, `image_url_3`, `image_url_4`) VALUES
(1, 1, NULL, 'Iphone 17 Pro', 'Apple', 'NT-SMX-001', '6.7 inch display, 128GB storage', 50000.00, 27, 'uploads/Apple-iPhone-17-Pro-1774411951-b400d6.jpg', 0, '2026-03-24 07:49:13', '2026-03-29 14:10:12', NULL, NULL, NULL),
(2, 1, NULL, 'Macbook Pro', 'Apple', 'NT-LTP-014', '14 inch business laptop', 450000.00, 14, 'uploads/111339_sp818-mbp13touch-space-select-202005-1774412037-678b03.png', 0, '2026-03-24 07:49:13', '2026-03-29 14:10:15', NULL, NULL, NULL),
(3, 2, NULL, 'Running Shoes', 'Stride', 'SD-RUN-220', 'Lightweight and comfortable', 8500.00, 40, 'uploads/jd_product_list-1774412106-5873b9.webp', 0, '2026-03-24 07:49:13', '2026-03-29 14:10:18', NULL, NULL, NULL),
(4, NULL, NULL, 'Blender Max', 'HomeMix', 'HM-BLD-300', '3-speed kitchen blender', 60000.00, 18, 'uploads/HNMB900-1774412164-44df9d.jpg', 0, '2026-03-24 07:49:13', '2026-03-29 08:14:40', NULL, NULL, NULL),
(5, NULL, NULL, 'frock', 'Aura', '12', 'test', 1000.00, 4, 'uploads/images-1774757308-ba3c39.jpg', 0, '2026-03-29 04:08:28', '2026-03-29 14:10:10', NULL, NULL, NULL),
(6, 93, NULL, 'Skirt', 'Aura', '11', 'test', 2000.00, 5, 'uploads/TW15939-1774789711-0f28da.webp', 1, '2026-03-29 13:08:31', '2026-03-30 01:59:36', '', '', ''),
(7, 80, NULL, 'Samsung Galaxy S26 Ultra', 'Samsung', 'ph12', 'SHOP NOW: Get an Amazon Gift Card when you order Samsung Galaxy S26 Ultra. Gift card included with purchase. You will receive an email once your gift card is available. Offer ends 4/5.\r\nPRIVACY DISPLAY: Automatically hide your screen from those beside you. The built-in privacy display can be preset¹ to turn on when receiving notifications, typing passwords, or using specific apps\r\nTYPE IT IN. TRANSFORM IT FAST: Enhance any shot in seconds on your smartphone by using Photo Assist² with Galaxy AI.³ Add objects, restore details, or apply new styles by simply typing or tapping\r\nNIGHTS, CAPTURED CLEARLY: From gigs to city lights, record and capture moments after dark with clarity using Nightography so your photos and videos stay crisp and clear on your Samsung Galaxy\r\nMAKE IT. EDIT IT. SHARE IT: Turn everyday moments into something personal with creative tools built right into your mobile phone, whether it’s a special contact photo, custom wallpaper, an invitation or more⁴\r\nHELP THAT KEEPS UP: Stay in the moment while Now Nudge with Galaxy AI helps you respond faster and stay organized with smart suggestions⁵ that appear exactly when you need them on your phone\r\nFIT EVERYONE IN THE SHOT: Group selfies are easier on your Samsung phone with a wider front camera⁶ that captures more of the scene, so no one gets left out of the moment', 466484.00, 10, 'uploads/61UnzIc-97L-_AC_SX679_-1774793866-32df08.jpg', 1, '2026-03-29 14:17:46', '2026-03-29 14:17:46', NULL, NULL, NULL),
(8, 81, NULL, 'HP 14 Laptop', 'HP', 'lap14', 'READY FOR ANYWHERE – With its thin and light design, 6.5 mm micro-edge bezel display, and 79% screen-to-body ratio, you’ll take this PC anywhere while you see and do more of what you love (1)\r\nMORE SCREEN, MORE FUN – With virtually no bezel encircling the screen, you’ll enjoy every bit of detail on this 14-inch HD (1366 x 768) display (2)\r\nALL-DAY PERFORMANCE – Tackle your busiest days with the dual-core, Intel Celeron N4020—the perfect processor for performance, power consumption, and value (3)\r\n4K READY – Smoothly stream 4K content and play your favorite next-gen games with Intel UHD Graphics 600 (4) (5)\r\nSTORAGE AND MEMORY – An embedded multimedia card provides reliable flash-based, 64 GB of storage while 4 GB of RAM expands your bandwidth and boosts your performance (6)', 57914.00, 10, 'uploads/815uX7wkOZS-_AC_SY300_SX300_QL70_FMwebp_-1774793973-8b5856.webp', 1, '2026-03-29 14:19:33', '2026-03-29 14:19:33', NULL, NULL, NULL),
(9, 90, NULL, 'Ninja Kitchen System', 'Ninja', '452', 'ALL-IN-ONE KITCHEN SYSTEM: Blend, chop, crush and more in this powerhouse blender and food processor combo.\r\nPOWERFUL MOTOR:1500-watt motor base to crush ice, blend frozen fruit, and power through tough ingredients with the 2-horsepower motor, designed for professional results.\r\nBLENDING TECHNOLOGY: The XL 72-oz.* pitcher, ideal for large batches and entertaining, features Total Crushing Blades that crush ice to snow in seconds and powers through tough ingredients. *64-oz. max liquid capacity.\r\nFOOD PROCESSING: 8-cup food processing bowl delivers consistent, even vegetable chopping, pureeing, and mixing. Includes a dedicated chopping blade, plus dough blade, capable of mixing up to 2 pounds of dough in just 30 seconds.\r\nON-THE-GO CONVENIENCE: Includes two 16-oz. Nutri Ninja Cups with To-Go Lids, perfect for personalized shakes and smoothies.\r\nVERSATILE FUNCTIONS: Choose from 4 functions – Blend, Mix, Crush, and Single-Serve – that best fits your recipe and meal prep needs.\r\nNINJA BLADES SYSTEM: 4 Unique blades optimized for every task. From Total Crushing Blades for XL Blending to Pro Extractor Blades for on-the-go and chopping and dough blades for the processor bowl.', 47209.00, 7, 'uploads/81ME5sqz5TL-_AC_SY300_SX300_QL70_FMwebp_-1774794178-a56619.webp', 1, '2026-03-29 14:22:58', '2026-03-29 14:22:58', NULL, NULL, NULL),
(10, 92, NULL, '1mm Stretchy Bracelet String', 'LUSTEMBER', '52', 'WIDELY APPLICATIONS : Elastic string for bracelets , beading, DIY crafting, drawstring bags, bracelets, pony bead jewelry, hair ties and party hat straps.\r\nEXCELLENT ELASTICITY : Elastic string is very stretchy, In the range of allowable tensile elastic deformation, can easily restore the original length without deformation.\r\nEASY TO WORK WITH : The Elastic cord is easy to cut and tie, flexible and relatively easy to knot while you are working with it. Don\'t need to make many knots to secure it.\r\nPREMIUM MATERIAL : Elastic Thread made from nylon polyester fabric material and braided rubber; Durable, sturdy, and long-lasting elasticity; Soft, flexible, and easy to cut and tie into secure knots.\r\nRAIBOW COLOR: 164 ft / 50 m colorful elastic cord with a variety of beads or necklaces will be more beautiful and unique.', 2200.00, 7, 'uploads/61kexNVS1CS-_AC_SY300_SX300_QL70_FMwebp_-1774794638-7d2860.webp', 1, '2026-03-29 14:30:38', '2026-03-29 14:30:38', NULL, NULL, NULL),
(11, 97, NULL, '1 Set Peel and Stick Door Moulding Trim Kit Premade Wall Moulding Panels Flexible Self Adhesive 3D Wainscoting Panels for Mirror Window Frame Decor', 'Colingmill', '4520', 'Door Molding Kit: you will receive 1 sets of pre-cut peel and stick wainscoting panels, a total of 10 pieces, which can be easily matched and connected to give a sophisticated look to a door or wall, meeting your various decoration needs\r\nInterior Decor: this wainscotting door panel kit will add depth and style to any room; You can try different spacing and amounts, even mix and match our different sizes to add variety to your space and breathe a new life\r\nSelf-adhesive Applications: the door molding kits come with built-in adhesive backing that makes them easy to use without the need for additional adhesives or tools; They can also be effortlessly affixed with silicone quick-drying glue or wall studs, giving you the flexibility to match your DIY style\r\nSave Your Time: our DIY door paneling is pre-cut to decorate your doors or walls without cutting or aligning; These wall and door molding kits can be painted with any interior paint to beautify your space and ensure a quick and effective makeover of your decor\r\nLightweight and Reliable: these wall panels for interior door are made of polystyrene material, lightweight but reliable; Besides, they retain a wood-like texture, beautiful and practical, not easy to deform, keeping your space looking elegant for long', 8500.00, 0, 'uploads/61npBbSCw-L-_AC_SY300_SX300_QL70_FMwebp_-1774794784-edf30b.webp', 1, '2026-03-29 14:33:04', '2026-03-29 14:33:04', NULL, NULL, NULL),
(13, 94, NULL, 'Tendenza Pleated Midi Skirt', 'Tendenza', 'TW15939', 'Skirt\r\nPleated\r\nMidi\r\nCasual Wear\r\nFabric : Woven\r\nFabric Composition : 100% Polyester\r\nModel Height 5\' 6\", wearing size 28\" Please bear in mind that the photo may be slightly different from the actual item in terms of colour due to lighting conditions or the display used to view   Wash And Care : Hand wash with cold water, Wash inside out dark colors separately, Dry in a shade', 1590.00, 78, 'uploads/TW15939-1774795096-4705c9.webp', 1, '2026-03-29 14:38:16', '2026-03-29 14:38:16', NULL, NULL, NULL),
(14, 95, NULL, 'Elegant Essence Strap Heel', 'Thilakawardana', 'TA16817', 'Feel It the trend setter presents a gorgeous collection of ladies footwear. Stylish and comfortable are the words that define the collection perfectly. From the house of Feel It presenting a classy range of footwear\'s that compliments your modern style statement. Wedges with super comfortable sole that keeps your feet happy, Style with your dress to complete your look.', 2500.00, 9, 'uploads/TA16817-1774795178-9c242f.webp', 1, '2026-03-29 14:39:38', '2026-03-29 14:39:38', NULL, NULL, NULL),
(15, 95, NULL, 'Elegant Essence Strap Heel', 'Thilakawardana', '8654', 'Feel It the trend setter presents a gorgeous collection of ladies footwear. Stylish and comfortable are the words that define the collection perfectly. From the house of Feel It presenting a classy range of footwear\'s that compliments your modern style statement. Wedges with super comfortable sole that keeps your feet happy, Style with your dress to complete your look.', 2000.00, 7, 'uploads/TA16816-1774798100-28a810.webp', 1, '2026-03-29 15:28:20', '2026-03-29 15:28:20', NULL, NULL, NULL),
(16, 93, NULL, 'Tendenza Printed Three Quarter Sleeve Dress', 'Tendenza', '6564', 'Dress\r\n3/4 Sleeve\r\nCasual Wear\r\nMaterial : Cotton\r\nMaterial Composition : 100% Cotton\r\nModel Height 5\' 6\" wearing size M Please bear in mind that the photo may be slightly different from the actual item in terms of colour due to lighting conditions or the display used to view', 2000.00, 20, 'uploads/TW15946-1774834327-013e2f.webp', 1, '2026-03-30 01:32:07', '2026-03-30 01:32:07', 'uploads/TW15946-2-1774834327-1f056c.webp', 'uploads/TW15946-3-1774834327-02baa1.webp', 'uploads/TW15946--1-1774834327-570ba7.webp');

-- --------------------------------------------------------

--
-- Table structure for table `promotions`
--

CREATE TABLE `promotions` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(120) NOT NULL,
  `tag_label` varchar(60) NOT NULL,
  `tag_icon` varchar(80) NOT NULL DEFAULT 'fa-solid fa-bolt',
  `emoji` varchar(8) NOT NULL DEFAULT '?️',
  `discount_text` varchar(80) NOT NULL,
  `btn_label` varchar(60) NOT NULL DEFAULT 'Shop Now',
  `btn_icon` varchar(80) NOT NULL DEFAULT 'fa-solid fa-bag-shopping',
  `link_url` varchar(255) NOT NULL DEFAULT '#',
  `slide_theme` varchar(30) NOT NULL DEFAULT 'theme-1',
  `sort_order` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `starts_at` datetime DEFAULT NULL,
  `ends_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `promotions`
--

INSERT INTO `promotions` (`id`, `title`, `tag_label`, `tag_icon`, `emoji`, `discount_text`, `btn_label`, `btn_icon`, `link_url`, `slide_theme`, `sort_order`, `is_active`, `starts_at`, `ends_at`, `created_at`, `updated_at`) VALUES
(1, 'Electronics Up to 40% Off', 'Flash Sale', 'fa-solid fa-bolt', '📱', 'Up to 40% Off', 'Shop Now', 'fa-solid fa-bag-shopping', 'index.php?page=products&category=electronics', 'theme-1', 1, 1, NULL, NULL, '2026-03-26 16:33:29', '2026-03-26 16:33:29'),
(2, 'Fashion & Style Drop 30% Off', 'New Arrivals', 'fa-solid fa-star', '👗', '30% Off', 'Explore', 'fa-solid fa-shirt', 'index.php?page=products&category=fashion', 'theme-2', 2, 0, NULL, NULL, '2026-03-26 16:33:29', '2026-03-26 17:23:23'),
(3, 'Home & Living 25% Off', 'Collection', 'fa-solid fa-house', '🪴', '25% Off', 'Browse', 'fa-solid fa-couch', 'index.php?page=products&category=home', 'theme-3', 3, 1, NULL, NULL, '2026-03-26 16:33:29', '2026-03-26 16:33:29'),
(4, 'test', 'test', 'fa-solid fa-star', '🛍️', 'test', 'Shop Now', 'fa-solid fa-bolt', 'http://localhost/RetailHub/public/index.php?page=products&category=home', 'theme-2', 1, 1, '2026-03-27 20:19:00', '2026-04-03 08:19:00', '2026-03-26 17:17:05', '2026-03-29 07:36:39');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL,
  `review_text` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `user_id`, `product_id`, `rating`, `review_text`, `created_at`) VALUES
(1, 3, 1, 5, 'Excellent phone for the price.', '2026-03-24 07:49:13'),
(2, 3, 3, 4, 'Very comfortable for daily use.', '2026-03-24 07:49:13');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `role_name` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `role_name`, `created_at`) VALUES
(1, 'Admin', '2026-03-24 07:49:13'),
(2, 'Customer', '2026-03-24 07:49:13'),
(3, 'Support', '2026-03-24 07:49:13');

-- --------------------------------------------------------

--
-- Stand-in structure for view `sales_summary`
-- (See below for the actual view)
--
CREATE TABLE `sales_summary` (
`order_date` date
,`total_orders` bigint(21)
,`revenue` decimal(32,2)
);

-- --------------------------------------------------------

--
-- Table structure for table `support_tickets`
--

CREATE TABLE `support_tickets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `ticket_type` enum('Inquiry','Complaint','Return') DEFAULT 'Inquiry',
  `description` text NOT NULL,
  `status` enum('Open','In Progress','Resolved','Closed') DEFAULT 'Open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `support_tickets`
--

INSERT INTO `support_tickets` (`id`, `user_id`, `subject`, `ticket_type`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 3, 'Late delivery inquiry', 'Inquiry', 'My order has not arrived yet.', 'Resolved', '2026-03-24 07:49:13', '2026-03-25 04:20:36'),
(2, 1, 'test', 'Complaint', 'test', 'Resolved', '2026-03-25 04:20:17', '2026-03-25 04:20:28'),
(3, 5, 'test', 'Complaint', 'test test', 'Open', '2026-03-25 15:56:28', '2026-03-25 15:56:28'),
(4, 5, 'test', 'Complaint', 'test test', 'Open', '2026-03-25 16:23:34', '2026-03-25 16:23:34'),
(5, 5, 'test', 'Complaint', 'test test', 'Open', '2026-03-25 16:23:47', '2026-03-25 16:23:47'),
(6, 6, 'test', 'Inquiry', 'ttt', 'Resolved', '2026-03-26 06:38:23', '2026-03-27 12:47:21'),
(7, 6, 'test', 'Inquiry', 'test', 'In Progress', '2026-03-29 03:22:57', '2026-03-29 03:25:30'),
(8, 7, 'test', 'Inquiry', 'test', 'Open', '2026-03-29 03:25:39', '2026-03-29 03:25:39');

-- --------------------------------------------------------

--
-- Table structure for table `support_ticket_replies`
--

CREATE TABLE `support_ticket_replies` (
  `id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `responder_id` int(11) NOT NULL,
  `reply_text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `support_ticket_replies`
--

INSERT INTO `support_ticket_replies` (`id`, `ticket_id`, `responder_id`, `reply_text`, `created_at`) VALUES
(1, 6, 7, 'testing', '2026-03-27 12:47:21'),
(2, 7, 7, 'testing', '2026-03-29 03:25:30');

-- --------------------------------------------------------

--
-- Stand-in structure for view `top_selling_products`
-- (See below for the actual view)
--
CREATE TABLE `top_selling_products` (
`id` int(11)
,`name` varchar(180)
,`units_sold` decimal(32,0)
,`sales_amount` decimal(32,2)
);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `full_name` varchar(120) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `contact_no` varchar(30) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `payment_preference` varchar(100) DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role_id`, `full_name`, `email`, `password_hash`, `contact_no`, `address`, `payment_preference`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'System Admin', 'admin@retail.local', '$2y$10$ez70zdjPaRpgAAbrygwzCO/hJyFEoLpxLnVUCDfmdb8PHqi8oHhDG', '0771234567', 'Colombo', 'Mock Card', 'Active', '2026-03-24 07:49:13', '2026-03-24 07:58:24'),
(2, 3, 'Support Agent', 'support@retail.local', '$2y$10$ez70zdjPaRpgAAbrygwzCO/hJyFEoLpxLnVUCDfmdb8PHqi8oHhDG', '0772345678', 'Kandy', 'Mock Wallet', 'Active', '2026-03-24 07:49:13', '2026-03-24 07:58:24'),
(3, 2, 'John Customer', 'john@retail.local', '$2y$10$ez70zdjPaRpgAAbrygwzCO/hJyFEoLpxLnVUCDfmdb8PHqi8oHhDG', '0773456789', 'Galle', 'Cash on Delivery', 'Inactive', '2026-03-24 07:49:13', '2026-03-29 08:15:44'),
(4, 2, 'test', 'test@gmail.com', '$2y$10$zu.0h80tcXlXsz5JfEUOV.swRXF/ta1lKqzqktGTFN9tdO2RwE5yu', '0772356645', 'Kelaniya', 'Card', 'Active', '2026-03-25 04:36:59', '2026-03-25 04:36:59'),
(5, 2, 'Chulani Vimukthi', 'vimukthichulani@gmail.com', '$2y$10$kZclJ7dz8ojz32BK7h0zZebt2WvEH1xyPSZ11WXnD.IbKI4n/S7eW', '0769061460', '717/F/12 Polhna Road, Kohalwila, Kelaniya', 'Cash on Delivery', 'Active', '2026-03-25 06:09:23', '2026-03-25 06:09:23'),
(6, 2, 'Vimukthi', 'v@gmail.com', '$2y$10$Ilrvl8M/EvuNxde1y.ESxOS1t.p.7oMHmKRDlW.Fl9d6C6ow9YOmS', '0769061460', 'kelaniya', '', 'Active', '2026-03-26 06:14:57', '2026-03-26 06:14:57'),
(7, 3, 'test', 'test123@gmail.com', '$2y$10$vlC9n8tiksHcS7CHMislXecQhAkLNX0vaxLLoqMF7zu9HSzLSWxK2', '', '', '', 'Active', '2026-03-27 07:33:41', '2026-03-27 07:33:41');

-- --------------------------------------------------------

--
-- Table structure for table `user_activity_logs`
--

CREATE TABLE `user_activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `activity_type` varchar(100) NOT NULL,
  `activity_detail` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_activity_logs`
--

INSERT INTO `user_activity_logs` (`id`, `user_id`, `activity_type`, `activity_detail`, `created_at`) VALUES
(1, 1, 'LOGIN', 'Admin logged in successfully', '2026-03-24 07:49:13'),
(2, 3, 'VIEW_PRODUCT', 'Viewed Smartphone X', '2026-03-24 07:49:13'),
(3, 3, 'ADD_TO_CART', 'Added Running Shoes to cart', '2026-03-24 07:49:13');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wishlist`
--

INSERT INTO `wishlist` (`id`, `user_id`, `product_id`, `created_at`) VALUES
(1, 3, 2, '2026-03-24 07:49:13'),
(2, 3, 4, '2026-03-24 07:49:13'),
(8, 6, 5, '2026-03-29 11:06:19'),
(9, 6, 6, '2026-03-29 14:04:55'),
(10, 6, 13, '2026-03-29 16:05:04');

-- --------------------------------------------------------

--
-- Structure for view `sales_summary`
--
DROP TABLE IF EXISTS `sales_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `sales_summary`  AS SELECT cast(`orders`.`created_at` as date) AS `order_date`, count(0) AS `total_orders`, sum(`orders`.`total_amount`) AS `revenue` FROM `orders` GROUP BY cast(`orders`.`created_at` as date) ORDER BY cast(`orders`.`created_at` as date) DESC ;

-- --------------------------------------------------------

--
-- Structure for view `top_selling_products`
--
DROP TABLE IF EXISTS `top_selling_products`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `top_selling_products`  AS SELECT `p`.`id` AS `id`, `p`.`name` AS `name`, sum(`oi`.`quantity`) AS `units_sold`, sum(`oi`.`subtotal`) AS `sales_amount` FROM (`order_items` `oi` join `products` `p` on(`p`.`id` = `oi`.`product_id`)) GROUP BY `p`.`id`, `p`.`name` ORDER BY sum(`oi`.`quantity`) DESC ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_product` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_parent` (`parent_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `idx_category_id` (`category_id`),
  ADD KEY `idx_sub_category_id` (`sub_category_id`);

--
-- Indexes for table `promotions`
--
ALTER TABLE `promotions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `support_ticket_replies`
--
ALTER TABLE `support_ticket_replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_id` (`ticket_id`),
  ADD KEY `responder_id` (`responder_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `user_activity_logs`
--
ALTER TABLE `user_activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_wishlist_item` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `promotions`
--
ALTER TABLE `promotions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `support_tickets`
--
ALTER TABLE `support_tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `support_ticket_replies`
--
ALTER TABLE `support_ticket_replies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `user_activity_logs`
--
ALTER TABLE `user_activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `fk_cat_parent` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_product_sub_cat` FOREIGN KEY (`sub_category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD CONSTRAINT `support_tickets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `support_ticket_replies`
--
ALTER TABLE `support_ticket_replies`
  ADD CONSTRAINT `support_ticket_replies_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `support_ticket_replies_ibfk_2` FOREIGN KEY (`responder_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);

--
-- Constraints for table `user_activity_logs`
--
ALTER TABLE `user_activity_logs`
  ADD CONSTRAINT `user_activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
