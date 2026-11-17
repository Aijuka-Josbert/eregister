/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.20-11.8.9-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: lmo_records
-- ------------------------------------------------------
-- Server version	11.8.9-MariaDB-2 from Debian

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `backup_logs`
--

DROP TABLE IF EXISTS `backup_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `backup_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `backup_reference` varchar(60) NOT NULL,
  `type` varchar(30) NOT NULL DEFAULT 'full',
  `storage_disk` varchar(30) NOT NULL DEFAULT 'local',
  `file_path` varchar(500) DEFAULT NULL,
  `file_size` bigint(20) unsigned NOT NULL DEFAULT 0,
  `sha256_hash` char(64) DEFAULT NULL,
  `is_encrypted` tinyint(1) NOT NULL DEFAULT 1,
  `status` varchar(30) NOT NULL DEFAULT 'running',
  `notes` text DEFAULT NULL,
  `triggered_by` bigint(20) unsigned DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `backup_logs_backup_reference_unique` (`backup_reference`),
  KEY `backup_logs_triggered_by_foreign` (`triggered_by`),
  KEY `backup_logs_status_index` (`status`),
  CONSTRAINT `backup_logs_triggered_by_foreign` FOREIGN KEY (`triggered_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `backup_logs`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `backup_logs` WRITE;
/*!40000 ALTER TABLE `backup_logs` DISABLE KEYS */;
INSERT INTO `backup_logs` VALUES
(1,'LMO-BKP-20261117-143419-5211','full','local','backups/2026/11/LMO-BKP-20261117-143419-5211.zip.enc',9175,'f25480cfcd871de9f3ecc2b4448d987f6152c63f4a35de1ff3af1bf361888a28',1,'verified','Backed up 5 documents, 5 files, and 11 audit records.',NULL,'2026-11-17 11:34:19','2026-11-17 11:34:19','2026-11-17 11:34:19');
/*!40000 ALTER TABLE `backup_logs` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` bigint(20) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` bigint(20) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `departments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `office_id` bigint(20) unsigned NOT NULL,
  `code` varchar(30) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `departments_office_id_code_unique` (`office_id`,`code`),
  KEY `departments_is_active_index` (`is_active`),
  CONSTRAINT `departments_office_id_foreign` FOREIGN KEY (`office_id`) REFERENCES `offices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departments`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `departments` WRITE;
/*!40000 ALTER TABLE `departments` DISABLE KEYS */;
INSERT INTO `departments` VALUES
(1,1,'REG','Central Registry & Records Unit',NULL,1,'2026-11-17 11:34:15','2026-11-17 11:34:15'),
(2,1,'PROT','Protocol, Invitations & Public Relations',NULL,1,'2026-11-17 11:34:15','2026-11-17 11:34:15'),
(3,1,'LEG','Legal & Policy Advisory Unit',NULL,1,'2026-11-17 11:34:15','2026-11-17 11:34:15'),
(4,1,'EXEC','Executive Secretariat & Administration',NULL,1,'2026-11-17 11:34:15','2026-11-17 11:34:15'),
(5,2,'DREG','Deputy Lord Mayor Registry',NULL,1,'2026-11-17 11:34:15','2026-11-17 11:34:15');
/*!40000 ALTER TABLE `departments` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `document_activity_logs`
--

DROP TABLE IF EXISTS `document_activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `document_activity_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `document_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `office_id` bigint(20) unsigned DEFAULT NULL,
  `action` varchar(80) NOT NULL,
  `severity` varchar(20) NOT NULL DEFAULT 'info',
  `description` varchar(500) NOT NULL,
  `properties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`properties`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `document_activity_logs_office_id_foreign` (`office_id`),
  KEY `document_activity_logs_document_id_created_at_index` (`document_id`,`created_at`),
  KEY `document_activity_logs_user_id_created_at_index` (`user_id`,`created_at`),
  KEY `document_activity_logs_action_index` (`action`),
  KEY `document_activity_logs_severity_index` (`severity`),
  KEY `document_activity_logs_ip_address_index` (`ip_address`),
  KEY `document_activity_logs_created_at_index` (`created_at`),
  CONSTRAINT `document_activity_logs_document_id_foreign` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `document_activity_logs_office_id_foreign` FOREIGN KEY (`office_id`) REFERENCES `offices` (`id`) ON DELETE SET NULL,
  CONSTRAINT `document_activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `document_activity_logs`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `document_activity_logs` WRITE;
/*!40000 ALTER TABLE `document_activity_logs` DISABLE KEYS */;
INSERT INTO `document_activity_logs` VALUES
(1,1,3,1,'document.registered','info','Registered document LMO-2026-00001 (Official Invitation to the Greater Kampala Metropolitan Infrastructure & Drainage Summit 2026)','{\"internal_reference\":\"LMO-2026-00001\",\"official_reference\":\"MKCCMA\\/PS\\/2026\\/084\",\"organization\":\"Ministry of Kampala Capital City and Metropolitan Affairs\",\"confidentiality_level\":\"public\",\"file_count\":0}','127.0.0.1','Symfony','2026-11-17 11:34:17'),
(2,1,NULL,1,'document.ocr_completed','info','OCR text extraction completed for LMO-2026-00001 (717 characters extracted).','{\"char_count\":717,\"file_count\":1}','127.0.0.1','Symfony','2026-11-17 11:34:18'),
(3,2,3,1,'document.registered','info','Registered document LMO-2026-00002 (Submission of Q2 Municipal Road Maintenance & Street Lighting Expenditure Report FY 2026/27)','{\"internal_reference\":\"LMO-2026-00002\",\"official_reference\":\"KCCA\\/ED\\/FIN\\/2026\\/319\",\"organization\":\"Kampala Capital City Authority \\u2014 Directorate of Engineering & Technical Services\",\"confidentiality_level\":\"internal\",\"file_count\":0}','127.0.0.1','Symfony','2026-11-17 11:34:18'),
(4,2,NULL,1,'document.ocr_completed','info','OCR text extraction completed for LMO-2026-00002 (578 characters extracted).','{\"char_count\":578,\"file_count\":1}','127.0.0.1','Symfony','2026-11-17 11:34:18'),
(5,3,3,1,'document.registered','info','Registered document LMO-2026-00003 (Confidential Legal Advisory on Municipal Property Valuation & Commercial Lease Renewals)','{\"internal_reference\":\"LMO-2026-00003\",\"official_reference\":\"LMO\\/LEG\\/CONF\\/2026\\/017\",\"organization\":\"Directorate of Legal Affairs\",\"confidentiality_level\":\"strictly_confidential\",\"file_count\":0}','127.0.0.1','Symfony','2026-11-17 11:34:18'),
(6,3,NULL,1,'document.ocr_completed','info','OCR text extraction completed for LMO-2026-00003 (514 characters extracted).','{\"char_count\":514,\"file_count\":1}','127.0.0.1','Symfony','2026-11-17 11:34:18'),
(7,4,3,1,'document.registered','info','Registered document LMO-2026-00004 (Joint Petition by Downtown Traders & Market Vendors on Trade Order and Loading Zones)','{\"internal_reference\":\"LMO-2026-00004\",\"official_reference\":\"KACITA\\/PET\\/2026\\/052\",\"organization\":\"Kampala City Traders Association (KACITA) & United Market Vendors\",\"confidentiality_level\":\"public\",\"file_count\":0}','127.0.0.1','Symfony','2026-11-17 11:34:18'),
(8,4,NULL,1,'document.ocr_completed','info','OCR text extraction completed for LMO-2026-00004 (487 characters extracted).','{\"char_count\":487,\"file_count\":1}','127.0.0.1','Symfony','2026-11-17 11:34:19'),
(9,5,3,1,'document.registered','info','Registered document LMO-2026-00005 (Diplomatic Invitation: East African Mayors Forum on Climate Finance & Green Cities)','{\"internal_reference\":\"LMO-2026-00005\",\"official_reference\":\"EACF\\/INV\\/2026\\/112\",\"organization\":\"East African Local Governments Forum Secretariat\",\"confidentiality_level\":\"internal\",\"file_count\":0}','127.0.0.1','Symfony','2026-11-17 11:34:19'),
(10,5,NULL,1,'document.ocr_completed','info','OCR text extraction completed for LMO-2026-00005 (464 characters extracted).','{\"char_count\":464,\"file_count\":1}','127.0.0.1','Symfony','2026-11-17 11:34:19'),
(11,5,2,1,'document.archived','warning','Archived document LMO-2026-00005: Delegation attendance confirmed and protocol arrangements concluded; archived for historical reference.','{\"archive_id\":1,\"reason\":\"Delegation attendance confirmed and protocol arrangements concluded; archived for historical reference.\",\"location_code\":\"LMO-VAULT-2026\",\"retention_until\":\"2029-11-17\"}','127.0.0.1','Symfony','2026-11-17 11:34:19'),
(12,NULL,NULL,NULL,'system.backup_completed','info','Completed encrypted system backup LMO-BKP-20261117-143419-5211 (9.0 KB)','{\"backup_reference\":\"LMO-BKP-20261117-143419-5211\",\"sha256\":\"f25480cfcd871de9f3ecc2b4448d987f6152c63f4a35de1ff3af1bf361888a28\"}','127.0.0.1','Symfony','2026-11-17 11:34:19'),
(23,NULL,1,1,'auth.login_success','info','User Eng. Daniel Kato (System Admin) (System Administrator) signed in.','{\"role\":\"system_admin\",\"office\":\"LMO\"}','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.145.2 Chrome/150.0.7871.250 Electron/43.8.3 Safari/537.36','2026-11-17 11:46:17'),
(24,NULL,1,1,'auth.logout','info','User Eng. Daniel Kato (System Admin) signed out.',NULL,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.145.2 Chrome/150.0.7871.250 Electron/43.8.3 Safari/537.36','2026-11-17 11:46:38'),
(25,NULL,1,1,'auth.login_success','info','User Eng. Daniel Kato (System Admin) (System Administrator) signed in.','{\"role\":\"system_admin\",\"office\":\"LMO\"}','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64; rv:153.0) Gecko/20100101 Firefox/153.0','2026-11-17 11:57:19'),
(26,NULL,1,1,'auth.logout','info','User Eng. Daniel Kato (System Admin) signed out.',NULL,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64; rv:153.0) Gecko/20100101 Firefox/153.0','2026-11-17 11:57:57'),
(27,NULL,2,1,'auth.login_success','info','User Grace Nabirye (Records Manager) (Records Manager) signed in.','{\"role\":\"records_manager\",\"office\":\"LMO\"}','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64; rv:153.0) Gecko/20100101 Firefox/153.0','2026-11-17 11:58:05'),
(28,NULL,2,1,'auth.logout','info','User Grace Nabirye (Records Manager) signed out.',NULL,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64; rv:153.0) Gecko/20100101 Firefox/153.0','2026-11-17 11:58:22'),
(29,NULL,4,1,'auth.login_success','info','User Patricia Achieng (Office Manager) (Office Manager / Executive) signed in.','{\"role\":\"office_manager\",\"office\":\"LMO\"}','127.0.0.1','Mozilla/5.0 (Linux; Android 14; SM-S938B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36','2026-11-17 12:01:27'),
(30,NULL,4,1,'auth.logout','info','User Patricia Achieng (Office Manager) signed out.',NULL,'127.0.0.1','Mozilla/5.0 (Linux; Android 14; SM-S938B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36','2026-11-17 12:01:47'),
(31,NULL,5,1,'auth.login_success','info','User Ronald Ssemanda (Read-Only Staff) (Read-Only User) signed in.','{\"role\":\"read_only\",\"office\":\"LMO\"}','127.0.0.1','Mozilla/5.0 (Linux; Android 14; SM-S938B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36','2026-11-17 12:01:58'),
(32,NULL,5,1,'auth.logout','info','User Ronald Ssemanda (Read-Only Staff) signed out.',NULL,'127.0.0.1','Mozilla/5.0 (Linux; Android 14; SM-S938B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36','2026-11-17 12:02:19'),
(33,NULL,1,1,'auth.login_success','info','User Eng. Daniel Kato (System Admin) (System Administrator) signed in.','{\"role\":\"system_admin\",\"office\":\"LMO\"}','127.0.0.1','Mozilla/5.0 (Linux; Android 14; SM-S938B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36','2026-11-17 12:02:24'),
(34,NULL,1,1,'auth.logout','info','User Eng. Daniel Kato (System Admin) signed out.',NULL,'127.0.0.1','Mozilla/5.0 (Linux; Android 14; SM-S938B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36','2026-11-17 12:02:56');
/*!40000 ALTER TABLE `document_activity_logs` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `document_approvals`
--

DROP TABLE IF EXISTS `document_approvals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `document_approvals` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `document_id` bigint(20) unsigned NOT NULL,
  `requested_by` bigint(20) unsigned NOT NULL,
  `approver_id` bigint(20) unsigned NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `comments` text DEFAULT NULL,
  `decided_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `document_approvals_document_id_foreign` (`document_id`),
  KEY `document_approvals_requested_by_foreign` (`requested_by`),
  KEY `document_approvals_approver_id_foreign` (`approver_id`),
  KEY `document_approvals_status_index` (`status`),
  CONSTRAINT `document_approvals_approver_id_foreign` FOREIGN KEY (`approver_id`) REFERENCES `users` (`id`),
  CONSTRAINT `document_approvals_document_id_foreign` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `document_approvals_requested_by_foreign` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `document_approvals`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `document_approvals` WRITE;
/*!40000 ALTER TABLE `document_approvals` DISABLE KEYS */;
/*!40000 ALTER TABLE `document_approvals` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `document_archives`
--

DROP TABLE IF EXISTS `document_archives`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `document_archives` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `document_id` bigint(20) unsigned NOT NULL,
  `archived_by` bigint(20) unsigned NOT NULL,
  `archive_reason` text NOT NULL,
  `archive_location_code` varchar(80) DEFAULT NULL,
  `retention_until` date DEFAULT NULL,
  `archived_at` timestamp NOT NULL,
  `restored_by` bigint(20) unsigned DEFAULT NULL,
  `restore_reason` text DEFAULT NULL,
  `restored_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `document_archives_document_id_foreign` (`document_id`),
  KEY `document_archives_archived_by_foreign` (`archived_by`),
  KEY `document_archives_restored_by_foreign` (`restored_by`),
  KEY `document_archives_retention_until_index` (`retention_until`),
  KEY `document_archives_archived_at_index` (`archived_at`),
  CONSTRAINT `document_archives_archived_by_foreign` FOREIGN KEY (`archived_by`) REFERENCES `users` (`id`),
  CONSTRAINT `document_archives_document_id_foreign` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `document_archives_restored_by_foreign` FOREIGN KEY (`restored_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `document_archives`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `document_archives` WRITE;
/*!40000 ALTER TABLE `document_archives` DISABLE KEYS */;
INSERT INTO `document_archives` VALUES
(1,5,2,'Delegation attendance confirmed and protocol arrangements concluded; archived for historical reference.','LMO-VAULT-2026','2029-11-17','2026-11-17 11:34:19',NULL,NULL,NULL,'2026-11-17 11:34:19','2026-11-17 11:34:19');
/*!40000 ALTER TABLE `document_archives` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `document_assignments`
--

DROP TABLE IF EXISTS `document_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `document_assignments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `document_id` bigint(20) unsigned NOT NULL,
  `assigned_by` bigint(20) unsigned NOT NULL,
  `assigned_to_user_id` bigint(20) unsigned DEFAULT NULL,
  `assigned_to_department_id` bigint(20) unsigned DEFAULT NULL,
  `priority` varchar(20) NOT NULL DEFAULT 'normal',
  `due_date` date DEFAULT NULL,
  `instructions` text DEFAULT NULL,
  `resolution_notes` text DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `document_assignments_document_id_foreign` (`document_id`),
  KEY `document_assignments_assigned_by_foreign` (`assigned_by`),
  KEY `document_assignments_assigned_to_user_id_foreign` (`assigned_to_user_id`),
  KEY `document_assignments_assigned_to_department_id_foreign` (`assigned_to_department_id`),
  KEY `document_assignments_priority_index` (`priority`),
  KEY `document_assignments_due_date_index` (`due_date`),
  KEY `document_assignments_status_index` (`status`),
  CONSTRAINT `document_assignments_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`),
  CONSTRAINT `document_assignments_assigned_to_department_id_foreign` FOREIGN KEY (`assigned_to_department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `document_assignments_assigned_to_user_id_foreign` FOREIGN KEY (`assigned_to_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `document_assignments_document_id_foreign` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `document_assignments`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `document_assignments` WRITE;
/*!40000 ALTER TABLE `document_assignments` DISABLE KEYS */;
/*!40000 ALTER TABLE `document_assignments` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `document_categories`
--

DROP TABLE IF EXISTS `document_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `document_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(80) NOT NULL,
  `name` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `color_code` varchar(20) NOT NULL DEFAULT '#245B91',
  `retention_years` int(10) unsigned NOT NULL DEFAULT 5,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `document_categories_slug_unique` (`slug`),
  KEY `document_categories_name_index` (`name`),
  KEY `document_categories_is_active_index` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `document_categories`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `document_categories` WRITE;
/*!40000 ALTER TABLE `document_categories` DISABLE KEYS */;
INSERT INTO `document_categories` VALUES
(1,'invitation','Official Invitation','State functions, diplomatic receptions, civic ceremonies, summits, and stakeholder invitations.','#C5A15B',3,1,'2026-11-17 11:34:17','2026-11-17 11:34:17'),
(2,'inter-ministerial-correspondence','Inter-Ministerial & Government Correspondence','Official letters from Ministries, Departments, and Agencies (MDAs).','#245B91',10,1,'2026-11-17 11:34:17','2026-11-17 11:34:17'),
(3,'legal-and-statutory','Legal Notice & Statutory Matter','Legal opinions, court pleadings, contracts, and statutory notices.','#C74646',15,1,'2026-11-17 11:34:17','2026-11-17 11:34:17'),
(4,'citizen-petition','Citizen & Community Petition','Public petitions, market vendor appeals, and community grievances.','#21865B',7,1,'2026-11-17 11:34:17','2026-11-17 11:34:17'),
(5,'budget-and-finance','Budget, Audit & Financial Report','Municipal budget allocations, audit queries, and financial statements.','#142B4A',10,1,'2026-11-17 11:34:17','2026-11-17 11:34:17'),
(6,'internal-memorandum','Internal Office Memorandum','Internal directives, circulars, and administrative memos.','#3283B8',5,1,'2026-11-17 11:34:17','2026-11-17 11:34:17');
/*!40000 ALTER TABLE `document_categories` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `document_files`
--

DROP TABLE IF EXISTS `document_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `document_files` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `document_id` bigint(20) unsigned NOT NULL,
  `uuid` char(36) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `storage_disk` varchar(30) NOT NULL DEFAULT 'local',
  `storage_path` varchar(500) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `extension` varchar(15) NOT NULL,
  `file_size` bigint(20) unsigned NOT NULL,
  `sha256_hash` char(64) NOT NULL,
  `page_order` int(10) unsigned NOT NULL DEFAULT 1,
  `capture_source` varchar(30) NOT NULL DEFAULT 'upload',
  `ocr_status` varchar(25) NOT NULL DEFAULT 'pending',
  `file_ocr_text` longtext DEFAULT NULL,
  `uploaded_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `document_files_uuid_unique` (`uuid`),
  KEY `document_files_uploaded_by_foreign` (`uploaded_by`),
  KEY `document_files_document_id_page_order_index` (`document_id`,`page_order`),
  KEY `document_files_sha256_hash_index` (`sha256_hash`),
  KEY `document_files_ocr_status_index` (`ocr_status`),
  CONSTRAINT `document_files_document_id_foreign` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `document_files_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `document_files`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `document_files` WRITE;
/*!40000 ALTER TABLE `document_files` DISABLE KEYS */;
INSERT INTO `document_files` VALUES
(1,1,'4f1a3d14-09dc-4207-88f3-a1900c684b39','MKCCMA_PS_2026_084.pdf','local','documents/2026/11/4f1a3d14-09dc-4207-88f3-a1900c684b39.pdf','application/pdf','pdf',1223,'c9b276c218fd2f5335f15be0f847ae8cb26a5e6edd0ce9f6f021cccb1b09f097',1,'upload','completed','LORD MAYOR’S OFFICE - OFFICIAL DIGITAL RECORD\nOfficial Reference: MKCCMA/PS/2026/084\nSubject: Official Invitation to the Greater Kampala Metropolitan Infrastructure Drainage Summ\n------------------------------------------------------------------------\nREPUBLIC OF UGANDA - MINISTRY OF KAMPALA CAPITAL CITY AND METROPOLITAN AFFAIRS.\nRef: MKCCMA/PS/2026/084. Subject: Official Invitation to the Greater Kampala Met\nropolitan Infrastructure and Drainage Summit 2026. The Ministry cordially invite\ns the Right Honourable Lord Mayor to officiate at the opening session concerning\n Nakivubo Channel rehabilitation and solar street lighting across all five urban\n divisions.',3,'2026-11-17 11:34:17','2026-11-17 11:34:18'),
(2,2,'dff3ae5a-9840-4801-9c30-03c8e7e47073','KCCA_ED_FIN_2026_319.pdf','local','documents/2026/11/dff3ae5a-9840-4801-9c30-03c8e7e47073.pdf','application/pdf','pdf',1065,'92df2ed1f0d0392017ebdb722599e2a81d18700ba768036eb92a9bdb1c71e3e9',1,'upload','completed','LORD MAYOR’S OFFICE - OFFICIAL DIGITAL RECORD\nOfficial Reference: KCCA/ED/FIN/2026/319\nSubject: Submission of Q2 Municipal Road Maintenance Street Lighting Expenditure Report FY 20\n------------------------------------------------------------------------\nKAMPALA CAPITAL CITY AUTHORITY. Ref: KCCA/ED/FIN/2026/319. Quarterly Engineering\n and Financial Report FY 2026/27. Total disbursement for road patching and culve\nrt installation reached UGX 14.8 Billion with 92 percent physical completion ver\nified by municipal engineers.',3,'2026-11-17 11:34:18','2026-11-17 11:34:18'),
(3,3,'b851b0ca-e0ef-4d67-b1b3-88ae1e36bec9','LMO_LEG_CONF_2026_017.pdf','local','documents/2026/11/b851b0ca-e0ef-4d67-b1b3-88ae1e36bec9.pdf','application/pdf','pdf',992,'0d9a88d55abf1eefabe9377a6a53700fb301d4f37fb88ae26b0394a1dd8d0e08',1,'upload','completed','LORD MAYOR’S OFFICE - OFFICIAL DIGITAL RECORD\nOfficial Reference: LMO/LEG/CONF/2026/017\nSubject: Confidential Legal Advisory on Municipal Property Valuation Commercial Lease Renewal\n------------------------------------------------------------------------\nSTRICTLY CONFIDENTIAL LEGAL PRIVILEGE. Ref: LMO/LEG/CONF/2026/017. Legal Advisor\ny on Municipal Commercial Lease Renewals and Valuation Tribunal proceedings. Una\nuthorized disclosure is strictly prohibited.',3,'2026-11-17 11:34:18','2026-11-17 11:34:18'),
(4,4,'327b1168-65a6-4043-b6b2-11e7c79a54e5','KACITA_PET_2026_052.pdf','local','documents/2026/11/327b1168-65a6-4043-b6b2-11e7c79a54e5.pdf','application/pdf','pdf',967,'3934d5cd5a178b7b9b572cec91cf20c4a6a04c1cc072201740eefc13839894af',1,'upload','completed','LORD MAYOR’S OFFICE - OFFICIAL DIGITAL RECORD\nOfficial Reference: KACITA/PET/2026/052\nSubject: Joint Petition by Downtown Traders Market Vendors on Trade Order and Loading Zones\n------------------------------------------------------------------------\nPETITION TO THE LORD MAYOR. Ref: KACITA/PET/2026/052. Downtown traders and marke\nt vendors respectfully request a stakeholder dialogue on designated delivery hou\nrs and sanitation fees.',3,'2026-11-17 11:34:18','2026-11-17 11:34:19'),
(5,5,'93e494ce-ec99-4696-9938-b29025f38f62','EACF_INV_2026_112.pdf','local','documents/2026/11/93e494ce-ec99-4696-9938-b29025f38f62.pdf','application/pdf','pdf',946,'657a54b60d058b5f66c9a402f4c23a6efb6cc42cc351da2e9982d48952374b84',1,'upload','completed','LORD MAYOR’S OFFICE - OFFICIAL DIGITAL RECORD\nOfficial Reference: EACF/INV/2026/112\nSubject: Diplomatic Invitation: East African Mayors Forum on Climate Finance Green Cities\n------------------------------------------------------------------------\nEAST AFRICAN LOCAL GOVERNMENTS FORUM. Ref: EACF/INV/2026/112. Official Invitatio\nn to the East African Mayors Roundtable on Climate Finance and Solid Waste Manag\nement.',3,'2026-11-17 11:34:19','2026-11-17 11:34:19');
/*!40000 ALTER TABLE `document_files` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `document_tag_pivot`
--

DROP TABLE IF EXISTS `document_tag_pivot`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `document_tag_pivot` (
  `document_id` bigint(20) unsigned NOT NULL,
  `document_tag_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`document_id`,`document_tag_id`),
  KEY `document_tag_pivot_document_tag_id_foreign` (`document_tag_id`),
  CONSTRAINT `document_tag_pivot_document_id_foreign` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `document_tag_pivot_document_tag_id_foreign` FOREIGN KEY (`document_tag_id`) REFERENCES `document_tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `document_tag_pivot`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `document_tag_pivot` WRITE;
/*!40000 ALTER TABLE `document_tag_pivot` DISABLE KEYS */;
INSERT INTO `document_tag_pivot` VALUES
(1,1),
(5,1),
(1,2),
(1,3),
(1,4),
(1,5),
(2,6),
(2,7),
(2,8),
(2,9),
(3,10),
(3,11),
(3,12),
(3,13),
(4,14),
(4,15),
(4,16),
(4,17),
(5,18),
(5,19),
(5,20);
/*!40000 ALTER TABLE `document_tag_pivot` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `document_tags`
--

DROP TABLE IF EXISTS `document_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `document_tags` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(80) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `document_tags_slug_unique` (`slug`),
  KEY `document_tags_name_index` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `document_tags`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `document_tags` WRITE;
/*!40000 ALTER TABLE `document_tags` DISABLE KEYS */;
INSERT INTO `document_tags` VALUES
(1,'invitation','Invitation','2026-11-17 11:34:17','2026-11-17 11:34:17'),
(2,'keynote','Keynote','2026-11-17 11:34:17','2026-11-17 11:34:17'),
(3,'infrastructure-summit','Infrastructure Summit','2026-11-17 11:34:17','2026-11-17 11:34:17'),
(4,'drainage','Drainage','2026-11-17 11:34:17','2026-11-17 11:34:17'),
(5,'mkccma','MKCCMA','2026-11-17 11:34:17','2026-11-17 11:34:17'),
(6,'engineering','Engineering','2026-11-17 11:34:18','2026-11-17 11:34:18'),
(7,'budget-fy202627','Budget FY2026/27','2026-11-17 11:34:18','2026-11-17 11:34:18'),
(8,'roads','Roads','2026-11-17 11:34:18','2026-11-17 11:34:18'),
(9,'street-lighting','Street Lighting','2026-11-17 11:34:18','2026-11-17 11:34:18'),
(10,'legal-opinion','Legal Opinion','2026-11-17 11:34:18','2026-11-17 11:34:18'),
(11,'confidential','Confidential','2026-11-17 11:34:18','2026-11-17 11:34:18'),
(12,'property-valuation','Property Valuation','2026-11-17 11:34:18','2026-11-17 11:34:18'),
(13,'arbitration','Arbitration','2026-11-17 11:34:18','2026-11-17 11:34:18'),
(14,'petition','Petition','2026-11-17 11:34:18','2026-11-17 11:34:18'),
(15,'market-vendors','Market Vendors','2026-11-17 11:34:18','2026-11-17 11:34:18'),
(16,'kacita','KACITA','2026-11-17 11:34:18','2026-11-17 11:34:18'),
(17,'trade-order','Trade Order','2026-11-17 11:34:18','2026-11-17 11:34:18'),
(18,'diplomatic','Diplomatic','2026-11-17 11:34:19','2026-11-17 11:34:19'),
(19,'climate-finance','Climate Finance','2026-11-17 11:34:19','2026-11-17 11:34:19'),
(20,'mayors-forum','Mayors Forum','2026-11-17 11:34:19','2026-11-17 11:34:19');
/*!40000 ALTER TABLE `document_tags` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `documents`
--

DROP TABLE IF EXISTS `documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `documents` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL,
  `internal_reference` varchar(40) NOT NULL,
  `official_reference` varchar(150) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `organization` varchar(200) NOT NULL,
  `sender` varchar(200) DEFAULT NULL,
  `recipient` varchar(200) DEFAULT NULL,
  `direction` varchar(20) NOT NULL DEFAULT 'incoming',
  `document_type` varchar(50) NOT NULL DEFAULT 'letter',
  `event_date` datetime DEFAULT NULL,
  `date_issued` date DEFAULT NULL,
  `date_received` date NOT NULL,
  `category_id` bigint(20) unsigned NOT NULL,
  `office_id` bigint(20) unsigned NOT NULL,
  `department_id` bigint(20) unsigned DEFAULT NULL,
  `confidentiality_level` varchar(30) NOT NULL DEFAULT 'internal',
  `status` varchar(30) NOT NULL DEFAULT 'registered',
  `primary_file_path` varchar(500) DEFAULT NULL,
  `file_count` int(10) unsigned NOT NULL DEFAULT 0,
  `ocr_status` varchar(25) NOT NULL DEFAULT 'pending',
  `ocr_text` longtext DEFAULT NULL,
  `ocr_error` text DEFAULT NULL,
  `ocr_processed_at` timestamp NULL DEFAULT NULL,
  `registered_by` bigint(20) unsigned NOT NULL,
  `registered_at` timestamp NOT NULL,
  `is_archived` tinyint(1) NOT NULL DEFAULT 0,
  `archived_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `documents_uuid_unique` (`uuid`),
  UNIQUE KEY `documents_internal_reference_unique` (`internal_reference`),
  KEY `documents_category_id_foreign` (`category_id`),
  KEY `documents_department_id_foreign` (`department_id`),
  KEY `documents_registered_by_foreign` (`registered_by`),
  KEY `idx_docs_office_archive_date` (`office_id`,`is_archived`,`date_received`),
  KEY `idx_docs_duplicate_check` (`official_reference`,`organization`),
  KEY `documents_official_reference_index` (`official_reference`),
  KEY `documents_title_index` (`title`),
  KEY `documents_organization_index` (`organization`),
  KEY `documents_sender_index` (`sender`),
  KEY `documents_recipient_index` (`recipient`),
  KEY `documents_direction_index` (`direction`),
  KEY `documents_document_type_index` (`document_type`),
  KEY `documents_event_date_index` (`event_date`),
  KEY `documents_date_issued_index` (`date_issued`),
  KEY `documents_date_received_index` (`date_received`),
  KEY `documents_confidentiality_level_index` (`confidentiality_level`),
  KEY `documents_status_index` (`status`),
  KEY `documents_ocr_status_index` (`ocr_status`),
  KEY `documents_registered_at_index` (`registered_at`),
  KEY `documents_is_archived_index` (`is_archived`),
  FULLTEXT KEY `ft_documents_search` (`official_reference`,`title`,`description`,`organization`,`sender`,`recipient`,`ocr_text`),
  CONSTRAINT `documents_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `document_categories` (`id`),
  CONSTRAINT `documents_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `documents_office_id_foreign` FOREIGN KEY (`office_id`) REFERENCES `offices` (`id`),
  CONSTRAINT `documents_registered_by_foreign` FOREIGN KEY (`registered_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `documents`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `documents` WRITE;
/*!40000 ALTER TABLE `documents` DISABLE KEYS */;
INSERT INTO `documents` VALUES
(1,'a83fcfc3-bb8d-464b-8ff6-e6b6cd77d381','LMO-2026-00001','MKCCMA/PS/2026/084','Official Invitation to the Greater Kampala Metropolitan Infrastructure & Drainage Summit 2026','Formal invitation addressed to the Right Honourable Lord Mayor to deliver the opening keynote address on urban flood resilience and municipal road upgrading at Serena Conference Centre.','Ministry of Kampala Capital City and Metropolitan Affairs','Permanent Secretary, MKCCMA','The Right Honourable Lord Mayor','incoming','invitation','2026-11-29 09:00:00','2026-11-15','2026-11-17',1,1,2,'public','registered',NULL,1,'completed','--- [Attachment #1: MKCCMA_PS_2026_084.pdf] ---\nLORD MAYOR’S OFFICE - OFFICIAL DIGITAL RECORD\nOfficial Reference: MKCCMA/PS/2026/084\nSubject: Official Invitation to the Greater Kampala Metropolitan Infrastructure Drainage Summ\n------------------------------------------------------------------------\nREPUBLIC OF UGANDA - MINISTRY OF KAMPALA CAPITAL CITY AND METROPOLITAN AFFAIRS.\nRef: MKCCMA/PS/2026/084. Subject: Official Invitation to the Greater Kampala Met\nropolitan Infrastructure and Drainage Summit 2026. The Ministry cordially invite\ns the Right Honourable Lord Mayor to officiate at the opening session concerning\n Nakivubo Channel rehabilitation and solar street lighting across all five urban\n divisions.',NULL,'2026-11-17 11:34:18',3,'2026-11-17 11:34:17',0,NULL,'2026-11-17 11:34:17','2026-11-17 11:34:18'),
(2,'2acbff4a-6bf4-4850-9876-12671c0d0032','LMO-2026-00002','KCCA/ED/FIN/2026/319','Submission of Q2 Municipal Road Maintenance & Street Lighting Expenditure Report FY 2026/27','Quarterly financial and engineering progress report covering tarmac restoration, drainage desilting, and solar lighting installations across Central, Kawempe, Makindye, Nakawa, and Rubaga divisions.','Kampala Capital City Authority — Directorate of Engineering & Technical Services','Director of Engineering & Technical Services','The Right Honourable Lord Mayor','incoming','report',NULL,'2026-11-13','2026-11-16',5,1,4,'internal','registered',NULL,1,'completed','--- [Attachment #1: KCCA_ED_FIN_2026_319.pdf] ---\nLORD MAYOR’S OFFICE - OFFICIAL DIGITAL RECORD\nOfficial Reference: KCCA/ED/FIN/2026/319\nSubject: Submission of Q2 Municipal Road Maintenance Street Lighting Expenditure Report FY 20\n------------------------------------------------------------------------\nKAMPALA CAPITAL CITY AUTHORITY. Ref: KCCA/ED/FIN/2026/319. Quarterly Engineering\n and Financial Report FY 2026/27. Total disbursement for road patching and culve\nrt installation reached UGX 14.8 Billion with 92 percent physical completion ver\nified by municipal engineers.',NULL,'2026-11-17 11:34:18',3,'2026-11-17 11:34:18',0,NULL,'2026-11-17 11:34:18','2026-11-17 11:34:18'),
(3,'32215b7e-b689-461e-9cb3-8d036de46226','LMO-2026-00003','LMO/LEG/CONF/2026/017','Confidential Legal Advisory on Municipal Property Valuation & Commercial Lease Renewals','Strictly privileged legal opinion regarding ongoing arbitration and lease renewal terms for prime municipal assets.','Directorate of Legal Affairs','Principal Legal Counsel','The Right Honourable Lord Mayor','incoming','legal_notice',NULL,'2026-11-14','2026-11-17',3,1,3,'strictly_confidential','registered',NULL,1,'completed','--- [Attachment #1: LMO_LEG_CONF_2026_017.pdf] ---\nLORD MAYOR’S OFFICE - OFFICIAL DIGITAL RECORD\nOfficial Reference: LMO/LEG/CONF/2026/017\nSubject: Confidential Legal Advisory on Municipal Property Valuation Commercial Lease Renewal\n------------------------------------------------------------------------\nSTRICTLY CONFIDENTIAL LEGAL PRIVILEGE. Ref: LMO/LEG/CONF/2026/017. Legal Advisor\ny on Municipal Commercial Lease Renewals and Valuation Tribunal proceedings. Una\nuthorized disclosure is strictly prohibited.',NULL,'2026-11-17 11:34:18',3,'2026-11-17 11:34:18',0,NULL,'2026-11-17 11:34:18','2026-11-17 11:34:18'),
(4,'4e50d5ed-2619-4488-a69c-0bceda5e5f34','LMO-2026-00004','KACITA/PET/2026/052','Joint Petition by Downtown Traders & Market Vendors on Trade Order and Loading Zones','Petition signed by representatives of St. Balikuddembe Market and downtown arcade traders requesting a consultative meeting with the Lord Mayor.','Kampala City Traders Association (KACITA) & United Market Vendors','Chairperson, KACITA Uganda','The Right Honourable Lord Mayor','incoming','petition',NULL,'2026-11-11','2026-11-12',4,1,1,'public','registered',NULL,1,'completed','--- [Attachment #1: KACITA_PET_2026_052.pdf] ---\nLORD MAYOR’S OFFICE - OFFICIAL DIGITAL RECORD\nOfficial Reference: KACITA/PET/2026/052\nSubject: Joint Petition by Downtown Traders Market Vendors on Trade Order and Loading Zones\n------------------------------------------------------------------------\nPETITION TO THE LORD MAYOR. Ref: KACITA/PET/2026/052. Downtown traders and marke\nt vendors respectfully request a stakeholder dialogue on designated delivery hou\nrs and sanitation fees.',NULL,'2026-11-17 11:34:19',3,'2026-11-17 11:34:18',0,NULL,'2026-11-17 11:34:18','2026-11-17 11:34:19'),
(5,'e02f109c-d55e-4d42-8f94-1d7b93c47e0c','LMO-2026-00005','EACF/INV/2026/112','Diplomatic Invitation: East African Mayors Forum on Climate Finance & Green Cities','Invitation for the Lord Mayor and delegation to attend the regional mayors roundtable on urban greening and solid waste recycling.','East African Local Governments Forum Secretariat','Secretary General, EALGF','The Right Honourable Lord Mayor','incoming','invitation','2026-12-12 10:00:00','2026-11-02','2026-11-07',1,1,2,'internal','archived',NULL,1,'completed','--- [Attachment #1: EACF_INV_2026_112.pdf] ---\nLORD MAYOR’S OFFICE - OFFICIAL DIGITAL RECORD\nOfficial Reference: EACF/INV/2026/112\nSubject: Diplomatic Invitation: East African Mayors Forum on Climate Finance Green Cities\n------------------------------------------------------------------------\nEAST AFRICAN LOCAL GOVERNMENTS FORUM. Ref: EACF/INV/2026/112. Official Invitatio\nn to the East African Mayors Roundtable on Climate Finance and Solid Waste Manag\nement.',NULL,'2026-11-17 11:34:19',3,'2026-11-17 11:34:19',1,'2026-11-17 11:34:19','2026-11-17 11:34:19','2026-11-17 11:34:19');
/*!40000 ALTER TABLE `documents` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` varchar(255) NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` smallint(5) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES
(1,'0001_01_01_000000_create_users_table',1),
(2,'0001_01_01_000001_create_cache_table',1),
(3,'0001_01_01_000002_create_jobs_table',1),
(4,'0001_01_01_000003_create_drms_core_tables',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) unsigned NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `offices`
--

DROP TABLE IF EXISTS `offices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `offices` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(150) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `offices_code_unique` (`code`),
  KEY `offices_name_index` (`name`),
  KEY `offices_is_active_index` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `offices`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `offices` WRITE;
/*!40000 ALTER TABLE `offices` DISABLE KEYS */;
INSERT INTO `offices` VALUES
(1,'LMO','Lord Mayor\'s Office','Executive Office of the Right Honourable Lord Mayor, City Hall.','City Hall, 2nd Floor, Executive Wing',1,'2026-11-17 11:34:15','2026-11-17 11:34:15'),
(2,'DLMO','Deputy Lord Mayor\'s Office','Office of the Deputy Lord Mayor.','City Hall, 2nd Floor, East Wing',1,'2026-11-17 11:34:15','2026-11-17 11:34:15'),
(3,'CSEC','Council Speaker & Secretariat Office','City Council Speaker and Legislative Records Liaison.','City Hall, 1st Floor, Council Chambers',1,'2026-11-17 11:34:15','2026-11-17 11:34:15');
/*!40000 ALTER TABLE `offices` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(80) NOT NULL,
  `name` varchar(120) NOT NULL,
  `module` varchar(60) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_slug_unique` (`slug`),
  KEY `permissions_module_index` (`module`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES
(1,'documents.view','View Authorized Documents','documents',NULL,'2026-11-17 11:34:15','2026-11-17 11:34:15'),
(2,'documents.create','Register & Scan New Documents','documents',NULL,'2026-11-17 11:34:15','2026-11-17 11:34:15'),
(3,'documents.edit','Edit Document Metadata','documents',NULL,'2026-11-17 11:34:15','2026-11-17 11:34:15'),
(4,'documents.download','Preview & Download Attached Files','documents',NULL,'2026-11-17 11:34:15','2026-11-17 11:34:15'),
(5,'documents.view_confidential','View Confidential Records','security',NULL,'2026-11-17 11:34:15','2026-11-17 11:34:15'),
(6,'documents.view_strictly_confidential','View Strictly Confidential Records','security',NULL,'2026-11-17 11:34:15','2026-11-17 11:34:15'),
(7,'documents.archive','Archive Documents to Vault','archives',NULL,'2026-11-17 11:34:15','2026-11-17 11:34:15'),
(8,'documents.restore','Restore Archived Documents','archives',NULL,'2026-11-17 11:34:15','2026-11-17 11:34:15'),
(9,'documents.assign','Assign & Route Documents for Action','workflow',NULL,'2026-11-17 11:34:15','2026-11-17 11:34:15'),
(10,'documents.approve','Approve / Endorse Documents','workflow',NULL,'2026-11-17 11:34:15','2026-11-17 11:34:15'),
(11,'ocr.retry','Trigger / Retry OCR Processing','ocr',NULL,'2026-11-17 11:34:16','2026-11-17 11:34:16'),
(12,'reports.view','View Institutional Reports','reports',NULL,'2026-11-17 11:34:16','2026-11-17 11:34:16'),
(13,'reports.export','Export PDF & Excel/CSV Reports','reports',NULL,'2026-11-17 11:34:16','2026-11-17 11:34:16'),
(14,'audit.view','Inspect Immutable Audit Logs','security',NULL,'2026-11-17 11:34:16','2026-11-17 11:34:16'),
(15,'users.manage','Manage Staff Accounts & Roles','administration',NULL,'2026-11-17 11:34:16','2026-11-17 11:34:16'),
(16,'settings.manage','Manage System Settings & Backups','administration',NULL,'2026-11-17 11:34:16','2026-11-17 11:34:16');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `role_permission`
--

DROP TABLE IF EXISTS `role_permission`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_permission` (
  `role_id` bigint(20) unsigned NOT NULL,
  `permission_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `role_permission_permission_id_foreign` (`permission_id`),
  CONSTRAINT `role_permission_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_permission_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_permission`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `role_permission` WRITE;
/*!40000 ALTER TABLE `role_permission` DISABLE KEYS */;
INSERT INTO `role_permission` VALUES
(1,1),
(2,1),
(3,1),
(4,1),
(5,1),
(1,2),
(2,2),
(3,2),
(1,3),
(2,3),
(3,3),
(1,4),
(2,4),
(3,4),
(4,4),
(5,4),
(1,5),
(2,5),
(4,5),
(1,6),
(2,6),
(4,6),
(1,7),
(2,7),
(1,8),
(2,8),
(1,9),
(2,9),
(4,9),
(1,10),
(4,10),
(1,11),
(2,11),
(3,11),
(1,12),
(2,12),
(3,12),
(4,12),
(1,13),
(2,13),
(4,13),
(1,14),
(2,14),
(4,14),
(1,15),
(1,16);
/*!40000 ALTER TABLE `role_permission` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(60) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_system` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES
(1,'system_admin','System Administrator','Full institutional system, user, audit, and backup administration.',1,'2026-11-17 11:34:16','2026-11-17 11:34:16'),
(2,'records_manager','Records Manager','Oversees the Registry, OCR queue, confidential records, digital archiving, and reports.',1,'2026-11-17 11:34:16','2026-11-17 11:34:16'),
(3,'records_officer','Records Officer','Day-to-day scanning, document registration, file upload, and search.',1,'2026-11-17 11:34:16','2026-11-17 11:34:16'),
(4,'office_manager','Office Manager / Executive','Executive leadership access to all office correspondence, confidential records, routing, and audit trails.',1,'2026-11-17 11:34:16','2026-11-17 11:34:16'),
(5,'read_only','Read-Only User','Authorized to search and view Public/Internal office records only.',1,'2026-11-17 11:34:16','2026-11-17 11:34:16');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES
('qpya19XqeJeaxbSJdXuq6kNrhHRwbuX0ZO9iFwRq',NULL,'127.0.0.1','Mozilla/5.0 (Linux; Android 14; SM-S938B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36','ZXlKcGRpSTZJa2cyYldkT2RHNXFPRTl6VGxreVVYWmlaSFpRY25jOVBTSXNJblpoYkhWbElqb2lNQzh6SzFGNE16a3hhamcyUkVSdVlXTnhaalYwWlVWd2QwNHZORXhzVURaSmRGTjNTbTFsVldzNGExRldaR0ZXUVVoS1kxWk9ZbXQ1ZWxBd0wyWnBTMnN6TTA1SmNqbHNZbGs0T0U5aVUwSjJiVWxHY1dKUVJUZENNR2RPVGxWSVJHbFVWMWwzUXpOa1NVNVZVM1ZuTlU5V2JrSlVkbTFOWlZGb01qWXdURE5HYlUweVEwczNWblZHTkdkR1dIVkZVRXRPT1hoT1pWTlNMM0JKVDJFcllVOXJjVVZ1THpodmIzYzVOR1ppU2xod1lXaGtUa1ZYUTFOUGVWSnVVa3RDYjNKUUt6VlpPR1JyWjNac0wyUjJVM0JPZGl0eVlWRjZlWFIxTmxGRmNqVnRhblJpWW5OeFpGaHJSVDBpTENKdFlXTWlPaUkwTjJabE1HUXdORGMwWlRrd1pHVXpOMk5qTTJJM1lqUTRZekU1WW1GbE5tTmpNV1JoWWpObU5qRTJZekV3TmpNMlltSTNaak5qWW1Nd056UmhNR0U1SWl3aWRHRm5Jam9pSW4wPQ==',1794927776),
('VzLxGoE7VFMZIvQnpM2MUxo8dqijYB9CkQJO2LFJ',NULL,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.145.2 Chrome/150.0.7871.250 Electron/43.8.3 Safari/537.36','ZXlKcGRpSTZJalJhZW5CSlltZEJUMGt6U3paUGNpOHpUMmwzVldjOVBTSXNJblpoYkhWbElqb2lRbmxXYVVKQ1MxRjBZbU4zWVZkSFZFOHJZVmwyVUdWc05WQk1lbmQxWlZneVNqWXdSa1JpZEROeE56Tm5OVEExWWpkalJqTkVVM0J0UWpaT1pHeHdWakpUVkRaMldsaEJPV0pvTmpGTFJpOURVMlJCT0hwTGVVeHVOQ3RzWTB4emNHeENMMHhIT0daTVlXWkJNRTlOUnpkbFNHUmFUVzk1WjNCSmNHMDNMMlZ3VW1odmFIRldaV3RoU21SR2RVaFJZbnB2Vm1zMU1ISm9LMHhDZUhWRmJrRlFSVFZzZVhORU0yTm1TMVpWTUVsbVdsaENMMDV3UlU4NUwyc3pkMGNyVmtoYU1YWk9WMnROYTB4T1NsaHdSV3RSTmpKRFVUMDlJaXdpYldGaklqb2lZemM1TURBek9HWm1aREk1TW1KaFlXRTJPRGxtWlRZNU9UUXlaVFJpWmpVME5EWmpOakpqTldNeU5ETmlNV0U0TURrMU1EYzJPRFF3WmpGbFlXWm1NU0lzSW5SaFp5STZJaUo5',1794926798);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `system_settings`
--

DROP TABLE IF EXISTS `system_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `type` varchar(30) NOT NULL DEFAULT 'string',
  `group` varchar(50) NOT NULL DEFAULT 'general',
  `description` varchar(255) DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `system_settings_key_unique` (`key`),
  KEY `system_settings_updated_by_foreign` (`updated_by`),
  KEY `system_settings_group_index` (`group`),
  CONSTRAINT `system_settings_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_settings`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `system_settings` WRITE;
/*!40000 ALTER TABLE `system_settings` DISABLE KEYS */;
INSERT INTO `system_settings` VALUES
(1,'institution_name','Lord Mayor\'s Office (LMO)','string','general','Primary Institutional Title',NULL,'2026-11-17 11:34:17','2026-11-17 11:34:17');
/*!40000 ALTER TABLE `system_settings` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `office_id` bigint(20) unsigned DEFAULT NULL,
  `department_id` bigint(20) unsigned DEFAULT NULL,
  `role_id` bigint(20) unsigned DEFAULT NULL,
  `employee_number` varchar(50) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `mfa_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `mfa_secret` text DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_employee_number_unique` (`employee_number`),
  KEY `users_office_id_foreign` (`office_id`),
  KEY `users_department_id_foreign` (`department_id`),
  KEY `users_role_id_foreign` (`role_id`),
  KEY `users_name_index` (`name`),
  KEY `users_is_active_index` (`is_active`),
  CONSTRAINT `users_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_office_id_foreign` FOREIGN KEY (`office_id`) REFERENCES `offices` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(1,1,4,1,'LMO-IT-001','Eng. Daniel Kato (System Admin)','Principal ICT & Security Officer','admin@lmo.go.ug','+256 772 100001',NULL,'$2y$12$jLnP/NKACApz/Sczpie8VOBCgYN7uTc87ECsG/VcO/xKtdJzxtQYC',1,0,NULL,'2026-11-17 12:02:24','127.0.0.1',NULL,'2026-11-17 11:34:17','2026-11-17 12:02:24'),
(2,1,1,2,'LMO-REG-002','Grace Nabirye (Records Manager)','Chief Records Manager','manager@lmo.go.ug','+256 772 100002',NULL,'$2y$12$jLnP/NKACApz/Sczpie8VOBCgYN7uTc87ECsG/VcO/xKtdJzxtQYC',1,0,NULL,'2026-11-17 11:58:05','127.0.0.1',NULL,'2026-11-17 11:34:17','2026-11-17 11:58:05'),
(3,1,1,3,'LMO-REG-003','Samuel Mugisha (Records Officer)','Registry & Scanning Officer','officer@lmo.go.ug','+256 772 100003',NULL,'$2y$12$jLnP/NKACApz/Sczpie8VOBCgYN7uTc87ECsG/VcO/xKtdJzxtQYC',1,0,NULL,NULL,NULL,NULL,'2026-11-17 11:34:17','2026-11-17 11:34:17'),
(4,1,4,4,'LMO-EXEC-004','Patricia Achieng (Office Manager)','Principal Private Secretary / Office Manager','executive@lmo.go.ug','+256 772 100004',NULL,'$2y$12$jLnP/NKACApz/Sczpie8VOBCgYN7uTc87ECsG/VcO/xKtdJzxtQYC',1,0,NULL,'2026-11-17 12:01:27','127.0.0.1',NULL,'2026-11-17 11:34:17','2026-11-17 12:01:27'),
(5,1,2,5,'LMO-PROT-005','Ronald Ssemanda (Read-Only Staff)','Protocol Assistant','readonly@lmo.go.ug','+256 772 100005',NULL,'$2y$12$jLnP/NKACApz/Sczpie8VOBCgYN7uTc87ECsG/VcO/xKtdJzxtQYC',1,0,NULL,'2026-11-17 12:01:58','127.0.0.1',NULL,'2026-11-17 11:34:17','2026-11-17 12:01:58'),
(6,2,5,3,'DLMO-REG-006','Brenda Atim (DLMO Records Officer)','Deputy Office Records Officer','deputy.records@lmo.go.ug','+256 772 100006',NULL,'$2y$12$jLnP/NKACApz/Sczpie8VOBCgYN7uTc87ECsG/VcO/xKtdJzxtQYC',1,0,NULL,NULL,NULL,NULL,'2026-11-17 11:34:17','2026-11-17 11:34:17');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2026-11-17 18:16:21
