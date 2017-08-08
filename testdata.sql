-- MySQL dump 10.13  Distrib 5.7.19, for Linux (x86_64)
--
-- Host: localhost    Database: reservation_manage
-- ------------------------------------------------------
-- Server version	5.7.19-0ubuntu0.16.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `categ_id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `categ_name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`categ_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Math'),(2,'Music'),(3,'English'),(4,'Physic');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `history`
--

DROP TABLE IF EXISTS `history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `history` (
  `hist_id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `hist_res_id` int(6) unsigned NOT NULL,
  `hist_tid` int(6) unsigned NOT NULL,
  `hist_sid` int(6) unsigned NOT NULL,
  `hist_categ_id` int(6) unsigned NOT NULL,
  `hist_pur_id` int(6) unsigned DEFAULT NULL,
  `hist_tp_id` int(6) unsigned NOT NULL,
  `hist_week_nb` int(10) unsigned NOT NULL,
  `hist_day_nb` int(10) unsigned NOT NULL,
  `hist_begin_nb` int(10) unsigned NOT NULL,
  `hist_end_nb` int(10) unsigned NOT NULL,
  `hist_action` tinyint(1) DEFAULT NULL,
  `hist_action_time` datetime DEFAULT NULL,
  `hist_session_create_time` datetime DEFAULT NULL,
  `hist_session_id` int(6) unsigned NOT NULL,
  PRIMARY KEY (`hist_id`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `history`
--

LOCK TABLES `history` WRITE;
/*!40000 ALTER TABLE `history` DISABLE KEYS */;
INSERT INTO `history` VALUES (1,1,1,2,2,NULL,13,2,14,38,39,0,'2017-08-04 23:58:14','2017-07-29 20:37:33',175),(2,2,1,2,1,NULL,7,3,26,38,40,0,'2017-08-04 23:58:14','2017-07-29 20:37:33',175),(3,3,1,2,1,NULL,7,3,23,38,40,0,'2017-08-04 23:58:14','2017-07-29 20:37:33',175),(4,4,1,2,1,NULL,7,3,25,44,47,0,'2017-08-04 23:58:14','2017-07-29 20:37:33',175),(5,5,1,2,1,NULL,7,3,23,30,31,0,'2017-08-04 23:58:14','2017-07-29 20:37:33',175),(6,6,1,2,1,NULL,7,3,22,31,33,0,'2017-08-04 23:58:14','2017-07-29 20:37:33',175),(7,7,1,2,1,NULL,7,3,21,34,37,0,'2017-08-04 23:58:14','2017-07-29 20:37:33',175),(8,2,1,2,1,NULL,7,3,27,11,12,2,'2017-08-05 01:56:09','2017-07-29 20:37:33',175),(9,4,1,2,1,NULL,7,3,26,18,19,2,'2017-08-05 01:56:09','2017-07-29 20:37:33',175),(11,4,1,2,1,NULL,7,3,26,38,39,2,'2017-08-05 02:04:55','2017-07-29 20:37:33',175),(12,5,1,2,1,NULL,7,3,24,2,3,2,'2017-08-05 02:13:32','2017-07-29 20:37:33',175),(13,3,1,2,1,NULL,7,3,24,10,12,2,'2017-08-05 02:13:32','2017-07-29 20:37:33',175),(15,6,1,2,1,NULL,7,3,22,31,33,1,'2017-08-05 02:26:24','2017-07-29 20:37:33',175),(16,7,1,2,1,NULL,7,3,21,34,37,1,'2017-08-05 02:26:24','2017-07-29 20:37:33',175),(18,6,1,2,1,NULL,7,3,22,31,33,1,'2017-08-05 02:28:52','2017-07-29 20:37:33',175),(19,7,1,2,1,NULL,7,3,21,34,37,1,'2017-08-05 02:28:52','2017-07-29 20:37:33',175),(21,6,1,2,1,NULL,7,3,22,31,33,1,'2017-08-05 02:31:37','2017-07-29 20:37:33',175),(22,7,1,2,1,NULL,7,3,21,34,37,1,'2017-08-05 02:31:37','2017-07-29 20:37:33',175),(24,1,1,2,2,NULL,13,2,14,38,39,0,'2017-08-04 23:58:14','2017-07-29 20:37:33',175),(25,4,1,2,1,NULL,7,3,26,38,39,0,'2017-08-05 02:04:55','2017-07-29 20:37:33',175),(26,8,1,2,1,NULL,7,3,24,1,1,0,'2017-08-05 02:35:36','2017-07-29 20:37:33',175),(27,9,1,2,1,NULL,7,3,24,9,13,0,'2017-08-05 02:35:36','2017-07-29 20:37:33',175),(31,2,1,2,1,NULL,7,3,27,11,12,1,'2017-08-05 02:35:36','2017-07-29 20:37:33',175),(32,5,1,2,1,NULL,7,3,24,22,23,2,'2017-08-05 02:35:36','2017-07-29 20:37:33',175),(33,3,1,2,1,NULL,7,3,24,30,32,2,'2017-08-05 02:35:36','2017-07-29 20:37:33',175),(35,1,1,2,2,NULL,13,2,14,38,39,0,'2017-08-04 23:58:14','2017-07-29 20:37:33',175),(36,3,1,2,1,NULL,7,3,24,30,32,0,'2017-08-05 02:35:36','2017-07-29 20:37:33',175),(37,4,1,2,1,NULL,7,3,26,38,39,0,'2017-08-05 02:04:55','2017-07-29 20:37:33',175),(38,8,1,2,1,NULL,7,3,24,1,1,0,'2017-08-05 02:35:36','2017-07-29 20:37:33',175),(39,11,1,2,1,NULL,7,3,23,12,14,0,'2017-08-05 03:25:44','2017-07-29 20:37:33',175),(42,9,1,2,1,NULL,7,3,24,9,13,1,'2017-08-05 03:25:44','2017-07-29 20:37:33',175),(43,5,1,2,1,NULL,7,3,24,42,43,2,'2017-08-05 03:25:44','2017-07-29 20:37:33',175),(44,11,1,2,1,NULL,7,3,23,12,14,1,'2017-08-05 03:33:29','2017-07-29 20:37:33',175),(45,3,1,2,1,NULL,7,3,25,2,4,2,'2017-08-05 03:33:29','2017-07-29 20:37:33',175);
/*!40000 ALTER TABLE `history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase`
--

DROP TABLE IF EXISTS `purchase`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase` (
  `pur_id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `pur_tid` int(6) unsigned NOT NULL,
  `pur_sid` int(6) unsigned NOT NULL,
  `pur_categ_id` int(6) unsigned NOT NULL,
  `pur_tp_id` int(6) unsigned NOT NULL,
  `pur_hour_total` int(10) unsigned NOT NULL,
  `pur_hour_rest` int(10) unsigned NOT NULL,
  `pur_statut` tinyint(1) DEFAULT NULL,
  `pur_create_time` datetime DEFAULT NULL,
  `pur_modify_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`pur_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase`
--

LOCK TABLES `purchase` WRITE;
/*!40000 ALTER TABLE `purchase` DISABLE KEYS */;
INSERT INTO `purchase` VALUES (1,1,2,1,1,10,10,NULL,'2017-08-06 14:11:34','2017-08-06 14:11:34'),(2,1,2,1,7,10,6,NULL,'2017-08-06 14:11:34','2017-08-06 14:11:34');
/*!40000 ALTER TABLE `purchase` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reservation`
--

DROP TABLE IF EXISTS `reservation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reservation` (
  `res_id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `res_tid` int(6) unsigned NOT NULL,
  `res_sid` int(6) unsigned NOT NULL,
  `res_categ_id` int(6) unsigned NOT NULL,
  `res_pur_id` int(6) unsigned DEFAULT NULL,
  `res_tp_id` int(6) unsigned NOT NULL,
  `res_week_nb` int(10) unsigned NOT NULL,
  `res_day_nb` int(10) unsigned NOT NULL,
  `res_begin_nb` int(10) unsigned NOT NULL,
  `res_end_nb` int(10) unsigned NOT NULL,
  `res_statut` tinyint(1) DEFAULT NULL,
  `res_create_time` datetime DEFAULT NULL,
  `res_modify_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `res_session_id` int(6) unsigned NOT NULL,
  PRIMARY KEY (`res_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reservation`
--

LOCK TABLES `reservation` WRITE;
/*!40000 ALTER TABLE `reservation` DISABLE KEYS */;
INSERT INTO `reservation` VALUES (1,1,2,2,NULL,13,2,14,38,39,1,'2017-08-04 23:58:14','2017-08-04 23:58:14',175),(2,1,2,1,NULL,7,3,27,11,12,3,'2017-08-04 23:58:14','2017-08-05 02:35:36',175),(3,1,2,1,NULL,7,3,25,2,4,1,'2017-08-04 23:58:14','2017-08-05 03:33:29',175),(4,1,2,1,NULL,7,3,26,38,39,2,'2017-08-04 23:58:14','2017-08-05 02:04:55',175),(5,1,2,1,NULL,7,3,24,42,43,4,'2017-08-04 23:58:14','2017-08-05 03:25:44',175),(6,1,2,1,NULL,7,3,22,31,33,3,'2017-08-04 23:58:14','2017-08-05 02:31:37',175),(7,1,2,1,NULL,7,3,21,34,37,3,'2017-08-04 23:58:14','2017-08-05 02:31:37',175),(8,1,2,1,2,7,3,24,1,1,2,'2017-08-05 02:35:36','2017-08-05 02:35:36',175),(9,1,2,1,NULL,7,3,24,9,13,3,'2017-08-05 02:35:36','2017-08-05 03:25:44',175),(11,1,2,1,NULL,7,3,23,12,14,3,'2017-08-05 03:25:44','2017-08-05 03:33:29',175);
/*!40000 ALTER TABLE `reservation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_balance`
--

DROP TABLE IF EXISTS `student_balance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_balance` (
  `sb_sid` int(6) unsigned NOT NULL,
  `sb_amount` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`sb_sid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_balance`
--

LOCK TABLES `student_balance` WRITE;
/*!40000 ALTER TABLE `student_balance` DISABLE KEYS */;
INSERT INTO `student_balance` VALUES (2,20000);
/*!40000 ALTER TABLE `student_balance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_operation`
--

DROP TABLE IF EXISTS `student_operation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_operation` (
  `ope_id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `ope_session_id` int(6) unsigned NOT NULL,
  `ope_res_id` int(6) unsigned DEFAULT NULL,
  `ope_tid` int(6) unsigned NOT NULL,
  `ope_categ_id` int(6) unsigned NOT NULL,
  `ope_tp_id` int(6) unsigned NOT NULL,
  `ope_week_nb` int(10) unsigned NOT NULL,
  `ope_day_nb` int(10) unsigned NOT NULL,
  `ope_begin_nb` int(10) unsigned NOT NULL,
  `ope_end_nb` int(10) unsigned NOT NULL,
  `ope_statut` tinyint(1) DEFAULT NULL,
  `ope_create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ope_pur_id` int(6) unsigned DEFAULT NULL,
  PRIMARY KEY (`ope_id`)
) ENGINE=InnoDB AUTO_INCREMENT=79 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_operation`
--

LOCK TABLES `student_operation` WRITE;
/*!40000 ALTER TABLE `student_operation` DISABLE KEYS */;
INSERT INTO `student_operation` VALUES (71,175,4,1,1,7,3,26,38,39,0,'2017-08-06 20:56:16',NULL),(72,175,5,1,1,7,3,25,14,15,2,'2017-08-06 20:56:30',NULL),(73,175,NULL,1,1,7,3,24,18,19,1,'2017-08-06 20:56:48',NULL),(75,175,8,1,1,7,3,24,1,1,0,'2017-08-07 21:32:56',2),(76,175,NULL,1,1,7,3,25,20,23,1,'2017-08-08 22:26:23',NULL),(77,175,NULL,1,1,7,3,24,26,28,1,'2017-08-08 22:26:28',NULL),(78,175,NULL,1,2,13,3,26,14,16,1,'2017-08-08 23:12:20',NULL);
/*!40000 ALTER TABLE `student_operation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_session`
--

DROP TABLE IF EXISTS `student_session`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_session` (
  `session_id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `session_sid` int(6) unsigned NOT NULL,
  `session_expire_time` datetime NOT NULL,
  `session_create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `session_statut` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`session_id`)
) ENGINE=InnoDB AUTO_INCREMENT=177 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_session`
--

LOCK TABLES `student_session` WRITE;
/*!40000 ALTER TABLE `student_session` DISABLE KEYS */;
INSERT INTO `student_session` VALUES (175,2,'2018-07-29 20:57:33','2017-07-29 20:37:33',1),(176,1,'2017-07-29 23:51:52','2017-07-29 23:31:52',1);
/*!40000 ALTER TABLE `student_session` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `teacher_categ`
--

DROP TABLE IF EXISTS `teacher_categ`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teacher_categ` (
  `tc_tid` int(6) unsigned NOT NULL,
  `tc_categ_id` int(6) unsigned NOT NULL,
  `tc_expire_time` datetime DEFAULT NULL,
  PRIMARY KEY (`tc_tid`,`tc_categ_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teacher_categ`
--

LOCK TABLES `teacher_categ` WRITE;
/*!40000 ALTER TABLE `teacher_categ` DISABLE KEYS */;
INSERT INTO `teacher_categ` VALUES (1,1,NULL),(1,2,NULL),(1,3,NULL),(1,4,NULL),(2,2,NULL),(2,3,NULL),(3,3,NULL);
/*!40000 ALTER TABLE `teacher_categ` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `teacher_prise`
--

DROP TABLE IF EXISTS `teacher_prise`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teacher_prise` (
  `tp_id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `tp_tid` int(6) unsigned NOT NULL,
  `tp_categ_id` int(6) unsigned NOT NULL,
  `tp_prise` int(11) DEFAULT NULL,
  `tp_effective_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`tp_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teacher_prise`
--

LOCK TABLES `teacher_prise` WRITE;
/*!40000 ALTER TABLE `teacher_prise` DISABLE KEYS */;
INSERT INTO `teacher_prise` VALUES (1,1,1,1000,'2017-07-19 01:42:54'),(2,1,1,NULL,'2017-07-19 01:43:08'),(3,1,2,2000,'2017-07-19 01:43:08'),(4,1,2,NULL,'2017-07-19 01:45:00'),(5,1,3,1300,'2017-07-19 01:45:00'),(6,1,4,1200,'2017-07-19 01:45:00'),(7,1,1,11100,'2017-07-19 01:46:10'),(8,1,2,11100,'2017-07-19 01:46:10'),(9,1,2,NULL,'2017-07-19 01:47:19'),(10,1,3,NULL,'2017-07-19 01:47:19'),(11,2,2,10000,'2017-07-19 01:50:16'),(12,2,3,10000,'2017-07-19 01:50:16'),(13,1,2,11100,'2017-07-29 20:36:17');
/*!40000 ALTER TABLE `teacher_prise` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `teacher_remuneration`
--

DROP TABLE IF EXISTS `teacher_remuneration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teacher_remuneration` (
  `tr_tid` int(6) unsigned NOT NULL,
  `tr_month_nb` int(10) unsigned NOT NULL,
  `tr_sum` int(11) NOT NULL DEFAULT '0',
  `tr_paydate` date DEFAULT NULL,
  `tr_paid` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`tr_tid`,`tr_month_nb`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teacher_remuneration`
--

LOCK TABLES `teacher_remuneration` WRITE;
/*!40000 ALTER TABLE `teacher_remuneration` DISABLE KEYS */;
/*!40000 ALTER TABLE `teacher_remuneration` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `teacher_schedule`
--

DROP TABLE IF EXISTS `teacher_schedule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teacher_schedule` (
  `ts_tid` int(6) unsigned NOT NULL,
  `ts_week_nb` int(10) unsigned NOT NULL,
  `ts_slot_0` char(48) NOT NULL,
  `ts_slot_1` char(48) NOT NULL,
  `ts_slot_2` char(48) NOT NULL,
  `ts_slot_3` char(48) NOT NULL,
  `ts_slot_4` char(48) NOT NULL,
  `ts_slot_5` char(48) NOT NULL,
  `ts_slot_6` char(48) NOT NULL,
  PRIMARY KEY (`ts_tid`,`ts_week_nb`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teacher_schedule`
--

LOCK TABLES `teacher_schedule` WRITE;
/*!40000 ALTER TABLE `teacher_schedule` DISABLE KEYS */;
INSERT INTO `teacher_schedule` VALUES (1,1,'100101101000000000000000000000000111111111111011','100101101100000000000000000000111111111011111011','100101101100000111111110000000111111111011111011','000000000111110111111110111111111111111011111000','100101101111111111111111111111111111111001111110','100101101111111111111111111111111111111000011000','100101101001111100000000000000000000000000000000'),(1,2,'000000000000000000000000000000000000011110000000','000000011111111111111111111111111110000110011000','000000011111111100000111111111111110011110011000','000000011100000000111111111111111110011111100000','000000000000000000111111111111110001111001111110','000000000000001111111111111111110000000001111100','000000000000000000000000000000000000000000000000'),(1,3,'000000000001111111111111111111111011111111000000','000000111111111111111111111111111111111111000000','111111111111111111111111111111111111111111000000','111111111111111111111111111111111111111111111111','111111111111111111111111111111111111111111111111','111111111111111111111111111111111111111110000000','111111111111111100000000000000000000000000000000');
/*!40000 ALTER TABLE `teacher_schedule` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `user_id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `user_name` varchar(50) DEFAULT NULL,
  `user_pwd` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'zhao','zhao'),(2,'qian','qian'),(3,'sun','sun'),(4,'li','li');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `weekly_action_stamp`
--

DROP TABLE IF EXISTS `weekly_action_stamp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `weekly_action_stamp` (
  `stamp_uid` int(6) unsigned NOT NULL,
  `stamp_week_nb` int(10) unsigned NOT NULL,
  `stamp_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`stamp_uid`,`stamp_week_nb`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `weekly_action_stamp`
--

LOCK TABLES `weekly_action_stamp` WRITE;
/*!40000 ALTER TABLE `weekly_action_stamp` DISABLE KEYS */;
INSERT INTO `weekly_action_stamp` VALUES (1,1,'2017-07-23 14:45:00'),(1,2,'2017-08-02 23:08:09'),(1,3,'2017-08-08 23:12:20'),(2,2,'2017-08-02 23:08:09'),(2,3,'2017-08-08 23:12:20');
/*!40000 ALTER TABLE `weekly_action_stamp` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-08-09  0:31:08
