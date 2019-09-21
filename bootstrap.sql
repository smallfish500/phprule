-- phpMyAdmin SQL Dump
-- version 4.8.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 21, 2019 at 06:05 PM
-- Server version: 10.0.38-MariaDB-0ubuntu0.16.04.1
-- PHP Version: 7.2.22-1+ubuntu16.04.1+deb.sury.org+1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rule`
--

-- --------------------------------------------------------

--
-- Table structure for table `addressbook`
--

CREATE TABLE `addressbook` (
  `id` int(10) UNSIGNED NOT NULL,
  `label` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `enabled` tinyint(1) UNSIGNED NOT NULL DEFAULT '1',
  `create_user_id` int(10) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `update_user_id` int(10) UNSIGNED DEFAULT NULL,
  `updated` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `addressbook`
--

INSERT INTO `addressbook` (`id`, `label`, `enabled`, `create_user_id`, `created`, `update_user_id`, `updated`) VALUES
(1, 'Perso', 1, 1, '2019-08-26 16:34:33', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `contact`
--

CREATE TABLE `contact` (
  `addressbook_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contact`
--

INSERT INTO `contact` (`addressbook_id`, `user_id`, `created`) VALUES
(1, 1, '2019-09-20 14:30:06'),
(1, 3, '2019-09-20 14:30:19'),
(1, 10, '2019-09-20 14:30:06');

-- --------------------------------------------------------

--
-- Table structure for table `detail`
--

CREATE TABLE `detail` (
  `id` int(11) UNSIGNED NOT NULL,
  `label` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tag` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `locked` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `detail`
--

INSERT INTO `detail` (`id`, `label`, `tag`, `locked`) VALUES
(1, 'Email', 'RFC6350', 1),
(2, 'Phone', 'RFC6350', 1);

-- --------------------------------------------------------

--
-- Table structure for table `privilege`
--

CREATE TABLE `privilege` (
  `id` int(11) UNSIGNED NOT NULL,
  `label` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `enabled` tinyint(1) UNSIGNED NOT NULL,
  `create_user_id` int(11) UNSIGNED NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_user_id` int(11) UNSIGNED DEFAULT NULL,
  `updated` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `privilege`
--

INSERT INTO `privilege` (`id`, `label`, `enabled`, `create_user_id`, `created`, `update_user_id`, `updated`) VALUES
(1, 'user_display', 1, 1, '2018-11-27 13:09:21', NULL, NULL),
(2, 'user_edit', 1, 1, '2018-12-12 16:04:41', 1, '2018-12-12 18:00:47'),
(3, 'addressbooks_display', 1, 1, '2019-09-20 13:17:37', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `role`
--

CREATE TABLE `role` (
  `id` int(11) UNSIGNED NOT NULL,
  `label` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tag` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `enabled` tinyint(1) UNSIGNED NOT NULL,
  `create_user_id` int(11) UNSIGNED NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_user_id` int(11) UNSIGNED DEFAULT NULL,
  `updated` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role`
--

INSERT INTO `role` (`id`, `label`, `tag`, `enabled`, `create_user_id`, `created`, `update_user_id`, `updated`) VALUES
(1, 'admin', 'admin', 1, 1, '2018-11-27 13:09:21', NULL, NULL),
(2, 'admin_view', 'admin', 1, 1, '2018-12-12 17:37:59', 1, '2018-12-12 18:00:04');

-- --------------------------------------------------------

--
-- Table structure for table `role_privilege`
--

CREATE TABLE `role_privilege` (
  `role_id` int(11) UNSIGNED NOT NULL,
  `privilege_id` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_privilege`
--

INSERT INTO `role_privilege` (`role_id`, `privilege_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(2, 1);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) UNSIGNED NOT NULL,
  `label` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` blob COMMENT 'AES crypt',
  `enabled` tinyint(1) UNSIGNED NOT NULL,
  `create_user_id` int(11) UNSIGNED NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_user_id` int(11) UNSIGNED DEFAULT NULL,
  `updated` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `label`, `password`, `enabled`, `create_user_id`, `created`, `update_user_id`, `updated`) VALUES
(1, 'jerome', 0x74def43734c0f248dc492bd10160004b, 1, 1, '2018-11-27 13:09:21', 1, '2019-09-20 15:10:13'),
(3, 'zob2zob2zob2', 0xf2d5f1698822dececa116b73c0309aee, 1, 1, '2019-09-19 16:14:56', 1, '2019-09-21 13:33:56'),
(10, 'misstest', 0x74def43734c0f248dc492bd10160004b, 1, 1, '2019-09-19 16:45:13', 1, '2019-09-20 15:11:06');

-- --------------------------------------------------------

--
-- Table structure for table `user_addressbook`
--

CREATE TABLE `user_addressbook` (
  `user_id` int(11) UNSIGNED NOT NULL,
  `addressbook_id` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_addressbook`
--

INSERT INTO `user_addressbook` (`user_id`, `addressbook_id`) VALUES
(1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_detail`
--

CREATE TABLE `user_detail` (
  `user_id` int(11) UNSIGNED NOT NULL,
  `detail_id` int(11) UNSIGNED NOT NULL,
  `value` blob NOT NULL COMMENT 'AES crypt',
  `created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_detail`
--

INSERT INTO `user_detail` (`user_id`, `detail_id`, `value`, `created`) VALUES
(1, 1, 0x75e5d4669d6d41e347118ce83c315462789fb395b49203160ea709e9f765f1a3, '2019-08-26 16:29:44'),
(1, 2, 0xdcc49f0997f43c879dd5d9c1ed271204, '2019-08-26 16:38:52');

-- --------------------------------------------------------

--
-- Table structure for table `user_role`
--

CREATE TABLE `user_role` (
  `user_id` int(11) UNSIGNED NOT NULL,
  `role_id` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_role`
--

INSERT INTO `user_role` (`user_id`, `role_id`) VALUES
(1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_wallet`
--

CREATE TABLE `user_wallet` (
  `user_id` int(11) UNSIGNED NOT NULL,
  `wallet_id` int(11) UNSIGNED NOT NULL,
  `value` blob NOT NULL COMMENT 'AES crypt',
  `created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wallet`
--

CREATE TABLE `wallet` (
  `id` int(11) UNSIGNED NOT NULL,
  `label` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wallet`
--

INSERT INTO `wallet` (`id`, `label`) VALUES
(1, 'password'),
(2, 'token'),
(3, 'recovery');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addressbook`
--
ALTER TABLE `addressbook`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contact`
--
ALTER TABLE `contact`
  ADD PRIMARY KEY (`addressbook_id`,`user_id`),
  ADD KEY `fk_contact_user` (`user_id`);

--
-- Indexes for table `detail`
--
ALTER TABLE `detail`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `privilege`
--
ALTER TABLE `privilege`
  ADD PRIMARY KEY (`id`),
  ADD KEY `x_p_create_user_id` (`create_user_id`),
  ADD KEY `x_p_update_user_id` (`update_user_id`);

--
-- Indexes for table `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`id`),
  ADD KEY `x_r_create_user_id` (`create_user_id`),
  ADD KEY `x_r_update_user_id` (`update_user_id`);

--
-- Indexes for table `role_privilege`
--
ALTER TABLE `role_privilege`
  ADD PRIMARY KEY (`role_id`,`privilege_id`),
  ADD KEY `IDX_D6D4495BD60322AC` (`role_id`),
  ADD KEY `IDX_D6D4495B32FB8AEA` (`privilege_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD KEY `x_u_create_user_id` (`create_user_id`),
  ADD KEY `x_u_update_user_id` (`update_user_id`);

--
-- Indexes for table `user_addressbook`
--
ALTER TABLE `user_addressbook`
  ADD PRIMARY KEY (`user_id`,`addressbook_id`),
  ADD KEY `fk_user_addressbook_addressbook` (`addressbook_id`),
  ADD KEY `fk_user_addressbook_user` (`user_id`);

--
-- Indexes for table `user_detail`
--
ALTER TABLE `user_detail`
  ADD KEY `fk_user_detail_detail` (`detail_id`),
  ADD KEY `fk_user_detail_user` (`user_id`);

--
-- Indexes for table `user_role`
--
ALTER TABLE `user_role`
  ADD PRIMARY KEY (`user_id`,`role_id`),
  ADD KEY `IDX_2DE8C6A3A76ED395` (`user_id`),
  ADD KEY `IDX_2DE8C6A3D60322AC` (`role_id`);

--
-- Indexes for table `user_wallet`
--
ALTER TABLE `user_wallet`
  ADD PRIMARY KEY (`user_id`,`wallet_id`),
  ADD KEY `fk_user_wallet_wallet` (`wallet_id`),
  ADD KEY `fk_user_wallet_user` (`user_id`);

--
-- Indexes for table `wallet`
--
ALTER TABLE `wallet`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addressbook`
--
ALTER TABLE `addressbook`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `detail`
--
ALTER TABLE `detail`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `privilege`
--
ALTER TABLE `privilege`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `role`
--
ALTER TABLE `role`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `wallet`
--
ALTER TABLE `wallet`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `contact`
--
ALTER TABLE `contact`
  ADD CONSTRAINT `fk_contact_addressbook` FOREIGN KEY (`addressbook_id`) REFERENCES `addressbook` (`id`),
  ADD CONSTRAINT `fk_contact_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `privilege`
--
ALTER TABLE `privilege`
  ADD CONSTRAINT `fk_p_create_user_id` FOREIGN KEY (`create_user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `fk_p_update_user_id` FOREIGN KEY (`update_user_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `role`
--
ALTER TABLE `role`
  ADD CONSTRAINT `fk_r_create_user_id` FOREIGN KEY (`create_user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `fk_r_update_user_id` FOREIGN KEY (`update_user_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `role_privilege`
--
ALTER TABLE `role_privilege`
  ADD CONSTRAINT `fk_rp_privilege_id` FOREIGN KEY (`privilege_id`) REFERENCES `privilege` (`id`),
  ADD CONSTRAINT `fk_rp_role_id` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`);

--
-- Constraints for table `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `fk_u_create_user_id` FOREIGN KEY (`create_user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `fk_u_update_user_id` FOREIGN KEY (`update_user_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `user_addressbook`
--
ALTER TABLE `user_addressbook`
  ADD CONSTRAINT `fk_user_addressbook_addressbook` FOREIGN KEY (`addressbook_id`) REFERENCES `addressbook` (`id`),
  ADD CONSTRAINT `fk_user_addressbook_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `user_detail`
--
ALTER TABLE `user_detail`
  ADD CONSTRAINT `fk_user_detail_detail` FOREIGN KEY (`detail_id`) REFERENCES `detail` (`id`),
  ADD CONSTRAINT `fk_user_detail_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `user_role`
--
ALTER TABLE `user_role`
  ADD CONSTRAINT `fk_ur_role_id` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`),
  ADD CONSTRAINT `fk_ur_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `user_wallet`
--
ALTER TABLE `user_wallet`
  ADD CONSTRAINT `fk_user_wallet_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `fk_user_wallet_wallet` FOREIGN KEY (`wallet_id`) REFERENCES `wallet` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
