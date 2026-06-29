
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
DROP TABLE IF EXISTS `cibo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cibo` (
  `codice` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(60) NOT NULL,
  `descr` varchar(255) NOT NULL,
  `prezzo` decimal(6,2) NOT NULL,
  `categoria` varchar(40) NOT NULL DEFAULT 'Vari',
  `in_evidenza` tinyint(1) NOT NULL DEFAULT 0,
  `immagine` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`codice`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `cibo` WRITE;
/*!40000 ALTER TABLE `cibo` DISABLE KEYS */;
INSERT INTO `cibo` VALUES (1,'Piadina Delicata','Piadina con rucola, stracchino e crudo.',6.50,'Piadine',1,'d09284cc625c7917.jpg'),(2,'Piadina al cotto','Piadina con prosciutto cotto e insalata.',6.00,'Piadine',0,'bcd9a248a03e0077.jpg'),(4,'Piadina Salsicciosa','Piadina con salsiccia e zucchine.',7.00,'Piadine',1,'092ebdb3d312095e.webp'),(6,'Panino classico','Panino con prosciutto crudo, salsa verde, melanzane e insalata.',5.00,'Panini',0,'2d69075c62c2affb.jpg'),(7,'Insalata mista','Insalata con lattuga, pomodoro, cipolla, crostini e maionese.',6.00,'Contorni',1,'4a2c20f7222cf907.jpg'),(8,'French Toast','Pane da toast con prosciutto e formaggio sottiletta.',10.00,'Panini',1,'d5daf56c57065bcf.jpg'),(9,'Piadina Copertina','Panino con salsa Abarthas, prosciutto e insalata.',6.00,'Piadine',0,'09c7a1a2b7621ee0.png'),(10,'Pancake','Pancakes multi strati con frutti di bosco',10.00,'Dessert',0,'5a078defd6d8062f.jpg');
/*!40000 ALTER TABLE `cibo` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `cliente`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cliente` (
  `emailc` varchar(120) NOT NULL,
  `nome` varchar(60) NOT NULL,
  `psw` varchar(255) NOT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`emailc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `cliente` WRITE;
/*!40000 ALTER TABLE `cliente` DISABLE KEYS */;
INSERT INTO `cliente` VALUES ('leti@leti.leti','Letizia','$2y$12$W0B/UWNPAPhrLrgPzfFa9.Ne7tS4EQNL9v9KexJ5dThT.OJ4SxjSC',1);
/*!40000 ALTER TABLE `cliente` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `composizione`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `composizione` (
  `codice_cibo` int(11) NOT NULL,
  `id_ingrediente` int(11) NOT NULL,
  `rimovibile` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`codice_cibo`,`id_ingrediente`),
  KEY `idx_comp_ingr` (`id_ingrediente`),
  CONSTRAINT `fk_comp_cibo` FOREIGN KEY (`codice_cibo`) REFERENCES `cibo` (`codice`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_comp_ingr` FOREIGN KEY (`id_ingrediente`) REFERENCES `ingrediente` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `composizione` WRITE;
/*!40000 ALTER TABLE `composizione` DISABLE KEYS */;
INSERT INTO `composizione` VALUES (1,1,0),(1,2,1),(1,3,1),(1,4,1),(2,1,0),(2,5,1),(4,1,0),(4,6,1),(4,7,1),(6,4,1),(6,8,0),(6,9,1),(6,10,1),(6,11,1),(7,11,1),(7,12,1),(7,13,1),(7,14,1),(7,15,1),(8,16,0),(8,17,1),(9,8,0),(9,11,1),(9,12,1),(9,15,1),(9,18,0),(10,19,0),(10,20,1);
/*!40000 ALTER TABLE `composizione` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `contiene`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contiene` (
  `riga_id` int(11) NOT NULL AUTO_INCREMENT,
  `id` int(11) NOT NULL,
  `codice` int(11) NOT NULL,
  `quantita` int(11) NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `ingredienti_rimossi` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`riga_id`),
  KEY `idx_contiene_id` (`id`),
  KEY `idx_contiene_codice` (`codice`),
  CONSTRAINT `fk_contiene_cibo` FOREIGN KEY (`codice`) REFERENCES `cibo` (`codice`) ON UPDATE CASCADE,
  CONSTRAINT `fk_contiene_ordine` FOREIGN KEY (`id`) REFERENCES `ordine` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `contiene` WRITE;
/*!40000 ALTER TABLE `contiene` DISABLE KEYS */;
INSERT INTO `contiene` VALUES (1,1,1,1,NULL,NULL),(2,1,4,1,NULL,NULL),(3,1,7,1,NULL,NULL);
/*!40000 ALTER TABLE `contiene` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ingrediente`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ingrediente` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(60) NOT NULL,
  `allergene` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_ingrediente_nome` (`nome`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `ingrediente` WRITE;
/*!40000 ALTER TABLE `ingrediente` DISABLE KEYS */;
INSERT INTO `ingrediente` VALUES (1,'Piadina base','Glutine'),(2,'Rucola',NULL),(3,'Stracchino','Latte'),(4,'Prosciutto crudo',NULL),(5,'Prosciutto cotto',NULL),(6,'Salsiccia',NULL),(7,'Crema di broccoli','Latte'),(8,'Pane','Glutine'),(9,'Salsa verde',NULL),(10,'Melanzane',NULL),(11,'Lattuga',NULL),(12,'Pomodoro',NULL),(13,'Cipolla',NULL),(14,'Crostini','Glutine'),(15,'Maionese','Uova'),(16,'Gramigna SG',NULL),(17,'Ragu','Sedano'),(18,'Cotoletta','Glutine'),(19,'Penne','Glutine'),(20,'Sugo di pomodoro',NULL);
/*!40000 ALTER TABLE `ingrediente` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ordine`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ordine` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prezzotot` decimal(8,2) NOT NULL,
  `indirizzo` varchar(150) NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `data_ord` datetime NOT NULL DEFAULT current_timestamp(),
  `emailc` varchar(120) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_ordine_emailc` (`emailc`),
  CONSTRAINT `fk_ordine_cliente` FOREIGN KEY (`emailc`) REFERENCES `cliente` (`emailc`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `ordine` WRITE;
/*!40000 ALTER TABLE `ordine` DISABLE KEYS */;
INSERT INTO `ordine` VALUES (1,24.50,'via letipesce 123 ferrara','test','2026-06-29 18:54:31','leti@leti.leti');
/*!40000 ALTER TABLE `ordine` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

