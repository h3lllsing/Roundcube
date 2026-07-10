-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: tyro_project
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `activity_log`
--

DROP TABLE IF EXISTS `activity_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `log_name` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `subject_type` varchar(255) DEFAULT NULL,
  `event` varchar(255) DEFAULT NULL,
  `subject_id` bigint(20) unsigned DEFAULT NULL,
  `causer_type` varchar(255) DEFAULT NULL,
  `causer_id` bigint(20) unsigned DEFAULT NULL,
  `properties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`properties`)),
  `batch_uuid` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subject` (`subject_type`,`subject_id`),
  KEY `causer` (`causer_type`,`causer_id`),
  KEY `activity_log_log_name_index` (`log_name`)
) ENGINE=InnoDB AUTO_INCREMENT=187 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_log`
--

LOCK TABLES `activity_log` WRITE;
/*!40000 ALTER TABLE `activity_log` DISABLE KEYS */;
INSERT INTO `activity_log` VALUES (3,'default','User cloned: admin@tyro.project -> h3llsing@gmail.com','App\\Models\\User','cloned',5,'App\\Models\\User',5,'{\"target_user_id\":7,\"target_user_name\":\"MASOOD NASIR\",\"target_user_email\":\"h3llsing@gmail.com\",\"copied_roles\":true,\"copied_overrides\":true,\"copied_status\":true}',NULL,'2026-06-28 16:29:04','2026-06-28 16:29:04'),(4,'default','Permission overrides updated for user: h3llsing@gmail.com','App\\Models\\User','updated',7,'App\\Models\\User',5,'{\"type\":\"permission_overrides\",\"modules_updated\":[19,24,9,22,14,6,1,8,26,2,25,20,18,13,11,21,7,17,27,16,5,10,15,12,4,3,23]}',NULL,'2026-06-28 16:30:51','2026-06-28 16:30:51'),(5,'default','Permission overrides updated for user: h3llsing@gmail.com','App\\Models\\User','updated',7,'App\\Models\\User',5,'{\"type\":\"permission_overrides\",\"modules_updated\":[19,24,9,22,14,6,1,8,26,2,25,20,18,13,11,21,7,17,27,16,5,10,15,12,4,3,23]}',NULL,'2026-06-28 16:35:32','2026-06-28 16:35:32'),(6,'default','deleted','App\\Models\\Hosting','deleted',1,'App\\Models\\User',7,'{\"old\":{\"user_id\":5,\"module_id\":2,\"service_provider_id\":3,\"name\":\"Main Website Hosting\",\"username\":\"mainuser\",\"password\":\"Pass@123\",\"cpanel_url\":\"https:\\/\\/cpanel.example.com\",\"plan\":\"Business\",\"domain\":\"example.com\",\"domain_ip\":null,\"mail_domain_ip\":null,\"cpanel_ip\":null,\"start_date\":\"2025-12-28T00:00:00.000000Z\",\"expiry_date\":\"2026-12-28T00:00:00.000000Z\",\"cost\":\"29.99\",\"status\":\"active\",\"notes\":\"Demo Main Website Hosting entry.\",\"monitoring_url\":null,\"last_ping_at\":null}}',NULL,'2026-06-28 16:37:01','2026-06-28 16:37:01'),(7,'default','deleted','App\\Models\\Hosting','deleted',2,'App\\Models\\User',7,'{\"old\":{\"user_id\":6,\"module_id\":2,\"service_provider_id\":2,\"name\":\"Client Portal Hosting\",\"username\":\"portaladmin\",\"password\":\"Pass@123\",\"cpanel_url\":\"https:\\/\\/cpanel.clientportal.com\",\"plan\":\"Premium\",\"domain\":\"clientportal.com\",\"domain_ip\":null,\"mail_domain_ip\":null,\"cpanel_ip\":null,\"start_date\":\"2025-12-28T00:00:00.000000Z\",\"expiry_date\":\"2026-12-28T00:00:00.000000Z\",\"cost\":\"49.99\",\"status\":\"active\",\"notes\":\"Demo Client Portal Hosting entry.\",\"monitoring_url\":null,\"last_ping_at\":null}}',NULL,'2026-06-28 16:37:03','2026-06-28 16:37:03'),(8,'default','deleted','App\\Models\\Domain','deleted',2,'App\\Models\\User',7,'{\"old\":{\"user_id\":5,\"module_id\":1,\"hosting_id\":null,\"service_provider_id\":2,\"name\":\"example.com\",\"registration_date\":\"2025-06-28T00:00:00.000000Z\",\"expiry_date\":\"2027-06-28T00:00:00.000000Z\",\"auto_renew\":true,\"cost\":\"12.99\",\"status\":\"active\",\"cloudflare_status\":null,\"dns_servers\":[\"ns1.example.com\",\"ns2.example.com\"],\"notes\":\"Demo domain entry.\",\"monitoring_url\":null,\"last_ping_at\":null}}',NULL,'2026-06-28 16:37:06','2026-06-28 16:37:06'),(9,'default','deleted','App\\Models\\Domain','deleted',3,'App\\Models\\User',7,'{\"old\":{\"user_id\":6,\"module_id\":1,\"hosting_id\":null,\"service_provider_id\":3,\"name\":\"mysite.org\",\"registration_date\":\"2025-06-28T00:00:00.000000Z\",\"expiry_date\":\"2027-06-28T00:00:00.000000Z\",\"auto_renew\":true,\"cost\":\"14.99\",\"status\":\"active\",\"cloudflare_status\":null,\"dns_servers\":[\"ns1.mysite.org\",\"ns2.mysite.org\"],\"notes\":\"Demo domain entry.\",\"monitoring_url\":null,\"last_ping_at\":null}}',NULL,'2026-06-28 16:37:08','2026-06-28 16:37:08'),(10,'default','deleted','App\\Models\\Vps','deleted',1,'App\\Models\\User',7,'{\"old\":{\"user_id\":5,\"module_id\":3,\"service_provider_id\":3,\"name\":\"Production Web Server\",\"plan\":\"s-2vcpu-2gb\",\"ip_address\":\"203.0.113.10\",\"password\":\"Vps@456\",\"os\":\"Ubuntu 24.04\",\"ram_mb\":2048,\"disk_gb\":50,\"cpu_cores\":2,\"department\":null,\"location\":null,\"login_ids\":null,\"additional_ips\":null,\"cost\":\"15.00\",\"start_date\":\"2026-03-28T00:00:00.000000Z\",\"expiry_date\":\"2027-03-28T00:00:00.000000Z\",\"status\":\"active\",\"notes\":\"Demo Production Web Server entry.\",\"monitoring_url\":null,\"last_ping_at\":null}}',NULL,'2026-06-28 16:37:15','2026-06-28 16:37:15'),(11,'default','deleted','App\\Models\\Vps','deleted',2,'App\\Models\\User',7,'{\"old\":{\"user_id\":6,\"module_id\":3,\"service_provider_id\":2,\"name\":\"Database Server\",\"plan\":\"s-4vcpu-8gb\",\"ip_address\":\"203.0.113.20\",\"password\":\"Vps@456\",\"os\":\"Debian 12\",\"ram_mb\":8192,\"disk_gb\":160,\"cpu_cores\":4,\"department\":null,\"location\":null,\"login_ids\":null,\"additional_ips\":null,\"cost\":\"48.00\",\"start_date\":\"2026-03-28T00:00:00.000000Z\",\"expiry_date\":\"2027-03-28T00:00:00.000000Z\",\"status\":\"active\",\"notes\":\"Demo Database Server entry.\",\"monitoring_url\":null,\"last_ping_at\":null}}',NULL,'2026-06-28 16:37:16','2026-06-28 16:37:16'),(12,'default','deleted','App\\Models\\Voip','deleted',1,'App\\Models\\User',7,'{\"old\":{\"user_id\":5,\"module_id\":4,\"service_provider_id\":4,\"name\":\"Main SIP Trunk\",\"extensions\":[\"101\",\"102\"],\"phone_number\":\"+1-212-555-0100\",\"type\":\"trunk\",\"direction\":\"both\",\"username\":\"sip_main\",\"password\":\"Voip@123\",\"extension_password\":\"Ext@456\",\"dashboard_url\":\"https:\\/\\/voip.mainsiptrunk.com\",\"server_ip\":\"10.0.0.10\",\"cost\":\"19.99\",\"start_date\":\"2025-12-28T00:00:00.000000Z\",\"expiry_date\":\"2026-12-28T00:00:00.000000Z\",\"status\":\"active\",\"number_status\":\"active\",\"outbound_code\":\"9\",\"team_details\":\"Demo team: Main SIP Trunk\",\"notes\":\"Demo Main SIP Trunk entry.\",\"monitoring_url\":null,\"last_ping_at\":null}}',NULL,'2026-06-28 16:37:23','2026-06-28 16:37:23'),(13,'default','deleted','App\\Models\\Voip','deleted',2,'App\\Models\\User',7,'{\"old\":{\"user_id\":6,\"module_id\":4,\"service_provider_id\":2,\"name\":\"Sales Phone Line\",\"extensions\":[\"201\"],\"phone_number\":\"+1-212-555-0200\",\"type\":\"sip\",\"direction\":\"inbound\",\"username\":\"sip_sales\",\"password\":\"Voip@123\",\"extension_password\":\"Ext@456\",\"dashboard_url\":\"https:\\/\\/voip.salesphoneline.com\",\"server_ip\":\"10.0.0.11\",\"cost\":\"14.99\",\"start_date\":\"2025-12-28T00:00:00.000000Z\",\"expiry_date\":\"2026-12-28T00:00:00.000000Z\",\"status\":\"active\",\"number_status\":\"active\",\"outbound_code\":\"9\",\"team_details\":\"Demo team: Sales Phone Line\",\"notes\":\"Demo Sales Phone Line entry.\",\"monitoring_url\":null,\"last_ping_at\":null}}',NULL,'2026-06-28 16:37:27','2026-06-28 16:37:27'),(14,'default','deleted','App\\Models\\OtherService','deleted',1,'App\\Models\\User',7,'{\"old\":{\"user_id\":5,\"module_id\":7,\"service_provider_id\":2,\"name\":\"Slack Premium\",\"service_type\":\"saas\",\"username\":\"admin\",\"password\":\"Svc@2024\",\"login_url\":\"https:\\/\\/slack.com\\/signin\",\"website\":null,\"cost\":\"8.00\",\"start_date\":null,\"expiry_date\":\"2027-06-28T00:00:00.000000Z\",\"status\":\"active\",\"notes\":\"Demo Slack Premium subscription.\",\"monitoring_url\":null,\"last_ping_at\":null}}',NULL,'2026-06-28 16:37:32','2026-06-28 16:37:32'),(15,'default','deleted','App\\Models\\OtherService','deleted',2,'App\\Models\\User',7,'{\"old\":{\"user_id\":6,\"module_id\":7,\"service_provider_id\":2,\"name\":\"GitHub Enterprise\",\"service_type\":\"saas\",\"username\":\"testuser\",\"password\":\"Svc@2024\",\"login_url\":\"https:\\/\\/github.com\\/login\",\"website\":null,\"cost\":\"21.00\",\"start_date\":null,\"expiry_date\":\"2027-06-28T00:00:00.000000Z\",\"status\":\"active\",\"notes\":\"Demo GitHub Enterprise subscription.\",\"monitoring_url\":null,\"last_ping_at\":null}}',NULL,'2026-06-28 16:37:34','2026-06-28 16:37:34'),(16,'default','deleted','App\\Models\\ExpiryTracker','deleted',1,'App\\Models\\User',7,'{\"old\":{\"user_id\":5,\"module_id\":8,\"service_provider_id\":3,\"name\":\"SSL Certificate\",\"username\":\"admin\",\"login_url\":\"https:\\/\\/namecheap.com\\/ssl\",\"password\":null,\"expiry_date\":\"2026-09-28T00:00:00.000000Z\",\"renewal_date\":\"2026-09-12T00:00:00.000000Z\",\"cost\":\"99.00\",\"status\":\"active\",\"notes\":\"Demo SSL Certificate tracker.\",\"monitoring_url\":null,\"last_ping_at\":null,\"email_notifications_enabled\":false,\"smtp_profile_id\":null,\"notify_days_before\":[30,15,7,1],\"notify_on_expiry_day\":false,\"notify_assigned_user\":true,\"notify_admins\":false,\"notify_custom_emails\":null,\"last_notification_sent_at\":null,\"next_notification_due_at\":null,\"disabled_by\":null,\"disabled_at\":null,\"disable_reason\":null}}',NULL,'2026-06-28 16:37:40','2026-06-28 16:37:40'),(17,'default','deleted','App\\Models\\ExpiryTracker','deleted',2,'App\\Models\\User',7,'{\"old\":{\"user_id\":6,\"module_id\":8,\"service_provider_id\":3,\"name\":\"Code Signing Cert\",\"username\":\"testuser\",\"login_url\":\"https:\\/\\/namecheap.com\\/code-signing\",\"password\":null,\"expiry_date\":\"2026-09-28T00:00:00.000000Z\",\"renewal_date\":\"2026-09-12T00:00:00.000000Z\",\"cost\":\"299.00\",\"status\":\"active\",\"notes\":\"Demo Code Signing Cert tracker.\",\"monitoring_url\":null,\"last_ping_at\":null,\"email_notifications_enabled\":false,\"smtp_profile_id\":null,\"notify_days_before\":[30,15,7,1],\"notify_on_expiry_day\":false,\"notify_assigned_user\":true,\"notify_admins\":false,\"notify_custom_emails\":null,\"last_notification_sent_at\":null,\"next_notification_due_at\":null,\"disabled_by\":null,\"disabled_at\":null,\"disable_reason\":null}}',NULL,'2026-06-28 16:37:42','2026-06-28 16:37:42'),(18,'default','deleted','App\\Models\\DomainEmail','deleted',1,'App\\Models\\User',7,'{\"old\":{\"user_id\":5,\"module_id\":6,\"service_provider_id\":4,\"domain_id\":2,\"email\":\"info@example.com\",\"password\":\"Email@789\",\"storage_mb\":1024,\"cost\":\"0.00\",\"expiry_date\":null,\"status\":\"active\",\"notes\":\"Demo domain email entry.\",\"monitoring_url\":null,\"last_ping_at\":null}}',NULL,'2026-06-28 16:37:51','2026-06-28 16:37:51'),(19,'default','deleted','App\\Models\\DomainEmail','deleted',2,'App\\Models\\User',7,'{\"old\":{\"user_id\":6,\"module_id\":6,\"service_provider_id\":3,\"domain_id\":2,\"email\":\"support@example.com\",\"password\":\"Support@123\",\"storage_mb\":1024,\"cost\":\"0.00\",\"expiry_date\":null,\"status\":\"active\",\"notes\":\"Demo domain email entry.\",\"monitoring_url\":null,\"last_ping_at\":null}}',NULL,'2026-06-28 16:37:53','2026-06-28 16:37:53'),(20,'default','deleted','App\\Models\\ServiceProvider','deleted',2,'App\\Models\\User',7,'{\"old\":{\"user_id\":5,\"module_id\":5,\"name\":\"DigitalOcean\",\"type\":\"vps\",\"provider\":\"DigitalOcean Inc.\",\"email\":null,\"website\":\"https:\\/\\/digitalocean.com\",\"password\":\"SP@demo2024\",\"cost\":\"0.00\",\"start_date\":\"2025-06-28T00:00:00.000000Z\",\"expiry_date\":\"2027-06-28T00:00:00.000000Z\",\"status\":\"active\",\"notes\":\"Demo service provider.\",\"monitoring_url\":null,\"last_ping_at\":null}}',NULL,'2026-06-28 16:38:01','2026-06-28 16:38:01'),(21,'default','deleted','App\\Models\\ServiceProvider','deleted',3,'App\\Models\\User',7,'{\"old\":{\"user_id\":5,\"module_id\":5,\"name\":\"Namecheap\",\"type\":\"domain\",\"provider\":\"Namecheap Inc.\",\"email\":null,\"website\":\"https:\\/\\/namecheap.com\",\"password\":\"SP@demo2024\",\"cost\":\"0.00\",\"start_date\":\"2025-06-28T00:00:00.000000Z\",\"expiry_date\":\"2027-06-28T00:00:00.000000Z\",\"status\":\"active\",\"notes\":\"Demo service provider.\",\"monitoring_url\":null,\"last_ping_at\":null}}',NULL,'2026-06-28 16:38:03','2026-06-28 16:38:03'),(22,'default','deleted','App\\Models\\ServiceProvider','deleted',4,'App\\Models\\User',7,'{\"old\":{\"user_id\":5,\"module_id\":5,\"name\":\"Google Workspace\",\"type\":\"email\",\"provider\":\"Google LLC\",\"email\":null,\"website\":\"https:\\/\\/workspace.google.com\",\"password\":\"SP@demo2024\",\"cost\":\"0.00\",\"start_date\":\"2025-06-28T00:00:00.000000Z\",\"expiry_date\":\"2027-06-28T00:00:00.000000Z\",\"status\":\"active\",\"notes\":\"Demo service provider.\",\"monitoring_url\":null,\"last_ping_at\":null}}',NULL,'2026-06-28 16:38:05','2026-06-28 16:38:05'),(23,'default','deleted','App\\Models\\VaultEntry','deleted',1,'App\\Models\\User',7,'{\"old\":{\"user_id\":5,\"module_id\":12,\"service_name\":\"AWS Root Account\",\"service_url\":\"https:\\/\\/aws.amazon.com\\/console\",\"username\":\"admin@example.com\",\"encrypted_password\":\"eyJpdiI6IlZid3VwSmY3VFpvWEFqMW5LR2k5ZGc9PSIsInZhbHVlIjoiWHhuem9OOUhZQkk2RWEvczMwaWMrUT09IiwibWFjIjoiZjE3OTFhYzc1NzZkZWU0YWRhOGQxZTY1MWZmYzhjZmM5ZDk4M2JiOWU3M2VkYTUyZjQ3NzE2MjU4YTdkYjk1YyIsInRhZyI6IiJ9\",\"description\":\"Demo vault entry - AWS root credentials.\"}}',NULL,'2026-06-28 16:38:15','2026-06-28 16:38:15'),(24,'default','deleted','App\\Models\\VaultEntry','deleted',2,'App\\Models\\User',7,'{\"old\":{\"user_id\":6,\"module_id\":12,\"service_name\":\"GitHub PAT\",\"service_url\":\"https:\\/\\/github.com\\/settings\\/tokens\",\"username\":\"testuser\",\"encrypted_password\":\"eyJpdiI6IktzT3E2UUlYNEgwNVZDMmM2a1UyMWc9PSIsInZhbHVlIjoiazdHRVZUOVhpR25na0lydjlIeHBWZz09IiwibWFjIjoiYWEwYTI0MDg5NDM4MTA0MTMwYWRmNjNkM2E4ZmFmMjFiNzQ3ZDEyMTVmZGQwNjU1ODM0Mzc2MjhjY2MyZDI0MyIsInRhZyI6IiJ9\",\"description\":\"Demo vault entry - GitHub personal access token.\"}}',NULL,'2026-06-28 16:38:16','2026-06-28 16:38:16'),(25,'default','Task Update server OS patches deleted','App\\Models\\Task','deleted',1,'App\\Models\\User',7,'{\"old\":{\"title\":\"Update server OS patches\",\"description\":\"Run apt update and upgrade on all production servers.\",\"module_id\":10,\"status\":\"pending\",\"priority\":\"high\",\"due_date\":\"2026-07-05T10:14:11.000000Z\"}}',NULL,'2026-06-28 16:38:24','2026-06-28 16:38:24'),(26,'default','Task Migrate DNS to Cloudflare deleted','App\\Models\\Task','deleted',2,'App\\Models\\User',7,'{\"old\":{\"title\":\"Migrate DNS to Cloudflare\",\"description\":\"Transfer DNS management from current provider to Cloudflare.\",\"module_id\":10,\"status\":\"in_progress\",\"priority\":\"medium\",\"due_date\":\"2026-07-05T10:14:11.000000Z\"}}',NULL,'2026-06-28 16:38:26','2026-06-28 16:38:26'),(27,'default','Role created: IT SUPPORT','HasinHayder\\Tyro\\Models\\Role','created',13,'App\\Models\\User',7,'{\"name\":\"IT SUPPORT\",\"slug\":\"IT-SUPPORT\"}',NULL,'2026-06-28 16:38:52','2026-06-28 16:38:52'),(28,'default','Role updated: IT SUPPORT','HasinHayder\\Tyro\\Models\\Role','updated',13,'App\\Models\\User',7,'{\"old\":{\"slug\":\"IT-SUPPORT\"},\"attributes\":{\"slug\":\"SUPPORT\"}}',NULL,'2026-06-28 16:39:01','2026-06-28 16:39:01'),(29,'default','User deleted: test@example.com','App\\Models\\User','deleted',6,'App\\Models\\User',7,'{\"email\":\"test@example.com\",\"name\":\"Test User\"}',NULL,'2026-06-28 16:39:39','2026-06-28 16:39:39'),(30,'default','Role deleted: User',NULL,'deleted',NULL,'App\\Models\\User',7,'{\"name\":\"User\",\"slug\":\"user\"}',NULL,'2026-06-28 16:39:49','2026-06-28 16:39:49'),(31,'default','Role deleted: Customer',NULL,'deleted',NULL,'App\\Models\\User',7,'{\"name\":\"Customer\",\"slug\":\"customer\"}',NULL,'2026-06-28 16:39:52','2026-06-28 16:39:52'),(32,'default','Role deleted: Editor',NULL,'deleted',NULL,'App\\Models\\User',7,'{\"name\":\"Editor\",\"slug\":\"editor\"}',NULL,'2026-06-28 16:39:54','2026-06-28 16:39:54'),(33,'default','Role deleted: All',NULL,'deleted',NULL,'App\\Models\\User',7,'{\"name\":\"All\",\"slug\":\"*\"}',NULL,'2026-06-28 16:40:01','2026-06-28 16:40:01'),(34,'default','Role updated: IT SUPPORT','HasinHayder\\Tyro\\Models\\Role','updated',13,'App\\Models\\User',7,'{\"old\":[],\"attributes\":[]}',NULL,'2026-06-28 16:40:04','2026-06-28 16:40:04'),(35,'default','Module Notes deleted','module','deleted',11,'App\\Models\\User',7,'{\"old\":{\"feature_id\":2,\"name\":\"Notes\",\"slug\":\"notes\",\"description\":null,\"is_active\":true}}',NULL,'2026-06-28 16:41:18','2026-06-28 16:41:18'),(36,'default','Note #1 deleted','App\\Models\\Note','deleted',1,'App\\Models\\User',7,'{\"old\":{\"content\":\"Important: Remember to renew the main domain before expiry.\",\"user_id\":5,\"notable_type\":null,\"notable_id\":null}}',NULL,'2026-06-28 16:41:38','2026-06-28 16:41:38'),(37,'default','Note #2 deleted','App\\Models\\Note','deleted',2,'App\\Models\\User',7,'{\"old\":{\"content\":\"Server maintenance scheduled for next Saturday at 2 AM.\",\"user_id\":6,\"notable_type\":null,\"notable_id\":null}}',NULL,'2026-06-28 16:41:39','2026-06-28 16:41:39'),(38,'default','created','App\\Models\\ServiceProvider','created',5,'App\\Models\\User',7,'{\"attributes\":{\"user_id\":7,\"module_id\":2,\"name\":\"MASOOD NASIR\",\"type\":\"Hosting VPS DOMAIN\",\"provider\":\"NICWAYS\",\"email\":\"cloudchapms@gmail.com\",\"website\":\"my.nicways.com\",\"password\":\"Itrs*1234\",\"cost\":\"71.98\",\"start_date\":\"2026-06-29T00:00:00.000000Z\",\"expiry_date\":\"2027-06-29T00:00:00.000000Z\",\"status\":\"active\",\"notes\":\"TESTING ACCOUNTS\",\"monitoring_url\":null,\"last_ping_at\":null}}',NULL,'2026-06-28 16:44:13','2026-06-28 16:44:13'),(39,'default','Password revealed for Service Provider: MASOOD NASIR','App\\Models\\ServiceProvider','revealed',5,'App\\Models\\User',7,'{\"type\":\"service_provider_password\"}',NULL,'2026-06-28 16:44:19','2026-06-28 16:44:19'),(40,'default','updated','App\\Models\\ServiceProvider','updated',5,'App\\Models\\User',7,'{\"attributes\":{\"name\":\"NICWAYS\"},\"old\":{\"name\":\"MASOOD NASIR\"}}',NULL,'2026-06-28 16:45:36','2026-06-28 16:45:36'),(41,'default','created','App\\Models\\Hosting','created',3,'App\\Models\\User',7,'{\"attributes\":{\"user_id\":7,\"module_id\":null,\"service_provider_id\":null,\"name\":\"MASOOD NASIR\",\"username\":\"h3llsing@gmail.com\",\"password\":\"Itrs*1234\",\"cpanel_url\":\"https:\\/\\/alphatach.com\\/cpanel\",\"plan\":\"Prime Plus\",\"domain\":\"alphatach.com\",\"domain_ip\":\"192.168.100.1\",\"mail_domain_ip\":\"192.168.100.1\",\"cpanel_ip\":\"192.168.100.1\",\"start_date\":\"2026-06-29T00:00:00.000000Z\",\"expiry_date\":\"2027-06-26T00:00:00.000000Z\",\"cost\":\"71.99\",\"status\":\"active\",\"notes\":null,\"monitoring_url\":null,\"last_ping_at\":null}}',NULL,'2026-06-28 16:47:14','2026-06-28 16:47:14'),(42,'default','Password revealed for Hosting: MASOOD NASIR','App\\Models\\Hosting','revealed',3,'App\\Models\\User',7,'{\"type\":\"hosting_password\"}',NULL,'2026-06-28 16:47:23','2026-06-28 16:47:23'),(43,'default','updated','App\\Models\\Hosting','updated',3,'App\\Models\\User',7,'{\"attributes\":{\"name\":\"alphatach.com\"},\"old\":{\"name\":\"MASOOD NASIR\"}}',NULL,'2026-06-28 16:47:49','2026-06-28 16:47:49'),(44,'default','created','App\\Models\\Domain','created',4,'App\\Models\\User',7,'{\"attributes\":{\"user_id\":7,\"module_id\":1,\"hosting_id\":3,\"service_provider_id\":5,\"name\":\"alphatach.com\",\"registration_date\":\"2026-06-29T00:00:00.000000Z\",\"expiry_date\":\"2027-06-29T00:00:00.000000Z\",\"auto_renew\":false,\"cost\":\"11.69\",\"status\":\"active\",\"cloudflare_status\":null,\"dns_servers\":[],\"notes\":null,\"monitoring_url\":null,\"last_ping_at\":null}}',NULL,'2026-06-28 16:49:01','2026-06-28 16:49:01'),(45,'default','updated','App\\Models\\Domain','updated',4,'App\\Models\\User',7,'{\"attributes\":{\"cloudflare_status\":\"enabled\"},\"old\":{\"cloudflare_status\":null}}',NULL,'2026-06-28 16:49:34','2026-06-28 16:49:34'),(46,'default','deleted','App\\Models\\Domain','deleted',4,'App\\Models\\User',7,'{\"old\":{\"user_id\":7,\"module_id\":1,\"hosting_id\":3,\"service_provider_id\":5,\"name\":\"alphatach.com\",\"registration_date\":\"2026-06-29T00:00:00.000000Z\",\"expiry_date\":\"2027-06-29T00:00:00.000000Z\",\"auto_renew\":false,\"cost\":\"11.69\",\"status\":\"active\",\"cloudflare_status\":\"enabled\",\"dns_servers\":[],\"notes\":null,\"monitoring_url\":null,\"last_ping_at\":null}}',NULL,'2026-06-28 16:50:14','2026-06-28 16:50:14'),(47,'default','created','App\\Models\\ServiceProvider','created',6,'App\\Models\\User',7,'{\"attributes\":{\"user_id\":7,\"module_id\":null,\"name\":\"Openprovider\",\"type\":\"Domains\",\"provider\":\"Openprovider\",\"email\":\"cloudchapms@gmail.com\",\"website\":\"https:\\/\\/openprovider.com\",\"password\":\"Openprovider\",\"cost\":null,\"start_date\":\"2026-06-29T00:00:00.000000Z\",\"expiry_date\":null,\"status\":\"active\",\"notes\":null,\"monitoring_url\":null,\"last_ping_at\":null}}',NULL,'2026-06-28 16:51:12','2026-06-28 16:51:12'),(48,'default','created','App\\Models\\Domain','created',5,'App\\Models\\User',7,'{\"attributes\":{\"user_id\":7,\"module_id\":null,\"hosting_id\":3,\"service_provider_id\":null,\"name\":\"alphatach.com\",\"registration_date\":\"2026-06-29T00:00:00.000000Z\",\"expiry_date\":\"2027-06-25T00:00:00.000000Z\",\"auto_renew\":false,\"cost\":\"11.29\",\"status\":\"active\",\"cloudflare_status\":\"enabled\",\"dns_servers\":[],\"notes\":null,\"monitoring_url\":null,\"last_ping_at\":null}}',NULL,'2026-06-28 16:51:47','2026-06-28 16:51:47'),(49,'default','updated','App\\Models\\Domain','updated',5,'App\\Models\\User',7,'{\"attributes\":{\"service_provider_id\":6},\"old\":{\"service_provider_id\":null}}',NULL,'2026-06-28 16:52:08','2026-06-28 16:52:08'),(50,'default','updated','App\\Models\\Domain','updated',5,'App\\Models\\User',7,'{\"attributes\":{\"module_id\":1},\"old\":{\"module_id\":null}}',NULL,'2026-06-28 16:52:58','2026-06-28 16:52:58'),(51,'default','updated','App\\Models\\ServiceProvider','updated',5,'App\\Models\\User',7,'{\"attributes\":{\"cost\":null},\"old\":{\"cost\":\"71.98\"}}',NULL,'2026-06-28 16:53:53','2026-06-28 16:53:53'),(52,'default','ModuleRolePermission for module 1, role 13 created','App\\Models\\ModuleRolePermission','created',136,'App\\Models\\User',7,'{\"attributes\":{\"module_id\":1,\"role_id\":13,\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":true}}',NULL,'2026-06-28 16:56:31','2026-06-28 16:56:31'),(53,'default','ModuleRolePermission for module 2, role 13 created','App\\Models\\ModuleRolePermission','created',137,'App\\Models\\User',7,'{\"attributes\":{\"module_id\":2,\"role_id\":13,\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":true}}',NULL,'2026-06-28 16:56:31','2026-06-28 16:56:31'),(54,'default','ModuleRolePermission for module 3, role 13 created','App\\Models\\ModuleRolePermission','created',138,'App\\Models\\User',7,'{\"attributes\":{\"module_id\":3,\"role_id\":13,\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":true}}',NULL,'2026-06-28 16:56:31','2026-06-28 16:56:31'),(55,'default','ModuleRolePermission for module 4, role 13 created','App\\Models\\ModuleRolePermission','created',139,'App\\Models\\User',7,'{\"attributes\":{\"module_id\":4,\"role_id\":13,\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":true}}',NULL,'2026-06-28 16:56:31','2026-06-28 16:56:31'),(56,'default','ModuleRolePermission for module 5, role 13 created','App\\Models\\ModuleRolePermission','created',140,'App\\Models\\User',7,'{\"attributes\":{\"module_id\":5,\"role_id\":13,\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":true}}',NULL,'2026-06-28 16:56:31','2026-06-28 16:56:31'),(57,'default','ModuleRolePermission for module 6, role 13 created','App\\Models\\ModuleRolePermission','created',141,'App\\Models\\User',7,'{\"attributes\":{\"module_id\":6,\"role_id\":13,\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":true}}',NULL,'2026-06-28 16:56:31','2026-06-28 16:56:31'),(58,'default','ModuleRolePermission for module 7, role 13 created','App\\Models\\ModuleRolePermission','created',142,'App\\Models\\User',7,'{\"attributes\":{\"module_id\":7,\"role_id\":13,\"can_create\":false,\"can_read\":false,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-06-28 16:56:31','2026-06-28 16:56:31'),(59,'default','ModuleRolePermission for module 8, role 13 created','App\\Models\\ModuleRolePermission','created',143,'App\\Models\\User',7,'{\"attributes\":{\"module_id\":8,\"role_id\":13,\"can_create\":false,\"can_read\":false,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-06-28 16:56:31','2026-06-28 16:56:31'),(60,'default','ModuleRolePermission for module 10, role 13 created','App\\Models\\ModuleRolePermission','created',144,'App\\Models\\User',7,'{\"attributes\":{\"module_id\":10,\"role_id\":13,\"can_create\":false,\"can_read\":false,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-06-28 16:56:31','2026-06-28 16:56:31'),(61,'default','ModuleRolePermission for module 12, role 13 created','App\\Models\\ModuleRolePermission','created',145,'App\\Models\\User',7,'{\"attributes\":{\"module_id\":12,\"role_id\":13,\"can_create\":false,\"can_read\":false,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-06-28 16:56:31','2026-06-28 16:56:31'),(62,'default','ModuleRolePermission for module 13, role 13 created','App\\Models\\ModuleRolePermission','created',146,'App\\Models\\User',7,'{\"attributes\":{\"module_id\":13,\"role_id\":13,\"can_create\":false,\"can_read\":false,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-06-28 16:56:31','2026-06-28 16:56:31'),(63,'default','ModuleRolePermission for module 14, role 13 created','App\\Models\\ModuleRolePermission','created',147,'App\\Models\\User',7,'{\"attributes\":{\"module_id\":14,\"role_id\":13,\"can_create\":false,\"can_read\":false,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-06-28 16:56:31','2026-06-28 16:56:31'),(64,'default','ModuleRolePermission for module 15, role 13 created','App\\Models\\ModuleRolePermission','created',148,'App\\Models\\User',7,'{\"attributes\":{\"module_id\":15,\"role_id\":13,\"can_create\":false,\"can_read\":false,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-06-28 16:56:31','2026-06-28 16:56:31'),(65,'default','ModuleRolePermission for module 16, role 13 created','App\\Models\\ModuleRolePermission','created',149,'App\\Models\\User',7,'{\"attributes\":{\"module_id\":16,\"role_id\":13,\"can_create\":false,\"can_read\":false,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-06-28 16:56:31','2026-06-28 16:56:31'),(66,'default','ModuleRolePermission for module 17, role 13 created','App\\Models\\ModuleRolePermission','created',150,'App\\Models\\User',7,'{\"attributes\":{\"module_id\":17,\"role_id\":13,\"can_create\":false,\"can_read\":false,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-06-28 16:56:31','2026-06-28 16:56:31'),(67,'default','ModuleRolePermission for module 18, role 13 created','App\\Models\\ModuleRolePermission','created',151,'App\\Models\\User',7,'{\"attributes\":{\"module_id\":18,\"role_id\":13,\"can_create\":false,\"can_read\":false,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-06-28 16:56:31','2026-06-28 16:56:31'),(68,'default','ModuleRolePermission for module 19, role 13 created','App\\Models\\ModuleRolePermission','created',152,'App\\Models\\User',7,'{\"attributes\":{\"module_id\":19,\"role_id\":13,\"can_create\":false,\"can_read\":false,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-06-28 16:56:31','2026-06-28 16:56:31'),(69,'default','ModuleRolePermission for module 20, role 13 created','App\\Models\\ModuleRolePermission','created',153,'App\\Models\\User',7,'{\"attributes\":{\"module_id\":20,\"role_id\":13,\"can_create\":false,\"can_read\":false,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-06-28 16:56:31','2026-06-28 16:56:31'),(70,'default','ModuleRolePermission for module 21, role 13 created','App\\Models\\ModuleRolePermission','created',154,'App\\Models\\User',7,'{\"attributes\":{\"module_id\":21,\"role_id\":13,\"can_create\":false,\"can_read\":false,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-06-28 16:56:31','2026-06-28 16:56:31'),(71,'default','ModuleRolePermission for module 22, role 13 created','App\\Models\\ModuleRolePermission','created',155,'App\\Models\\User',7,'{\"attributes\":{\"module_id\":22,\"role_id\":13,\"can_create\":false,\"can_read\":false,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-06-28 16:56:31','2026-06-28 16:56:31'),(72,'default','ModuleRolePermission for module 23, role 13 created','App\\Models\\ModuleRolePermission','created',156,'App\\Models\\User',7,'{\"attributes\":{\"module_id\":23,\"role_id\":13,\"can_create\":false,\"can_read\":false,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-06-28 16:56:31','2026-06-28 16:56:31'),(73,'default','ModuleRolePermission for module 24, role 13 created','App\\Models\\ModuleRolePermission','created',157,'App\\Models\\User',7,'{\"attributes\":{\"module_id\":24,\"role_id\":13,\"can_create\":false,\"can_read\":false,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-06-28 16:56:31','2026-06-28 16:56:31'),(74,'default','ModuleRolePermission for module 25, role 13 created','App\\Models\\ModuleRolePermission','created',158,'App\\Models\\User',7,'{\"attributes\":{\"module_id\":25,\"role_id\":13,\"can_create\":false,\"can_read\":false,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-06-28 16:56:31','2026-06-28 16:56:31'),(75,'default','ModuleRolePermission for module 26, role 13 created','App\\Models\\ModuleRolePermission','created',159,'App\\Models\\User',7,'{\"attributes\":{\"module_id\":26,\"role_id\":13,\"can_create\":false,\"can_read\":false,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-06-28 16:56:31','2026-06-28 16:56:31'),(76,'default','ModuleRolePermission for module 27, role 13 created','App\\Models\\ModuleRolePermission','created',160,'App\\Models\\User',7,'{\"attributes\":{\"module_id\":27,\"role_id\":13,\"can_create\":false,\"can_read\":false,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-06-28 16:56:31','2026-06-28 16:56:31'),(77,'default','Template \'IT Support\' applied to role \'IT SUPPORT\'','HasinHayder\\Tyro\\Models\\Role','template_applied',13,'App\\Models\\User',7,'{\"template\":{\"id\":3,\"name\":\"IT Support\",\"slug\":\"it-support\"},\"role\":{\"id\":13,\"name\":\"IT SUPPORT\",\"slug\":\"SUPPORT\"},\"changed_count\":0,\"added_count\":25,\"unchanged_count\":0}',NULL,'2026-06-28 16:56:31','2026-06-28 16:56:31'),(78,'default','Privilege \"Manage Roles\" attached to role: IT SUPPORT','HasinHayder\\Tyro\\Models\\Role','updated',13,'App\\Models\\User',7,'{\"privilege_id\":8,\"privilege_name\":\"Manage Roles\",\"action\":\"attached\"}',NULL,'2026-06-28 16:57:09','2026-06-28 16:57:09'),(79,'default','Privilege \"Generate Reports\" attached to role: IT SUPPORT','HasinHayder\\Tyro\\Models\\Role','updated',13,'App\\Models\\User',7,'{\"privilege_id\":6,\"privilege_name\":\"Generate Reports\",\"action\":\"attached\"}',NULL,'2026-06-28 16:57:18','2026-06-28 16:57:18'),(80,'default','Privilege \"Manage Users\" attached to role: IT SUPPORT','HasinHayder\\Tyro\\Models\\Role','updated',13,'App\\Models\\User',7,'{\"privilege_id\":7,\"privilege_name\":\"Manage Users\",\"action\":\"attached\"}',NULL,'2026-06-28 16:57:21','2026-06-28 16:57:21'),(81,'default','Privilege \"View Billing\" attached to role: IT SUPPORT','HasinHayder\\Tyro\\Models\\Role','updated',13,'App\\Models\\User',7,'{\"privilege_id\":9,\"privilege_name\":\"View Billing\",\"action\":\"attached\"}',NULL,'2026-06-28 16:57:24','2026-06-28 16:57:24'),(82,'default','Privilege \"Wildcard\" attached to role: IT SUPPORT','HasinHayder\\Tyro\\Models\\Role','updated',13,'App\\Models\\User',7,'{\"privilege_id\":10,\"privilege_name\":\"Wildcard\",\"action\":\"attached\"}',NULL,'2026-06-28 16:57:27','2026-06-28 16:57:27'),(83,'default','Privilege created: IT SUPPORT',NULL,'created',NULL,'App\\Models\\User',7,'{\"name\":\"IT SUPPORT\",\"slug\":\"SUPPORT\"}',NULL,'2026-06-28 16:57:45','2026-06-28 16:57:45'),(84,'default','Privilege \"Manage Roles\" detached from role: IT SUPPORT','HasinHayder\\Tyro\\Models\\Role','updated',13,'App\\Models\\User',7,'{\"privilege_id\":8,\"privilege_name\":\"Manage Roles\",\"action\":\"detached\"}',NULL,'2026-06-28 17:04:36','2026-06-28 17:04:36'),(85,'default','Privilege \"Generate Reports\" detached from role: IT SUPPORT','HasinHayder\\Tyro\\Models\\Role','updated',13,'App\\Models\\User',7,'{\"privilege_id\":6,\"privilege_name\":\"Generate Reports\",\"action\":\"detached\"}',NULL,'2026-06-28 17:04:38','2026-06-28 17:04:38'),(86,'default','Privilege \"Manage Users\" detached from role: IT SUPPORT','HasinHayder\\Tyro\\Models\\Role','updated',13,'App\\Models\\User',7,'{\"privilege_id\":7,\"privilege_name\":\"Manage Users\",\"action\":\"detached\"}',NULL,'2026-06-28 17:04:41','2026-06-28 17:04:41'),(87,'default','Privilege \"View Billing\" detached from role: IT SUPPORT','HasinHayder\\Tyro\\Models\\Role','updated',13,'App\\Models\\User',7,'{\"privilege_id\":9,\"privilege_name\":\"View Billing\",\"action\":\"detached\"}',NULL,'2026-06-28 17:04:43','2026-06-28 17:04:43'),(88,'default','Privilege \"Wildcard\" detached from role: IT SUPPORT','HasinHayder\\Tyro\\Models\\Role','updated',13,'App\\Models\\User',7,'{\"privilege_id\":10,\"privilege_name\":\"Wildcard\",\"action\":\"detached\"}',NULL,'2026-06-28 17:04:45','2026-06-28 17:04:45'),(89,'default','created','domain','created',6,NULL,NULL,'{\"attributes\":{\"user_id\":8,\"module_id\":null,\"hosting_id\":null,\"service_provider_id\":null,\"name\":\"idempotent-test.com\",\"registration_date\":\"2023-12-29T00:00:00.000000Z\",\"expiry_date\":\"2027-06-15T00:00:00.000000Z\",\"auto_renew\":false,\"cost\":\"8.77\",\"status\":\"active\",\"cloudflare_status\":null,\"dns_servers\":null,\"notes\":null,\"monitoring_url\":null,\"last_ping_at\":null}}',NULL,'2026-06-29 18:23:41','2026-06-29 18:23:41'),(90,'default','created','App\\Models\\ExpiryTracker','created',3,NULL,NULL,'{\"attributes\":{\"user_id\":8,\"module_id\":1,\"service_provider_id\":null,\"name\":\"idempotent-test.com\",\"username\":null,\"login_url\":null,\"password\":null,\"expiry_date\":null,\"renewal_date\":null,\"cost\":null,\"status\":\"active\",\"notes\":null,\"monitoring_url\":null,\"last_ping_at\":null,\"email_notifications_enabled\":true,\"smtp_profile_id\":null,\"notify_days_before\":[30,15,7,1],\"notify_on_expiry_day\":false,\"notify_assigned_user\":true,\"notify_admins\":false,\"notify_custom_emails\":null,\"last_notification_sent_at\":null,\"next_notification_due_at\":null,\"disabled_by\":null,\"disabled_at\":null,\"disable_reason\":null,\"trackable_type\":\"domain\",\"trackable_id\":6}}',NULL,'2026-06-29 18:23:41','2026-06-29 18:23:41'),(91,'default','updated','domain','updated',6,NULL,NULL,'{\"attributes\":{\"user_id\":9,\"name\":\"updated-name.com\",\"expiry_date\":\"2028-01-01T00:00:00.000000Z\"},\"old\":{\"user_id\":8,\"name\":\"idempotent-test.com\",\"expiry_date\":\"2027-06-15T00:00:00.000000Z\"}}',NULL,'2026-06-29 18:23:41','2026-06-29 18:23:41'),(92,'default','updated','App\\Models\\ExpiryTracker','updated',3,NULL,NULL,'{\"attributes\":{\"user_id\":9,\"name\":\"updated-name.com\"},\"old\":{\"user_id\":8,\"name\":\"idempotent-test.com\"}}',NULL,'2026-06-29 18:23:41','2026-06-29 18:23:41'),(93,'default','created','service_provider','created',7,NULL,NULL,'{\"attributes\":{\"user_id\":10,\"module_id\":null,\"name\":\"Windler, Rolfson and Ortiz\",\"type\":\"email\",\"provider\":\"Ziemann, McLaughlin and Huels\",\"email\":null,\"website\":\"https:\\/\\/www.pagac.com\\/et-facilis-dolor-impedit-et\",\"password\":\"mraX^y,\",\"cost\":\"355.95\",\"start_date\":\"2024-08-11T00:00:00.000000Z\",\"expiry_date\":\"2026-10-15T00:00:00.000000Z\",\"status\":\"active\",\"notes\":null,\"monitoring_url\":null,\"last_ping_at\":null}}',NULL,'2026-06-29 18:23:41','2026-06-29 18:23:41'),(94,'default','created','domain_email','created',3,NULL,NULL,'{\"attributes\":{\"user_id\":8,\"module_id\":null,\"service_provider_id\":7,\"domain_id\":null,\"email\":\"test@example.com\",\"password\":null,\"storage_mb\":102400,\"cost\":\"39.35\",\"expiry_date\":\"2027-06-15T00:00:00.000000Z\",\"status\":\"active\",\"notes\":\"Illum mollitia consectetur aut.\",\"monitoring_url\":null,\"last_ping_at\":null}}',NULL,'2026-06-29 18:23:41','2026-06-29 18:23:41'),(95,'default','created','App\\Models\\ExpiryTracker','created',4,NULL,NULL,'{\"attributes\":{\"user_id\":8,\"module_id\":null,\"service_provider_id\":7,\"name\":\"test@example.com\",\"username\":null,\"login_url\":null,\"password\":null,\"expiry_date\":null,\"renewal_date\":null,\"cost\":null,\"status\":\"active\",\"notes\":null,\"monitoring_url\":null,\"last_ping_at\":null,\"email_notifications_enabled\":true,\"smtp_profile_id\":null,\"notify_days_before\":[30,15,7,1],\"notify_on_expiry_day\":false,\"notify_assigned_user\":true,\"notify_admins\":false,\"notify_custom_emails\":null,\"last_notification_sent_at\":null,\"next_notification_due_at\":null,\"disabled_by\":null,\"disabled_at\":null,\"disable_reason\":null,\"trackable_type\":\"domain_email\",\"trackable_id\":3}}',NULL,'2026-06-29 18:23:41','2026-06-29 18:23:41'),(96,'default','updated','domain_email','updated',3,NULL,NULL,'{\"attributes\":{\"email\":\"new@example.com\"},\"old\":{\"email\":\"test@example.com\"}}',NULL,'2026-06-29 18:23:41','2026-06-29 18:23:41'),(97,'default','updated','App\\Models\\ExpiryTracker','updated',4,NULL,NULL,'{\"attributes\":{\"name\":\"new@example.com\"},\"old\":{\"name\":\"test@example.com\"}}',NULL,'2026-06-29 18:23:41','2026-06-29 18:23:41'),(98,'default','created','domain','created',7,NULL,NULL,'{\"attributes\":{\"user_id\":11,\"module_id\":null,\"hosting_id\":null,\"service_provider_id\":null,\"name\":\"remove-test.com\",\"registration_date\":\"2022-12-16T00:00:00.000000Z\",\"expiry_date\":\"2027-06-15T00:00:00.000000Z\",\"auto_renew\":true,\"cost\":\"16.82\",\"status\":\"active\",\"cloudflare_status\":null,\"dns_servers\":null,\"notes\":null,\"monitoring_url\":null,\"last_ping_at\":null}}',NULL,'2026-06-29 18:24:32','2026-06-29 18:24:32'),(99,'default','created','App\\Models\\ExpiryTracker','created',5,NULL,NULL,'{\"attributes\":{\"user_id\":11,\"module_id\":1,\"service_provider_id\":null,\"name\":\"remove-test.com\",\"username\":null,\"login_url\":null,\"password\":null,\"expiry_date\":null,\"renewal_date\":null,\"cost\":null,\"status\":\"active\",\"notes\":null,\"monitoring_url\":null,\"last_ping_at\":null,\"email_notifications_enabled\":true,\"smtp_profile_id\":null,\"notify_days_before\":[30,15,7,1],\"notify_on_expiry_day\":false,\"notify_assigned_user\":true,\"notify_admins\":false,\"notify_custom_emails\":null,\"last_notification_sent_at\":null,\"next_notification_due_at\":null,\"disabled_by\":null,\"disabled_at\":null,\"disable_reason\":null,\"trackable_type\":\"domain\",\"trackable_id\":7}}',NULL,'2026-06-29 18:24:32','2026-06-29 18:24:32'),(100,'default','created','hosting','created',4,NULL,NULL,'{\"attributes\":{\"user_id\":11,\"module_id\":null,\"service_provider_id\":null,\"name\":\"hosting-test.com\",\"username\":\"igreen\",\"password\":\"_v~4&TUPjG^U\",\"cpanel_url\":\"http:\\/\\/www.lowe.info\\/aliquam-nemo-quo-officiis-amet-vel-facere-rem-esse\",\"plan\":\"Enterprise\",\"domain\":\"wuckert.com\",\"domain_ip\":null,\"mail_domain_ip\":null,\"cpanel_ip\":null,\"start_date\":\"2025-02-09T00:00:00.000000Z\",\"expiry_date\":\"2027-06-15T00:00:00.000000Z\",\"cost\":\"116.19\",\"status\":\"active\",\"notes\":\"Maiores aut et et quas.\",\"monitoring_url\":null,\"last_ping_at\":null}}',NULL,'2026-06-29 18:24:32','2026-06-29 18:24:32'),(101,'default','created','App\\Models\\ExpiryTracker','created',6,NULL,NULL,'{\"attributes\":{\"user_id\":11,\"module_id\":1,\"service_provider_id\":null,\"name\":\"remove-test.com\",\"username\":null,\"login_url\":null,\"password\":null,\"expiry_date\":null,\"renewal_date\":null,\"cost\":null,\"status\":\"active\",\"notes\":null,\"monitoring_url\":null,\"last_ping_at\":null,\"email_notifications_enabled\":true,\"smtp_profile_id\":null,\"notify_days_before\":[30,15,7,1],\"notify_on_expiry_day\":false,\"notify_assigned_user\":true,\"notify_admins\":false,\"notify_custom_emails\":null,\"last_notification_sent_at\":null,\"next_notification_due_at\":null,\"disabled_by\":null,\"disabled_at\":null,\"disable_reason\":null,\"trackable_type\":\"domain\",\"trackable_id\":7}}',NULL,'2026-06-29 18:24:32','2026-06-29 18:24:32'),(102,'default','created','domain','created',8,NULL,NULL,'{\"attributes\":{\"user_id\":11,\"module_id\":null,\"hosting_id\":null,\"service_provider_id\":null,\"name\":\"no-expiry.com\",\"registration_date\":\"2026-01-04T00:00:00.000000Z\",\"expiry_date\":null,\"auto_renew\":false,\"cost\":\"15.22\",\"status\":\"active\",\"cloudflare_status\":null,\"dns_servers\":[\"toy.com\",\"stark.net\"],\"notes\":null,\"monitoring_url\":null,\"last_ping_at\":null}}',NULL,'2026-06-29 18:24:32','2026-06-29 18:24:32'),(103,'default','created','App\\Models\\ExpiryTracker','created',7,NULL,NULL,'{\"attributes\":{\"user_id\":11,\"module_id\":1,\"service_provider_id\":null,\"name\":\"no-expiry.com\",\"username\":null,\"login_url\":null,\"password\":null,\"expiry_date\":null,\"renewal_date\":null,\"cost\":null,\"status\":\"active\",\"notes\":null,\"monitoring_url\":null,\"last_ping_at\":null,\"email_notifications_enabled\":true,\"smtp_profile_id\":null,\"notify_days_before\":[30,15,7,1],\"notify_on_expiry_day\":false,\"notify_assigned_user\":true,\"notify_admins\":false,\"notify_custom_emails\":null,\"last_notification_sent_at\":null,\"next_notification_due_at\":null,\"disabled_by\":null,\"disabled_at\":null,\"disable_reason\":null,\"trackable_type\":\"domain\",\"trackable_id\":8}}',NULL,'2026-06-29 18:24:32','2026-06-29 18:24:32'),(104,'default','created','domain','created',9,NULL,NULL,'{\"attributes\":{\"user_id\":12,\"module_id\":null,\"hosting_id\":null,\"service_provider_id\":null,\"name\":\"expiry-sync-test.com\",\"registration_date\":\"2024-08-23T00:00:00.000000Z\",\"expiry_date\":\"2027-06-15T00:00:00.000000Z\",\"auto_renew\":true,\"cost\":\"28.07\",\"status\":\"active\",\"cloudflare_status\":null,\"dns_servers\":null,\"notes\":\"Et neque quia animi adipisci quibusdam omnis.\",\"monitoring_url\":null,\"last_ping_at\":null}}',NULL,'2026-06-29 20:03:02','2026-06-29 20:03:02'),(105,'default','created','App\\Models\\ExpiryTracker','created',8,NULL,NULL,'{\"attributes\":{\"user_id\":12,\"module_id\":1,\"service_provider_id\":null,\"name\":\"expiry-sync-test.com\",\"username\":null,\"login_url\":null,\"password\":null,\"expiry_date\":\"2027-06-15T00:00:00.000000Z\",\"renewal_date\":null,\"cost\":null,\"status\":\"active\",\"notes\":null,\"monitoring_url\":null,\"last_ping_at\":null,\"email_notifications_enabled\":true,\"smtp_profile_id\":null,\"notify_days_before\":[30,15,7,1],\"notify_on_expiry_day\":false,\"notify_assigned_user\":true,\"notify_admins\":false,\"notify_custom_emails\":null,\"last_notification_sent_at\":null,\"next_notification_due_at\":null,\"disabled_by\":null,\"disabled_at\":null,\"disable_reason\":null,\"trackable_type\":\"domain\",\"trackable_id\":9}}',NULL,'2026-06-29 20:03:02','2026-06-29 20:03:02'),(106,'default','updated','domain','updated',9,NULL,NULL,'{\"attributes\":{\"expiry_date\":\"2028-01-01T00:00:00.000000Z\"},\"old\":{\"expiry_date\":\"2027-06-15T00:00:00.000000Z\"}}',NULL,'2026-06-29 20:03:02','2026-06-29 20:03:02'),(107,'default','updated','App\\Models\\ExpiryTracker','updated',8,NULL,NULL,'{\"attributes\":{\"expiry_date\":\"2028-01-01T00:00:00.000000Z\"},\"old\":{\"expiry_date\":\"2027-06-15T00:00:00.000000Z\"}}',NULL,'2026-06-29 20:03:02','2026-06-29 20:03:02'),(108,'default','Module Renewals updated','module','updated',8,NULL,NULL,'{\"attributes\":{\"name\":\"Renewals\"},\"old\":{\"name\":\"Expiry Trackers\"}}',NULL,'2026-07-02 22:20:06','2026-07-02 22:20:06'),(109,'default','ModuleRolePermission for module 1, role 15 created','App\\Models\\ModuleRolePermission','created',161,NULL,NULL,'{\"attributes\":{\"module_id\":1,\"role_id\":15,\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":true,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(110,'default','ModuleRolePermission for module 1, role 16 created','App\\Models\\ModuleRolePermission','created',162,NULL,NULL,'{\"attributes\":{\"module_id\":1,\"role_id\":16,\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(111,'default','ModuleRolePermission for module 1, role 14 created','App\\Models\\ModuleRolePermission','created',163,NULL,NULL,'{\"attributes\":{\"module_id\":1,\"role_id\":14,\"can_create\":true,\"can_read\":true,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(112,'default','ModuleRolePermission for module 2, role 15 created','App\\Models\\ModuleRolePermission','created',164,NULL,NULL,'{\"attributes\":{\"module_id\":2,\"role_id\":15,\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":true,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(113,'default','ModuleRolePermission for module 2, role 16 created','App\\Models\\ModuleRolePermission','created',165,NULL,NULL,'{\"attributes\":{\"module_id\":2,\"role_id\":16,\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(114,'default','ModuleRolePermission for module 2, role 14 created','App\\Models\\ModuleRolePermission','created',166,NULL,NULL,'{\"attributes\":{\"module_id\":2,\"role_id\":14,\"can_create\":true,\"can_read\":true,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(115,'default','ModuleRolePermission for module 3, role 15 created','App\\Models\\ModuleRolePermission','created',167,NULL,NULL,'{\"attributes\":{\"module_id\":3,\"role_id\":15,\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":true,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(116,'default','ModuleRolePermission for module 3, role 16 created','App\\Models\\ModuleRolePermission','created',168,NULL,NULL,'{\"attributes\":{\"module_id\":3,\"role_id\":16,\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(117,'default','ModuleRolePermission for module 3, role 14 created','App\\Models\\ModuleRolePermission','created',169,NULL,NULL,'{\"attributes\":{\"module_id\":3,\"role_id\":14,\"can_create\":true,\"can_read\":true,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(118,'default','ModuleRolePermission for module 4, role 15 created','App\\Models\\ModuleRolePermission','created',170,NULL,NULL,'{\"attributes\":{\"module_id\":4,\"role_id\":15,\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":true,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(119,'default','ModuleRolePermission for module 4, role 16 created','App\\Models\\ModuleRolePermission','created',171,NULL,NULL,'{\"attributes\":{\"module_id\":4,\"role_id\":16,\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(120,'default','ModuleRolePermission for module 4, role 14 created','App\\Models\\ModuleRolePermission','created',172,NULL,NULL,'{\"attributes\":{\"module_id\":4,\"role_id\":14,\"can_create\":true,\"can_read\":true,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(121,'default','ModuleRolePermission for module 5, role 15 created','App\\Models\\ModuleRolePermission','created',173,NULL,NULL,'{\"attributes\":{\"module_id\":5,\"role_id\":15,\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":true,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(122,'default','ModuleRolePermission for module 5, role 16 created','App\\Models\\ModuleRolePermission','created',174,NULL,NULL,'{\"attributes\":{\"module_id\":5,\"role_id\":16,\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(123,'default','ModuleRolePermission for module 5, role 14 created','App\\Models\\ModuleRolePermission','created',175,NULL,NULL,'{\"attributes\":{\"module_id\":5,\"role_id\":14,\"can_create\":true,\"can_read\":true,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(124,'default','ModuleRolePermission for module 6, role 15 created','App\\Models\\ModuleRolePermission','created',176,NULL,NULL,'{\"attributes\":{\"module_id\":6,\"role_id\":15,\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":true,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(125,'default','ModuleRolePermission for module 6, role 16 created','App\\Models\\ModuleRolePermission','created',177,NULL,NULL,'{\"attributes\":{\"module_id\":6,\"role_id\":16,\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(126,'default','ModuleRolePermission for module 6, role 14 created','App\\Models\\ModuleRolePermission','created',178,NULL,NULL,'{\"attributes\":{\"module_id\":6,\"role_id\":14,\"can_create\":true,\"can_read\":true,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(127,'default','ModuleRolePermission for module 7, role 15 created','App\\Models\\ModuleRolePermission','created',179,NULL,NULL,'{\"attributes\":{\"module_id\":7,\"role_id\":15,\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":true,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(128,'default','ModuleRolePermission for module 7, role 16 created','App\\Models\\ModuleRolePermission','created',180,NULL,NULL,'{\"attributes\":{\"module_id\":7,\"role_id\":16,\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(129,'default','ModuleRolePermission for module 7, role 14 created','App\\Models\\ModuleRolePermission','created',181,NULL,NULL,'{\"attributes\":{\"module_id\":7,\"role_id\":14,\"can_create\":true,\"can_read\":true,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(130,'default','ModuleRolePermission for module 8, role 15 created','App\\Models\\ModuleRolePermission','created',182,NULL,NULL,'{\"attributes\":{\"module_id\":8,\"role_id\":15,\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":true,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(131,'default','ModuleRolePermission for module 8, role 16 created','App\\Models\\ModuleRolePermission','created',183,NULL,NULL,'{\"attributes\":{\"module_id\":8,\"role_id\":16,\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(132,'default','ModuleRolePermission for module 8, role 14 created','App\\Models\\ModuleRolePermission','created',184,NULL,NULL,'{\"attributes\":{\"module_id\":8,\"role_id\":14,\"can_create\":true,\"can_read\":true,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(133,'default','ModuleRolePermission for module 9, role 15 created','App\\Models\\ModuleRolePermission','created',185,NULL,NULL,'{\"attributes\":{\"module_id\":9,\"role_id\":15,\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":true,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(134,'default','ModuleRolePermission for module 9, role 16 created','App\\Models\\ModuleRolePermission','created',186,NULL,NULL,'{\"attributes\":{\"module_id\":9,\"role_id\":16,\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(135,'default','ModuleRolePermission for module 9, role 14 created','App\\Models\\ModuleRolePermission','created',187,NULL,NULL,'{\"attributes\":{\"module_id\":9,\"role_id\":14,\"can_create\":true,\"can_read\":true,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(136,'default','ModuleRolePermission for module 10, role 15 created','App\\Models\\ModuleRolePermission','created',188,NULL,NULL,'{\"attributes\":{\"module_id\":10,\"role_id\":15,\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":true,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(137,'default','ModuleRolePermission for module 10, role 16 created','App\\Models\\ModuleRolePermission','created',189,NULL,NULL,'{\"attributes\":{\"module_id\":10,\"role_id\":16,\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(138,'default','ModuleRolePermission for module 10, role 14 created','App\\Models\\ModuleRolePermission','created',190,NULL,NULL,'{\"attributes\":{\"module_id\":10,\"role_id\":14,\"can_create\":true,\"can_read\":true,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(139,'default','ModuleRolePermission for module 12, role 15 created','App\\Models\\ModuleRolePermission','created',191,NULL,NULL,'{\"attributes\":{\"module_id\":12,\"role_id\":15,\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":true,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(140,'default','ModuleRolePermission for module 12, role 16 created','App\\Models\\ModuleRolePermission','created',192,NULL,NULL,'{\"attributes\":{\"module_id\":12,\"role_id\":16,\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(141,'default','ModuleRolePermission for module 12, role 14 created','App\\Models\\ModuleRolePermission','created',193,NULL,NULL,'{\"attributes\":{\"module_id\":12,\"role_id\":14,\"can_create\":true,\"can_read\":true,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(142,'default','ModuleRolePermission for module 13, role 15 created','App\\Models\\ModuleRolePermission','created',194,NULL,NULL,'{\"attributes\":{\"module_id\":13,\"role_id\":15,\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":true,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(143,'default','ModuleRolePermission for module 13, role 16 created','App\\Models\\ModuleRolePermission','created',195,NULL,NULL,'{\"attributes\":{\"module_id\":13,\"role_id\":16,\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(144,'default','ModuleRolePermission for module 13, role 14 created','App\\Models\\ModuleRolePermission','created',196,NULL,NULL,'{\"attributes\":{\"module_id\":13,\"role_id\":14,\"can_create\":true,\"can_read\":true,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(145,'default','ModuleRolePermission for module 14, role 15 created','App\\Models\\ModuleRolePermission','created',197,NULL,NULL,'{\"attributes\":{\"module_id\":14,\"role_id\":15,\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":true,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(146,'default','ModuleRolePermission for module 14, role 16 created','App\\Models\\ModuleRolePermission','created',198,NULL,NULL,'{\"attributes\":{\"module_id\":14,\"role_id\":16,\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(147,'default','ModuleRolePermission for module 14, role 14 created','App\\Models\\ModuleRolePermission','created',199,NULL,NULL,'{\"attributes\":{\"module_id\":14,\"role_id\":14,\"can_create\":true,\"can_read\":true,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(148,'default','ModuleRolePermission for module 15, role 15 created','App\\Models\\ModuleRolePermission','created',200,NULL,NULL,'{\"attributes\":{\"module_id\":15,\"role_id\":15,\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":true,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(149,'default','ModuleRolePermission for module 15, role 16 created','App\\Models\\ModuleRolePermission','created',201,NULL,NULL,'{\"attributes\":{\"module_id\":15,\"role_id\":16,\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(150,'default','ModuleRolePermission for module 15, role 14 created','App\\Models\\ModuleRolePermission','created',202,NULL,NULL,'{\"attributes\":{\"module_id\":15,\"role_id\":14,\"can_create\":true,\"can_read\":true,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(151,'default','ModuleRolePermission for module 16, role 15 created','App\\Models\\ModuleRolePermission','created',203,NULL,NULL,'{\"attributes\":{\"module_id\":16,\"role_id\":15,\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":true,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(152,'default','ModuleRolePermission for module 16, role 16 created','App\\Models\\ModuleRolePermission','created',204,NULL,NULL,'{\"attributes\":{\"module_id\":16,\"role_id\":16,\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(153,'default','ModuleRolePermission for module 16, role 14 created','App\\Models\\ModuleRolePermission','created',205,NULL,NULL,'{\"attributes\":{\"module_id\":16,\"role_id\":14,\"can_create\":true,\"can_read\":true,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(154,'default','ModuleRolePermission for module 17, role 15 created','App\\Models\\ModuleRolePermission','created',206,NULL,NULL,'{\"attributes\":{\"module_id\":17,\"role_id\":15,\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":true,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(155,'default','ModuleRolePermission for module 17, role 16 created','App\\Models\\ModuleRolePermission','created',207,NULL,NULL,'{\"attributes\":{\"module_id\":17,\"role_id\":16,\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(156,'default','ModuleRolePermission for module 17, role 14 created','App\\Models\\ModuleRolePermission','created',208,NULL,NULL,'{\"attributes\":{\"module_id\":17,\"role_id\":14,\"can_create\":true,\"can_read\":true,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(157,'default','ModuleRolePermission for module 18, role 15 created','App\\Models\\ModuleRolePermission','created',209,NULL,NULL,'{\"attributes\":{\"module_id\":18,\"role_id\":15,\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":true,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(158,'default','ModuleRolePermission for module 18, role 16 created','App\\Models\\ModuleRolePermission','created',210,NULL,NULL,'{\"attributes\":{\"module_id\":18,\"role_id\":16,\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(159,'default','ModuleRolePermission for module 18, role 14 created','App\\Models\\ModuleRolePermission','created',211,NULL,NULL,'{\"attributes\":{\"module_id\":18,\"role_id\":14,\"can_create\":true,\"can_read\":true,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(160,'default','ModuleRolePermission for module 19, role 15 created','App\\Models\\ModuleRolePermission','created',212,NULL,NULL,'{\"attributes\":{\"module_id\":19,\"role_id\":15,\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":true,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(161,'default','ModuleRolePermission for module 19, role 16 created','App\\Models\\ModuleRolePermission','created',213,NULL,NULL,'{\"attributes\":{\"module_id\":19,\"role_id\":16,\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(162,'default','ModuleRolePermission for module 19, role 14 created','App\\Models\\ModuleRolePermission','created',214,NULL,NULL,'{\"attributes\":{\"module_id\":19,\"role_id\":14,\"can_create\":true,\"can_read\":true,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(163,'default','ModuleRolePermission for module 20, role 15 created','App\\Models\\ModuleRolePermission','created',215,NULL,NULL,'{\"attributes\":{\"module_id\":20,\"role_id\":15,\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":true,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(164,'default','ModuleRolePermission for module 20, role 16 created','App\\Models\\ModuleRolePermission','created',216,NULL,NULL,'{\"attributes\":{\"module_id\":20,\"role_id\":16,\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(165,'default','ModuleRolePermission for module 20, role 14 created','App\\Models\\ModuleRolePermission','created',217,NULL,NULL,'{\"attributes\":{\"module_id\":20,\"role_id\":14,\"can_create\":true,\"can_read\":true,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(166,'default','ModuleRolePermission for module 21, role 15 created','App\\Models\\ModuleRolePermission','created',218,NULL,NULL,'{\"attributes\":{\"module_id\":21,\"role_id\":15,\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":true,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(167,'default','ModuleRolePermission for module 21, role 16 created','App\\Models\\ModuleRolePermission','created',219,NULL,NULL,'{\"attributes\":{\"module_id\":21,\"role_id\":16,\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(168,'default','ModuleRolePermission for module 21, role 14 created','App\\Models\\ModuleRolePermission','created',220,NULL,NULL,'{\"attributes\":{\"module_id\":21,\"role_id\":14,\"can_create\":true,\"can_read\":true,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(169,'default','ModuleRolePermission for module 22, role 15 created','App\\Models\\ModuleRolePermission','created',221,NULL,NULL,'{\"attributes\":{\"module_id\":22,\"role_id\":15,\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":true,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(170,'default','ModuleRolePermission for module 22, role 16 created','App\\Models\\ModuleRolePermission','created',222,NULL,NULL,'{\"attributes\":{\"module_id\":22,\"role_id\":16,\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(171,'default','ModuleRolePermission for module 22, role 14 created','App\\Models\\ModuleRolePermission','created',223,NULL,NULL,'{\"attributes\":{\"module_id\":22,\"role_id\":14,\"can_create\":true,\"can_read\":true,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(172,'default','ModuleRolePermission for module 23, role 15 created','App\\Models\\ModuleRolePermission','created',224,NULL,NULL,'{\"attributes\":{\"module_id\":23,\"role_id\":15,\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":true,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(173,'default','ModuleRolePermission for module 23, role 16 created','App\\Models\\ModuleRolePermission','created',225,NULL,NULL,'{\"attributes\":{\"module_id\":23,\"role_id\":16,\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(174,'default','ModuleRolePermission for module 23, role 14 created','App\\Models\\ModuleRolePermission','created',226,NULL,NULL,'{\"attributes\":{\"module_id\":23,\"role_id\":14,\"can_create\":true,\"can_read\":true,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(175,'default','ModuleRolePermission for module 24, role 15 created','App\\Models\\ModuleRolePermission','created',227,NULL,NULL,'{\"attributes\":{\"module_id\":24,\"role_id\":15,\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":true,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(176,'default','ModuleRolePermission for module 24, role 16 created','App\\Models\\ModuleRolePermission','created',228,NULL,NULL,'{\"attributes\":{\"module_id\":24,\"role_id\":16,\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(177,'default','ModuleRolePermission for module 24, role 14 created','App\\Models\\ModuleRolePermission','created',229,NULL,NULL,'{\"attributes\":{\"module_id\":24,\"role_id\":14,\"can_create\":true,\"can_read\":true,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(178,'default','ModuleRolePermission for module 25, role 15 created','App\\Models\\ModuleRolePermission','created',230,NULL,NULL,'{\"attributes\":{\"module_id\":25,\"role_id\":15,\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":true,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(179,'default','ModuleRolePermission for module 25, role 16 created','App\\Models\\ModuleRolePermission','created',231,NULL,NULL,'{\"attributes\":{\"module_id\":25,\"role_id\":16,\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(180,'default','ModuleRolePermission for module 25, role 14 created','App\\Models\\ModuleRolePermission','created',232,NULL,NULL,'{\"attributes\":{\"module_id\":25,\"role_id\":14,\"can_create\":true,\"can_read\":true,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(181,'default','ModuleRolePermission for module 26, role 15 created','App\\Models\\ModuleRolePermission','created',233,NULL,NULL,'{\"attributes\":{\"module_id\":26,\"role_id\":15,\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":true,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(182,'default','ModuleRolePermission for module 26, role 16 created','App\\Models\\ModuleRolePermission','created',234,NULL,NULL,'{\"attributes\":{\"module_id\":26,\"role_id\":16,\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(183,'default','ModuleRolePermission for module 26, role 14 created','App\\Models\\ModuleRolePermission','created',235,NULL,NULL,'{\"attributes\":{\"module_id\":26,\"role_id\":14,\"can_create\":true,\"can_read\":true,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(184,'default','ModuleRolePermission for module 27, role 15 created','App\\Models\\ModuleRolePermission','created',236,NULL,NULL,'{\"attributes\":{\"module_id\":27,\"role_id\":15,\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":true,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(185,'default','ModuleRolePermission for module 27, role 16 created','App\\Models\\ModuleRolePermission','created',237,NULL,NULL,'{\"attributes\":{\"module_id\":27,\"role_id\":16,\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(186,'default','ModuleRolePermission for module 27, role 14 created','App\\Models\\ModuleRolePermission','created',238,NULL,NULL,'{\"attributes\":{\"module_id\":27,\"role_id\":14,\"can_create\":true,\"can_read\":true,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false}}',NULL,'2026-07-02 22:20:08','2026-07-02 22:20:08');
/*!40000 ALTER TABLE `activity_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `asset_assignments`
--

DROP TABLE IF EXISTS `asset_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `asset_assignments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` bigint(20) unsigned NOT NULL,
  `assigned_to` bigint(20) unsigned NOT NULL,
  `department` varchar(255) DEFAULT NULL,
  `assigned_by` bigint(20) unsigned NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expected_return_at` timestamp NULL DEFAULT NULL,
  `returned_at` timestamp NULL DEFAULT NULL,
  `condition_on_return` varchar(255) DEFAULT NULL,
  `assignment_reason` varchar(255) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `asset_assignments_assigned_by_foreign` (`assigned_by`),
  KEY `asset_assignments_asset_id_index` (`asset_id`),
  KEY `asset_assignments_assigned_to_index` (`assigned_to`),
  KEY `asset_assignments_returned_at_index` (`returned_at`),
  CONSTRAINT `asset_assignments_asset_id_foreign` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `asset_assignments_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `asset_assignments_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asset_assignments`
--

LOCK TABLES `asset_assignments` WRITE;
/*!40000 ALTER TABLE `asset_assignments` DISABLE KEYS */;
/*!40000 ALTER TABLE `asset_assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `asset_categories`
--

DROP TABLE IF EXISTS `asset_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `asset_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `asset_categories_slug_unique` (`slug`),
  KEY `asset_categories_deleted_at_index` (`deleted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asset_categories`
--

LOCK TABLES `asset_categories` WRITE;
/*!40000 ALTER TABLE `asset_categories` DISABLE KEYS */;
INSERT INTO `asset_categories` VALUES (1,'Laptop','laptop','Portable computers including notebooks and ultrabooks',1,1,'2026-06-28 05:14:10','2026-06-28 05:14:10',NULL),(2,'Headphone','headphone','Audio headsets, headphones and earbuds',1,2,'2026-06-28 05:14:10','2026-06-28 05:14:10',NULL),(3,'Mouse','mouse','Computer mice and pointing devices',1,3,'2026-06-28 05:14:10','2026-06-28 05:14:10',NULL),(4,'Network Device','network-device','Routers, switches, firewalls, WiFi APs, IP phones, CCTV, NVR/DVR',1,4,'2026-06-28 05:14:10','2026-06-28 05:14:10',NULL);
/*!40000 ALTER TABLE `asset_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `asset_locations`
--

DROP TABLE IF EXISTS `asset_locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `asset_locations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `asset_locations_deleted_at_index` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asset_locations`
--

LOCK TABLES `asset_locations` WRITE;
/*!40000 ALTER TABLE `asset_locations` DISABLE KEYS */;
/*!40000 ALTER TABLE `asset_locations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `asset_types`
--

DROP TABLE IF EXISTS `asset_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `asset_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `brand` varchar(255) NOT NULL,
  `model_number` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `asset_types_category_id_brand_name_unique` (`category_id`,`brand`,`name`),
  KEY `asset_types_deleted_at_index` (`deleted_at`),
  CONSTRAINT `asset_types_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `asset_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asset_types`
--

LOCK TABLES `asset_types` WRITE;
/*!40000 ALTER TABLE `asset_types` DISABLE KEYS */;
INSERT INTO `asset_types` VALUES (1,1,'Latitude 5540','Dell','Latitude 5540',1,'2026-06-28 05:14:10','2026-06-28 05:14:10',NULL),(2,1,'Latitude 7440','Dell','Latitude 7440',1,'2026-06-28 05:14:10','2026-06-28 05:14:10',NULL),(3,1,'EliteBook 840 G10','HP','840 G10',1,'2026-06-28 05:14:10','2026-06-28 05:14:10',NULL),(4,1,'ThinkPad X1 Carbon Gen 11','Lenovo','X1C Gen 11',1,'2026-06-28 05:14:10','2026-06-28 05:14:10',NULL),(5,1,'MacBook Pro 14\"','Apple','MPP14',1,'2026-06-28 05:14:10','2026-06-28 05:14:10',NULL),(6,2,'WH-1000XM5','Sony','WH1000XM5',1,'2026-06-28 05:14:10','2026-06-28 05:14:10',NULL),(7,2,'Evolve2 65','Jabra','EV65',1,'2026-06-28 05:14:10','2026-06-28 05:14:10',NULL),(8,2,'Zone 900','Logitech','Z900',1,'2026-06-28 05:14:10','2026-06-28 05:14:10',NULL),(9,3,'MX Master 3S','Logitech','MX3S',1,'2026-06-28 05:14:10','2026-06-28 05:14:10',NULL),(10,3,'Surface Mouse','Microsoft','SFC-00001',1,'2026-06-28 05:14:10','2026-06-28 05:14:10',NULL),(11,3,'DeathAdder V3','Razer','DAV3',1,'2026-06-28 05:14:10','2026-06-28 05:14:10',NULL),(12,4,'Catalyst 9300-24T','Cisco','C9300-24T',1,'2026-06-28 05:14:10','2026-06-28 05:14:10',NULL),(13,4,'RB4011iGS+RM','MikroTik','RB4011',1,'2026-06-28 05:14:10','2026-06-28 05:14:10',NULL),(14,4,'EdgeRouter 12','Ubiquiti','ER-12',1,'2026-06-28 05:14:10','2026-06-28 05:14:10',NULL),(15,4,'FortiGate 60F','Fortinet','FG-60F',1,'2026-06-28 05:14:10','2026-06-28 05:14:10',NULL),(16,4,'UniFi 6 Pro','Ubiquiti','U6-Pro',1,'2026-06-28 05:14:10','2026-06-28 05:14:10',NULL),(17,4,'IP Phone 8845','Cisco','CP-8845',1,'2026-06-28 05:14:10','2026-06-28 05:14:10',NULL),(18,4,'DS-2CD2386G2-I','Hikvision','2CD2386G2',1,'2026-06-28 05:14:10','2026-06-28 05:14:10',NULL),(19,4,'DS-7608NI-I2/8P','Hikvision','DS-7608NI',1,'2026-06-28 05:14:10','2026-06-28 05:14:10',NULL);
/*!40000 ALTER TABLE `asset_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `assets`
--

DROP TABLE IF EXISTS `assets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `assets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `asset_tag` varchar(255) NOT NULL,
  `category_id` bigint(20) unsigned NOT NULL,
  `type_id` bigint(20) unsigned NOT NULL,
  `serial_number` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'available',
  `assigned_to` bigint(20) unsigned DEFAULT NULL,
  `location_id` bigint(20) unsigned DEFAULT NULL,
  `department` varchar(255) DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  `return_date` date DEFAULT NULL,
  `condition` varchar(255) DEFAULT NULL,
  `specifications` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`specifications`)),
  `notes` text DEFAULT NULL,
  `primary_image` varchar(255) DEFAULT NULL,
  `vault_entry_id` bigint(20) unsigned DEFAULT NULL,
  `qr_identifier` varchar(255) DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `module_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `assets_asset_tag_unique` (`asset_tag`),
  UNIQUE KEY `assets_qr_identifier_unique` (`qr_identifier`),
  KEY `assets_category_id_foreign` (`category_id`),
  KEY `assets_type_id_foreign` (`type_id`),
  KEY `assets_vault_entry_id_foreign` (`vault_entry_id`),
  KEY `assets_user_id_foreign` (`user_id`),
  KEY `assets_module_id_foreign` (`module_id`),
  KEY `assets_status_index` (`status`),
  KEY `assets_assigned_to_index` (`assigned_to`),
  KEY `assets_location_id_index` (`location_id`),
  KEY `assets_condition_index` (`condition`),
  KEY `assets_deleted_at_index` (`deleted_at`),
  CONSTRAINT `assets_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `assets_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `asset_categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `assets_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `asset_locations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `assets_module_id_foreign` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE SET NULL,
  CONSTRAINT `assets_type_id_foreign` FOREIGN KEY (`type_id`) REFERENCES `asset_types` (`id`) ON DELETE CASCADE,
  CONSTRAINT `assets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `assets_vault_entry_id_foreign` FOREIGN KEY (`vault_entry_id`) REFERENCES `password_vault` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assets`
--

LOCK TABLES `assets` WRITE;
/*!40000 ALTER TABLE `assets` DISABLE KEYS */;
/*!40000 ALTER TABLE `assets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attachments`
--

DROP TABLE IF EXISTS `attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attachments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `notable_type` varchar(255) DEFAULT NULL,
  `notable_id` bigint(20) unsigned DEFAULT NULL,
  `filename` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `mime_type` varchar(255) NOT NULL,
  `size` bigint(20) NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attachments_user_id_foreign` (`user_id`),
  KEY `attachments_notable_type_notable_id_index` (`notable_type`,`notable_id`),
  KEY `attachments_deleted_at_index` (`deleted_at`),
  CONSTRAINT `attachments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attachments`
--

LOCK TABLES `attachments` WRITE;
/*!40000 ALTER TABLE `attachments` DISABLE KEYS */;
/*!40000 ALTER TABLE `attachments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
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
-- Table structure for table `domain_emails`
--

DROP TABLE IF EXISTS `domain_emails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `domain_emails` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `module_id` bigint(20) unsigned DEFAULT NULL,
  `service_provider_id` bigint(20) unsigned DEFAULT NULL,
  `domain_id` bigint(20) unsigned DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password` text DEFAULT NULL,
  `storage_mb` int(11) DEFAULT NULL,
  `cost` decimal(10,2) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `monitoring_url` varchar(255) DEFAULT NULL,
  `last_ping_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `domain_emails_user_id_foreign` (`user_id`),
  KEY `domain_emails_module_id_foreign` (`module_id`),
  KEY `domain_emails_domain_id_foreign` (`domain_id`),
  KEY `domain_emails_service_provider_id_foreign` (`service_provider_id`),
  KEY `domain_emails_deleted_at_index` (`deleted_at`),
  KEY `domain_emails_status_index` (`status`),
  CONSTRAINT `domain_emails_domain_id_foreign` FOREIGN KEY (`domain_id`) REFERENCES `domains` (`id`) ON DELETE SET NULL,
  CONSTRAINT `domain_emails_module_id_foreign` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE SET NULL,
  CONSTRAINT `domain_emails_service_provider_id_foreign` FOREIGN KEY (`service_provider_id`) REFERENCES `service_providers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `domain_emails_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `domain_emails`
--

LOCK TABLES `domain_emails` WRITE;
/*!40000 ALTER TABLE `domain_emails` DISABLE KEYS */;
INSERT INTO `domain_emails` VALUES (1,5,6,4,2,'info@example.com','eyJpdiI6ImRvT3hndFNQY1dJWldSclQvMWM5UUE9PSIsInZhbHVlIjoiVytkdk9ueHVEWFU4ZjZjcnplTVZsZz09IiwibWFjIjoiNTUwZjRkYjdkYjFiNGFkYzA2ZWNkMzA1ZmNiYWZiMzJkNTcwNjgwOGRhYzQ3YWZlZDA1Zjk1YTU3ZmE5N2MwZSIsInRhZyI6IiJ9',1024,0.00,NULL,'active','Demo domain email entry.',NULL,NULL,'2026-06-28 05:14:11','2026-06-28 16:37:51','2026-06-28 16:37:51'),(2,6,6,3,2,'support@example.com','eyJpdiI6IkNzbU85VHYwSWtNaldRMkFwL1hXeHc9PSIsInZhbHVlIjoiNFh6VmkyeVphcHBBMkhvdHJaZEZZZz09IiwibWFjIjoiZTFhODg0YzhkMzhiYWM1NmVhYThiNmVlMTY2NWY1YmQ4ODQ1ZTU4YzkxNjA0ZjliOGIyOWI1NzlmOGQyYmMxYSIsInRhZyI6IiJ9',1024,0.00,NULL,'active','Demo domain email entry.',NULL,NULL,'2026-06-28 05:14:11','2026-06-28 16:37:53','2026-06-28 16:37:53'),(3,8,NULL,7,NULL,'new@example.com',NULL,102400,39.35,'2027-06-15','active','Illum mollitia consectetur aut.',NULL,NULL,'2026-06-29 18:23:41','2026-06-29 18:23:41',NULL);
/*!40000 ALTER TABLE `domain_emails` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `domains`
--

DROP TABLE IF EXISTS `domains`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `domains` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `module_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `registration_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `auto_renew` tinyint(1) NOT NULL DEFAULT 0,
  `cost` decimal(10,2) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `cloudflare_status` varchar(20) DEFAULT NULL,
  `dns_servers` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `monitoring_url` varchar(255) DEFAULT NULL,
  `last_ping_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `hosting_id` bigint(20) unsigned DEFAULT NULL,
  `service_provider_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `domains_user_id_foreign` (`user_id`),
  KEY `domains_module_id_foreign` (`module_id`),
  KEY `domains_hosting_id_foreign` (`hosting_id`),
  KEY `domains_service_provider_id_foreign` (`service_provider_id`),
  KEY `domains_deleted_at_index` (`deleted_at`),
  KEY `domains_status_index` (`status`),
  CONSTRAINT `domains_hosting_id_foreign` FOREIGN KEY (`hosting_id`) REFERENCES `hostings` (`id`) ON DELETE SET NULL,
  CONSTRAINT `domains_module_id_foreign` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE SET NULL,
  CONSTRAINT `domains_service_provider_id_foreign` FOREIGN KEY (`service_provider_id`) REFERENCES `service_providers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `domains_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `domains`
--

LOCK TABLES `domains` WRITE;
/*!40000 ALTER TABLE `domains` DISABLE KEYS */;
INSERT INTO `domains` VALUES (2,5,1,'example.com','2025-06-28','2027-06-28',1,12.99,'active',NULL,'[\"ns1.example.com\",\"ns2.example.com\"]','Demo domain entry.',NULL,NULL,'2026-06-28 05:14:11','2026-06-28 16:37:06','2026-06-28 16:37:06',NULL,2),(3,6,1,'mysite.org','2025-06-28','2027-06-28',1,14.99,'active',NULL,'[\"ns1.mysite.org\",\"ns2.mysite.org\"]','Demo domain entry.',NULL,NULL,'2026-06-28 05:14:11','2026-06-28 16:37:08','2026-06-28 16:37:08',NULL,3),(4,7,1,'alphatach.com','2026-06-29','2027-06-29',0,11.69,'active','enabled','[]',NULL,NULL,NULL,'2026-06-28 16:49:01','2026-06-28 16:50:14','2026-06-28 16:50:14',3,5),(5,7,1,'alphatach.com','2026-06-29','2027-06-25',0,11.29,'active','enabled','[]',NULL,NULL,NULL,'2026-06-28 16:51:47','2026-06-28 16:52:58',NULL,3,6),(6,9,NULL,'updated-name.com','2023-12-29','2028-01-01',0,8.77,'active',NULL,NULL,NULL,NULL,NULL,'2026-06-29 18:23:41','2026-06-29 18:23:41',NULL,NULL,NULL),(7,11,NULL,'remove-test.com','2022-12-16','2027-06-15',1,16.82,'active',NULL,NULL,NULL,NULL,NULL,'2026-06-29 18:24:32','2026-06-29 18:24:32',NULL,NULL,NULL),(8,11,NULL,'no-expiry.com','2026-01-04',NULL,0,15.22,'active',NULL,'[\"toy.com\",\"stark.net\"]',NULL,NULL,NULL,'2026-06-29 18:24:32','2026-06-29 18:24:32',NULL,NULL,NULL),(9,12,NULL,'expiry-sync-test.com','2024-08-23','2028-01-01',1,28.07,'active',NULL,NULL,'Et neque quia animi adipisci quibusdam omnis.',NULL,NULL,'2026-06-29 20:03:02','2026-06-29 20:03:02',NULL,NULL,NULL);
/*!40000 ALTER TABLE `domains` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `expiry_tracker_notifications`
--

DROP TABLE IF EXISTS `expiry_tracker_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `expiry_tracker_notifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `expiry_tracker_id` bigint(20) unsigned NOT NULL,
  `smtp_profile_id` bigint(20) unsigned DEFAULT NULL,
  `sender_email` varchar(255) NOT NULL,
  `reminder_day` int(11) NOT NULL,
  `recipient_email` varchar(255) NOT NULL,
  `recipient_type` varchar(50) NOT NULL,
  `trigger_source` varchar(20) NOT NULL DEFAULT 'cron',
  `status` varchar(20) NOT NULL DEFAULT 'queued',
  `sent_at` timestamp NULL DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `etn_tracker_day_email_idx` (`expiry_tracker_id`,`reminder_day`,`recipient_email`),
  KEY `etn_status_idx` (`status`),
  KEY `etn_smtp_profile_idx` (`smtp_profile_id`),
  KEY `etn_trigger_idx` (`trigger_source`),
  KEY `expiry_tracker_notifications_smtp_profile_id_index` (`smtp_profile_id`),
  CONSTRAINT `expiry_tracker_notifications_expiry_tracker_id_foreign` FOREIGN KEY (`expiry_tracker_id`) REFERENCES `expiry_trackers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expiry_tracker_notifications`
--

LOCK TABLES `expiry_tracker_notifications` WRITE;
/*!40000 ALTER TABLE `expiry_tracker_notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `expiry_tracker_notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `expiry_trackers`
--

DROP TABLE IF EXISTS `expiry_trackers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `expiry_trackers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `module_id` bigint(20) unsigned DEFAULT NULL,
  `service_provider_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `login_url` varchar(255) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `renewal_date` date DEFAULT NULL,
  `cost` decimal(10,2) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `email_notifications_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `monitoring_url` varchar(255) DEFAULT NULL,
  `last_ping_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `smtp_profile_id` bigint(20) unsigned DEFAULT NULL,
  `notify_days_before` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '[30,15,7,1]' CHECK (json_valid(`notify_days_before`)),
  `notify_on_expiry_day` tinyint(1) NOT NULL DEFAULT 0,
  `notify_assigned_user` tinyint(1) NOT NULL DEFAULT 1,
  `notify_admins` tinyint(1) NOT NULL DEFAULT 0,
  `notify_custom_emails` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`notify_custom_emails`)),
  `last_notification_sent_at` timestamp NULL DEFAULT NULL,
  `next_notification_due_at` date DEFAULT NULL,
  `disabled_by` bigint(20) unsigned DEFAULT NULL,
  `disabled_at` timestamp NULL DEFAULT NULL,
  `disable_reason` varchar(255) DEFAULT NULL,
  `trackable_type` varchar(255) DEFAULT NULL,
  `trackable_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `expiry_trackers_user_id_foreign` (`user_id`),
  KEY `expiry_trackers_module_id_foreign` (`module_id`),
  KEY `expiry_trackers_expiry_date_index` (`expiry_date`),
  KEY `expiry_trackers_service_provider_id_foreign` (`service_provider_id`),
  KEY `expiry_trackers_smtp_profile_id_foreign` (`smtp_profile_id`),
  KEY `expiry_trackers_disabled_by_foreign` (`disabled_by`),
  KEY `expiry_trackers_deleted_at_index` (`deleted_at`),
  KEY `expiry_trackers_status_index` (`status`),
  KEY `expiry_trackers_next_notification_due_at_index` (`next_notification_due_at`),
  KEY `expiry_trackers_email_notifications_enabled_index` (`email_notifications_enabled`),
  KEY `expiry_trackers_trackable_type_trackable_id_index` (`trackable_type`,`trackable_id`),
  CONSTRAINT `expiry_trackers_disabled_by_foreign` FOREIGN KEY (`disabled_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `expiry_trackers_module_id_foreign` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE SET NULL,
  CONSTRAINT `expiry_trackers_service_provider_id_foreign` FOREIGN KEY (`service_provider_id`) REFERENCES `service_providers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `expiry_trackers_smtp_profile_id_foreign` FOREIGN KEY (`smtp_profile_id`) REFERENCES `smtp_profiles` (`id`) ON DELETE SET NULL,
  CONSTRAINT `expiry_trackers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expiry_trackers`
--

LOCK TABLES `expiry_trackers` WRITE;
/*!40000 ALTER TABLE `expiry_trackers` DISABLE KEYS */;
INSERT INTO `expiry_trackers` VALUES (1,5,8,3,'SSL Certificate','admin','https://namecheap.com/ssl','2026-09-28','2026-09-12',99.00,'active','Demo SSL Certificate tracker.',0,NULL,NULL,'2026-06-28 05:14:11','2026-06-28 16:37:40','2026-06-28 16:37:40',NULL,'[30,15,7,1]',0,1,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(2,6,8,3,'Code Signing Cert','testuser','https://namecheap.com/code-signing','2026-09-28','2026-09-12',299.00,'active','Demo Code Signing Cert tracker.',0,NULL,NULL,'2026-06-28 05:14:11','2026-06-28 16:37:42','2026-06-28 16:37:42',NULL,'[30,15,7,1]',0,1,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(3,9,1,NULL,'updated-name.com',NULL,NULL,NULL,NULL,NULL,'active',NULL,1,NULL,NULL,'2026-06-29 18:23:41','2026-06-29 18:23:41',NULL,NULL,'[30,15,7,1]',0,1,0,NULL,NULL,NULL,NULL,NULL,NULL,'domain',6),(4,8,NULL,7,'new@example.com',NULL,NULL,NULL,NULL,NULL,'active',NULL,1,NULL,NULL,'2026-06-29 18:23:41','2026-06-29 18:23:41',NULL,NULL,'[30,15,7,1]',0,1,0,NULL,NULL,NULL,NULL,NULL,NULL,'domain_email',3),(5,11,1,NULL,'remove-test.com',NULL,NULL,NULL,NULL,NULL,'active',NULL,1,NULL,NULL,'2026-06-29 18:24:32','2026-06-29 18:24:32','2026-06-29 18:24:32',NULL,'[30,15,7,1]',0,1,0,NULL,NULL,NULL,NULL,NULL,NULL,'domain',7),(6,11,1,NULL,'remove-test.com',NULL,NULL,NULL,NULL,NULL,'active',NULL,1,NULL,NULL,'2026-06-29 18:24:32','2026-06-29 18:24:32',NULL,NULL,'[30,15,7,1]',0,1,0,NULL,NULL,NULL,NULL,NULL,NULL,'domain',7),(7,11,1,NULL,'no-expiry.com',NULL,NULL,NULL,NULL,NULL,'active',NULL,1,NULL,NULL,'2026-06-29 18:24:32','2026-06-29 18:24:32',NULL,NULL,'[30,15,7,1]',0,1,0,NULL,NULL,NULL,NULL,NULL,NULL,'domain',8),(8,12,1,NULL,'expiry-sync-test.com',NULL,NULL,'2028-01-01',NULL,NULL,'active',NULL,1,NULL,NULL,'2026-06-29 20:03:02','2026-06-29 20:03:02',NULL,NULL,'[30,15,7,1]',0,1,0,NULL,NULL,NULL,NULL,NULL,NULL,'domain',9);
/*!40000 ALTER TABLE `expiry_trackers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
-- Table structure for table `features`
--

DROP TABLE IF EXISTS `features`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `features` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `features_slug_unique` (`slug`),
  KEY `features_created_by_foreign` (`created_by`),
  KEY `features_updated_by_foreign` (`updated_by`),
  KEY `features_deleted_at_index` (`deleted_at`),
  CONSTRAINT `features_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `features_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `features`
--

LOCK TABLES `features` WRITE;
/*!40000 ALTER TABLE `features` DISABLE KEYS */;
INSERT INTO `features` VALUES (1,'Infrastructure','infrastructure','Manage domains, hosting, VPS, VoIP and other infrastructure services','server',1,NULL,NULL,'2026-06-28 05:14:10','2026-06-28 05:14:10',NULL),(2,'Productivity','productivity','Tasks, notes, vault and monitoring tools','briefcase',1,NULL,NULL,'2026-06-28 05:14:10','2026-06-28 05:14:10',NULL),(3,'Administration','administration','User management, roles, permissions and system settings','shield',1,NULL,NULL,'2026-06-28 05:14:10','2026-06-28 05:14:10',NULL),(4,'Integration','integration','Webhooks, tokens, import/export and reporting','link',1,NULL,NULL,'2026-06-28 05:14:10','2026-06-28 05:14:10',NULL);
/*!40000 ALTER TABLE `features` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hostings`
--

DROP TABLE IF EXISTS `hostings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hostings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `module_id` bigint(20) unsigned DEFAULT NULL,
  `service_provider_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` text DEFAULT NULL,
  `cpanel_url` varchar(255) DEFAULT NULL,
  `plan` varchar(255) DEFAULT NULL,
  `domain` varchar(255) DEFAULT NULL,
  `domain_ip` varchar(45) DEFAULT NULL,
  `mail_domain_ip` varchar(45) DEFAULT NULL,
  `cpanel_ip` varchar(45) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `cost` decimal(10,2) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `monitoring_url` varchar(255) DEFAULT NULL,
  `last_ping_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hostings_user_id_foreign` (`user_id`),
  KEY `hostings_module_id_foreign` (`module_id`),
  KEY `hostings_service_provider_id_foreign` (`service_provider_id`),
  KEY `hostings_deleted_at_index` (`deleted_at`),
  KEY `hostings_status_index` (`status`),
  CONSTRAINT `hostings_module_id_foreign` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE SET NULL,
  CONSTRAINT `hostings_service_provider_id_foreign` FOREIGN KEY (`service_provider_id`) REFERENCES `service_providers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `hostings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hostings`
--

LOCK TABLES `hostings` WRITE;
/*!40000 ALTER TABLE `hostings` DISABLE KEYS */;
INSERT INTO `hostings` VALUES (1,5,2,3,'Main Website Hosting','mainuser','eyJpdiI6IjRPRUtweHBVOEdvcC9xbDVWeHNLM0E9PSIsInZhbHVlIjoiRFZ1N1o3dkovQmE4U2lkdzUyeC90QT09IiwibWFjIjoiY2JkNjI3OGU4YWNmOTI0NDg1NzNhNTU3ZDVjODU5YTcxMWYzNzUzYTJjYWMyYzA0ZjVlOGU4ZjMyZGFlMGJjMCIsInRhZyI6IiJ9','https://cpanel.example.com','Business','example.com',NULL,NULL,NULL,'2025-12-28','2026-12-28',29.99,'active','Demo Main Website Hosting entry.',NULL,NULL,'2026-06-28 05:14:11','2026-06-28 16:37:01','2026-06-28 16:37:01'),(2,6,2,2,'Client Portal Hosting','portaladmin','eyJpdiI6Ikx0OUpyam55bFc1djMrcGVnZzJWeVE9PSIsInZhbHVlIjoiMXRJa2d3SzZhUTFSWUdhWFFLQ254dz09IiwibWFjIjoiYzgyOWYwMDExMDY3MjhhMWJhMjk0NGVlNDgwZjEwOGU5ZTA4NmZiMWM4NmM3YzU3YTEzMTk0MjViNTNkN2ZlZiIsInRhZyI6IiJ9','https://cpanel.clientportal.com','Premium','clientportal.com',NULL,NULL,NULL,'2025-12-28','2026-12-28',49.99,'active','Demo Client Portal Hosting entry.',NULL,NULL,'2026-06-28 05:14:11','2026-06-28 16:37:03','2026-06-28 16:37:03'),(3,7,NULL,NULL,'alphatach.com','h3llsing@gmail.com','eyJpdiI6ImF2eE5SbTdWcEQvdG1XQnVodWFhVXc9PSIsInZhbHVlIjoiRmZhWmoyQUtJRVJPWVhEZEtoM1NMZz09IiwibWFjIjoiZTRmZGQ2MTFmNjA1ODc0ZDlmZjRkM2NiN2UyZTkzM2RkOGVlM2MyMzc2YWEwODM4YWMwNTI4NzAxNWVjN2YwZCIsInRhZyI6IiJ9','https://alphatach.com/cpanel','Prime Plus','alphatach.com','192.168.100.1','192.168.100.1','192.168.100.1','2026-06-29','2027-06-26',71.99,'active',NULL,NULL,NULL,'2026-06-28 16:47:14','2026-06-28 16:47:49',NULL),(4,11,NULL,NULL,'hosting-test.com','igreen','eyJpdiI6IkU1cGw1dTNLOHBsRVIrN3ZndU93cWc9PSIsInZhbHVlIjoiK2t2VlJsODdHQmw2S3lodlhtVDF1UT09IiwibWFjIjoiMGM3MmM2ZTFkMGFkYzUzMTgwODY0MGQ0ZjIyZTc0NGJlMjRiNDAwZmIyN2JhOGQ1ODhlMWFjNTMyNGEwYjc3ZiIsInRhZyI6IiJ9','http://www.lowe.info/aliquam-nemo-quo-officiis-amet-vel-facere-rem-esse','Enterprise','wuckert.com',NULL,NULL,NULL,'2025-02-09','2027-06-15',116.19,'active','Maiores aut et et quas.',NULL,NULL,'2026-06-29 18:24:32','2026-06-29 18:24:32',NULL);
/*!40000 ALTER TABLE `hostings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = utf8 */;
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
-- Table structure for table `login_audits`
--

DROP TABLE IF EXISTS `login_audits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `login_audits` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `event` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `login_audits_user_id_index` (`user_id`),
  KEY `login_audits_event_index` (`event`),
  KEY `login_audits_created_at_index` (`created_at`),
  KEY `login_audits_email_index` (`email`),
  CONSTRAINT `login_audits_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login_audits`
--

LOCK TABLES `login_audits` WRITE;
/*!40000 ALTER TABLE `login_audits` DISABLE KEYS */;
INSERT INTO `login_audits` VALUES (1,NULL,'admin@tyro.project','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','login_failed','2026-06-28 03:16:21','2026-06-28 03:16:21'),(2,NULL,'admin@tyro.project','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','login_failed','2026-06-28 05:01:55','2026-06-28 05:01:55'),(3,NULL,'admin@tyro.project','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','login_failed','2026-06-28 05:04:59','2026-06-28 05:04:59'),(4,NULL,'admin@tyro.project','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','login_failed','2026-06-28 05:05:01','2026-06-28 05:05:01'),(5,NULL,'admin@tyro.project','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','login_failed','2026-06-28 05:05:10','2026-06-28 05:05:10'),(6,NULL,'admin@tyro.project','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','login_failed','2026-06-28 05:07:35','2026-06-28 05:07:35'),(7,NULL,'admin@tyro.project','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','login_failed','2026-06-28 05:07:49','2026-06-28 05:07:49'),(8,NULL,'admin@tyro.project','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','login_failed','2026-06-28 05:08:15','2026-06-28 05:08:15'),(9,NULL,'admin@tyro.project','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','login_failed','2026-06-28 05:13:14','2026-06-28 05:13:14'),(10,NULL,'admin@tyro.project','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','login_failed','2026-06-28 05:13:15','2026-06-28 05:13:15'),(11,5,'admin@tyro.project','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','login_success','2026-06-28 05:17:05','2026-06-28 05:17:05'),(12,5,'admin@tyro.project','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','login_success','2026-06-28 13:13:24','2026-06-28 13:13:24'),(13,5,'admin@tyro.project','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','logout','2026-06-28 16:35:58','2026-06-28 16:35:58'),(14,7,'h3llsing@gmail.com','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','login_success','2026-06-28 16:36:02','2026-06-28 16:36:02'),(15,7,'h3llsing@gmail.com','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','logout','2026-07-01 17:46:50','2026-07-01 17:46:50'),(16,7,'h3llsing@gmail.com','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','login_success','2026-07-01 17:46:54','2026-07-01 17:46:54'),(17,NULL,'admin@tyro.project','127.0.0.1','curl/8.18.0','login_failed','2026-07-01 18:44:53','2026-07-01 18:44:53'),(18,NULL,'admin@tyro.project','127.0.0.1','curl/8.18.0','login_failed','2026-07-01 18:48:17','2026-07-01 18:48:17'),(19,NULL,'admin@tyro.project','127.0.0.1','curl/8.18.0','login_failed','2026-07-01 19:06:14','2026-07-01 19:06:14'),(20,NULL,'test@example.com','127.0.0.1','curl/8.18.0','login_failed','2026-07-01 19:07:59','2026-07-01 19:07:59'),(21,NULL,'test@example.com','127.0.0.1','curl/8.18.0','login_failed','2026-07-01 19:08:35','2026-07-01 19:08:35'),(22,7,'h3llsing@gmail.com','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','login_success','2026-07-01 20:25:03','2026-07-01 20:25:03'),(23,7,'h3llsing@gmail.com','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','login_success','2026-07-02 14:58:07','2026-07-02 14:58:07'),(24,7,'h3llsing@gmail.com','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','logout','2026-07-02 15:47:59','2026-07-02 15:47:59'),(25,7,'h3llsing@gmail.com','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','login_success','2026-07-02 15:48:01','2026-07-02 15:48:01'),(26,7,'h3llsing@gmail.com','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','logout','2026-07-02 19:11:15','2026-07-02 19:11:15'),(27,7,'h3llsing@gmail.com','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','login_success','2026-07-02 19:11:19','2026-07-02 19:11:19'),(28,7,'h3llsing@gmail.com','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','login_success','2026-07-02 19:11:24','2026-07-02 19:11:24'),(29,NULL,'admin@tyro.project','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8115','login_failed','2026-07-02 19:58:50','2026-07-02 19:58:50'),(30,NULL,'test@example.com','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8115','login_failed','2026-07-02 19:59:17','2026-07-02 19:59:17'),(31,NULL,'admin@tyro.project','::1',NULL,'login_failed','2026-07-02 20:01:17','2026-07-02 20:01:17'),(32,16,'t_8f770c87@t.co','::1',NULL,'login_success','2026-07-02 20:03:06','2026-07-02 20:03:06'),(33,17,'t_2f0f4e05@t.co','::1',NULL,'login_success','2026-07-02 20:04:31','2026-07-02 20:04:31'),(34,18,'t_772e0405@t.co','::1',NULL,'login_success','2026-07-02 20:05:06','2026-07-02 20:05:06'),(35,7,'h3llsing@gmail.com','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','logout','2026-07-02 22:04:25','2026-07-02 22:04:25');
/*!40000 ALTER TABLE `login_audits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2022_05_17_181447_create_roles_table',1),(5,'2022_05_17_181456_create_user_roles_table',1),(6,'2025_01_01_000001_create_attachments_table',1),(7,'2025_01_01_000001_create_privileges_table',1),(8,'2025_01_01_000002_create_privilege_role_table',1),(9,'2025_01_01_000003_add_suspension_columns_to_users_table',1),(10,'2026_02_15_000000_create_tyro_audit_logs_table',1),(11,'2026_05_23_121243_create_personal_access_tokens_table',1),(12,'2026_05_23_121348_create_features_table',1),(13,'2026_05_23_121530_create_modules_table',1),(14,'2026_05_23_121531_create_module_role_permissions_table',1),(15,'2026_05_23_122139_create_tasks_table',1),(16,'2026_05_23_122140_create_task_user_table',1),(17,'2026_05_23_122456_create_notes_table',1),(18,'2026_05_23_123751_create_activity_log_table',1),(19,'2026_05_23_123752_add_event_column_to_activity_log_table',1),(20,'2026_05_23_123753_add_batch_uuid_column_to_activity_log_table',1),(21,'2026_05_23_124105_create_notifications_table',1),(22,'2026_05_23_124106_create_password_vault_table',1),(23,'2026_05_24_000002_add_suspended_and_softdeletes_to_users',1),(24,'2026_05_24_000003_create_login_audits_table',1),(25,'2026_05_24_053553_create_expiry_trackers_table',1),(26,'2026_05_24_054011_create_domains_table',1),(27,'2026_05_24_054201_create_hostings_table',1),(28,'2026_05_24_054202_create_vps_table',1),(29,'2026_05_24_070001_create_voip_table',1),(30,'2026_05_24_070002_create_service_providers_table',1),(31,'2026_05_24_070003_create_domain_emails_table',1),(32,'2026_05_24_070004_create_other_services_table',1),(33,'2026_05_24_080000_add_monitoring_to_service_tables',1),(34,'2026_05_24_090000_create_webhooks_table',1),(35,'2026_06_21_000001_add_indexes_to_expiry_trackers_and_notifications',1),(36,'2026_06_22_055918_add_soft_deletes_to_notes_table',1),(37,'2026_06_22_081951_add_password_to_domain_emails_table',1),(38,'2026_06_22_100920_add_credentials_to_hostings_table',1),(39,'2026_06_22_105805_add_hosting_id_to_domains_table',1),(40,'2026_06_22_111154_add_service_provider_id_to_hostings_domains_vps',1),(41,'2026_06_22_235118_add_voip_new_fields_to_voip_table',1),(42,'2026_06_23_000001_add_service_provider_id_to_voip_other_services_expiry_trackers',1),(43,'2026_06_23_000002_add_service_provider_id_to_domain_emails',1),(44,'2026_06_23_182722_remove_password_from_expiry_trackers_table',1),(45,'2026_06_23_184212_add_password_to_vps_and_service_providers',1),(46,'2026_06_25_000001_add_vps_new_fields',1),(47,'2026_06_25_000002_add_hosting_ip_fields',1),(48,'2026_06_25_000003_add_email_to_service_providers',1),(49,'2026_06_25_000004_add_cloudflare_status_to_domains',1),(50,'2026_06_25_000005_create_user_module_permissions_table',1),(51,'2026_06_25_000006_add_can_reveal_to_module_role_permissions',1),(52,'2026_06_25_090300_create_role_templates_table',1),(53,'2026_06_25_100000_create_smtp_profiles_table',1),(54,'2026_06_25_100001_add_notification_columns_to_expiry_trackers',1),(55,'2026_06_25_100002_create_expiry_tracker_notifications_table',1),(56,'2026_06_26_000001_create_asset_categories_table',1),(57,'2026_06_26_000002_create_asset_types_table',1),(58,'2026_06_26_000003_create_asset_locations_table',1),(59,'2026_06_26_000004_create_assets_table',1),(60,'2026_06_26_000005_create_asset_assignments_table',1),(61,'2026_06_27_000001_add_performance_indexes',1),(62,'2026_06_30_000001_add_trackable_to_expiry_trackers_table',2);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `module_role_permissions`
--

DROP TABLE IF EXISTS `module_role_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `module_role_permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `module_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  `can_create` tinyint(1) NOT NULL DEFAULT 0,
  `can_read` tinyint(1) NOT NULL DEFAULT 0,
  `can_update` tinyint(1) NOT NULL DEFAULT 0,
  `can_delete` tinyint(1) NOT NULL DEFAULT 0,
  `can_approve` tinyint(1) NOT NULL DEFAULT 0,
  `can_export` tinyint(1) NOT NULL DEFAULT 0,
  `can_reveal` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `module_role_permissions_module_id_role_id_unique` (`module_id`,`role_id`),
  KEY `module_role_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `module_role_permissions_module_id_foreign` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE,
  CONSTRAINT `module_role_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=239 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `module_role_permissions`
--

LOCK TABLES `module_role_permissions` WRITE;
/*!40000 ALTER TABLE `module_role_permissions` DISABLE KEYS */;
INSERT INTO `module_role_permissions` VALUES (1,1,12,1,1,1,1,1,1,1,'2026-06-28 05:14:10','2026-06-28 05:14:10'),(2,2,12,1,1,1,1,1,1,1,'2026-06-28 05:14:10','2026-06-28 05:14:10'),(3,3,12,1,1,1,1,1,1,1,'2026-06-28 05:14:10','2026-06-28 05:14:10'),(4,4,12,1,1,1,1,1,1,1,'2026-06-28 05:14:10','2026-06-28 05:14:10'),(5,5,12,1,1,1,1,1,1,1,'2026-06-28 05:14:10','2026-06-28 05:14:10'),(6,6,12,1,1,1,1,1,1,1,'2026-06-28 05:14:10','2026-06-28 05:14:10'),(7,7,12,1,1,1,1,1,1,1,'2026-06-28 05:14:10','2026-06-28 05:14:10'),(8,8,12,1,1,1,1,1,1,1,'2026-06-28 05:14:10','2026-06-28 05:14:10'),(9,9,12,1,1,1,1,1,1,1,'2026-06-28 05:14:10','2026-06-28 05:14:10'),(10,10,12,1,1,1,1,1,1,1,'2026-06-28 05:14:10','2026-06-28 05:14:10'),(11,11,12,1,1,1,1,1,1,1,'2026-06-28 05:14:10','2026-06-28 05:14:10'),(12,12,12,1,1,1,1,1,1,1,'2026-06-28 05:14:10','2026-06-28 05:14:10'),(13,13,12,1,1,1,1,1,1,1,'2026-06-28 05:14:10','2026-06-28 05:14:10'),(14,14,12,1,1,1,1,1,1,1,'2026-06-28 05:14:10','2026-06-28 05:14:10'),(15,15,12,1,1,1,1,1,1,1,'2026-06-28 05:14:10','2026-06-28 05:14:10'),(16,16,12,1,1,1,1,1,1,1,'2026-06-28 05:14:10','2026-06-28 05:14:10'),(17,17,12,1,1,1,1,1,1,1,'2026-06-28 05:14:10','2026-06-28 05:14:10'),(18,18,12,1,1,1,1,1,1,1,'2026-06-28 05:14:10','2026-06-28 05:14:10'),(19,19,12,1,1,1,1,1,1,1,'2026-06-28 05:14:10','2026-06-28 05:14:10'),(20,20,12,1,1,1,1,1,1,1,'2026-06-28 05:14:10','2026-06-28 05:14:10'),(21,21,12,1,1,1,1,1,1,1,'2026-06-28 05:14:10','2026-06-28 05:14:10'),(22,22,12,1,1,1,1,1,1,1,'2026-06-28 05:14:10','2026-06-28 05:14:10'),(23,23,12,1,1,1,1,1,1,1,'2026-06-28 05:14:10','2026-06-28 05:14:10'),(24,24,12,1,1,1,1,1,1,1,'2026-06-28 05:14:10','2026-06-28 05:14:10'),(25,25,12,1,1,1,1,1,1,1,'2026-06-28 05:14:10','2026-06-28 05:14:10'),(26,26,12,1,1,1,1,1,1,1,'2026-06-28 05:14:10','2026-06-28 05:14:10'),(27,27,12,1,1,1,1,1,1,1,'2026-06-28 05:14:10','2026-06-28 05:14:10'),(28,1,7,1,1,1,1,0,1,0,'2026-06-28 05:14:10','2026-06-28 05:14:10'),(32,2,7,1,1,1,1,0,1,0,'2026-06-28 05:14:10','2026-06-28 05:14:10'),(36,3,7,1,1,1,1,0,1,0,'2026-06-28 05:14:10','2026-06-28 05:14:10'),(40,4,7,1,1,1,1,0,1,0,'2026-06-28 05:14:11','2026-06-28 05:14:11'),(44,5,7,1,1,1,1,0,1,0,'2026-06-28 05:14:11','2026-06-28 05:14:11'),(48,6,7,1,1,1,1,0,1,0,'2026-06-28 05:14:11','2026-06-28 05:14:11'),(52,7,7,1,1,1,1,0,1,0,'2026-06-28 05:14:11','2026-06-28 05:14:11'),(56,8,7,1,1,1,1,0,1,0,'2026-06-28 05:14:11','2026-06-28 05:14:11'),(60,9,7,1,1,1,1,0,1,0,'2026-06-28 05:14:11','2026-06-28 05:14:11'),(64,10,7,1,1,1,1,0,1,0,'2026-06-28 05:14:11','2026-06-28 05:14:11'),(68,11,7,1,1,1,1,0,1,0,'2026-06-28 05:14:11','2026-06-28 05:14:11'),(72,12,7,1,1,1,1,0,1,0,'2026-06-28 05:14:11','2026-06-28 05:14:11'),(76,13,7,1,1,1,1,0,1,0,'2026-06-28 05:14:11','2026-06-28 05:14:11'),(80,14,7,1,1,1,1,0,1,0,'2026-06-28 05:14:11','2026-06-28 05:14:11'),(84,15,7,1,1,1,1,0,1,0,'2026-06-28 05:14:11','2026-06-28 05:14:11'),(88,16,7,1,1,1,1,0,1,0,'2026-06-28 05:14:11','2026-06-28 05:14:11'),(92,17,7,1,1,1,1,0,1,0,'2026-06-28 05:14:11','2026-06-28 05:14:11'),(96,18,7,1,1,1,1,0,1,0,'2026-06-28 05:14:11','2026-06-28 05:14:11'),(100,19,7,1,1,1,1,0,1,0,'2026-06-28 05:14:11','2026-06-28 05:14:11'),(104,20,7,1,1,1,1,0,1,0,'2026-06-28 05:14:11','2026-06-28 05:14:11'),(108,21,7,1,1,1,1,0,1,0,'2026-06-28 05:14:11','2026-06-28 05:14:11'),(112,22,7,1,1,1,1,0,1,0,'2026-06-28 05:14:11','2026-06-28 05:14:11'),(116,23,7,1,1,1,1,0,1,0,'2026-06-28 05:14:11','2026-06-28 05:14:11'),(120,24,7,1,1,1,1,0,1,0,'2026-06-28 05:14:11','2026-06-28 05:14:11'),(124,25,7,1,1,1,1,0,1,0,'2026-06-28 05:14:11','2026-06-28 05:14:11'),(128,26,7,1,1,1,1,0,1,0,'2026-06-28 05:14:11','2026-06-28 05:14:11'),(132,27,7,1,1,1,1,0,1,0,'2026-06-28 05:14:11','2026-06-28 05:14:11'),(136,1,13,1,1,1,0,0,0,1,'2026-06-28 16:56:31','2026-06-28 16:56:31'),(137,2,13,1,1,1,0,0,0,1,'2026-06-28 16:56:31','2026-06-28 16:56:31'),(138,3,13,1,1,1,0,0,0,1,'2026-06-28 16:56:31','2026-06-28 16:56:31'),(139,4,13,1,1,1,0,0,0,1,'2026-06-28 16:56:31','2026-06-28 16:56:31'),(140,5,13,1,1,1,0,0,0,1,'2026-06-28 16:56:31','2026-06-28 16:56:31'),(141,6,13,1,1,1,0,0,0,1,'2026-06-28 16:56:31','2026-06-28 16:56:31'),(142,7,13,0,0,0,0,0,0,0,'2026-06-28 16:56:31','2026-06-28 16:56:31'),(143,8,13,0,0,0,0,0,0,0,'2026-06-28 16:56:31','2026-06-28 16:56:31'),(144,10,13,0,0,0,0,0,0,0,'2026-06-28 16:56:31','2026-06-28 16:56:31'),(145,12,13,0,0,0,0,0,0,0,'2026-06-28 16:56:31','2026-06-28 16:56:31'),(146,13,13,0,0,0,0,0,0,0,'2026-06-28 16:56:31','2026-06-28 16:56:31'),(147,14,13,0,0,0,0,0,0,0,'2026-06-28 16:56:31','2026-06-28 16:56:31'),(148,15,13,0,0,0,0,0,0,0,'2026-06-28 16:56:31','2026-06-28 16:56:31'),(149,16,13,0,0,0,0,0,0,0,'2026-06-28 16:56:31','2026-06-28 16:56:31'),(150,17,13,0,0,0,0,0,0,0,'2026-06-28 16:56:31','2026-06-28 16:56:31'),(151,18,13,0,0,0,0,0,0,0,'2026-06-28 16:56:31','2026-06-28 16:56:31'),(152,19,13,0,0,0,0,0,0,0,'2026-06-28 16:56:31','2026-06-28 16:56:31'),(153,20,13,0,0,0,0,0,0,0,'2026-06-28 16:56:31','2026-06-28 16:56:31'),(154,21,13,0,0,0,0,0,0,0,'2026-06-28 16:56:31','2026-06-28 16:56:31'),(155,22,13,0,0,0,0,0,0,0,'2026-06-28 16:56:31','2026-06-28 16:56:31'),(156,23,13,0,0,0,0,0,0,0,'2026-06-28 16:56:31','2026-06-28 16:56:31'),(157,24,13,0,0,0,0,0,0,0,'2026-06-28 16:56:31','2026-06-28 16:56:31'),(158,25,13,0,0,0,0,0,0,0,'2026-06-28 16:56:31','2026-06-28 16:56:31'),(159,26,13,0,0,0,0,0,0,0,'2026-06-28 16:56:31','2026-06-28 16:56:31'),(160,27,13,0,0,0,0,0,0,0,'2026-06-28 16:56:31','2026-06-28 16:56:31'),(161,1,15,1,1,1,0,0,1,0,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(162,1,16,1,1,1,0,0,0,0,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(163,1,14,1,1,0,0,0,0,0,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(164,2,15,1,1,1,0,0,1,0,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(165,2,16,1,1,1,0,0,0,0,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(166,2,14,1,1,0,0,0,0,0,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(167,3,15,1,1,1,0,0,1,0,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(168,3,16,1,1,1,0,0,0,0,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(169,3,14,1,1,0,0,0,0,0,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(170,4,15,1,1,1,0,0,1,0,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(171,4,16,1,1,1,0,0,0,0,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(172,4,14,1,1,0,0,0,0,0,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(173,5,15,1,1,1,0,0,1,0,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(174,5,16,1,1,1,0,0,0,0,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(175,5,14,1,1,0,0,0,0,0,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(176,6,15,1,1,1,0,0,1,0,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(177,6,16,1,1,1,0,0,0,0,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(178,6,14,1,1,0,0,0,0,0,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(179,7,15,1,1,1,0,0,1,0,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(180,7,16,1,1,1,0,0,0,0,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(181,7,14,1,1,0,0,0,0,0,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(182,8,15,1,1,1,0,0,1,0,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(183,8,16,1,1,1,0,0,0,0,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(184,8,14,1,1,0,0,0,0,0,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(185,9,15,1,1,1,0,0,1,0,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(186,9,16,1,1,1,0,0,0,0,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(187,9,14,1,1,0,0,0,0,0,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(188,10,15,1,1,1,0,0,1,0,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(189,10,16,1,1,1,0,0,0,0,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(190,10,14,1,1,0,0,0,0,0,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(191,12,15,1,1,1,0,0,1,0,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(192,12,16,1,1,1,0,0,0,0,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(193,12,14,1,1,0,0,0,0,0,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(194,13,15,1,1,1,0,0,1,0,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(195,13,16,1,1,1,0,0,0,0,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(196,13,14,1,1,0,0,0,0,0,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(197,14,15,1,1,1,0,0,1,0,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(198,14,16,1,1,1,0,0,0,0,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(199,14,14,1,1,0,0,0,0,0,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(200,15,15,1,1,1,0,0,1,0,'2026-07-02 22:20:07','2026-07-02 22:20:07'),(201,15,16,1,1,1,0,0,0,0,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(202,15,14,1,1,0,0,0,0,0,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(203,16,15,1,1,1,0,0,1,0,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(204,16,16,1,1,1,0,0,0,0,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(205,16,14,1,1,0,0,0,0,0,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(206,17,15,1,1,1,0,0,1,0,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(207,17,16,1,1,1,0,0,0,0,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(208,17,14,1,1,0,0,0,0,0,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(209,18,15,1,1,1,0,0,1,0,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(210,18,16,1,1,1,0,0,0,0,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(211,18,14,1,1,0,0,0,0,0,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(212,19,15,1,1,1,0,0,1,0,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(213,19,16,1,1,1,0,0,0,0,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(214,19,14,1,1,0,0,0,0,0,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(215,20,15,1,1,1,0,0,1,0,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(216,20,16,1,1,1,0,0,0,0,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(217,20,14,1,1,0,0,0,0,0,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(218,21,15,1,1,1,0,0,1,0,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(219,21,16,1,1,1,0,0,0,0,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(220,21,14,1,1,0,0,0,0,0,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(221,22,15,1,1,1,0,0,1,0,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(222,22,16,1,1,1,0,0,0,0,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(223,22,14,1,1,0,0,0,0,0,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(224,23,15,1,1,1,0,0,1,0,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(225,23,16,1,1,1,0,0,0,0,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(226,23,14,1,1,0,0,0,0,0,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(227,24,15,1,1,1,0,0,1,0,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(228,24,16,1,1,1,0,0,0,0,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(229,24,14,1,1,0,0,0,0,0,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(230,25,15,1,1,1,0,0,1,0,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(231,25,16,1,1,1,0,0,0,0,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(232,25,14,1,1,0,0,0,0,0,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(233,26,15,1,1,1,0,0,1,0,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(234,26,16,1,1,1,0,0,0,0,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(235,26,14,1,1,0,0,0,0,0,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(236,27,15,1,1,1,0,0,1,0,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(237,27,16,1,1,1,0,0,0,0,'2026-07-02 22:20:08','2026-07-02 22:20:08'),(238,27,14,1,1,0,0,0,0,0,'2026-07-02 22:20:08','2026-07-02 22:20:08');
/*!40000 ALTER TABLE `module_role_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `modules`
--

DROP TABLE IF EXISTS `modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `modules` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `feature_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `modules_feature_id_slug_unique` (`feature_id`,`slug`),
  KEY `modules_created_by_foreign` (`created_by`),
  KEY `modules_updated_by_foreign` (`updated_by`),
  KEY `modules_deleted_at_index` (`deleted_at`),
  CONSTRAINT `modules_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `modules_feature_id_foreign` FOREIGN KEY (`feature_id`) REFERENCES `features` (`id`) ON DELETE CASCADE,
  CONSTRAINT `modules_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `modules`
--

LOCK TABLES `modules` WRITE;
/*!40000 ALTER TABLE `modules` DISABLE KEYS */;
INSERT INTO `modules` VALUES (1,1,'Domains','domains',NULL,1,NULL,NULL,'2026-06-28 05:14:10','2026-06-28 05:14:10',NULL),(2,1,'Hosting','hostings',NULL,1,NULL,NULL,'2026-06-28 05:14:10','2026-06-28 05:14:10',NULL),(3,1,'VPS','vps',NULL,1,NULL,NULL,'2026-06-28 05:14:10','2026-06-28 05:14:10',NULL),(4,1,'VoIP','voip',NULL,1,NULL,NULL,'2026-06-28 05:14:10','2026-06-28 05:14:10',NULL),(5,1,'Service Providers','service-providers',NULL,1,NULL,NULL,'2026-06-28 05:14:10','2026-06-28 05:14:10',NULL),(6,1,'Domain Emails','domain-emails',NULL,1,NULL,NULL,'2026-06-28 05:14:10','2026-06-28 05:14:10',NULL),(7,1,'Other Services','other-services',NULL,1,NULL,NULL,'2026-06-28 05:14:10','2026-06-28 05:14:10',NULL),(8,1,'Renewals','expiry-trackers',NULL,1,NULL,NULL,'2026-06-28 05:14:10','2026-07-02 22:20:06',NULL),(9,1,'Assets','assets',NULL,1,NULL,NULL,'2026-06-28 05:14:10','2026-06-28 05:14:10',NULL),(10,2,'Tasks','tasks',NULL,1,NULL,NULL,'2026-06-28 05:14:10','2026-06-28 05:14:10',NULL),(11,2,'Notes','notes',NULL,1,NULL,NULL,'2026-06-28 05:14:10','2026-06-28 16:41:18','2026-06-28 16:41:18'),(12,2,'Vault','vault',NULL,1,NULL,NULL,'2026-06-28 05:14:10','2026-06-28 05:14:10',NULL),(13,2,'Monitor','monitor',NULL,1,NULL,NULL,'2026-06-28 05:14:10','2026-06-28 05:14:10',NULL),(14,2,'Calendar','calendar',NULL,1,NULL,NULL,'2026-06-28 05:14:10','2026-06-28 05:14:10',NULL),(15,3,'Users','users',NULL,1,NULL,NULL,'2026-06-28 05:14:10','2026-06-28 05:14:10',NULL),(16,3,'Roles','roles',NULL,1,NULL,NULL,'2026-06-28 05:14:10','2026-06-28 05:14:10',NULL),(17,3,'Privileges','privileges',NULL,1,NULL,NULL,'2026-06-28 05:14:10','2026-06-28 05:14:10',NULL),(18,3,'Module Permissions','module-permissions',NULL,1,NULL,NULL,'2026-06-28 05:14:10','2026-06-28 05:14:10',NULL),(19,3,'Activity Logs','activity-logs',NULL,1,NULL,NULL,'2026-06-28 05:14:10','2026-06-28 05:14:10',NULL),(20,3,'Login Audits','login-audits',NULL,1,NULL,NULL,'2026-06-28 05:14:10','2026-06-28 05:14:10',NULL),(21,3,'Notifications','notifications',NULL,1,NULL,NULL,'2026-06-28 05:14:10','2026-06-28 05:14:10',NULL),(22,3,'Attachments','attachments',NULL,1,NULL,NULL,'2026-06-28 05:14:10','2026-06-28 05:14:10',NULL),(23,4,'Webhooks','webhooks',NULL,1,NULL,NULL,'2026-06-28 05:14:10','2026-06-28 05:14:10',NULL),(24,4,'API Tokens','tokens',NULL,1,NULL,NULL,'2026-06-28 05:14:10','2026-06-28 05:14:10',NULL),(25,4,'Import','import',NULL,1,NULL,NULL,'2026-06-28 05:14:10','2026-06-28 05:14:10',NULL),(26,4,'Export','export',NULL,1,NULL,NULL,'2026-06-28 05:14:10','2026-06-28 05:14:10',NULL),(27,4,'Reports','reports',NULL,1,NULL,NULL,'2026-06-28 05:14:10','2026-06-28 05:14:10',NULL);
/*!40000 ALTER TABLE `modules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notes`
--

DROP TABLE IF EXISTS `notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `content` text NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `notable_type` varchar(255) DEFAULT NULL,
  `notable_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notes_user_id_foreign` (`user_id`),
  KEY `notes_notable_type_notable_id_index` (`notable_type`,`notable_id`),
  KEY `notes_deleted_at_index` (`deleted_at`),
  CONSTRAINT `notes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notes`
--

LOCK TABLES `notes` WRITE;
/*!40000 ALTER TABLE `notes` DISABLE KEYS */;
INSERT INTO `notes` VALUES (1,'Important: Remember to renew the main domain before expiry.',5,NULL,NULL,'2026-06-28 05:14:11','2026-06-28 16:41:38','2026-06-28 16:41:38'),(2,'Server maintenance scheduled for next Saturday at 2 AM.',6,NULL,NULL,'2026-06-28 05:14:11','2026-06-28 16:41:39','2026-06-28 16:41:39');
/*!40000 ALTER TABLE `notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`),
  KEY `notifications_type_notifiable_id_index` (`type`,`notifiable_id`),
  KEY `notifications_read_at_index` (`read_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `other_services`
--

DROP TABLE IF EXISTS `other_services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `other_services` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `module_id` bigint(20) unsigned DEFAULT NULL,
  `service_provider_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `service_type` varchar(255) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` text DEFAULT NULL,
  `login_url` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `cost` decimal(10,2) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `monitoring_url` varchar(255) DEFAULT NULL,
  `last_ping_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `other_services_user_id_foreign` (`user_id`),
  KEY `other_services_module_id_foreign` (`module_id`),
  KEY `other_services_service_provider_id_foreign` (`service_provider_id`),
  KEY `other_services_deleted_at_index` (`deleted_at`),
  KEY `other_services_status_index` (`status`),
  CONSTRAINT `other_services_module_id_foreign` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE SET NULL,
  CONSTRAINT `other_services_service_provider_id_foreign` FOREIGN KEY (`service_provider_id`) REFERENCES `service_providers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `other_services_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `other_services`
--

LOCK TABLES `other_services` WRITE;
/*!40000 ALTER TABLE `other_services` DISABLE KEYS */;
INSERT INTO `other_services` VALUES (1,5,7,2,'Slack Premium','saas','admin','eyJpdiI6IlVEaUxEWC9lbXhuWHVQUVNKMWozN2c9PSIsInZhbHVlIjoiUStwN3VUNm1wcXVYZ1JMdjlXVSswUT09IiwibWFjIjoiZTc1YmVhMGEyOTc3ODM1ODExZmY0YWExOWRjZmJlZmEyY2ExYjMwNzQ1ZGRlOGY3YjQwYzJiZWE5ZmRkZTBmMCIsInRhZyI6IiJ9','https://slack.com/signin',NULL,8.00,NULL,'2027-06-28','active','Demo Slack Premium subscription.',NULL,NULL,'2026-06-28 05:14:11','2026-06-28 16:37:32','2026-06-28 16:37:32'),(2,6,7,2,'GitHub Enterprise','saas','testuser','eyJpdiI6ImlZVnFrbE9vL3lkWnlGUVhLd1hFbnc9PSIsInZhbHVlIjoiMXJMT1ZpbTNVVitPRDU3T3RQK21vUT09IiwibWFjIjoiZGVlMDBjZDUzNTY5NDRiMTY0MjIyMmY1MDc0YWM4NGQwNjM1NzA0YTMzZWQxOThlODJkZmQyZjk2YTI1NjYzOCIsInRhZyI6IiJ9','https://github.com/login',NULL,21.00,NULL,'2027-06-28','active','Demo GitHub Enterprise subscription.',NULL,NULL,'2026-06-28 05:14:11','2026-06-28 16:37:34','2026-06-28 16:37:34');
/*!40000 ALTER TABLE `other_services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
-- Table structure for table `password_vault`
--

DROP TABLE IF EXISTS `password_vault`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_vault` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `module_id` bigint(20) unsigned DEFAULT NULL,
  `service_name` varchar(255) NOT NULL,
  `service_url` varchar(255) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `encrypted_password` text NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `password_vault_user_id_foreign` (`user_id`),
  KEY `password_vault_module_id_foreign` (`module_id`),
  KEY `password_vault_deleted_at_index` (`deleted_at`),
  CONSTRAINT `password_vault_module_id_foreign` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE SET NULL,
  CONSTRAINT `password_vault_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_vault`
--

LOCK TABLES `password_vault` WRITE;
/*!40000 ALTER TABLE `password_vault` DISABLE KEYS */;
INSERT INTO `password_vault` VALUES (1,5,12,'AWS Root Account','https://aws.amazon.com/console','admin@example.com','eyJpdiI6IlZid3VwSmY3VFpvWEFqMW5LR2k5ZGc9PSIsInZhbHVlIjoiWHhuem9OOUhZQkk2RWEvczMwaWMrUT09IiwibWFjIjoiZjE3OTFhYzc1NzZkZWU0YWRhOGQxZTY1MWZmYzhjZmM5ZDk4M2JiOWU3M2VkYTUyZjQ3NzE2MjU4YTdkYjk1YyIsInRhZyI6IiJ9','Demo vault entry - AWS root credentials.','2026-06-28 05:14:11','2026-06-28 16:38:15','2026-06-28 16:38:15'),(2,6,12,'GitHub PAT','https://github.com/settings/tokens','testuser','eyJpdiI6IktzT3E2UUlYNEgwNVZDMmM2a1UyMWc9PSIsInZhbHVlIjoiazdHRVZUOVhpR25na0lydjlIeHBWZz09IiwibWFjIjoiYWEwYTI0MDg5NDM4MTA0MTMwYWRmNjNkM2E4ZmFmMjFiNzQ3ZDEyMTVmZGQwNjU1ODM0Mzc2MjhjY2MyZDI0MyIsInRhZyI6IiJ9','Demo vault entry - GitHub personal access token.','2026-06-28 05:14:11','2026-06-28 16:38:16','2026-06-28 16:38:16');
/*!40000 ALTER TABLE `password_vault` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
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
-- Table structure for table `privilege_role`
--

DROP TABLE IF EXISTS `privilege_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `privilege_role` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` bigint(20) unsigned NOT NULL,
  `privilege_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `privilege_role_role_id_privilege_id_unique` (`role_id`,`privilege_id`),
  KEY `privilege_role_privilege_id_foreign` (`privilege_id`),
  CONSTRAINT `privilege_role_privilege_id_foreign` FOREIGN KEY (`privilege_id`) REFERENCES `privileges` (`id`) ON DELETE CASCADE,
  CONSTRAINT `privilege_role_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `privilege_role`
--

LOCK TABLES `privilege_role` WRITE;
/*!40000 ALTER TABLE `privilege_role` DISABLE KEYS */;
INSERT INTO `privilege_role` VALUES (9,7,6,'2026-06-28 05:14:09','2026-06-28 05:14:09'),(10,12,6,'2026-06-28 05:14:09','2026-06-28 05:14:09'),(11,7,7,'2026-06-28 05:14:09','2026-06-28 05:14:09'),(12,12,7,'2026-06-28 05:14:09','2026-06-28 05:14:09'),(13,12,8,'2026-06-28 05:14:09','2026-06-28 05:14:09'),(14,7,9,'2026-06-28 05:14:09','2026-06-28 05:14:09'),(22,14,9,'2026-07-01 19:07:22','2026-07-01 19:07:22'),(23,17,10,'2026-07-01 19:07:22','2026-07-01 19:07:22');
/*!40000 ALTER TABLE `privilege_role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `privileges`
--

DROP TABLE IF EXISTS `privileges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `privileges` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `privileges_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `privileges`
--

LOCK TABLES `privileges` WRITE;
/*!40000 ALTER TABLE `privileges` DISABLE KEYS */;
INSERT INTO `privileges` VALUES (6,'Generate Reports','report.generate','Allows generating system-wide reports.','2026-06-28 05:14:09','2026-06-28 05:14:09'),(7,'Manage Users','users.manage','Allows creating, editing, and deleting users.','2026-06-28 05:14:09','2026-06-28 05:14:09'),(8,'Manage Roles','roles.manage','Allows editing Tyro roles.','2026-06-28 05:14:09','2026-06-28 05:14:09'),(9,'View Billing','billing.view','Allows viewing billing statements.','2026-06-28 05:14:09','2026-06-28 05:14:09'),(10,'Wildcard','*','Grants every privilege.','2026-06-28 05:14:09','2026-06-28 05:14:09'),(11,'IT SUPPORT','SUPPORT',NULL,'2026-06-28 16:57:45','2026-06-28 16:57:45');
/*!40000 ALTER TABLE `privileges` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_templates`
--

DROP TABLE IF EXISTS `role_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role_templates` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `version` varchar(255) NOT NULL DEFAULT '1.0',
  `is_protected` tinyint(1) NOT NULL DEFAULT 1,
  `is_dangerous` tinyint(1) NOT NULL DEFAULT 0,
  `permissions_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`permissions_json`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_templates_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_templates`
--

LOCK TABLES `role_templates` WRITE;
/*!40000 ALTER TABLE `role_templates` DISABLE KEYS */;
INSERT INTO `role_templates` VALUES (1,'Super Admin','super-admin','Full access to all modules with all permissions. Intended for the super-admin role only. Dangerous ÔÇö should not be applied to normal roles.','1.0',1,1,'{\"domains\":{\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":true,\"can_approve\":true,\"can_export\":true,\"can_reveal\":true,\"can_import\":true},\"hostings\":{\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":true,\"can_approve\":true,\"can_export\":true,\"can_reveal\":true,\"can_import\":true},\"vps\":{\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":true,\"can_approve\":true,\"can_export\":true,\"can_reveal\":true,\"can_import\":true},\"voip\":{\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":true,\"can_approve\":true,\"can_export\":true,\"can_reveal\":true,\"can_import\":true},\"service-providers\":{\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":true,\"can_approve\":true,\"can_export\":true,\"can_reveal\":true,\"can_import\":true},\"domain-emails\":{\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":true,\"can_approve\":true,\"can_export\":true,\"can_reveal\":true,\"can_import\":true},\"other-services\":{\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":true,\"can_approve\":true,\"can_export\":true,\"can_reveal\":true,\"can_import\":true},\"expiry-trackers\":{\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":true,\"can_approve\":true,\"can_export\":true,\"can_reveal\":true,\"can_import\":true},\"tasks\":{\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":true,\"can_approve\":true,\"can_export\":true,\"can_reveal\":true,\"can_import\":true},\"notes\":{\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":true,\"can_approve\":true,\"can_export\":true,\"can_reveal\":true,\"can_import\":true},\"vault\":{\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":true,\"can_approve\":true,\"can_export\":true,\"can_reveal\":true,\"can_import\":true},\"monitor\":{\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":true,\"can_approve\":true,\"can_export\":true,\"can_reveal\":true,\"can_import\":true},\"calendar\":{\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":true,\"can_approve\":true,\"can_export\":true,\"can_reveal\":true,\"can_import\":true},\"users\":{\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":true,\"can_approve\":true,\"can_export\":true,\"can_reveal\":true,\"can_import\":true},\"roles\":{\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":true,\"can_approve\":true,\"can_export\":true,\"can_reveal\":true,\"can_import\":true},\"privileges\":{\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":true,\"can_approve\":true,\"can_export\":true,\"can_reveal\":true,\"can_import\":true},\"module-permissions\":{\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":true,\"can_approve\":true,\"can_export\":true,\"can_reveal\":true,\"can_import\":true},\"activity-logs\":{\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":true,\"can_approve\":true,\"can_export\":true,\"can_reveal\":true,\"can_import\":true},\"login-audits\":{\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":true,\"can_approve\":true,\"can_export\":true,\"can_reveal\":true,\"can_import\":true},\"notifications\":{\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":true,\"can_approve\":true,\"can_export\":true,\"can_reveal\":true,\"can_import\":true},\"attachments\":{\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":true,\"can_approve\":true,\"can_export\":true,\"can_reveal\":true,\"can_import\":true},\"webhooks\":{\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":true,\"can_approve\":true,\"can_export\":true,\"can_reveal\":true,\"can_import\":true},\"tokens\":{\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":true,\"can_approve\":true,\"can_export\":true,\"can_reveal\":true,\"can_import\":true},\"import\":{\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":true,\"can_approve\":true,\"can_export\":true,\"can_reveal\":true,\"can_import\":true},\"export\":{\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":true,\"can_approve\":true,\"can_export\":true,\"can_reveal\":true,\"can_import\":true},\"reports\":{\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":true,\"can_approve\":true,\"can_export\":true,\"can_reveal\":true,\"can_import\":true}}','2026-06-28 05:14:11','2026-06-28 05:14:11'),(2,'Admin','admin','Operational administrator with infrastructure and productivity access. No RBAC/System management. No delete, import, or approval permissions.','1.0',1,0,'{\"domains\":{\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":true,\"can_reveal\":true,\"can_import\":false},\"hostings\":{\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":true,\"can_reveal\":true,\"can_import\":false},\"vps\":{\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":true,\"can_reveal\":true,\"can_import\":false},\"voip\":{\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":true,\"can_reveal\":true,\"can_import\":false},\"service-providers\":{\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":true,\"can_reveal\":true,\"can_import\":false},\"domain-emails\":{\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":true,\"can_reveal\":true,\"can_import\":false},\"other-services\":{\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":true,\"can_reveal\":true,\"can_import\":false},\"expiry-trackers\":{\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":true,\"can_reveal\":true,\"can_import\":false},\"tasks\":{\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"notes\":{\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"vault\":{\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"monitor\":{\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"calendar\":{\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"users\":{\"can_create\":false,\"can_read\":true,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"roles\":{\"can_create\":false,\"can_read\":false,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"privileges\":{\"can_create\":false,\"can_read\":false,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"module-permissions\":{\"can_create\":false,\"can_read\":false,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"activity-logs\":{\"can_create\":false,\"can_read\":true,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"login-audits\":{\"can_create\":false,\"can_read\":true,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"notifications\":{\"can_create\":false,\"can_read\":true,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"attachments\":{\"can_create\":false,\"can_read\":true,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"webhooks\":{\"can_create\":false,\"can_read\":true,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"tokens\":{\"can_create\":false,\"can_read\":false,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"import\":{\"can_create\":false,\"can_read\":false,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"export\":{\"can_create\":false,\"can_read\":false,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"reports\":{\"can_create\":false,\"can_read\":true,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false}}','2026-06-28 05:14:11','2026-06-28 05:14:11'),(3,'IT Support','it-support','Help desk / support staff. Can manage and reveal passwords on 6 operational infrastructure modules (domains, hosting, VPS, VoIP, providers, domain emails). No delete, export, or import.','1.0',1,0,'{\"domains\":{\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":true,\"can_import\":false},\"hostings\":{\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":true,\"can_import\":false},\"vps\":{\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":true,\"can_import\":false},\"voip\":{\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":true,\"can_import\":false},\"service-providers\":{\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":true,\"can_import\":false},\"domain-emails\":{\"can_create\":true,\"can_read\":true,\"can_update\":true,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":true,\"can_import\":false},\"other-services\":{\"can_create\":false,\"can_read\":false,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"expiry-trackers\":{\"can_create\":false,\"can_read\":false,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"tasks\":{\"can_create\":false,\"can_read\":false,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"notes\":{\"can_create\":false,\"can_read\":false,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"vault\":{\"can_create\":false,\"can_read\":false,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"monitor\":{\"can_create\":false,\"can_read\":false,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"calendar\":{\"can_create\":false,\"can_read\":false,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"users\":{\"can_create\":false,\"can_read\":false,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"roles\":{\"can_create\":false,\"can_read\":false,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"privileges\":{\"can_create\":false,\"can_read\":false,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"module-permissions\":{\"can_create\":false,\"can_read\":false,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"activity-logs\":{\"can_create\":false,\"can_read\":false,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"login-audits\":{\"can_create\":false,\"can_read\":false,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"notifications\":{\"can_create\":false,\"can_read\":false,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"attachments\":{\"can_create\":false,\"can_read\":false,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"webhooks\":{\"can_create\":false,\"can_read\":false,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"tokens\":{\"can_create\":false,\"can_read\":false,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"import\":{\"can_create\":false,\"can_read\":false,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"export\":{\"can_create\":false,\"can_read\":false,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"reports\":{\"can_create\":false,\"can_read\":false,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false}}','2026-06-28 05:14:11','2026-06-28 05:14:11'),(4,'Read Only','read-only','Read-only access to operational modules. Cannot create, update, delete, export, reveal, or import. Suitable for auditors and compliance personnel.','1.0',1,0,'{\"domains\":{\"can_create\":false,\"can_read\":true,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"hostings\":{\"can_create\":false,\"can_read\":true,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"vps\":{\"can_create\":false,\"can_read\":true,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"voip\":{\"can_create\":false,\"can_read\":true,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"service-providers\":{\"can_create\":false,\"can_read\":true,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"domain-emails\":{\"can_create\":false,\"can_read\":true,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"other-services\":{\"can_create\":false,\"can_read\":true,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"expiry-trackers\":{\"can_create\":false,\"can_read\":true,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"tasks\":{\"can_create\":false,\"can_read\":true,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"notes\":{\"can_create\":false,\"can_read\":true,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"vault\":{\"can_create\":false,\"can_read\":true,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"monitor\":{\"can_create\":false,\"can_read\":true,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"calendar\":{\"can_create\":false,\"can_read\":true,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"users\":{\"can_create\":false,\"can_read\":true,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"activity-logs\":{\"can_create\":false,\"can_read\":true,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"notifications\":{\"can_create\":false,\"can_read\":true,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"attachments\":{\"can_create\":false,\"can_read\":true,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"webhooks\":{\"can_create\":false,\"can_read\":true,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"reports\":{\"can_create\":false,\"can_read\":true,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"roles\":{\"can_create\":false,\"can_read\":false,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"privileges\":{\"can_create\":false,\"can_read\":false,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"module-permissions\":{\"can_create\":false,\"can_read\":false,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"login-audits\":{\"can_create\":false,\"can_read\":false,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"tokens\":{\"can_create\":false,\"can_read\":false,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"import\":{\"can_create\":false,\"can_read\":false,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false},\"export\":{\"can_create\":false,\"can_read\":false,\"can_update\":false,\"can_delete\":false,\"can_approve\":false,\"can_export\":false,\"can_reveal\":false,\"can_import\":false}}','2026-06-28 05:14:11','2026-06-28 05:14:11');
/*!40000 ALTER TABLE `role_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `roles_slug_index` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (7,'Administrator','admin','2026-06-28 05:14:09','2026-06-28 05:14:09'),(12,'Super Admin','super-admin','2026-06-28 05:14:09','2026-06-28 05:14:09'),(13,'IT SUPPORT','SUPPORT','2026-06-28 16:38:52','2026-06-28 16:39:01'),(14,'User','user','2026-07-01 19:07:22','2026-07-01 19:07:22'),(15,'Customer','customer','2026-07-01 19:07:22','2026-07-01 19:07:22'),(16,'Editor','editor','2026-07-01 19:07:22','2026-07-01 19:07:22'),(17,'All','*','2026-07-01 19:07:22','2026-07-01 19:07:22');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_providers`
--

DROP TABLE IF EXISTS `service_providers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_providers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `module_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `provider` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `password` text DEFAULT NULL,
  `cost` decimal(10,2) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `monitoring_url` varchar(255) DEFAULT NULL,
  `last_ping_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `service_providers_user_id_foreign` (`user_id`),
  KEY `service_providers_module_id_foreign` (`module_id`),
  KEY `service_providers_deleted_at_index` (`deleted_at`),
  KEY `service_providers_status_index` (`status`),
  CONSTRAINT `service_providers_module_id_foreign` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE SET NULL,
  CONSTRAINT `service_providers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_providers`
--

LOCK TABLES `service_providers` WRITE;
/*!40000 ALTER TABLE `service_providers` DISABLE KEYS */;
INSERT INTO `service_providers` VALUES (2,5,5,'DigitalOcean','vps','DigitalOcean Inc.',NULL,'https://digitalocean.com','eyJpdiI6IlBodko5U3p0c0lhTndEUmliVUl0a0E9PSIsInZhbHVlIjoiYkJXT3lnRk9pUTlPaStFckgvS3F0dz09IiwibWFjIjoiOGU0NzE4ZDgwMDA5ZDE3M2M2YmE5MmViOTYzOGMyM2VkZWY3NzI0MTkyOThmZmIyZWY0OWFkNzMyNzgwNGMxNSIsInRhZyI6IiJ9',0.00,'2025-06-28','2027-06-28','active','Demo service provider.',NULL,NULL,'2026-06-28 05:14:11','2026-06-28 16:38:01','2026-06-28 16:38:01'),(3,5,5,'Namecheap','domain','Namecheap Inc.',NULL,'https://namecheap.com','eyJpdiI6IkI5VU1YUlNFdnJ3NGVoM1pPb0pCWmc9PSIsInZhbHVlIjoiL3RhN0xWdWEzaWxYSFRjS1BOOHRlUT09IiwibWFjIjoiNzI0NDFkMDJmOTRiOGFhNDUwZWM5MWFiNGYzNzQ4NDgxNTBlMGY0ZWMyZDNmZjBjZWEwYjk0YTg3OWJkZWRmNiIsInRhZyI6IiJ9',0.00,'2025-06-28','2027-06-28','active','Demo service provider.',NULL,NULL,'2026-06-28 05:14:11','2026-06-28 16:38:03','2026-06-28 16:38:03'),(4,5,5,'Google Workspace','email','Google LLC',NULL,'https://workspace.google.com','eyJpdiI6InIwWjQxd3BVMzdmL3o3dVBOa0N6Snc9PSIsInZhbHVlIjoidG5yTFFVN0hNQUZKNDlJcTZkQ3RvQT09IiwibWFjIjoiNGY3ZGRjZTJhYzEzYzFkY2I5NmExN2NhMzlhOThjZTA4NzVlM2E0NGE5M2Q0OTY1ZWUyNDQ4OTFiMWMwMzQzYiIsInRhZyI6IiJ9',0.00,'2025-06-28','2027-06-28','active','Demo service provider.',NULL,NULL,'2026-06-28 05:14:11','2026-06-28 16:38:05','2026-06-28 16:38:05'),(5,7,2,'NICWAYS','Hosting VPS DOMAIN','NICWAYS','cloudchapms@gmail.com','my.nicways.com','eyJpdiI6Ik4xSUJwTEdzMzZ0MFNoazlCTGxjMXc9PSIsInZhbHVlIjoiNFZzWk8zQmJRZ2I0ejV5azlTR00wZz09IiwibWFjIjoiOWFiNTdhYzA3Y2IxYmJmYTRlY2RkZTIxNTVjNWEyYmI3MWQ1MmQ2MTcyNzJjYTk2MGFhNzFkMDRiYjllZjAzMiIsInRhZyI6IiJ9',NULL,'2026-06-29','2027-06-29','active','TESTING ACCOUNTS',NULL,NULL,'2026-06-28 16:44:13','2026-06-28 16:53:53',NULL),(6,7,NULL,'Openprovider','Domains','Openprovider','cloudchapms@gmail.com','https://openprovider.com','eyJpdiI6Im5tYVo2NEtRdFhWcTlQQ01tNEZ0c3c9PSIsInZhbHVlIjoiOGxqZUNsR21USmd6Qy9weDdHTTJwdz09IiwibWFjIjoiYjZkODFhZWZlMjc5MGMwZjMwMjY3OWRiOTBmNDE1N2EyZDM3YmQzMGRmNGI2NTEyZDAyNmE2OTZmNjZkYzVmZiIsInRhZyI6IiJ9',NULL,'2026-06-29',NULL,'active',NULL,NULL,NULL,'2026-06-28 16:51:12','2026-06-28 16:51:12',NULL),(7,10,NULL,'Windler, Rolfson and Ortiz','email','Ziemann, McLaughlin and Huels',NULL,'https://www.pagac.com/et-facilis-dolor-impedit-et','eyJpdiI6InlIVElVdWdpQkZNSEFBYThjaUlHTHc9PSIsInZhbHVlIjoiQVJ3NmtKUnY3Ukt0Z2VVRFJVTHJJdz09IiwibWFjIjoiNGY0MWQ4ZDJlYTY0NDgzYWViYjY4YTJjMjRmYzFkZjlkMDI4MmQ3ODY0MmQ2Njg2YTcyOTY2YzAwNGJkODY3OCIsInRhZyI6IiJ9',355.95,'2024-08-11','2026-10-15','active',NULL,NULL,NULL,'2026-06-29 18:23:41','2026-06-29 18:23:41',NULL);
/*!40000 ALTER TABLE `service_providers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_foreign` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`),
  CONSTRAINT `sessions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `smtp_profiles`
--

DROP TABLE IF EXISTS `smtp_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `smtp_profiles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `sender_name` varchar(255) NOT NULL,
  `sender_email` varchar(255) NOT NULL,
  `reply_to_email` varchar(255) DEFAULT NULL,
  `smtp_host` varchar(255) NOT NULL,
  `smtp_port` int(11) NOT NULL DEFAULT 587,
  `smtp_encryption` varchar(255) DEFAULT NULL,
  `smtp_username` varchar(255) NOT NULL,
  `smtp_password` text NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `priority` int(11) NOT NULL DEFAULT 100,
  `last_tested_at` timestamp NULL DEFAULT NULL,
  `last_test_status` varchar(20) DEFAULT NULL,
  `last_test_error` text DEFAULT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `smtp_profiles_created_by_foreign` (`created_by`),
  KEY `sp_active_idx` (`is_active`),
  KEY `smtp_profiles_is_default_index` (`is_default`),
  CONSTRAINT `smtp_profiles_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `smtp_profiles`
--

LOCK TABLES `smtp_profiles` WRITE;
/*!40000 ALTER TABLE `smtp_profiles` DISABLE KEYS */;
/*!40000 ALTER TABLE `smtp_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `task_user`
--

DROP TABLE IF EXISTS `task_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `task_user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `task_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `task_user_task_id_user_id_unique` (`task_id`,`user_id`),
  KEY `task_user_user_id_foreign` (`user_id`),
  CONSTRAINT `task_user_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `task_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task_user`
--

LOCK TABLES `task_user` WRITE;
/*!40000 ALTER TABLE `task_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `task_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tasks`
--

DROP TABLE IF EXISTS `tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tasks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `module_id` bigint(20) unsigned DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `priority` varchar(255) NOT NULL DEFAULT 'medium',
  `due_date` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tasks_module_id_foreign` (`module_id`),
  KEY `tasks_created_by_foreign` (`created_by`),
  KEY `tasks_updated_by_foreign` (`updated_by`),
  KEY `tasks_status_index` (`status`),
  KEY `tasks_priority_index` (`priority`),
  KEY `tasks_due_date_index` (`due_date`),
  KEY `tasks_deleted_at_index` (`deleted_at`),
  CONSTRAINT `tasks_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tasks_module_id_foreign` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tasks_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tasks`
--

LOCK TABLES `tasks` WRITE;
/*!40000 ALTER TABLE `tasks` DISABLE KEYS */;
INSERT INTO `tasks` VALUES (1,'Update server OS patches','Run apt update and upgrade on all production servers.',10,5,5,'pending','high','2026-07-05 10:14:11','2026-06-28 05:14:11','2026-06-28 16:38:24','2026-06-28 16:38:24'),(2,'Migrate DNS to Cloudflare','Transfer DNS management from current provider to Cloudflare.',10,6,6,'in_progress','medium','2026-07-05 10:14:11','2026-06-28 05:14:11','2026-06-28 16:38:26','2026-06-28 16:38:26');
/*!40000 ALTER TABLE `tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tyro_audit_logs`
--

DROP TABLE IF EXISTS `tyro_audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tyro_audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `event` varchar(255) NOT NULL,
  `auditable_type` varchar(255) DEFAULT NULL,
  `auditable_id` bigint(20) unsigned DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `tyro_audit_logs_auditable_type_auditable_id_index` (`auditable_type`,`auditable_id`),
  KEY `tyro_audit_logs_user_id_index` (`user_id`),
  KEY `tyro_audit_logs_event_index` (`event`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tyro_audit_logs`
--

LOCK TABLES `tyro_audit_logs` WRITE;
/*!40000 ALTER TABLE `tyro_audit_logs` DISABLE KEYS */;
INSERT INTO `tyro_audit_logs` VALUES (13,7,'role.created','HasinHayder\\Tyro\\Models\\Role',13,NULL,'{\"name\":\"IT SUPPORT\",\"slug\":\"IT-SUPPORT\",\"id\":13}','{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/149.0.0.0 Safari\\/537.36\",\"is_console\":false}','2026-06-28 21:38:52'),(14,7,'role.updated','HasinHayder\\Tyro\\Models\\Role',13,'{\"id\":13,\"name\":\"IT SUPPORT\",\"slug\":\"IT-SUPPORT\",\"created_at\":\"2026-06-28T21:38:52.000000Z\",\"updated_at\":\"2026-06-28T21:38:52.000000Z\"}','{\"slug\":\"SUPPORT\",\"updated_at\":\"2026-06-28 21:39:01\"}','{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/149.0.0.0 Safari\\/537.36\",\"is_console\":false}','2026-06-28 21:39:01'),(15,7,'role.deleted','HasinHayder\\Tyro\\Models\\Role',8,'{\"id\":8,\"name\":\"User\",\"slug\":\"user\"}',NULL,'{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/149.0.0.0 Safari\\/537.36\",\"is_console\":false}','2026-06-28 21:39:49'),(16,7,'role.deleted','HasinHayder\\Tyro\\Models\\Role',9,'{\"id\":9,\"name\":\"Customer\",\"slug\":\"customer\"}',NULL,'{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/149.0.0.0 Safari\\/537.36\",\"is_console\":false}','2026-06-28 21:39:52'),(17,7,'role.deleted','HasinHayder\\Tyro\\Models\\Role',10,'{\"id\":10,\"name\":\"Editor\",\"slug\":\"editor\"}',NULL,'{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/149.0.0.0 Safari\\/537.36\",\"is_console\":false}','2026-06-28 21:39:54'),(18,7,'role.deleted','HasinHayder\\Tyro\\Models\\Role',11,'{\"id\":11,\"name\":\"All\",\"slug\":\"*\"}',NULL,'{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/149.0.0.0 Safari\\/537.36\",\"is_console\":false}','2026-06-28 21:40:01'),(19,7,'privilege.attached','HasinHayder\\Tyro\\Models\\Role',13,NULL,'{\"privilege_id\":8,\"privilege_slug\":\"roles.manage\"}','{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/149.0.0.0 Safari\\/537.36\",\"is_console\":false}','2026-06-28 21:57:09'),(20,7,'privilege.attached','HasinHayder\\Tyro\\Models\\Role',13,NULL,'{\"privilege_id\":6,\"privilege_slug\":\"report.generate\"}','{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/149.0.0.0 Safari\\/537.36\",\"is_console\":false}','2026-06-28 21:57:18'),(21,7,'privilege.attached','HasinHayder\\Tyro\\Models\\Role',13,NULL,'{\"privilege_id\":7,\"privilege_slug\":\"users.manage\"}','{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/149.0.0.0 Safari\\/537.36\",\"is_console\":false}','2026-06-28 21:57:21'),(22,7,'privilege.attached','HasinHayder\\Tyro\\Models\\Role',13,NULL,'{\"privilege_id\":9,\"privilege_slug\":\"billing.view\"}','{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/149.0.0.0 Safari\\/537.36\",\"is_console\":false}','2026-06-28 21:57:24'),(23,7,'privilege.attached','HasinHayder\\Tyro\\Models\\Role',13,NULL,'{\"privilege_id\":10,\"privilege_slug\":\"*\"}','{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/149.0.0.0 Safari\\/537.36\",\"is_console\":false}','2026-06-28 21:57:27'),(24,7,'privilege.created','HasinHayder\\Tyro\\Models\\Privilege',11,NULL,'{\"name\":\"IT SUPPORT\",\"slug\":\"SUPPORT\",\"description\":null,\"id\":11}','{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/149.0.0.0 Safari\\/537.36\",\"is_console\":false}','2026-06-28 21:57:45'),(25,7,'privilege.detached','HasinHayder\\Tyro\\Models\\Role',13,NULL,'{\"privilege_id\":8,\"privilege_slug\":\"roles.manage\"}','{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/149.0.0.0 Safari\\/537.36\",\"is_console\":false}','2026-06-28 22:04:36'),(26,7,'privilege.detached','HasinHayder\\Tyro\\Models\\Role',13,NULL,'{\"privilege_id\":6,\"privilege_slug\":\"report.generate\"}','{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/149.0.0.0 Safari\\/537.36\",\"is_console\":false}','2026-06-28 22:04:38'),(27,7,'privilege.detached','HasinHayder\\Tyro\\Models\\Role',13,NULL,'{\"privilege_id\":7,\"privilege_slug\":\"users.manage\"}','{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/149.0.0.0 Safari\\/537.36\",\"is_console\":false}','2026-06-28 22:04:41'),(28,7,'privilege.detached','HasinHayder\\Tyro\\Models\\Role',13,NULL,'{\"privilege_id\":9,\"privilege_slug\":\"billing.view\"}','{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/149.0.0.0 Safari\\/537.36\",\"is_console\":false}','2026-06-28 22:04:43'),(29,7,'privilege.detached','HasinHayder\\Tyro\\Models\\Role',13,NULL,'{\"privilege_id\":10,\"privilege_slug\":\"*\"}','{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/149.0.0.0 Safari\\/537.36\",\"is_console\":false}','2026-06-28 22:04:45');
/*!40000 ALTER TABLE `tyro_audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_module_permissions`
--

DROP TABLE IF EXISTS `user_module_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_module_permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `module_id` bigint(20) unsigned NOT NULL,
  `can_create` tinyint(1) DEFAULT NULL,
  `can_read` tinyint(1) DEFAULT NULL,
  `can_update` tinyint(1) DEFAULT NULL,
  `can_delete` tinyint(1) DEFAULT NULL,
  `can_approve` tinyint(1) DEFAULT NULL,
  `can_export` tinyint(1) DEFAULT NULL,
  `can_reveal` tinyint(1) DEFAULT NULL,
  `can_import` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_module_permissions_user_id_module_id_unique` (`user_id`,`module_id`),
  KEY `user_module_permissions_module_id_foreign` (`module_id`),
  CONSTRAINT `user_module_permissions_module_id_foreign` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_module_permissions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_module_permissions`
--

LOCK TABLES `user_module_permissions` WRITE;
/*!40000 ALTER TABLE `user_module_permissions` DISABLE KEYS */;
INSERT INTO `user_module_permissions` VALUES (1,7,19,1,1,1,1,1,1,1,1,'2026-06-28 16:30:51','2026-06-28 16:30:51'),(2,7,24,1,1,1,1,1,1,1,1,'2026-06-28 16:35:32','2026-06-28 16:35:32'),(3,7,9,1,1,1,1,1,1,1,1,'2026-06-28 16:35:32','2026-06-28 16:35:32'),(4,7,22,1,1,1,1,1,1,1,1,'2026-06-28 16:35:32','2026-06-28 16:35:32'),(5,7,14,1,1,1,1,1,1,1,1,'2026-06-28 16:35:32','2026-06-28 16:35:32'),(6,7,6,1,1,1,1,1,1,1,1,'2026-06-28 16:35:32','2026-06-28 16:35:32'),(7,7,1,1,1,1,1,1,1,1,1,'2026-06-28 16:35:32','2026-06-28 16:35:32'),(8,7,8,1,1,1,1,1,1,1,1,'2026-06-28 16:35:32','2026-06-28 16:35:32'),(9,7,26,1,1,1,1,1,1,1,1,'2026-06-28 16:35:32','2026-06-28 16:35:32'),(10,7,2,1,1,1,1,1,1,1,1,'2026-06-28 16:35:32','2026-06-28 16:35:32'),(11,7,25,1,1,1,1,1,1,1,1,'2026-06-28 16:35:32','2026-06-28 16:35:32'),(12,7,20,1,1,1,1,1,1,1,1,'2026-06-28 16:35:32','2026-06-28 16:35:32'),(13,7,18,1,1,1,1,1,1,1,1,'2026-06-28 16:35:32','2026-06-28 16:35:32'),(14,7,13,1,1,1,1,1,1,1,1,'2026-06-28 16:35:32','2026-06-28 16:35:32'),(15,7,11,1,1,1,1,1,1,1,1,'2026-06-28 16:35:32','2026-06-28 16:35:32'),(16,7,21,1,1,1,1,1,1,1,1,'2026-06-28 16:35:32','2026-06-28 16:35:32'),(17,7,7,1,1,1,1,1,1,1,1,'2026-06-28 16:35:32','2026-06-28 16:35:32'),(18,7,17,1,1,1,1,1,1,1,1,'2026-06-28 16:35:32','2026-06-28 16:35:32'),(19,7,27,1,1,1,1,1,1,1,1,'2026-06-28 16:35:32','2026-06-28 16:35:32'),(20,7,16,1,1,1,1,1,1,1,1,'2026-06-28 16:35:32','2026-06-28 16:35:32'),(21,7,5,1,1,1,1,1,1,1,1,'2026-06-28 16:35:32','2026-06-28 16:35:32'),(22,7,10,1,1,1,1,1,1,1,1,'2026-06-28 16:35:32','2026-06-28 16:35:32'),(23,7,15,1,1,1,1,1,1,1,1,'2026-06-28 16:35:32','2026-06-28 16:35:32'),(24,7,12,1,1,1,1,1,1,1,1,'2026-06-28 16:35:32','2026-06-28 16:35:32'),(25,7,4,1,1,1,1,1,1,1,1,'2026-06-28 16:35:32','2026-06-28 16:35:32'),(26,7,3,1,1,1,1,1,1,1,1,'2026-06-28 16:35:32','2026-06-28 16:35:32'),(27,7,23,1,1,1,1,1,1,1,1,'2026-06-28 16:35:32','2026-06-28 16:35:32');
/*!40000 ALTER TABLE `user_module_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_roles`
--

DROP TABLE IF EXISTS `user_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_roles_user_id_role_id_unique` (`user_id`,`role_id`),
  KEY `user_roles_role_id_foreign` (`role_id`),
  CONSTRAINT `user_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_roles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_roles`
--

LOCK TABLES `user_roles` WRITE;
/*!40000 ALTER TABLE `user_roles` DISABLE KEYS */;
INSERT INTO `user_roles` VALUES (3,5,7,'2026-06-28 05:14:10','2026-06-28 05:14:10'),(5,5,12,'2026-06-28 05:14:11','2026-06-28 05:14:11'),(6,7,7,'2026-06-28 16:29:04','2026-06-28 16:29:04'),(7,7,12,'2026-06-28 16:29:04','2026-06-28 16:29:04');
/*!40000 ALTER TABLE `user_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `suspended_at` timestamp NULL DEFAULT NULL,
  `suspension_reason` text DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_deleted_at_index` (`deleted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (5,'Tyro Admin','admin@tyro.project',NULL,'$2y$12$SNolqetH2b/cfrGsZhZA9eWdWZ3DoZF1Nh3nkOWz8t8Wx2gJL.HMO',NULL,'2026-06-28 05:14:10','2026-07-02 22:20:02',NULL,NULL,NULL),(6,'Test User','test@example.com',NULL,'$2y$12$/wibGxbi3hobCmP7F3F9a.7XKxKHsLjmUjTanKV6uJAmJJLT0.//K',NULL,'2026-06-28 05:14:10','2026-06-28 16:39:39',NULL,NULL,'2026-06-28 16:39:39'),(7,'MASOOD NASIR','h3llsing@gmail.com',NULL,'$2y$12$fc12HPv43pMyndchtMzGb.cdrYQpstRYQduaza3UCD8UQzzgVhp9W','Ms2ShzPdQnFzjSSUW9vMfxguEAXmSxH8IrziQT0swctnSUwRJFSYsTbF74ut','2026-06-28 16:29:04','2026-06-28 16:29:04',NULL,NULL,NULL),(8,'Emmett Kertzmann','darron03@example.net','2026-06-29 18:23:41','$2y$12$KljXmbUbCBnVumeIw.6oPu0gg4C9aqGV8AGWllEFOX4agnWn/UWam','S05LIASNT4','2026-06-29 18:23:41','2026-06-29 18:23:41',NULL,NULL,NULL),(9,'Prince Schuppe','jewell.kris@example.com','2026-06-29 18:23:41','$2y$12$KljXmbUbCBnVumeIw.6oPu0gg4C9aqGV8AGWllEFOX4agnWn/UWam','Ln2YoxhzGO','2026-06-29 18:23:41','2026-06-29 18:23:41',NULL,NULL,NULL),(10,'Valentin Green','stoltenberg.aditya@example.org','2026-06-29 18:23:41','$2y$12$KljXmbUbCBnVumeIw.6oPu0gg4C9aqGV8AGWllEFOX4agnWn/UWam','1VYsQ3P4Ch','2026-06-29 18:23:41','2026-06-29 18:23:41',NULL,NULL,NULL),(11,'Foster Rice','xdooley@example.net','2026-06-29 18:24:32','$2y$12$Z.i5Oihy8pC.Cbmx.7SxceB3x4OLt6tKrzoY9Zu6UQp7/lzfqHX.O','T9OBUswD92','2026-06-29 18:24:32','2026-06-29 18:24:32',NULL,NULL,NULL),(12,'Henry Kozey','omccullough@example.net','2026-06-29 20:03:02','$2y$12$WUDYtOwesEnx7VjplLUBJO7lVMkCKRN716Oy/wXFo.4iBCPtDCnGW','c3cIpCBhhN','2026-06-29 20:03:02','2026-06-29 20:03:02',NULL,NULL,NULL),(14,'Test User POGLTKHD','test_POGLTKHD@example.com',NULL,'$2y$12$ufy8b7UaHN7cJAbG.5uX2OpPnlFPdMddu/SuUGtnJyz1cYkB7tf9C',NULL,'2026-07-02 19:59:43','2026-07-02 19:59:43',NULL,NULL,NULL),(15,'Test User','test_df38bd44@test.com',NULL,'$2y$12$en3aCWgHtxjeQ8fSOeHCRuapOPJxCv4TcLUmtRyg0tUB7TgtxtlbW',NULL,'2026-07-02 20:01:18','2026-07-02 20:01:18',NULL,NULL,NULL),(16,'Test User','t_8f770c87@t.co',NULL,'$2y$12$FfR3tiRcQ50A0fS6J0ijRuMYZm/wCFnD43n6pFakKwPsFXl.5jS3a','x4lRnaBfewVLyAVStX9DvINbvWt4sm1PrrmkiJgpWVAGWLRXF0TyPJnvXUaT','2026-07-02 20:03:05','2026-07-02 20:03:05',NULL,NULL,NULL),(17,'Test','t_2f0f4e05@t.co',NULL,'$2y$12$b/Gq4qd1NayuqP7gBKr.HOTwFKJVYzDNYKaatdBxyjzeGEMlKmFra','jP5dFgI7i7p2fZeh616ij5Y40akDYbl6UBkcOCJLzKStFEGmspgSlrSXQkoM','2026-07-02 20:04:31','2026-07-02 20:04:31',NULL,NULL,NULL),(18,'T','t_772e0405@t.co',NULL,'$2y$12$2wM2KMvLYmGg9lq2aIWHyu9YiMVoBDMxv6yJ0vE/NurAlThS6NOiK','qruDBOlcYx0X2uZ1v1MxF2h9d7oyqTi0FvsKjfgcCuXX5NP4y2402rG8yAeJ','2026-07-02 20:05:05','2026-07-02 20:05:05',NULL,NULL,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `voip`
--

DROP TABLE IF EXISTS `voip`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `voip` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `module_id` bigint(20) unsigned DEFAULT NULL,
  `service_provider_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `extensions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`extensions`)),
  `phone_number` varchar(255) DEFAULT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'sip',
  `username` varchar(255) DEFAULT NULL,
  `password` text DEFAULT NULL,
  `dashboard_url` varchar(255) DEFAULT NULL,
  `cost` decimal(10,2) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `monitoring_url` varchar(255) DEFAULT NULL,
  `last_ping_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `extension_password` text DEFAULT NULL,
  `server_ip` varchar(255) DEFAULT NULL,
  `direction` varchar(255) DEFAULT NULL,
  `number_status` varchar(255) DEFAULT NULL,
  `outbound_code` varchar(255) DEFAULT NULL,
  `team_details` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `voip_user_id_foreign` (`user_id`),
  KEY `voip_module_id_foreign` (`module_id`),
  KEY `voip_service_provider_id_foreign` (`service_provider_id`),
  KEY `voip_deleted_at_index` (`deleted_at`),
  KEY `voip_status_index` (`status`),
  CONSTRAINT `voip_module_id_foreign` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE SET NULL,
  CONSTRAINT `voip_service_provider_id_foreign` FOREIGN KEY (`service_provider_id`) REFERENCES `service_providers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `voip_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `voip`
--

LOCK TABLES `voip` WRITE;
/*!40000 ALTER TABLE `voip` DISABLE KEYS */;
INSERT INTO `voip` VALUES (1,5,4,4,'Main SIP Trunk','[\"101\",\"102\"]','+1-212-555-0100','trunk','sip_main','eyJpdiI6InBRZEdwS00wMXd5dmJXVjFOOElxNGc9PSIsInZhbHVlIjoiekk3SGwzVnkvSU12OHNla3ZrMGx5QT09IiwibWFjIjoiMTZhNDIwYTk3NjIyMDg2NTY0MDJhODY5YzRkNzU0YTM3ODVlMjhiMGUxZmZiMzM5Y2YwOTFkM2M2NDQxMTAxMCIsInRhZyI6IiJ9','https://voip.mainsiptrunk.com',19.99,'2025-12-28','2026-12-28','active','Demo Main SIP Trunk entry.',NULL,NULL,'2026-06-28 05:14:11','2026-06-28 16:37:23','2026-06-28 16:37:23','eyJpdiI6ImloeGlUTGtzWmhrNEdnMjNIRktzaHc9PSIsInZhbHVlIjoieXpxVmxPNk13T2NUdVBKS2pDVjhHUT09IiwibWFjIjoiY2M2NTkyY2FiNGMzZmY4NTI5YjA1MzYzNzFlMmNjNzQ4NTc2MDYzNTdhZGI5NGZjMjM3ZDQ3YmE0MTkxZTQwYSIsInRhZyI6IiJ9','10.0.0.10','both','active','9','Demo team: Main SIP Trunk'),(2,6,4,2,'Sales Phone Line','[\"201\"]','+1-212-555-0200','sip','sip_sales','eyJpdiI6IjlqaDgzaGVKbFY0R1Q2MURBVTVQYnc9PSIsInZhbHVlIjoiNElIMHVMdFBOTFBXTU9kR0dzMWVWUT09IiwibWFjIjoiMDA0MjRjMjVkYzE3Y2YxNzBhZGE3NDkzMTQyZWMxZjg2MDlhMTgyY2E5MDdlMDM4NDZlNDdkNGQwODU1YmJiZSIsInRhZyI6IiJ9','https://voip.salesphoneline.com',14.99,'2025-12-28','2026-12-28','active','Demo Sales Phone Line entry.',NULL,NULL,'2026-06-28 05:14:11','2026-06-28 16:37:27','2026-06-28 16:37:27','eyJpdiI6IkN6OHJSbU51Ykt0VURaakVjSEFxUkE9PSIsInZhbHVlIjoid09aM0gvckVCVU9JYzd4ZWpaa0M0dz09IiwibWFjIjoiYjNkODUwYTdkZDIzZjgzNTcyNGI3ZGZiN2Y5OTcwZmUzMjg2MGZiOGU5NDhiZGZhODAyNTM2OTU4OWU4YjE3YSIsInRhZyI6IiJ9','10.0.0.11','inbound','active','9','Demo team: Sales Phone Line');
/*!40000 ALTER TABLE `voip` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vps`
--

DROP TABLE IF EXISTS `vps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vps` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `module_id` bigint(20) unsigned DEFAULT NULL,
  `service_provider_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `plan` varchar(255) DEFAULT NULL,
  `ip_address` varchar(255) DEFAULT NULL,
  `password` text DEFAULT NULL,
  `os` varchar(255) DEFAULT NULL,
  `ram_mb` int(11) DEFAULT NULL,
  `disk_gb` int(11) DEFAULT NULL,
  `cpu_cores` int(11) DEFAULT NULL,
  `department` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `login_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`login_ids`)),
  `additional_ips` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`additional_ips`)),
  `cost` decimal(10,2) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `monitoring_url` varchar(255) DEFAULT NULL,
  `last_ping_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vps_user_id_foreign` (`user_id`),
  KEY `vps_module_id_foreign` (`module_id`),
  KEY `vps_service_provider_id_foreign` (`service_provider_id`),
  KEY `vps_deleted_at_index` (`deleted_at`),
  KEY `vps_status_index` (`status`),
  CONSTRAINT `vps_module_id_foreign` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE SET NULL,
  CONSTRAINT `vps_service_provider_id_foreign` FOREIGN KEY (`service_provider_id`) REFERENCES `service_providers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `vps_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vps`
--

LOCK TABLES `vps` WRITE;
/*!40000 ALTER TABLE `vps` DISABLE KEYS */;
INSERT INTO `vps` VALUES (1,5,3,3,'Production Web Server','s-2vcpu-2gb','203.0.113.10','eyJpdiI6ImJTZmMrcjdnWDFxWjlDYzRtL3RHakE9PSIsInZhbHVlIjoidHVMd1hyS1hxbWxSeDhsWnZac2FsZz09IiwibWFjIjoiZWQzZDIyYTIwYzUzYzM3ZmRkMWUyODMyOTE0NDE0YzkzN2M2NDBhY2FhY2UwMWYxNTUyNTVhYjc0YzdjNWYyZiIsInRhZyI6IiJ9','Ubuntu 24.04',2048,50,2,NULL,NULL,NULL,NULL,15.00,'2026-03-28','2027-03-28','active','Demo Production Web Server entry.',NULL,NULL,'2026-06-28 05:14:11','2026-06-28 16:37:15','2026-06-28 16:37:15'),(2,6,3,2,'Database Server','s-4vcpu-8gb','203.0.113.20','eyJpdiI6IkRMZXoweXJUVGxRSGF0VlNkZE9qMXc9PSIsInZhbHVlIjoicnhOaGg5dlFKUE1kZHhObnNZTVc1QT09IiwibWFjIjoiY2M1N2VmNGNlODdmNzI5YTI4NmMzNzlmMTc4OTYwYmM0Njg4ZTg1MWM0NDlhY2Y2MDUzOTgxZGI0MDA1NDFlNiIsInRhZyI6IiJ9','Debian 12',8192,160,4,NULL,NULL,NULL,NULL,48.00,'2026-03-28','2027-03-28','active','Demo Database Server entry.',NULL,NULL,'2026-06-28 05:14:11','2026-06-28 16:37:16','2026-06-28 16:37:16');
/*!40000 ALTER TABLE `vps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `webhooks`
--

DROP TABLE IF EXISTS `webhooks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `webhooks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `events` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`events`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_fired_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `webhooks_user_id_foreign` (`user_id`),
  KEY `webhooks_deleted_at_index` (`deleted_at`),
  CONSTRAINT `webhooks_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `webhooks`
--

LOCK TABLES `webhooks` WRITE;
/*!40000 ALTER TABLE `webhooks` DISABLE KEYS */;
/*!40000 ALTER TABLE `webhooks` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-03  8:20:55
