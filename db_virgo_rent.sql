-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: db_virgo_rent
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
-- Table structure for table `tbl_admin`
--

DROP TABLE IF EXISTS `tbl_admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_admin` (
  `id_admin` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id_admin`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_admin`
--

LOCK TABLES `tbl_admin` WRITE;
/*!40000 ALTER TABLE `tbl_admin` DISABLE KEYS */;
INSERT INTO `tbl_admin` VALUES (1,'admin','$2y$10$zv.2xGeX4qcAG9lyfOiwbOp.1srqabHTYh91PGT.g1nWL2CCcT8eK');
/*!40000 ALTER TABLE `tbl_admin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_driver`
--

DROP TABLE IF EXISTS `tbl_driver`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_driver` (
  `id_driver` int(11) NOT NULL AUTO_INCREMENT,
  `nama_driver` varchar(100) NOT NULL,
  `pengalaman` varchar(50) NOT NULL,
  `tarif_driver` int(11) NOT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_driver`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_driver`
--

LOCK TABLES `tbl_driver` WRITE;
/*!40000 ALTER TABLE `tbl_driver` DISABLE KEYS */;
INSERT INTO `tbl_driver` VALUES (7,'Sugril','2 Tahun',150000,'driver_6a69d9c0b25aa.jpg'),(8,'saipul','5 tahun',150000,'driver_6a70fa0e900bc.jpg');
/*!40000 ALTER TABLE `tbl_driver` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_kendaraan`
--

DROP TABLE IF EXISTS `tbl_kendaraan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_kendaraan` (
  `id_kendaraan` int(11) NOT NULL AUTO_INCREMENT,
  `nama_mobil` varchar(100) NOT NULL,
  `kategori` varchar(50) NOT NULL,
  `transmisi` varchar(20) NOT NULL,
  `bahan_bakar` varchar(20) NOT NULL,
  `kapasitas_kursi` int(11) NOT NULL,
  `harga_sewa` int(11) NOT NULL,
  `status` varchar(20) DEFAULT 'Tersedia',
  `gambar` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_kendaraan`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_kendaraan`
--

LOCK TABLES `tbl_kendaraan` WRITE;
/*!40000 ALTER TABLE `tbl_kendaraan` DISABLE KEYS */;
INSERT INTO `tbl_kendaraan` VALUES (10,'Daihatsu Terios','suv','Manual','Bensin',7,350000,'Tersedia','mobil_6a70e7c71f715.webp'),(11,'Toyota innova Reborn','mpv','Manual','Bensin',7,600000,'Tersedia','mobil_6a70f8da89910.webp'),(12,'Honda Brio','city','Manual','Bensin',5,350000,'Tersedia','mobil_6a70f933668e4.jpg'),(13,'Toyota Calya','mpv','Manual','Bensin',4,275000,'Tersedia','mobil_6a70f9a06c816.jpg');
/*!40000 ALTER TABLE `tbl_kendaraan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_login_session`
--

DROP TABLE IF EXISTS `tbl_login_session`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_login_session` (
  `id_session` int(11) NOT NULL AUTO_INCREMENT,
  `id_admin` int(11) NOT NULL,
  `session_token` varchar(128) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `login_time` datetime DEFAULT current_timestamp(),
  `last_activity` datetime DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_session`),
  KEY `idx_admin` (`id_admin`),
  KEY `idx_token` (`session_token`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_login_session`
--

LOCK TABLES `tbl_login_session` WRITE;
/*!40000 ALTER TABLE `tbl_login_session` DISABLE KEYS */;
INSERT INTO `tbl_login_session` VALUES (1,1,'c089a07fc6a0c627c2ddaebded7c5d2d5acdbe0464b4414c6e821c330ffedeb3','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-08-04 04:30:53','2026-08-04 04:31:07','2026-08-04 07:30:53',0),(2,1,'c494ccda7be04260824b49080468c73161781857b77d7a53fd9ddf5d1919e683','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36','2026-08-04 04:31:46','2026-08-04 04:31:46','2026-08-04 07:31:46',0),(3,1,'aec452eaa8c8b432822d1bf79b6ad3a1101c6cfd8eb6b0ed074edbbf5fa4b2aa','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-08-04 04:32:01','2026-08-04 04:32:01','2026-08-04 07:32:01',0),(4,1,'4b469ecd27781432767e9104923398e077a0c1adced7291af5ed479769cf2869','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0','2026-08-04 04:32:41','2026-08-04 04:32:46','2026-08-04 07:32:41',0),(5,1,'4f39173bd7fb03e13b4470fd3b27cbc506db6dacc5617517e068d74eb24325ae','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-08-04 04:33:03','2026-08-04 04:33:03','2026-08-04 07:33:03',0),(6,1,'2096788e2b6b1d01b2b7144a1cbbcf7614b2dceb8792ac03b97baffa2fe35bc3','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-08-04 04:33:11','2026-08-04 04:33:53','2026-08-04 07:33:11',0),(7,1,'cbf06268bd276e0ef0a17a6db6b69d49879ac66c2ee618d13c89345a78175cb7','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-08-04 04:34:00','2026-08-04 04:34:00','2026-08-04 07:34:00',0),(8,1,'c9c286c421948fc36044a67ce1fc8fcb72d226ae8431fe7f55534ee662354eb6','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0','2026-08-04 05:41:06','2026-08-04 05:41:30','2026-08-04 08:41:06',0),(9,1,'d41febc6d4bedf1f46246f2fcc61c29cd2b1e0608089bc4a832dcebfe4521852','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0','2026-08-04 05:42:31','2026-08-04 05:42:31','2026-08-04 08:42:31',0),(10,1,'0804cb0ac0da9c9dcd04280387df6a6f89a46630e233d80c711430edb211789f','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-08-04 06:00:02','2026-08-04 06:00:27','2026-08-04 09:00:02',0),(11,1,'3e4b549c4d02cbcc5080bfcfab16ddabc82d2a073af34837a2ed6fde962c1e88','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-08-04 06:01:43','2026-08-04 06:14:24','2026-08-04 09:01:43',0),(12,1,'8982e408c4bad7a6b5198e58968c63a711b0853a06f37801b1d519faeff31fb8','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-08-04 06:26:39','2026-08-04 06:27:13','2026-08-04 09:26:39',0),(13,1,'bbc2513fde67ec4f3a6b834b805d4bc319467a0bb71634ac893d3993ef45e700','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-08-04 06:30:39','2026-08-04 06:30:40','2026-08-04 09:30:39',0),(14,1,'615435543d5a199b129857a092c69daa5260f6e0addccb6387106f69812cae5b','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0','2026-08-05 09:07:48','2026-08-05 09:07:48','2026-08-05 12:07:48',0),(15,1,'0729f80996499fe6dc13dfcbf46c6cd5e797449cb1df360820617015a40f373e','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0','2026-08-05 09:08:16','2026-08-05 09:08:16','2026-08-05 12:08:16',0),(16,1,'2b47b59b5d7a748ae420a1131406afa2cc49484c67a4ee0676f2a7a9e7efc31a','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36','2026-08-05 09:09:06','2026-08-05 09:09:06','2026-08-05 12:09:06',0),(17,1,'8474b77ac3e3540c689a9ef6f2159206a52f6d27be4a11c0e57b1e33ac53000a','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36','2026-08-05 09:33:46','2026-08-05 09:33:46','2026-08-05 12:33:46',0),(18,1,'570de4729d41727b1de0d53ac969c468ead35d39892f00d8cbd8becfa2c9f2cc','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36','2026-08-05 09:36:19','2026-08-05 09:36:48','2026-08-05 12:36:19',0),(19,1,'259fa8a9395f3400e33a6414749b86c58c9b6269d51386b5fbaca82354029b86','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36','2026-08-05 09:37:25','2026-08-05 09:39:51','2026-08-05 12:37:25',0),(20,1,'9812504b53185e51df0dbf14d9e47947ee1ddef378c580bb9b9726cc75ce4eea','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36','2026-08-05 09:47:41','2026-08-05 09:49:15','2026-08-05 12:47:41',0),(21,1,'9c14f40fc105942764cc87b3b02d1d0c42037e70422db65e236c9f203111c4fc','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36','2026-08-05 09:57:26','2026-08-05 09:58:08','2026-08-05 12:57:26',0),(22,1,'3e9e5978e294a9d63dade6d881c7a6c0ba24354610240e5e81ffdf5cc50ebcf4','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36','2026-08-05 14:36:49','2026-08-05 14:37:03','2026-08-05 17:36:49',0),(23,1,'5a5d7b8050781727b64ac2457e849083ae3fd75e5fdb03e9df8bc2955fb9f716','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36','2026-08-05 17:48:10','2026-08-05 17:50:03','2026-08-05 20:48:10',0),(24,1,'7e126af68668395bd0fafd38133eb9733cd2186e8abcdecd6a66ec30a26b8414','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36','2026-08-05 18:15:38','2026-08-05 18:38:04','2026-08-05 21:15:38',0),(25,1,'6acdd6259a8d8b609050e7a767119e4246e84ae298e7aa02237a591441a0eb42','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36','2026-08-05 18:38:20','2026-08-05 19:00:37','2026-08-05 21:38:20',0),(26,1,'d87bd89ba762aa38e0cce2c7246cb02b01a1caf3a122e57469997d1144b1c3cf','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36','2026-08-05 19:00:40','2026-08-05 19:09:04','2026-08-05 22:00:40',0),(27,1,'b28f50facc545b6c5b85f04b4f31086294b0ce363f71afab795cb2ca4fcefb11','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36','2026-08-05 19:09:45','2026-08-05 21:37:09','2026-08-05 22:09:45',0),(28,1,'865f6b2e7cfaaa49a7d4a12f215dd276c5f7b82420bb4ff446ce2c66363a570a','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36','2026-08-05 21:37:16','2026-08-06 00:41:42','2026-08-06 00:37:16',0),(29,1,'f208031d729bdd0c97d9a58768cf23d237c8594a62c0256d0f0628da7cfc88a4','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0','2026-08-06 01:54:01','2026-08-06 01:56:11','2026-08-06 04:54:01',0),(30,1,'fd64d87b457858a952b7f5c941f21a8d12fe10a9ecf5b55f596d3d63f65356b1','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36','2026-08-06 01:56:12','2026-08-06 01:57:20','2026-08-06 04:56:12',0),(31,1,'2de4a2fa08fe62ffefaa05f3c6a11c491f8e1c5be461dec52e4bda5b154f8445','::1','curl/8.21.0','2026-08-06 22:31:24','2026-08-06 22:31:44','2026-08-07 01:31:24',0),(32,1,'799ce54fc4b516f845e65b7113b0a56adbe0402dcc436cb4247234f74b489476','::1','curl/8.21.0','2026-08-06 22:32:24','2026-08-06 22:32:24','2026-08-07 01:32:24',0),(33,1,'6e1275ff13d45840bec0a80e94daa5ba671d796bfcb818e33b22ddd99d1f70a2','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36','2026-08-07 00:11:11','2026-08-07 00:12:00','2026-08-07 03:11:11',0),(34,1,'dbd2ed00037a64a7aa5026aaed6bdebde2eb03d35d4c244f157dff0b2bf33810','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36','2026-08-07 00:12:36','2026-08-07 00:20:32','2026-08-07 03:12:36',0),(35,1,'9e2a5db5b7b21db8d13b7ac38affa5f2f29bc05937d5184d4c04975b801efd7b','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36','2026-08-07 00:20:52','2026-08-07 00:22:11','2026-08-07 03:20:52',0),(36,1,'fca33c5f2474eba6c56f9a8a6ef37a7acac232e748b46c9b2a12e3967efbbbb3','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36','2026-08-07 00:23:01','2026-08-07 00:30:10','2026-08-07 03:23:01',0),(37,1,'e4c4b0508a47e4785dbab1b56062cd918218acfaa53ef2b620f0bafb07402a9f','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36','2026-08-07 00:37:41','2026-08-07 00:37:58','2026-08-07 03:37:41',0),(38,1,'de3ecc80ff3f0332880e809075a4aeb89bf199bb58efe827fd8b705e570c4d52','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36','2026-08-07 00:38:18','2026-08-07 00:49:32','2026-08-07 03:38:18',0),(39,1,'58e6eae5d2dc07a90c770843a6346203d9b81e01fa3c1b5d4f9affa3af2fc5f9','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36','2026-08-07 20:41:51','2026-08-07 20:42:23','2026-08-07 23:41:51',0),(40,1,'828c4b00d9d527eaafb0a1222d40712b3b4d8bcde102ad73aa0fcb157eba7b1d','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36','2026-08-07 20:43:29','2026-08-07 20:43:40','2026-08-07 23:43:29',0),(41,1,'8de1d23c583d8fabab26bdc79f32a3e337a053116ae9f97abb19301ff6ca66d3','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36','2026-08-07 20:44:12','2026-08-07 20:56:16','2026-08-07 23:44:12',1);
/*!40000 ALTER TABLE `tbl_login_session` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_reservasi`
--

DROP TABLE IF EXISTS `tbl_reservasi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_reservasi` (
  `id_reservasi` int(11) NOT NULL AUTO_INCREMENT,
  `nama_pemesan` varchar(100) NOT NULL,
  `no_wa` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `alamat_jemput` text NOT NULL,
  `id_kendaraan` int(11) DEFAULT NULL,
  `id_driver` int(11) DEFAULT NULL,
  `id_wisata` int(11) DEFAULT NULL,
  `tanggal_mulai` date NOT NULL,
  `durasi_hari` int(11) NOT NULL,
  `jumlah_orang` int(11) NOT NULL,
  `catatan` text DEFAULT NULL,
  `total_harga` int(11) NOT NULL,
  `status_reservasi` varchar(20) DEFAULT 'Menunggu Konfirmasi',
  PRIMARY KEY (`id_reservasi`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_reservasi`
--

LOCK TABLES `tbl_reservasi` WRITE;
/*!40000 ALTER TABLE `tbl_reservasi` DISABLE KEYS */;
INSERT INTO `tbl_reservasi` VALUES (6,'suhail ','081268656842','rehan2@gmail.com','ede',5,NULL,NULL,'2026-08-02',3,1,'',1800000,'Selesai'),(7,'rendi','081268656842','rehan2@gmail.com','ede',1,NULL,NULL,'2026-08-03',2,1,'',550000,'Selesai'),(8,'riandi fajri','083136907167','riandi24002@gmail.com','Bukittinggi ',4,NULL,NULL,'2026-08-02',2,1,'',700000,'Selesai'),(9,'Karya Motor','083136907167','karyamotor638@gmail.com','Bukittinggi',1,NULL,NULL,'2026-08-05',2,2,'',550000,'Selesai'),(10,'Karya Motor','083136907167','karyamotor638@gmail.com','Bukittinggi',4,NULL,NULL,'2026-08-04',2,1,'',700000,'Dibatalkan'),(11,'Karya Motor','083136907167','karyamotor638@gmail.com','Bukittinggi',1,NULL,NULL,'2026-08-13',2,1,'',550000,'Dibatalkan'),(12,'Karya Motor','083136907167','karyamotor638@gmail.com','Bukittinggi',9,NULL,NULL,'2026-08-04',2,4,'',600000,'Selesai'),(13,'riandi','083136907167','riandi@njj','bukittinggi',NULL,NULL,1,'2026-08-05',3,4,'',800000,'Selesai'),(14,'riandi','083136907167','riandi@njj','bukittinggi',NULL,NULL,1,'2026-08-05',2,1,'',800000,'Selesai'),(15,'putra','083136907167','karyamotor638@gmail.com','Bukittinggi',10,NULL,NULL,'2026-08-05',2,5,'',700000,'Selesai'),(16,'agung','083136907167','karyamotor638@gmail.com','Bukittinggi',12,NULL,NULL,'2026-08-05',7,3,'uahfiwefhiwewe',2450000,'Selesai'),(17,'lol','083136907167','karyamotor638@gmail.com','Bukittinggi',11,NULL,NULL,'2026-08-05',2,1,'',1200000,'Selesai'),(18,'Karya Motor','083136907167','karyamotor638@gmail.com','Bukittinggi',13,NULL,NULL,'2026-08-05',3,1,'',825000,'Selesai'),(19,'Riandi andi','083136907167','riandi24042000@gmail.com','kjhh',10,NULL,NULL,'2026-08-05',1,1,'',350000,'Selesai'),(20,'Karya Motor','083136907167','karyamotor638@gmail.com','Bukittinggi',11,NULL,NULL,'2026-08-13',2,1,'',1200000,'Selesai'),(21,'farhan','083136907167','riandi24042000@gmail.com','kjhh',10,NULL,NULL,'2026-08-05',3,1,'nanti langsung ke lokasi saja',1050000,'Selesai'),(22,'Riandi andi','083136907167','riandi24042000@gmail.com','kjhh',11,NULL,NULL,'2026-08-05',3,1,'',1800000,'Selesai'),(23,'Riandi andi','083136907167','riandi24042000@gmail.com','kjhh',12,NULL,NULL,'2026-08-05',2,1,'',700000,'Selesai'),(24,'Riandi andi','083136907167','riandi24042000@gmail.com','kjhh',13,NULL,NULL,'2026-08-05',2,1,'',550000,'Selesai'),(25,'Riandi andi','083136907167','riandi24042000@gmail.com','kjhh',10,NULL,NULL,'2026-08-05',2,1,'',700000,'Selesai'),(26,'Riandi andi','083136907167','riandi24042000@gmail.com','kjhh',11,NULL,NULL,'2026-08-05',2,1,'',1200000,'Selesai'),(27,'Riandi andi','083136907167','riandi24042000@gmail.com','kjhh',12,NULL,NULL,'2026-08-05',2,1,'',700000,'Selesai'),(28,'Riandi andi','083136907167','riandi24042000@gmail.com','kjhh',13,NULL,NULL,'2026-08-05',2,1,'',550000,'Selesai'),(29,'Riandi andi','083136907167','riandi24042000@gmail.com','kjhh',13,NULL,NULL,'2026-08-05',1,1,'',275000,'Selesai'),(30,'Riandi andi','083136907167','riandi24042000@gmail.com','kjhh',13,NULL,NULL,'2026-08-05',2,1,'',550000,'Selesai'),(31,'Riandi andi','083136907167','riandi24042000@gmail.com','kjhh',NULL,NULL,1,'2026-08-05',2,1,'',800000,'Selesai'),(32,'Riandi andi','083136907167','riandi24042000@gmail.com','kjhh',NULL,NULL,1,'2026-08-05',1,1,'',0,'Selesai'),(33,'Riandi andi','083136907167','riandi24042000@gmail.com','kjhh',NULL,NULL,1,'2026-08-06',1,1,'',800000,'Selesai'),(34,'Karya Motor','083136907167','karyamotor638@gmail.com','Bukittinggi',NULL,NULL,1,'2026-08-06',2,1,'',800000,'Selesai'),(35,'Karya Motor','083136907167','karyamotor638@gmail.com','Bukittinggi',NULL,NULL,1,'2026-08-06',5,1,'',800000,'Selesai'),(36,'riandi','083136907167','riandi@njj','bukittinggi',NULL,NULL,1,'2026-08-06',2,1,'',800000,'Selesai'),(37,'Karya Motor','083136907167','karyamotor638@gmail.com','Bukittinggi',10,NULL,NULL,'2026-08-07',1,1,'',350000,'Selesai'),(38,'Honda Brio','081268656842','riandi24042000@gmail.com','ede',10,NULL,NULL,'2026-08-13',3,1,'',1050000,'Selesai'),(39,'mobil','081268656842','rehan2@gmail.com','ede',11,NULL,NULL,'2026-08-07',2,1,'',1200000,'Selesai');
/*!40000 ALTER TABLE `tbl_reservasi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_reviews`
--

DROP TABLE IF EXISTS `tbl_reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_reviews` (
  `id_review` int(11) NOT NULL AUTO_INCREMENT,
  `id_kendaraan` int(11) NOT NULL,
  `nama_pelanggan` varchar(100) NOT NULL,
  `rating` tinyint(4) NOT NULL DEFAULT 5,
  `komentar` text DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Approved',
  `tanggal` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_review`),
  KEY `idx_review_kendaraan` (`id_kendaraan`),
  KEY `idx_review_status` (`status`),
  CONSTRAINT `fk_review_kendaraan` FOREIGN KEY (`id_kendaraan`) REFERENCES `tbl_kendaraan` (`id_kendaraan`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `chk_rating` CHECK (`rating` between 1 and 5)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_reviews`
--

LOCK TABLES `tbl_reviews` WRITE;
/*!40000 ALTER TABLE `tbl_reviews` DISABLE KEYS */;
INSERT INTO `tbl_reviews` VALUES (1,10,'Karya Motor',4,'sangat bagus','Approved','2026-08-05 14:25:20');
/*!40000 ALTER TABLE `tbl_reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_wisata`
--

DROP TABLE IF EXISTS `tbl_wisata`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_wisata` (
  `id_wisata` int(11) NOT NULL AUTO_INCREMENT,
  `nama_paket` varchar(100) NOT NULL,
  `durasi` varchar(50) NOT NULL,
  `harga` int(11) NOT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_wisata`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_wisata`
--

LOCK TABLES `tbl_wisata` WRITE;
/*!40000 ALTER TABLE `tbl_wisata` DISABLE KEYS */;
INSERT INTO `tbl_wisata` VALUES (1,'City Tour Pekanbaru','1 Hari',800000,NULL),(2,'Wisata Alam Riau','2 Hari 1 Malam',1500000,NULL),(3,'Wisata Sejarah Siak','2 Hari 1 Malam',2000000,NULL),(4,'Wisata Pantai Dumai','3 Hari 2 Malam',2500000,NULL),(5,'Wisata Kepulauan Riau','5 Hari 4 Malam',5000000,NULL);
/*!40000 ALTER TABLE `tbl_wisata` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-08-07 23:58:56
