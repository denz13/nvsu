-- MySQL dump 10.13  Distrib 8.0.43, for Win64 (x86_64)
--
-- Host: localhost    Database: midone
-- ------------------------------------------------------
-- Server version	8.0.43

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `announcement`
--

DROP TABLE IF EXISTS `announcement`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `announcement` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `announcement`
--

LOCK TABLES `announcement` WRITE;
/*!40000 ALTER TABLE `announcement` DISABLE KEYS */;
INSERT INTO `announcement` VALUES (1,'cd csdcsd','zzzz','active','2025-10-31 20:55:08','2025-10-31 20:58:23',NULL),(2,'csdcsdcsd','csdcsdcsd','active','2025-10-31 20:58:30','2025-10-31 20:59:08','2025-10-31 20:59:08');
/*!40000 ALTER TABLE `announcement` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attendance_payments`
--

DROP TABLE IF EXISTS `attendance_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attendance_payments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `students_id` varchar(45) DEFAULT NULL,
  `events_id` varchar(45) DEFAULT NULL,
  `amount_paid` varchar(45) DEFAULT NULL,
  `payment_status` varchar(45) DEFAULT NULL,
  `waiver_reason` varchar(255) DEFAULT NULL,
  `waiver_attachments` varchar(255) DEFAULT NULL,
  `waiver_amount` varchar(255) DEFAULT NULL,
  `status` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendance_payments`
--

LOCK TABLES `attendance_payments` WRITE;
/*!40000 ALTER TABLE `attendance_payments` DISABLE KEYS */;
INSERT INTO `attendance_payments` VALUES (1,'1','2','40','approved','ccc',NULL,'10','active','2025-11-01 00:43:49','2025-11-01 00:44:57',NULL);
/*!40000 ALTER TABLE `attendance_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attendance_payments_time_schedule`
--

DROP TABLE IF EXISTS `attendance_payments_time_schedule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attendance_payments_time_schedule` (
  `id` int NOT NULL AUTO_INCREMENT,
  `attendance_payments_id` varchar(255) DEFAULT NULL,
  `type_of_schedule_pay` varchar(255) DEFAULT NULL,
  `log_time` datetime DEFAULT NULL,
  `workstate` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendance_payments_time_schedule`
--

LOCK TABLES `attendance_payments_time_schedule` WRITE;
/*!40000 ALTER TABLE `attendance_payments_time_schedule` DISABLE KEYS */;
INSERT INTO `attendance_payments_time_schedule` VALUES (1,'1','afternoon','2025-10-01 12:48:00','0','active','2025-11-01 00:43:49','2025-11-01 00:43:49',NULL),(2,'1','afternoon','2025-10-01 16:48:00','1','active','2025-11-01 00:43:49','2025-11-01 00:43:49',NULL);
/*!40000 ALTER TABLE `attendance_payments_time_schedule` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `college`
--

DROP TABLE IF EXISTS `college`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `college` (
  `id` int NOT NULL AUTO_INCREMENT,
  `college_name` varchar(255) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `college`
--

LOCK TABLES `college` WRITE;
/*!40000 ALTER TABLE `college` DISABLE KEYS */;
INSERT INTO `college` VALUES (1,'sdccsdcsdcsdcsd','storage/colleges/1761446444_Gemini_Generated_Image_eb9ftkeb9ftkeb9f.png','active','2025-10-25 18:40:44','2025-10-25 18:40:44',NULL);
/*!40000 ALTER TABLE `college` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `events` (
  `id` int NOT NULL AUTO_INCREMENT,
  `event_name` varchar(255) DEFAULT NULL,
  `semester_id` varchar(255) DEFAULT NULL,
  `event_description` varchar(255) DEFAULT NULL,
  `start_datetime_morning` datetime DEFAULT NULL,
  `end_datetime_morning` datetime DEFAULT NULL,
  `start_datetime_afternoon` datetime DEFAULT NULL,
  `end_datetime_afternoon` datetime DEFAULT NULL,
  `fines` varchar(255) DEFAULT NULL,
  `event_schedule_type` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `events`
--

LOCK TABLES `events` WRITE;
/*!40000 ALTER TABLE `events` DISABLE KEYS */;
INSERT INTO `events` VALUES (1,'Kieran Koch','1','Esse quia quos sequ',NULL,NULL,'1970-03-13 13:00:00','1974-03-26 16:31:00','32','half_day_afternoon','active','2025-10-30 17:47:38','2025-10-30 17:47:38',NULL),(2,'testing','1','testing','2025-10-01 09:49:00','2025-10-01 10:47:00','2025-10-01 12:48:00','2025-10-01 16:48:00','100','whole_day','active','2025-10-30 17:48:45','2025-10-30 17:48:45',NULL);
/*!40000 ALTER TABLE `events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `events_assign_participants`
--

DROP TABLE IF EXISTS `events_assign_participants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `events_assign_participants` (
  `id` int NOT NULL AUTO_INCREMENT,
  `events_id` varchar(255) DEFAULT NULL,
  `college_id` varchar(255) DEFAULT NULL,
  `program_id` varchar(255) DEFAULT NULL,
  `organization_id` varchar(255) DEFAULT NULL,
  `semester_id` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `events_assign_participants`
--

LOCK TABLES `events_assign_participants` WRITE;
/*!40000 ALTER TABLE `events_assign_participants` DISABLE KEYS */;
INSERT INTO `events_assign_participants` VALUES (1,'2','1','1',NULL,'1','active','2025-10-30 17:50:22','2025-10-30 17:50:22',NULL);
/*!40000 ALTER TABLE `events_assign_participants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `events_lates_deduction`
--

DROP TABLE IF EXISTS `events_lates_deduction`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `events_lates_deduction` (
  `id` int NOT NULL AUTO_INCREMENT,
  `events_id` varchar(255) DEFAULT NULL,
  `time_in_morning` time DEFAULT NULL,
  `time_out_morning` time DEFAULT NULL,
  `time_in_afternoon` time DEFAULT NULL,
  `time_out_afternoon` time DEFAULT NULL,
  `late_penalty` varchar(255) DEFAULT NULL,
  `semester_id` varchar(45) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `events_lates_deduction`
--

LOCK TABLES `events_lates_deduction` WRITE;
/*!40000 ALTER TABLE `events_lates_deduction` DISABLE KEYS */;
INSERT INTO `events_lates_deduction` VALUES (1,'2','09:55:00','10:49:00','12:55:00','17:55:00','12.50','1','active','2025-10-30 17:56:36','2025-10-30 17:56:36',NULL);
/*!40000 ALTER TABLE `events_lates_deduction` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `events_list_of_participants`
--

DROP TABLE IF EXISTS `events_list_of_participants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `events_list_of_participants` (
  `id` int NOT NULL AUTO_INCREMENT,
  `events_assign_participants_id` varchar(255) DEFAULT NULL,
  `students_id` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `events_list_of_participants`
--

LOCK TABLES `events_list_of_participants` WRITE;
/*!40000 ALTER TABLE `events_list_of_participants` DISABLE KEYS */;
INSERT INTO `events_list_of_participants` VALUES (1,'1','1','active','2025-10-30 17:50:22','2025-10-30 17:50:22',NULL);
/*!40000 ALTER TABLE `events_list_of_participants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `generated_receipt`
--

DROP TABLE IF EXISTS `generated_receipt`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `generated_receipt` (
  `id` int NOT NULL AUTO_INCREMENT,
  `attendance_payments_id` varchar(45) DEFAULT NULL,
  `official_receipts` varchar(255) DEFAULT NULL,
  `status` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `generated_receipt`
--

LOCK TABLES `generated_receipt` WRITE;
/*!40000 ALTER TABLE `generated_receipt` DISABLE KEYS */;
INSERT INTO `generated_receipt` VALUES (1,'1','OR-20251031-0001','active','2025-10-31 02:47:23','2025-10-31 02:47:23',NULL);
/*!40000 ALTER TABLE `generated_receipt` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `message`
--

DROP TABLE IF EXISTS `message`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `message` (
  `id` int NOT NULL AUTO_INCREMENT,
  `from_id` varchar(255) DEFAULT NULL,
  `message_content` varchar(255) DEFAULT NULL,
  `to_id` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `message`
--

LOCK TABLES `message` WRITE;
/*!40000 ALTER TABLE `message` DISABLE KEYS */;
INSERT INTO `message` VALUES (1,'1','HOY','1','read','2025-10-31 20:35:58','2025-10-31 20:35:58',NULL),(2,'1','VVDFVFDVFD','1','read','2025-10-31 20:39:27','2025-10-31 20:39:28',NULL);
/*!40000 ALTER TABLE `message` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2014_10_12_000000_create_users_table',1),(2,'2014_10_12_100000_create_password_resets_table',1),(3,'2019_08_19_000000_create_failed_jobs_table',1),(4,'2019_12_14_000001_create_personal_access_tokens_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `module`
--

DROP TABLE IF EXISTS `module`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `module` (
  `id` int NOT NULL AUTO_INCREMENT,
  `module` varchar(255) DEFAULT NULL,
  `status` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `module`
--

LOCK TABLES `module` WRITE;
/*!40000 ALTER TABLE `module` DISABLE KEYS */;
INSERT INTO `module` VALUES (1,'Chat','active','2025-11-01 05:27:29',NULL,NULL),(2,'Calendar','active','2025-11-01 05:27:29',NULL,NULL),(3,'Announcement','active','2025-11-01 05:27:29',NULL,NULL),(4,'Information','active','2025-11-01 05:27:29',NULL,NULL),(5,'Events','active','2025-11-01 05:27:29',NULL,NULL),(6,'User Management','active','2025-11-01 05:27:29',NULL,NULL),(7,'Scanner','active','2025-11-01 05:27:29',NULL,NULL),(8,'Attendance Management','active','2025-11-01 05:27:29',NULL,NULL),(9,'Settings','active','2025-11-01 05:27:29',NULL,NULL);
/*!40000 ALTER TABLE `module` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `organization`
--

DROP TABLE IF EXISTS `organization`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `organization` (
  `id` int NOT NULL AUTO_INCREMENT,
  `organization_name` varchar(255) DEFAULT NULL,
  `organization_description` varchar(255) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `organization`
--

LOCK TABLES `organization` WRITE;
/*!40000 ALTER TABLE `organization` DISABLE KEYS */;
INSERT INTO `organization` VALUES (1,'aaaa','xasxaxass','storage/organizations/1761442618_Gemini_Generated_Image_eb9ftkeb9ftkeb9f.png','active','2025-10-25 17:36:58','2025-10-25 17:37:07',NULL),(2,'csdcsd','csdcsdcsdcsd','storage/organizations/1761442637_482349334_1157297672764892_1296471932904069408_n.jpg','active','2025-10-25 17:37:17','2025-10-25 17:37:23','2025-10-25 17:37:23');
/*!40000 ALTER TABLE `organization` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permission_settings`
--

DROP TABLE IF EXISTS `permission_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permission_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `users_id` varchar(45) DEFAULT NULL,
  `students_id` varchar(45) DEFAULT NULL,
  `status` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permission_settings`
--

LOCK TABLES `permission_settings` WRITE;
/*!40000 ALTER TABLE `permission_settings` DISABLE KEYS */;
INSERT INTO `permission_settings` VALUES (1,NULL,'1','active','2025-10-31 21:40:42','2025-10-31 21:40:42',NULL),(2,'1',NULL,'active','2025-11-01 00:30:31','2025-11-01 00:30:31',NULL);
/*!40000 ALTER TABLE `permission_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permission_settings_list`
--

DROP TABLE IF EXISTS `permission_settings_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permission_settings_list` (
  `id` int NOT NULL AUTO_INCREMENT,
  `permission_settings_id` varchar(45) DEFAULT NULL,
  `module_id` varchar(45) DEFAULT NULL,
  `status` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permission_settings_list`
--

LOCK TABLES `permission_settings_list` WRITE;
/*!40000 ALTER TABLE `permission_settings_list` DISABLE KEYS */;
INSERT INTO `permission_settings_list` VALUES (1,'1','3','active','2025-10-31 21:40:42','2025-10-31 21:46:22','2025-10-31 21:46:22'),(2,'1','8','active','2025-10-31 21:40:42','2025-10-31 21:46:22','2025-10-31 21:46:22'),(3,'1','2','active','2025-10-31 21:40:42','2025-10-31 21:46:22','2025-10-31 21:46:22'),(4,'1','1','active','2025-10-31 21:40:42','2025-10-31 21:46:22','2025-10-31 21:46:22'),(5,'1','4','active','2025-10-31 21:40:42','2025-10-31 21:46:22','2025-10-31 21:46:22'),(6,'1','7','active','2025-10-31 21:40:42','2025-10-31 21:46:22','2025-10-31 21:46:22'),(7,'1','9','active','2025-10-31 21:40:42','2025-10-31 21:46:22','2025-10-31 21:46:22'),(8,'1','6','active','2025-10-31 21:40:42','2025-10-31 21:46:22','2025-10-31 21:46:22'),(9,'1','3','active','2025-10-31 21:46:22','2025-10-31 21:46:22',NULL),(10,'1','8','active','2025-10-31 21:46:22','2025-10-31 21:46:22',NULL),(11,'1','2','active','2025-10-31 21:46:22','2025-10-31 21:46:22',NULL),(12,'1','4','active','2025-10-31 21:46:22','2025-10-31 21:46:22',NULL),(13,'1','7','active','2025-10-31 21:46:22','2025-10-31 21:46:22',NULL),(14,'1','9','active','2025-10-31 21:46:22','2025-10-31 21:46:22',NULL),(15,'1','6','active','2025-10-31 21:46:22','2025-10-31 21:46:22',NULL),(16,'2','3','active','2025-11-01 00:30:31','2025-11-01 00:30:31',NULL),(17,'2','8','active','2025-11-01 00:30:31','2025-11-01 00:30:31',NULL),(18,'2','2','active','2025-11-01 00:30:31','2025-11-01 00:30:31',NULL),(19,'2','1','active','2025-11-01 00:30:31','2025-11-01 00:30:31',NULL),(20,'2','5','active','2025-11-01 00:30:31','2025-11-01 00:30:31',NULL),(21,'2','4','active','2025-11-01 00:30:31','2025-11-01 00:30:31',NULL),(22,'2','7','active','2025-11-01 00:30:31','2025-11-01 00:30:31',NULL),(23,'2','9','active','2025-11-01 00:30:31','2025-11-01 00:30:31',NULL),(24,'2','6','active','2025-11-01 00:30:31','2025-11-01 00:30:31',NULL);
/*!40000 ALTER TABLE `permission_settings_list` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `program`
--

DROP TABLE IF EXISTS `program`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `program` (
  `id` int NOT NULL AUTO_INCREMENT,
  `college_id` varchar(45) DEFAULT NULL,
  `program_name` varchar(255) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `program`
--

LOCK TABLES `program` WRITE;
/*!40000 ALTER TABLE `program` DISABLE KEYS */;
INSERT INTO `program` VALUES (1,'1','ccdscsdcsd','storage/programs/1761446704_Gemini_Generated_Image_eb9ftkeb9ftkeb9f.png','active','2025-10-25 18:45:04','2025-10-25 18:45:04',NULL);
/*!40000 ALTER TABLE `program` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `semester`
--

DROP TABLE IF EXISTS `semester`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `semester` (
  `id` int NOT NULL AUTO_INCREMENT,
  `school_year` varchar(255) DEFAULT NULL,
  `semester` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `semester`
--

LOCK TABLES `semester` WRITE;
/*!40000 ALTER TABLE `semester` DISABLE KEYS */;
INSERT INTO `semester` VALUES (1,'2024-2025','1st Semester','active','2025-10-25 17:45:06','2025-10-25 17:45:06',NULL);
/*!40000 ALTER TABLE `semester` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `students`
--

DROP TABLE IF EXISTS `students`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `students` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_number` varchar(255) DEFAULT NULL,
  `student_name` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `year_level` varchar(255) DEFAULT NULL,
  `college_id` varchar(45) DEFAULT NULL,
  `program_id` varchar(45) DEFAULT NULL,
  `organization_id` varchar(45) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `barcode` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `students`
--

LOCK TABLES `students` WRITE;
/*!40000 ALTER TABLE `students` DISABLE KEYS */;
INSERT INTO `students` VALUES (1,'2323324','csdcdscsd','cdscds','1st Year','1','1','1','storage/students/1761986891_tubil.png','2323324csdcd','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','active','2025-10-25 18:59:31','2025-11-01 00:48:11',NULL);
/*!40000 ALTER TABLE `students` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_settings`
--

DROP TABLE IF EXISTS `system_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `key` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_settings`
--

LOCK TABLES `system_settings` WRITE;
/*!40000 ALTER TABLE `system_settings` DISABLE KEYS */;
INSERT INTO `system_settings` VALUES (1,'login_logo_center','image','storage/system_settings/Z7EqVjcpjNQFtZQncInuFjUSJ1Rnu7VuHvPG2QLl.png','active','2025-10-30 03:51:57','2025-10-29 20:30:41',NULL),(2,'login_logo_top','image','storage/system_settings/9VeRpzKYQamqlet4ZuWwFcQc3rtkU6ILbLWeLC05.png','active','2025-10-30 03:51:57','2025-10-29 20:32:35',NULL),(3,'login_logo_center_text','text','EVENTS','active','2025-10-30 03:51:57','2025-10-29 20:37:47',NULL),(4,'login_logo_top_text','text','EVENTS','active','2025-10-30 03:51:57','2025-10-29 20:37:53',NULL),(5,'sidebar_top_logo','image','storage/system_settings/6kYe73nmx0q15WUAIdkaAqloCq1QMrMQBkdjPhjF.jpg','active','2025-10-30 03:51:57','2025-11-01 00:47:07',NULL),(6,'sidebar_top_text','text','EVENTS manage','active','2025-10-30 03:51:57','2025-11-01 00:47:33',NULL);
/*!40000 ALTER TABLE `system_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_attendance`
--

DROP TABLE IF EXISTS `tbl_attendance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_attendance` (
  `id` int NOT NULL AUTO_INCREMENT,
  `event_id` int DEFAULT NULL,
  `student_id` int DEFAULT NULL,
  `log_time` datetime DEFAULT NULL,
  `workstate` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scan_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_attendance`
--

LOCK TABLES `tbl_attendance` WRITE;
/*!40000 ALTER TABLE `tbl_attendance` DISABLE KEYS */;
INSERT INTO `tbl_attendance` VALUES (1,2,1,'2025-10-01 09:54:00','0','','1','active','2025-10-31 01:59:37',NULL,NULL),(2,2,1,'2025-10-01 10:40:00','1',NULL,'1','active','2025-10-31 01:59:37',NULL,NULL),(5,2,1,'2025-11-01 08:35:53','0','Left4code','1','active','2025-11-01 00:35:53','2025-11-01 00:35:53',NULL),(6,2,1,'2025-11-01 08:35:53','1','Left4code','1','active','2025-11-01 00:35:53','2025-11-01 00:35:53',NULL);
/*!40000 ALTER TABLE `tbl_attendance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` int NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Left4code','midone@left4code.com','2025-10-25 15:56:01','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','storage/users/1761957593_tubil.png','female',1,NULL,'gTEhrf5PpP69bzAd2TL8kJ6LF1QBT7nzoYpMqelVRc1Dyjx1j5SbMHq7Kt0Y',NULL,'2025-10-31 16:48:15'),(2,'Kyler White','dayton65@example.com','2025-10-25 15:56:01','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NULL,'male',1,NULL,'T5Dcr74J5D','2025-10-25 15:56:01','2025-10-25 15:56:01'),(3,'Helga Pfannerstill','vallie00@example.org','2025-10-25 15:56:01','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NULL,'male',1,NULL,'tR8jBeqwfa','2025-10-25 15:56:01','2025-10-25 15:56:01'),(4,'Prof. Horace Conroy','damore.jensen@example.com','2025-10-25 15:56:01','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NULL,'male',1,NULL,'Vw77o801zFfc3apsPnGP8lDnvkAPydSlH9MU3NFuTzERV8jR3GgKGMNiP4Fm','2025-10-25 15:56:01','2025-10-25 15:56:01'),(5,'Jett Terry Jr.','bode.whitney@example.com','2025-10-25 15:56:01','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NULL,'male',1,NULL,'QtjiTxLb1j','2025-10-25 15:56:01','2025-10-25 15:56:01'),(6,'Guillermo Torp','van21@example.com','2025-10-25 15:56:01','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NULL,'female',1,NULL,'LEoUoEqVHL','2025-10-25 15:56:01','2025-10-25 15:56:01'),(7,'Chaz Kihn','stephania.leuschke@example.net','2025-10-25 15:56:01','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NULL,'female',1,NULL,'LQ2BI1GYyF','2025-10-25 15:56:01','2025-10-25 15:56:01'),(8,'Christopher Bauch','mariam.hagenes@example.net','2025-10-25 15:56:01','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NULL,'female',1,NULL,'iHcYUuLk3v','2025-10-25 15:56:01','2025-10-25 15:56:01'),(9,'Joanny Daugherty','jaskolski.roberto@example.org','2025-10-25 15:56:01','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NULL,'male',1,NULL,'2ZfRevXoIg','2025-10-25 15:56:01','2025-10-25 15:56:01'),(10,'Conor Ratke','albertha14@example.com','2025-10-25 15:56:01','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NULL,'male',1,NULL,'BDRm8PhV2A','2025-10-25 15:56:01','2025-10-25 15:56:01');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-12-02 22:06:21
