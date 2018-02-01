-- phpMyAdmin SQL Dump
-- version 3.3.8.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Mar 31, 2016 at 01:53 PM
-- Server version: 5.5.36
-- PHP Version: 5.3.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `rel_squibdrv_sup`
--

-- --------------------------------------------------------

--
-- Table structure for table `client_instences`
--

CREATE TABLE IF NOT EXISTS `client_instences` (
  `cinstence_id` int(11) NOT NULL AUTO_INCREMENT,
  `client_name` varchar(20) DEFAULT NULL,
  `client_email` varchar(30) DEFAULT NULL,
  `instence_name` varchar(20) DEFAULT NULL,
  `instence_url` varchar(120) DEFAULT NULL,
  `db_host` varchar(20) DEFAULT NULL,
  `db_user` varchar(20) DEFAULT NULL,
  `db_password` varchar(20) DEFAULT NULL,
  `db_prefix` varchar(20) DEFAULT NULL,
  `login_username` varchar(20) DEFAULT NULL,
  `login_password` varchar(20) DEFAULT NULL,
  `client_status` tinyint(4) DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `updateddate` date DEFAULT NULL,
  `instance_ip` varchar(20) DEFAULT NULL,
  `default_drive_space` varchar(20) DEFAULT NULL,
  `sync_status` tinyint(4) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  PRIMARY KEY (`cinstence_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=25 ;

--
-- Dumping data for table `client_instences`
--

INSERT INTO `client_instences` (`cinstence_id`, `client_name`, `client_email`, `instence_name`, `instence_url`, `db_host`, `db_user`, `db_password`, `db_prefix`, `login_username`, `login_password`, `client_status`, `created_date`, `updateddate`, `instance_ip`, `default_drive_space`, `sync_status`, `expiry_date`) VALUES
(14, 'Bala Subramanian', 'bala@bluehorizoninfotech.com', 'bluehorizon', 'http%3A%2F%2Fbluehorizon.squibdrive.com', 'localhost', 'root', 'RDN2M2wwcDNyIQ==', 'P', 'Developer', 'ZGV2ZWxvcGVyQSFAMQ==', 1, '2016-02-15', '2016-03-17', '192.168.1.1', '5', 1, '2016-07-30'),
(15, 'James Faasse', 'jt@inetready.com', 'iNETready', 'http%3A%2F%2Finetready.squibdrive.com', 'localhost', 'root', 'LzFOM3RyMzRkeSE=', 'inetready_', 'Superadmin', 'LzFOM3RyMzRkeSE=', 0, '2016-02-16', '2016-03-11', '123.45.6.78', '5', 1, '2016-07-30'),
(16, 'Jose Rivera', 'joserivera@synergem.com', 'synergem', 'http%3A%2F%2Fsynergem.squibdrive.com', 'localhost', 'root', 'U3luM3JnM20xIQ==', 'synergem_', 'Synergem', 'U3luM3JnM20xIQ==', 0, '2016-02-16', '2016-03-11', '23.127.156.217', '5 GB', 1, '2016-06-30'),
(17, 'Eric Clapton', 'ericclapton@seaworld.com', 'Seaworld', 'http%3A%2F%2Fseaworld.squibdrive.com', 'localhost', 'root', 'U3luM3JnM20xIQ==', 'seaworld_', 'SeaWorld', 'U3luM3JnM20xIQ==', 0, '2016-02-16', '2016-03-11', '127.12.113.12', '5 GB', 1, '2016-04-30'),
(21, 'Nicolai Larson', 'nlarson@gmail.com', 'google', 'http%3A%2F%2Fgoogle.squibdrive.com', 'localhost', 'root', 'RzAwZ2wzbTMh', 'google_', 'Google', 'RzAwZ2wzbTMh', 0, '2016-02-25', '2016-03-11', '123.45.6.7.0', '5', 1, '2016-07-30'),
(23, 'Bill Jackson', 'billjackson@cocacola.com', 'CocaCola', 'http%3A%2F%2Fcocacola.squibdrive.com', 'localhost', 'root', 'QzBjNGMwbDQxMjMh', 'cocacola_', 'cocacola', 'YXJ1bmtzMUAxIUE=', 0, '2016-03-03', '2016-03-11', '192.168.1.1', '5', 0, '2016-03-30'),
(24, 'Nikhil', 'nikhil.agarwal@dotsquares.com', 'dotsquares', 'http%3A%2F%2Fdotsquares.squibdrive.net', 'localhost', 'dotsquares', 'MU4zdHIzNGR5IQ==', 'dotsquares_', 'dotsquares', 'MU4zdHIzNGR5IQ==', 0, '2016-03-16', '2016-03-16', '129.1.29.1', '5', 0, '2016-04-09');

-- --------------------------------------------------------

--
-- Table structure for table `Cloud`
--

CREATE TABLE IF NOT EXISTS `Cloud` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `max_simul_file_upload` tinyint(4) DEFAULT NULL,
  `max_file_size` tinyint(4) DEFAULT NULL,
  `date_format` varchar(20) DEFAULT NULL,
  `allowed_types` varchar(20) DEFAULT NULL,
  `excluded_types` varchar(20) DEFAULT NULL,
  `preview_extensions` varchar(20) DEFAULT NULL,
  `updated_date` date DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `updated_by` varchar(20) DEFAULT NULL,
  `created_by` varchar(20) DEFAULT NULL,
  `default_limit_size` int(11) DEFAULT NULL,
  `limit_unit` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `Cloud`
--

INSERT INTO `Cloud` (`id`, `max_simul_file_upload`, `max_file_size`, `date_format`, `allowed_types`, `excluded_types`, `preview_extensions`, `updated_date`, `created_date`, `updated_by`, `created_by`, `default_limit_size`, `limit_unit`) VALUES
(1, 8, 30, '3', 'anBnLHBuZyxnaWYsZG9j', 'cGhwLHB5dGhvbixleGUs', '1', '2016-03-12', NULL, 'admin', NULL, 200, '0');

-- --------------------------------------------------------

--
-- Table structure for table `error_logs`
--

CREATE TABLE IF NOT EXISTS `error_logs` (
  `error_ids` int(11) NOT NULL AUTO_INCREMENT,
  `instence_id` int(11) NOT NULL,
  `errors` varchar(20) DEFAULT NULL,
  `dated` date DEFAULT NULL,
  PRIMARY KEY (`error_ids`),
  KEY `instence_id` (`instence_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `error_logs`
--


-- --------------------------------------------------------

--
-- Table structure for table `Globl_sett_new_Inst`
--

CREATE TABLE IF NOT EXISTS `Globl_sett_new_Inst` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cinstence_id` int(11) DEFAULT NULL,
  `map_view` varchar(20) DEFAULT NULL,
  `map_view_c` varchar(20) DEFAULT NULL,
  `camp_view` varchar(20) DEFAULT NULL,
  `chart_view` varchar(20) DEFAULT NULL,
  `roi_view` varchar(20) DEFAULT NULL,
  `reg_verification` varchar(20) DEFAULT NULL,
  `encrypt_url_name` varchar(120) DEFAULT NULL,
  `template` int(10) DEFAULT NULL,
  `banner_ad_layout` varchar(20) DEFAULT NULL,
  `verification_status` varchar(20) DEFAULT NULL,
  `verification_method` varchar(20) DEFAULT NULL,
  `notify_admin_of_reg` varchar(20) DEFAULT NULL,
  `squibKey_plugin_customer_reg_form_name` varchar(20) DEFAULT NULL,
  `squib_tracker_script` tinytext,
  `url_not_found_redirect_page` varchar(120) DEFAULT NULL,
  `google_map_api_details` tinytext,
  `created_Date` date DEFAULT NULL,
  `updated_date` date DEFAULT NULL,
  `created_by` varchar(20) DEFAULT NULL,
  `updated_by` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cinstence_id` (`cinstence_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=14 ;

--
-- Dumping data for table `Globl_sett_new_Inst`
--

INSERT INTO `Globl_sett_new_Inst` (`id`, `cinstence_id`, `map_view`, `map_view_c`, `camp_view`, `chart_view`, `roi_view`, `reg_verification`, `encrypt_url_name`, `template`, `banner_ad_layout`, `verification_status`, `verification_method`, `notify_admin_of_reg`, `squibKey_plugin_customer_reg_form_name`, `squib_tracker_script`, `url_not_found_redirect_page`, `google_map_api_details`, `created_Date`, `updated_date`, `created_by`, `updated_by`) VALUES
(3, 14, 'C', '2', 'M', 'H', 'Y', 'Y', 'Y', 2, '2', 'Y', 'M', 'Y', 'Form1', 'Test script', 'http%3A%2F%2Fdev.squibdrive.net%2F%23%2Fapp%2Fmanageinstances', 'Test Map', '2016-02-15', '2016-02-15', 'Admin', 'Admin'),
(4, 15, 'G', NULL, 'M', 'H', 'Y', 'Y', 'Y', 1, '1', 'Y', 'M', 'Y', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 16, 'G', NULL, 'M', 'H', 'Y', 'Y', 'Y', 1, '1', 'Y', 'M', 'Y', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 17, 'G', NULL, 'M', 'H', 'Y', 'Y', 'Y', 1, '1', 'Y', 'M', 'Y', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(10, 21, 'G', NULL, 'M', 'H', 'Y', 'Y', 'Y', 1, '1', 'Y', 'M', 'Y', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(12, 23, 'G', NULL, 'M', 'H', 'Y', 'Y', 'Y', 1, '1', 'Y', 'M', 'Y', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(13, 24, 'G', NULL, 'M', 'H', 'Y', 'Y', 'Y', 1, '1', 'Y', 'M', 'Y', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `instance_files`
--

CREATE TABLE IF NOT EXISTS `instance_files` (
  `file_id` int(11) NOT NULL AUTO_INCREMENT,
  `cinstence_id` int(10) DEFAULT NULL,
  `orgnl_file_name` varchar(50) DEFAULT NULL,
  `savd_file_name` varchar(50) DEFAULT NULL,
  `file_type` varchar(20) DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  PRIMARY KEY (`file_id`),
  KEY `cinstence_id` (`cinstence_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=223 ;

--
-- Dumping data for table `instance_files`
--

INSERT INTO `instance_files` (`file_id`, `cinstence_id`, `orgnl_file_name`, `savd_file_name`, `file_type`, `created_date`) VALUES
(70, 20, 'attach-blue.png', '20_1456922675_646290.png', 'png', '2016-03-02'),
(90, 14, 'test.odt', '14_1457604961_443901.odt', 'odt', '2016-03-10'),
(134, 23, 'porkpieicons-2.ai', '23_1457626334_855197.ai', 'ai', '2016-03-10'),
(203, 17, 'Bollywoodblast.mpg', '17_1458235161_789002.mpg', 'mpg', '2016-03-17'),
(204, 17, 'cloud_file_list.php', '17_1458235162_322167.php', 'php', '2016-03-17'),
(205, 24, 'Flat Folder Icon.rar', '24_1458235424_851661.rar', 'rar', '2016-03-17'),
(172, 14, 'IMG_9223.PNG', '14_1457984416_381236.PNG', 'PNG', '2016-03-14'),
(93, 21, 'landing-page-HTML-template.zip', '21_1457623692_843359.zip', 'zip', '2016-03-10'),
(170, 14, 'preview-slide-fix.mp4', '14_1457984322_174887.mp4', 'mp4', '2016-03-14'),
(209, 24, 'Bollywoodblast.mpg', '24_1458235454_383780.mpg', 'mpg', '2016-03-17'),
(206, 24, 'url-12.html', '24_1458235424_57011.html', 'html', '2016-03-17'),
(207, 24, 'Profit and loss statement1[1].xls', '24_1458235424_244910.xls', 'xls', '2016-03-17'),
(97, 15, 'export.css', '15_1457623973_672998.css', 'css', '2016-03-10'),
(217, 15, 'url-9.html', '15_1458235921_194456.html', 'html', '2016-03-17'),
(83, 21, 'Basics-of-Cloud-Storage-Copy.jpg', '21_1457568475_802051.jpg', 'jpg', '2016-03-10'),
(221, 24, 'iwebkey-commercial.mp4', '24_1458236740_356593.mp4', 'mp4', '2016-03-17'),
(169, 14, 'upload-function-change.mp4', '14_1457983188_561601.mp4', 'mp4', '2016-03-14'),
(168, 14, 'update-instance-fix.mp4', '14_1457983129_157989.mp4', 'mp4', '2016-03-14'),
(96, 15, 'logo.psd', '15_1457623940_304320.psd', 'psd', '2016-03-10'),
(95, 15, 'landing-page-HTML-template.zip', '15_1457623901_935843.zip', 'zip', '2016-03-10'),
(210, 24, 'SQUIBdrive-Manual.docx', '24_1458235456_173528.docx', 'docx', '2016-03-17'),
(198, 17, 'url-12.html', '17_1458235129_69115.html', 'html', '2016-03-17'),
(37, 19, '6-Part-Webinar-683x1024.jpg', '19_1456843643_838311.jpg', 'jpg', '2016-03-01'),
(38, 20, '6-million-dollar-campaign-ebook-v3.pdf', '20_1456843701_448051.pdf', 'pdf', '2016-03-01'),
(159, 15, 'inetready-logo.jpg', '15_1457678473_917360.jpg', 'jpg', '2016-03-11'),
(220, 15, 'kobetricks.mov', '15_1458235991_578722.mov', 'mov', '2016-03-17'),
(116, 15, 'iwebkey-commercial.mp4', '15_1457625391_283950.mp4', 'mp4', '2016-03-10'),
(115, 14, 'iwebkey-commercial.mp4', '14_1457625302_735709.mp4', 'mp4', '2016-03-10'),
(100, 15, 'WakeApp-CS2.eps', '15_1457624091_290869.eps', 'eps', '2016-03-10'),
(99, 15, 'WakeApp-CS2.ai', '15_1457624053_638355.ai', 'ai', '2016-03-10'),
(98, 15, 'Adobe-Flash-Player.dmg', '15_1457624004_993061.dmg', 'dmg', '2016-03-10'),
(208, 24, 'click_counter.php', '24_1458235442_779309.php', 'php', '2016-03-17'),
(199, 17, 'Profit and loss statement1[1].xls', '17_1458235129_564730.xls', 'xls', '2016-03-17'),
(162, 15, 'image.png', '15_1457683770_953913.png', 'png', '2016-03-11'),
(54, 19, 'davidflake.jpg', '19_1456866446_418476.jpg', 'jpg', '2016-03-01'),
(55, 19, 'dog-man-morph.jpg', '19_1456866447_83414.jpg', 'jpg', '2016-03-01'),
(56, 19, 'elephant-squid.jpg', '19_1456866447_898920.jpg', 'jpg', '2016-03-01'),
(57, 19, 'theundetectablesheader.jpg', '19_1456866448_338074.jpg', 'jpg', '2016-03-01'),
(58, 19, 'theundetectablesimagebanner.jpg', '19_1456866448_889180.jpg', 'jpg', '2016-03-01'),
(59, 19, 'theundetectableslogo.jpg', '19_1456866453_280957.jpg', 'jpg', '2016-03-01'),
(60, 19, 'seo-video-youtube.mp4', '19_1456866454_992683.mp4', 'mp4', '2016-03-01'),
(167, 14, 'file-name-type-match-fix.mp4', '14_1457983058_195744.mp4', 'mp4', '2016-03-14'),
(166, 14, 'cloud-settings-needs-completion.mp4', '14_1457983038_801633.mp4', 'mp4', '2016-03-14'),
(184, 14, 'add-instance-functionality-fix.mp4', '14_1457988981_341544.mp4', 'mp4', '2016-03-14'),
(165, 14, 'breadcrumb-filename-in-header-fix.mp4', '14_1457983012_595236.mp4', 'mp4', '2016-03-14'),
(171, 14, 'IMG_9222.PNG', '14_1457984415_456097.PNG', 'PNG', '2016-03-14'),
(71, 20, 'attach-blue.png', '20_1456922675_167322.png', 'png', '2016-03-02'),
(72, 20, 'test.odt', '20_1456922676_69867.odt', 'odt', '2016-03-02'),
(73, 20, 'test.odt', '20_1456922676_58151.odt', 'odt', '2016-03-02'),
(213, 15, 'Flat Folder Icon.rar', '15_1458235909_546004.rar', 'rar', '2016-03-17'),
(103, 16, 'AdobeFlashPlayer.dmg', '16_1457624459_902769.dmg', 'dmg', '2016-03-10'),
(104, 16, 'WakeApp-CS2.ai', '16_1457624467_547278.ai', 'ai', '2016-03-10'),
(105, 16, 'WakeApp-CS2.eps', '16_1457624505_711285.eps', 'eps', '2016-03-10'),
(120, 16, 'alert.mp3', '16_1457625714_104846.mp3', 'mp3', '2016-03-10'),
(113, 16, 'kobetricks.mov', '16_1457624814_745080.mov', 'mov', '2016-03-10'),
(114, 16, 'iwebkey-commercial.mp4', '16_1457625122_567318.mp4', 'mp4', '2016-03-10'),
(219, 15, 'SQUIBdrive-Manual.docx', '15_1458235923_941599.docx', 'docx', '2016-03-17'),
(118, 15, 'Beside.mp3', '15_1457625657_408471.mp3', 'mp3', '2016-03-10'),
(193, 16, 'Mobile-up-close-and-personal.pdf', '16_1458234312_908256.pdf', 'pdf', '2016-03-17'),
(197, 17, 'Flat Blue Folder Icon.rar', '17_1458235129_347728.rar', 'rar', '2016-03-17'),
(125, 17, 'Mechanize.mp3', '17_1457625871_613946.mp3', 'mp3', '2016-03-10'),
(126, 17, 'iwebkey-commercial.mp4', '17_1457625912_376495.mp4', 'mp4', '2016-03-10'),
(196, 17, 'Mobile-up-close-and-personal.pdf', '17_1458235126_410289.pdf', 'pdf', '2016-03-17'),
(130, 21, 'Mechanize.mp3', '21_1457626020_49634.mp3', 'mp3', '2016-03-10'),
(131, 21, 'iwebkey-commercial.mp4', '21_1457626061_555907.mp4', 'mp4', '2016-03-10'),
(202, 17, 'Silvestre E Ramos - Schedule your Call.ics', '17_1458235148_335617.ics', 'ics', '2016-03-17'),
(138, 23, 'Mechanize.mp3', '23_1457626375_391271.mp3', 'mp3', '2016-03-10'),
(139, 23, 'iwebkey-commercial.mp4', '23_1457626416_208585.mp4', 'mp4', '2016-03-10'),
(140, 23, 'video_bg.mp4', '23_1457626420_562333.mp4', 'mp4', '2016-03-10'),
(194, 24, 'Mobile-up-close-and-personal.pdf', '24_1458234868_38815.pdf', 'pdf', '2016-03-17'),
(142, 23, 'Beside.mp3', '23_1457626449_644155.mp3', 'mp3', '2016-03-10'),
(195, 21, 'Mobile-up-close-and-personal.pdf', '21_1458234978_360678.pdf', 'pdf', '2016-03-17'),
(200, 17, '48[2].doc', '17_1458235130_508976.doc', 'doc', '2016-03-17'),
(201, 17, 'porkpieicons.psd', '17_1458235148_251388.psd', 'psd', '2016-03-17'),
(216, 15, 'DomainDownloadList-286223229.csv', '15_1458235921_823996.csv', 'csv', '2016-03-17'),
(215, 15, 'Bollywoodblast.mpg', '15_1458235921_781091.mpg', 'mpg', '2016-03-17'),
(150, 15, 'Vizibility Distributor Overview.pdf', '15_1457627282_938919.pdf', 'pdf', '2016-03-10'),
(156, 23, 'coca-cola-logo.jpg', '23_1457678342_470509.jpg', 'jpg', '2016-03-11'),
(158, 16, 'jose-rivera.jpg', '16_1457678383_886955.jpg', 'jpg', '2016-03-11'),
(185, 14, 'previous-next-click-thru-bug-fix.mp4', '14_1458052582_628718.mp4', 'mp4', '2016-03-15'),
(173, 14, 'IMG_9224.PNG', '14_1457984417_726577.PNG', 'PNG', '2016-03-14'),
(174, 14, 'IMG_9225.PNG', '14_1457984417_443587.PNG', 'PNG', '2016-03-14'),
(175, 14, 'IMG_9226.PNG', '14_1457984418_279665.PNG', 'PNG', '2016-03-14'),
(176, 14, 'IMG_9227.PNG', '14_1457984418_993198.PNG', 'PNG', '2016-03-14'),
(177, 14, 'IMG_9228.PNG', '14_1457984419_116589.PNG', 'PNG', '2016-03-14'),
(178, 14, 'IMG_9229.PNG', '14_1457984420_205513.PNG', 'PNG', '2016-03-14'),
(179, 14, 'Dashboard-Datatable-Edits.mp4', '14_1457984595_260367.mp4', 'mp4', '2016-03-14'),
(186, 24, 'lightbox-feature-specs.mp4', '24_1458107665_809445.mp4', 'mp4', '2016-03-16'),
(187, 24, 'beyondadmin-theme.zip', '24_1458108487_165196.zip', 'zip', '2016-03-16'),
(212, 24, 'porkpieicons.psd', '24_1458235590_881369.psd', 'psd', '2016-03-17');

-- --------------------------------------------------------

--
-- Table structure for table `msg_notification`
--

CREATE TABLE IF NOT EXISTS `msg_notification` (
  `notification_id` int(11) NOT NULL AUTO_INCREMENT,
  `cinstance_id` int(11) NOT NULL,
  `messages` tinytext,
  `from_addrs` varchar(20) DEFAULT NULL,
  `to_addrs` varchar(20) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `status` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`notification_id`),
  KEY `cinstance_id` (`cinstance_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `msg_notification`
--


-- --------------------------------------------------------

--
-- Table structure for table `plugins_permissions`
--

CREATE TABLE IF NOT EXISTS `plugins_permissions` (
  `pid` int(11) NOT NULL AUTO_INCREMENT,
  `cinstance_id` int(11) NOT NULL,
  `plugins_pages` varchar(100) DEFAULT NULL,
  `allowed_status` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`pid`),
  KEY `cinstance_id` (`cinstance_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `plugins_permissions`
--


-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `admin_id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_name` varchar(20) DEFAULT NULL,
  `email_id` varchar(30) DEFAULT NULL,
  `passwd` varchar(40) DEFAULT NULL,
  `user_status` tinyint(4) DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `updated_date` date DEFAULT NULL,
  `vanity_api_details` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`admin_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=27 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`admin_id`, `admin_name`, `email_id`, `passwd`, `user_status`, `created_date`, `updated_date`, `vanity_api_details`) VALUES
(1, 'admin', 'admin@gmail.com', 'YWRtaW4=', 1, NULL, NULL, NULL),
(20, 'support', 'support@gmail.com', 'c3VwcG9ydA==', 1, NULL, NULL, NULL),
(22, 'James Faasse', 'jt@inetready.com', 'LzFuM3RyMzRkeSE=', 0, NULL, NULL, NULL),
(26, 'Arnav', 'arnavdots@dotsquares.com', 'SGVsbG9AMTIz', 0, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `vanity_domains`
--

CREATE TABLE IF NOT EXISTS `vanity_domains` (
  `vdid` int(11) NOT NULL AUTO_INCREMENT,
  `cinstance_id` int(11) DEFAULT NULL,
  `vanity domain_url` varchar(120) DEFAULT NULL,
  `status` tinyint(4) DEFAULT NULL,
  `created date` date DEFAULT NULL,
  `updated date` date DEFAULT NULL,
  PRIMARY KEY (`vdid`),
  KEY `cinstance_id` (`cinstance_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `vanity_domains`
--


--
-- Constraints for dumped tables
--

--
-- Constraints for table `error_logs`
--
ALTER TABLE `error_logs`
  ADD CONSTRAINT `error_logs_ibfk_1` FOREIGN KEY (`instence_id`) REFERENCES `client_instences` (`cinstence_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `Globl_sett_new_Inst`
--
ALTER TABLE `Globl_sett_new_Inst`
  ADD CONSTRAINT `Globl_sett_new_Inst_ibfk_1` FOREIGN KEY (`cinstence_id`) REFERENCES `client_instences` (`cinstence_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `msg_notification`
--
ALTER TABLE `msg_notification`
  ADD CONSTRAINT `msg_notification_ibfk_1` FOREIGN KEY (`cinstance_id`) REFERENCES `client_instences` (`cinstence_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `plugins_permissions`
--
ALTER TABLE `plugins_permissions`
  ADD CONSTRAINT `plugins_permissions_ibfk_1` FOREIGN KEY (`cinstance_id`) REFERENCES `client_instences` (`cinstence_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `vanity_domains`
--
ALTER TABLE `vanity_domains`
  ADD CONSTRAINT `vanity_domains_ibfk_1` FOREIGN KEY (`cinstance_id`) REFERENCES `client_instences` (`cinstence_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
