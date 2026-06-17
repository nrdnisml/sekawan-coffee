-- MySQL dump 10.13  Distrib 8.0.19, for Win64 (x86_64)
--
-- Host: localhost    Database: sekawan_db
-- ------------------------------------------------------
-- Server version	5.5.5-10.11.14-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `entity` varchar(255) NOT NULL,
  `entity_id` bigint(20) unsigned DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `activity_logs_user_id_foreign` (`user_id`),
  CONSTRAINT `activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_logs`
--

LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('admin@sekawan.com|127.0.0.1','i:1;',1781692531),('admin@sekawan.com|127.0.0.1:timer','i:1781692530;',1781692530);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `expenses`
--

DROP TABLE IF EXISTS `expenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `expenses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `expense_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `expenses_user_id_foreign` (`user_id`),
  CONSTRAINT `expenses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expenses`
--

LOCK TABLES `expenses` WRITE;
/*!40000 ALTER TABLE `expenses` DISABLE KEYS */;
/*!40000 ALTER TABLE `expenses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
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
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
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

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_05_28_044455_create_products_table',1),(5,'2026_05_28_044455_create_transactions_table',1),(6,'2026_05_28_044456_create_expenses_table',1),(7,'2026_05_28_044456_create_stock_movements_table',1),(8,'2026_05_28_044456_create_transaction_items_table',1),(9,'2026_05_28_044457_create_activity_logs_table',1),(10,'2026_05_28_044457_create_product_price_histories_table',1),(11,'2026_05_28_082331_add_image_url_to_products_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_price_histories`
--

DROP TABLE IF EXISTS `product_price_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_price_histories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `old_price` decimal(15,2) NOT NULL,
  `new_price` decimal(15,2) NOT NULL,
  `changed_by` bigint(20) unsigned DEFAULT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `product_price_histories_product_id_foreign` (`product_id`),
  KEY `product_price_histories_changed_by_foreign` (`changed_by`),
  CONSTRAINT `product_price_histories_changed_by_foreign` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `product_price_histories_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_price_histories`
--

LOCK TABLES `product_price_histories` WRITE;
/*!40000 ALTER TABLE `product_price_histories` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_price_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `price` decimal(15,2) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (11,'Espresso',15000.00,'Single shot espresso',NULL,65,1,'2026-05-28 08:34:15','2026-05-28 08:38:31'),(12,'Caffe Latte',25000.00,'Espresso with steamed milk',NULL,23,1,'2026-05-28 08:34:15','2026-05-28 08:38:31'),(13,'asperiores saepe',38.79,'Est consequatur cumque velit perferendis.',NULL,17,1,'2026-05-28 08:34:15','2026-05-28 08:38:31'),(14,'veniam optio',7.98,'Quia et atque ut ipsam exercitationem ut eaque sint.',NULL,73,1,'2026-05-28 08:34:15','2026-05-28 08:38:31'),(15,'laborum numquam',23.09,'Molestias eos eligendi eaque libero velit.',NULL,15,1,'2026-05-28 08:34:15','2026-05-28 08:38:31'),(16,'est rerum',10.76,'Quis autem necessitatibus veritatis vitae architecto est harum explicabo.',NULL,124,1,'2026-05-28 08:34:15','2026-05-28 08:38:31'),(17,'qui eos',28.46,'Nisi illo tempora in deleniti consectetur quidem molestias.',NULL,67,1,'2026-05-28 08:34:15','2026-05-28 08:38:31'),(18,'rerum inventore',27.66,'Tempore optio labore adipisci nemo aliquid.',NULL,68,1,'2026-05-28 08:34:15','2026-05-28 08:38:31'),(19,'veniam voluptas',2.91,'Veritatis quisquam aliquam voluptatibus facilis debitis.',NULL,71,1,'2026-05-28 08:34:15','2026-05-28 08:38:31'),(20,'non incidunt',22.46,'Quo dolorem eius fugit fugiat saepe corrupti.',NULL,45,1,'2026-05-28 08:34:15','2026-05-28 08:38:31'),(21,'optio rem',48.38,'Veniam qui similique quas et accusamus.',NULL,52,1,'2026-05-28 08:34:15','2026-05-28 08:38:31'),(22,'illo est',30.52,'Explicabo doloribus ut qui aut.',NULL,25,1,'2026-05-28 08:34:15','2026-05-28 08:38:31'),(23,'Espresso',15000.00,'Single shot espresso',NULL,91,1,'2026-05-28 08:38:30','2026-05-28 08:38:31'),(24,'Caffe Latte',25000.00,'Espresso with steamed milk',NULL,20,1,'2026-05-28 08:38:30','2026-05-28 08:38:31'),(25,'ipsam ducimus',36.83,'Rem dolore quas sint dolor.',NULL,95,1,'2026-05-28 08:38:30','2026-05-28 08:38:32'),(26,'eum ut',33.17,'Rerum et omnis error nisi ad voluptatibus.',NULL,51,1,'2026-05-28 08:38:30','2026-05-28 08:38:32'),(27,'aliquid ipsam',17.55,'Sunt delectus vel et perferendis et nostrum.',NULL,64,1,'2026-05-28 08:38:30','2026-05-28 08:38:32'),(28,'possimus doloremque',21.92,'Delectus et aut quas esse est molestiae et.',NULL,7,1,'2026-05-28 08:38:30','2026-05-28 08:38:32'),(29,'cum sequi',23.92,'Occaecati reiciendis eveniet sunt adipisci.',NULL,61,1,'2026-05-28 08:38:30','2026-05-28 08:38:32'),(30,'omnis est',26.64,'Sint enim at fugit sed non adipisci omnis.',NULL,94,1,'2026-05-28 08:38:30','2026-05-28 08:38:32'),(31,'laborum numquam',37.96,'Doloremque sit molestiae accusantium autem et omnis quia odit.',NULL,44,1,'2026-05-28 08:38:30','2026-05-28 08:38:33'),(32,'voluptas corrupti',40.59,'Nihil minus illum excepturi.',NULL,90,1,'2026-05-28 08:38:30','2026-05-28 08:38:33'),(33,'numquam eos',9.36,'Eaque rerum fugit tempore tenetur qui.',NULL,58,1,'2026-05-28 08:38:30','2026-05-28 08:38:33'),(34,'dicta praesentium',37.28,'Beatae nostrum quasi soluta quae perspiciatis est sed.',NULL,101,1,'2026-05-28 08:38:30','2026-05-28 08:38:33');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('mQQmp8iOmSGKgO2OGSwBKgfEDHiBfFqnIGfJbQAo',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.120.0 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiUVBtcXVDMWRhbEVoa3BNbUtFbjFsUnhHSGRzZHF4dlc3bFB3bDJlNCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1781692444),('zYKOgosFwWbCmajiLzTWNhYQMtutzlePM4gTMkqr',8,'127.0.0.1','Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoickhWNThRZ090TzBwVk9mY2pQQlpPWXRwVEJhRllnREk3OTBhZHBJcCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzA6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9wcm9kdWN0cyI7czo1OiJyb3V0ZSI7czoxNDoicHJvZHVjdHMuaW5kZXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aTo4O30=',1781692512);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_movements`
--

DROP TABLE IF EXISTS `stock_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_movements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `type` enum('in','out','adjustment') NOT NULL,
  `quantity` int(11) NOT NULL,
  `reference_type` varchar(255) NOT NULL,
  `reference_id` bigint(20) unsigned DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `stock_movements_product_id_foreign` (`product_id`),
  CONSTRAINT `stock_movements_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=195 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_movements`
--

LOCK TABLES `stock_movements` WRITE;
/*!40000 ALTER TABLE `stock_movements` DISABLE KEYS */;
INSERT INTO `stock_movements` VALUES (5,11,'out',3,'manual',NULL,'Staff consumption','2026-05-28 08:34:15'),(6,11,'adjustment',13,'manual',NULL,'Stock opname adjustment','2026-05-28 08:34:15'),(7,11,'out',3,'manual',NULL,'Expired','2026-05-28 08:34:15'),(8,12,'in',16,'manual',NULL,'Supplier delivery','2026-05-28 08:34:15'),(9,12,'out',2,'manual',NULL,'Expired','2026-05-28 08:34:15'),(10,12,'out',2,'manual',NULL,'Damaged','2026-05-28 08:34:15'),(11,12,'in',19,'manual',NULL,'Restock','2026-05-28 08:34:15'),(12,12,'out',2,'manual',NULL,'Damaged','2026-05-28 08:34:15'),(13,13,'in',12,'manual',NULL,'Restock','2026-05-28 08:34:15'),(14,13,'adjustment',93,'manual',NULL,'Stock opname adjustment','2026-05-28 08:34:15'),(15,13,'in',12,'manual',NULL,'Supplier delivery','2026-05-28 08:34:15'),(16,13,'in',10,'manual',NULL,'Restock','2026-05-28 08:34:15'),(17,13,'adjustment',37,'manual',NULL,'Stock opname adjustment','2026-05-28 08:34:15'),(18,14,'in',19,'manual',NULL,'Found in warehouse','2026-05-28 08:34:15'),(19,14,'adjustment',30,'manual',NULL,'Stock opname adjustment','2026-05-28 08:34:15'),(20,14,'out',1,'manual',NULL,'Damaged','2026-05-28 08:34:15'),(21,14,'out',1,'manual',NULL,'Expired','2026-05-28 08:34:15'),(22,14,'in',18,'manual',NULL,'Restock','2026-05-28 08:34:15'),(23,15,'adjustment',15,'manual',NULL,'Stock opname adjustment','2026-05-28 08:34:15'),(24,15,'out',4,'manual',NULL,'Damaged','2026-05-28 08:34:15'),(25,15,'out',5,'manual',NULL,'Damaged','2026-05-28 08:34:15'),(26,15,'out',4,'manual',NULL,'Expired','2026-05-28 08:34:15'),(27,15,'adjustment',79,'manual',NULL,'Stock opname adjustment','2026-05-28 08:34:15'),(28,15,'out',1,'manual',NULL,'Damaged','2026-05-28 08:34:15'),(29,16,'in',20,'manual',NULL,'Supplier delivery','2026-05-28 08:34:15'),(30,16,'adjustment',25,'manual',NULL,'Stock opname adjustment','2026-05-28 08:34:15'),(31,16,'adjustment',19,'manual',NULL,'Stock opname adjustment','2026-05-28 08:34:15'),(32,17,'adjustment',52,'manual',NULL,'Stock opname adjustment','2026-05-28 08:34:15'),(33,17,'out',5,'manual',NULL,'Staff consumption','2026-05-28 08:34:15'),(34,17,'adjustment',95,'manual',NULL,'Stock opname adjustment','2026-05-28 08:34:15'),(35,17,'adjustment',81,'manual',NULL,'Stock opname adjustment','2026-05-28 08:34:15'),(36,17,'in',12,'manual',NULL,'Restock','2026-05-28 08:34:15'),(37,18,'out',4,'manual',NULL,'Damaged','2026-05-28 08:34:15'),(38,18,'out',4,'manual',NULL,'Expired','2026-05-28 08:34:15'),(39,18,'out',2,'manual',NULL,'Staff consumption','2026-05-28 08:34:15'),(40,18,'out',1,'manual',NULL,'Expired','2026-05-28 08:34:16'),(41,18,'in',8,'manual',NULL,'Restock','2026-05-28 08:34:16'),(42,19,'out',2,'manual',NULL,'Expired','2026-05-28 08:34:16'),(43,19,'adjustment',56,'manual',NULL,'Stock opname adjustment','2026-05-28 08:34:16'),(44,19,'adjustment',45,'manual',NULL,'Stock opname adjustment','2026-05-28 08:34:16'),(45,19,'in',5,'manual',NULL,'Restock','2026-05-28 08:34:16'),(46,20,'out',5,'manual',NULL,'Damaged','2026-05-28 08:34:16'),(47,20,'in',13,'manual',NULL,'Supplier delivery','2026-05-28 08:34:16'),(48,20,'out',1,'manual',NULL,'Expired','2026-05-28 08:34:16'),(49,20,'adjustment',89,'manual',NULL,'Stock opname adjustment','2026-05-28 08:34:16'),(50,20,'adjustment',86,'manual',NULL,'Stock opname adjustment','2026-05-28 08:34:16'),(51,20,'adjustment',62,'manual',NULL,'Stock opname adjustment','2026-05-28 08:34:16'),(52,20,'adjustment',89,'manual',NULL,'Stock opname adjustment','2026-05-28 08:34:16'),(53,21,'adjustment',96,'manual',NULL,'Stock opname adjustment','2026-05-28 08:34:16'),(54,21,'in',14,'manual',NULL,'Restock','2026-05-28 08:34:16'),(55,21,'adjustment',35,'manual',NULL,'Stock opname adjustment','2026-05-28 08:34:16'),(56,21,'in',10,'manual',NULL,'Restock','2026-05-28 08:34:16'),(57,21,'in',16,'manual',NULL,'Supplier delivery','2026-05-28 08:34:16'),(58,21,'out',5,'manual',NULL,'Expired','2026-05-28 08:34:16'),(59,21,'out',4,'manual',NULL,'Expired','2026-05-28 08:34:16'),(60,22,'adjustment',54,'manual',NULL,'Stock opname adjustment','2026-05-28 08:34:16'),(61,22,'adjustment',39,'manual',NULL,'Stock opname adjustment','2026-05-28 08:34:16'),(62,22,'adjustment',73,'manual',NULL,'Stock opname adjustment','2026-05-28 08:34:16'),(63,22,'out',2,'manual',NULL,'Damaged','2026-05-28 08:34:16'),(64,22,'adjustment',15,'manual',NULL,'Stock opname adjustment','2026-05-28 08:34:16'),(65,22,'adjustment',20,'manual',NULL,'Stock opname adjustment','2026-05-28 08:34:16'),(66,11,'out',5,'manual',NULL,'Expired','2026-05-28 08:38:30'),(67,11,'in',5,'manual',NULL,'Restock','2026-05-28 08:38:31'),(68,11,'in',7,'manual',NULL,'Restock','2026-05-28 08:38:31'),(69,11,'adjustment',56,'manual',NULL,'Stock opname adjustment','2026-05-28 08:38:31'),(70,11,'in',6,'manual',NULL,'Restock','2026-05-28 08:38:31'),(71,11,'in',5,'manual',NULL,'Restock','2026-05-28 08:38:31'),(72,11,'out',2,'manual',NULL,'Expired','2026-05-28 08:38:31'),(73,12,'in',9,'manual',NULL,'Restock','2026-05-28 08:38:31'),(74,12,'out',3,'manual',NULL,'Damaged','2026-05-28 08:38:31'),(75,12,'in',19,'manual',NULL,'Supplier delivery','2026-05-28 08:38:31'),(76,12,'adjustment',24,'manual',NULL,'Stock opname adjustment','2026-05-28 08:38:31'),(77,12,'out',1,'manual',NULL,'Expired','2026-05-28 08:38:31'),(78,13,'adjustment',13,'manual',NULL,'Stock opname adjustment','2026-05-28 08:38:31'),(79,13,'out',1,'manual',NULL,'Expired','2026-05-28 08:38:31'),(80,13,'in',14,'manual',NULL,'Supplier delivery','2026-05-28 08:38:31'),(81,13,'adjustment',20,'manual',NULL,'Stock opname adjustment','2026-05-28 08:38:31'),(82,13,'out',3,'manual',NULL,'Expired','2026-05-28 08:38:31'),(83,14,'out',1,'manual',NULL,'Damaged','2026-05-28 08:38:31'),(84,14,'in',19,'manual',NULL,'Restock','2026-05-28 08:38:31'),(85,14,'in',9,'manual',NULL,'Restock','2026-05-28 08:38:31'),(86,15,'adjustment',43,'manual',NULL,'Stock opname adjustment','2026-05-28 08:38:31'),(87,15,'in',12,'manual',NULL,'Supplier delivery','2026-05-28 08:38:31'),(88,15,'adjustment',96,'manual',NULL,'Stock opname adjustment','2026-05-28 08:38:31'),(89,15,'adjustment',19,'manual',NULL,'Stock opname adjustment','2026-05-28 08:38:31'),(90,15,'out',4,'manual',NULL,'Damaged','2026-05-28 08:38:31'),(91,16,'adjustment',66,'manual',NULL,'Stock opname adjustment','2026-05-28 08:38:31'),(92,16,'adjustment',94,'manual',NULL,'Stock opname adjustment','2026-05-28 08:38:31'),(93,16,'out',2,'manual',NULL,'Damaged','2026-05-28 08:38:31'),(94,16,'in',6,'manual',NULL,'Restock','2026-05-28 08:38:31'),(95,16,'in',9,'manual',NULL,'Found in warehouse','2026-05-28 08:38:31'),(96,16,'out',2,'manual',NULL,'Damaged','2026-05-28 08:38:31'),(97,16,'in',19,'manual',NULL,'Supplier delivery','2026-05-28 08:38:31'),(98,17,'in',6,'manual',NULL,'Found in warehouse','2026-05-28 08:38:31'),(99,17,'adjustment',21,'manual',NULL,'Stock opname adjustment','2026-05-28 08:38:31'),(100,17,'in',6,'manual',NULL,'Supplier delivery','2026-05-28 08:38:31'),(101,17,'in',12,'manual',NULL,'Found in warehouse','2026-05-28 08:38:31'),(102,17,'in',7,'manual',NULL,'Restock','2026-05-28 08:38:31'),(103,17,'in',12,'manual',NULL,'Found in warehouse','2026-05-28 08:38:31'),(104,17,'adjustment',67,'manual',NULL,'Stock opname adjustment','2026-05-28 08:38:31'),(105,18,'adjustment',21,'manual',NULL,'Stock opname adjustment','2026-05-28 08:38:31'),(106,18,'in',7,'manual',NULL,'Restock','2026-05-28 08:38:31'),(107,18,'in',15,'manual',NULL,'Supplier delivery','2026-05-28 08:38:31'),(108,18,'in',13,'manual',NULL,'Supplier delivery','2026-05-28 08:38:31'),(109,18,'in',17,'manual',NULL,'Restock','2026-05-28 08:38:31'),(110,18,'out',5,'manual',NULL,'Staff consumption','2026-05-28 08:38:31'),(111,19,'in',12,'manual',NULL,'Supplier delivery','2026-05-28 08:38:31'),(112,19,'adjustment',14,'manual',NULL,'Stock opname adjustment','2026-05-28 08:38:31'),(113,19,'out',3,'manual',NULL,'Staff consumption','2026-05-28 08:38:31'),(114,19,'out',4,'manual',NULL,'Expired','2026-05-28 08:38:31'),(115,19,'adjustment',71,'manual',NULL,'Stock opname adjustment','2026-05-28 08:38:31'),(116,20,'adjustment',37,'manual',NULL,'Stock opname adjustment','2026-05-28 08:38:31'),(117,20,'adjustment',10,'manual',NULL,'Stock opname adjustment','2026-05-28 08:38:31'),(118,20,'in',19,'manual',NULL,'Found in warehouse','2026-05-28 08:38:31'),(119,20,'in',19,'manual',NULL,'Supplier delivery','2026-05-28 08:38:31'),(120,20,'out',3,'manual',NULL,'Expired','2026-05-28 08:38:31'),(121,21,'adjustment',20,'manual',NULL,'Stock opname adjustment','2026-05-28 08:38:31'),(122,21,'adjustment',23,'manual',NULL,'Stock opname adjustment','2026-05-28 08:38:31'),(123,21,'in',5,'manual',NULL,'Supplier delivery','2026-05-28 08:38:31'),(124,21,'in',5,'manual',NULL,'Restock','2026-05-28 08:38:31'),(125,21,'in',19,'manual',NULL,'Found in warehouse','2026-05-28 08:38:31'),(126,22,'adjustment',15,'manual',NULL,'Stock opname adjustment','2026-05-28 08:38:31'),(127,22,'adjustment',20,'manual',NULL,'Stock opname adjustment','2026-05-28 08:38:31'),(128,22,'out',2,'manual',NULL,'Expired','2026-05-28 08:38:31'),(129,22,'in',9,'manual',NULL,'Restock','2026-05-28 08:38:31'),(130,22,'out',2,'manual',NULL,'Staff consumption','2026-05-28 08:38:31'),(131,23,'out',3,'manual',NULL,'Damaged','2026-05-28 08:38:31'),(132,23,'adjustment',38,'manual',NULL,'Stock opname adjustment','2026-05-28 08:38:31'),(133,23,'adjustment',63,'manual',NULL,'Stock opname adjustment','2026-05-28 08:38:31'),(134,23,'in',14,'manual',NULL,'Restock','2026-05-28 08:38:31'),(135,23,'in',14,'manual',NULL,'Supplier delivery','2026-05-28 08:38:31'),(136,24,'adjustment',74,'manual',NULL,'Stock opname adjustment','2026-05-28 08:38:31'),(137,24,'adjustment',20,'manual',NULL,'Stock opname adjustment','2026-05-28 08:38:31'),(138,24,'out',4,'manual',NULL,'Expired','2026-05-28 08:38:31'),(139,24,'out',3,'manual',NULL,'Damaged','2026-05-28 08:38:31'),(140,24,'in',7,'manual',NULL,'Supplier delivery','2026-05-28 08:38:31'),(141,25,'out',4,'manual',NULL,'Staff consumption','2026-05-28 08:38:32'),(142,25,'adjustment',60,'manual',NULL,'Stock opname adjustment','2026-05-28 08:38:32'),(143,25,'in',20,'manual',NULL,'Supplier delivery','2026-05-28 08:38:32'),(144,25,'out',3,'manual',NULL,'Staff consumption','2026-05-28 08:38:32'),(145,25,'out',2,'manual',NULL,'Damaged','2026-05-28 08:38:32'),(146,25,'adjustment',37,'manual',NULL,'Stock opname adjustment','2026-05-28 08:38:32'),(147,25,'adjustment',95,'manual',NULL,'Stock opname adjustment','2026-05-28 08:38:32'),(148,26,'adjustment',65,'manual',NULL,'Stock opname adjustment','2026-05-28 08:38:32'),(149,26,'out',1,'manual',NULL,'Expired','2026-05-28 08:38:32'),(150,26,'in',12,'manual',NULL,'Restock','2026-05-28 08:38:32'),(151,26,'out',4,'manual',NULL,'Expired','2026-05-28 08:38:32'),(152,26,'out',4,'manual',NULL,'Staff consumption','2026-05-28 08:38:32'),(153,26,'adjustment',51,'manual',NULL,'Stock opname adjustment','2026-05-28 08:38:32'),(154,27,'out',5,'manual',NULL,'Staff consumption','2026-05-28 08:38:32'),(155,27,'adjustment',62,'manual',NULL,'Stock opname adjustment','2026-05-28 08:38:32'),(156,27,'in',7,'manual',NULL,'Found in warehouse','2026-05-28 08:38:32'),(157,27,'adjustment',70,'manual',NULL,'Stock opname adjustment','2026-05-28 08:38:32'),(158,27,'out',5,'manual',NULL,'Expired','2026-05-28 08:38:32'),(159,27,'out',1,'manual',NULL,'Staff consumption','2026-05-28 08:38:32'),(160,28,'adjustment',23,'manual',NULL,'Stock opname adjustment','2026-05-28 08:38:32'),(161,28,'out',4,'manual',NULL,'Expired','2026-05-28 08:38:32'),(162,28,'out',2,'manual',NULL,'Damaged','2026-05-28 08:38:32'),(163,28,'adjustment',12,'manual',NULL,'Stock opname adjustment','2026-05-28 08:38:32'),(164,28,'out',5,'manual',NULL,'Staff consumption','2026-05-28 08:38:32'),(165,29,'adjustment',35,'manual',NULL,'Stock opname adjustment','2026-05-28 08:38:32'),(166,29,'adjustment',45,'manual',NULL,'Stock opname adjustment','2026-05-28 08:38:32'),(167,29,'out',4,'manual',NULL,'Damaged','2026-05-28 08:38:32'),(168,29,'in',12,'manual',NULL,'Supplier delivery','2026-05-28 08:38:32'),(169,29,'in',13,'manual',NULL,'Found in warehouse','2026-05-28 08:38:32'),(170,29,'out',5,'manual',NULL,'Staff consumption','2026-05-28 08:38:32'),(171,30,'out',5,'manual',NULL,'Expired','2026-05-28 08:38:32'),(172,30,'out',5,'manual',NULL,'Damaged','2026-05-28 08:38:32'),(173,30,'adjustment',94,'manual',NULL,'Stock opname adjustment','2026-05-28 08:38:32'),(174,31,'in',20,'manual',NULL,'Restock','2026-05-28 08:38:32'),(175,31,'adjustment',92,'manual',NULL,'Stock opname adjustment','2026-05-28 08:38:33'),(176,31,'adjustment',45,'manual',NULL,'Stock opname adjustment','2026-05-28 08:38:33'),(177,31,'out',1,'manual',NULL,'Expired','2026-05-28 08:38:33'),(178,32,'adjustment',59,'manual',NULL,'Stock opname adjustment','2026-05-28 08:38:33'),(179,32,'adjustment',42,'manual',NULL,'Stock opname adjustment','2026-05-28 08:38:33'),(180,32,'adjustment',78,'manual',NULL,'Stock opname adjustment','2026-05-28 08:38:33'),(181,32,'out',4,'manual',NULL,'Damaged','2026-05-28 08:38:33'),(182,32,'adjustment',93,'manual',NULL,'Stock opname adjustment','2026-05-28 08:38:33'),(183,32,'out',3,'manual',NULL,'Damaged','2026-05-28 08:38:33'),(184,33,'in',7,'manual',NULL,'Restock','2026-05-28 08:38:33'),(185,33,'in',16,'manual',NULL,'Found in warehouse','2026-05-28 08:38:33'),(186,33,'adjustment',70,'manual',NULL,'Stock opname adjustment','2026-05-28 08:38:33'),(187,33,'adjustment',42,'manual',NULL,'Stock opname adjustment','2026-05-28 08:38:33'),(188,33,'in',16,'manual',NULL,'Supplier delivery','2026-05-28 08:38:33'),(189,34,'adjustment',79,'manual',NULL,'Stock opname adjustment','2026-05-28 08:38:33'),(190,34,'in',12,'manual',NULL,'Supplier delivery','2026-05-28 08:38:33'),(191,34,'in',7,'manual',NULL,'Supplier delivery','2026-05-28 08:38:33'),(192,34,'out',2,'manual',NULL,'Expired','2026-05-28 08:38:33'),(193,34,'adjustment',81,'manual',NULL,'Stock opname adjustment','2026-05-28 08:38:33'),(194,34,'in',20,'manual',NULL,'Restock','2026-05-28 08:38:33');
/*!40000 ALTER TABLE `stock_movements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transaction_items`
--

DROP TABLE IF EXISTS `transaction_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transaction_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `transaction_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `price` decimal(15,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `subtotal` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `transaction_items_transaction_id_foreign` (`transaction_id`),
  KEY `transaction_items_product_id_foreign` (`product_id`),
  CONSTRAINT `transaction_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `transaction_items_transaction_id_foreign` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaction_items`
--

LOCK TABLES `transaction_items` WRITE;
/*!40000 ALTER TABLE `transaction_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `transaction_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `transaction_code` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `total_amount` decimal(15,2) NOT NULL,
  `paid_amount` decimal(15,2) NOT NULL,
  `change_amount` decimal(15,2) NOT NULL,
  `payment_method` enum('cash','qris','transfer') NOT NULL,
  `status` enum('completed','cancelled','refunded') NOT NULL DEFAULT 'completed',
  `transaction_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `transactions_transaction_code_unique` (`transaction_code`),
  KEY `transactions_user_id_foreign` (`user_id`),
  CONSTRAINT `transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transactions`
--

LOCK TABLES `transactions` WRITE;
/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','cashier') NOT NULL DEFAULT 'cashier',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_unique` (`username`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (8,'Admin Sekawan','admin','admin@sekawan.com',NULL,'$2y$12$d5RBHAzuRHfZAa9XZuDMZOUjGRad3LLTufGYyWKP/Tdnz4xRf0WWa','admin',1,NULL,NULL,'2026-05-28 08:32:28','2026-05-28 08:38:30'),(9,'Cashier Sekawan','cashier','cashier@sekawan.com',NULL,'$2y$12$TuA/wQ.8kCXpa0Xay8N4yuUB3DWF4VSSF/1QgLt7GCiyMnptQqM2u','cashier',1,NULL,NULL,'2026-05-28 08:34:15','2026-05-28 08:38:30');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'sekawan_db'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-17 17:38:09
