-- MySQL dump 10.13  Distrib 5.5.55, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: radius
-- ------------------------------------------------------
-- Server version	5.5.55-0+deb8u1

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
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent` int(10) unsigned DEFAULT NULL,
  `name` varchar(32) NOT NULL DEFAULT '',
  `value` varchar(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `parent` (`parent`),
  KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=36 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,NULL,'devices','ver. 1');
INSERT INTO `settings` VALUES (2,1,'device_types','');
INSERT INTO `settings` VALUES (3,2,'cable','');
INSERT INTO `settings` VALUES (4,3,'fiber','1');
INSERT INTO `settings` VALUES (5,3,'fiber','2');
INSERT INTO `settings` VALUES (6,2,'switch','');
INSERT INTO `settings` VALUES (7,6,'cuper','1');
INSERT INTO `settings` VALUES (8,2,'patchpanel','');
INSERT INTO `settings` VALUES (9,8,'coupler','1');
INSERT INTO `settings` VALUES (10,8,'fiber','2');
INSERT INTO `settings` VALUES (11,2,'onu','');
INSERT INTO `settings` VALUES (12,2,'mconverter','');
INSERT INTO `settings` VALUES (13,2,'splitter','');
INSERT INTO `settings` VALUES (14,2,'server','');
INSERT INTO `settings` VALUES (15,2,'divisor','');
INSERT INTO `settings` VALUES (16,2,'ups','');
INSERT INTO `settings` VALUES (17,2,'wifi','');
INSERT INTO `settings` VALUES (18,11,'fiber','1');
INSERT INTO `settings` VALUES (19,11,'cuper','2');
INSERT INTO `settings` VALUES (20,12,'fiber','1');
INSERT INTO `settings` VALUES (21,12,'cuper','2');
INSERT INTO `settings` VALUES (22,13,'fiber','1');
INSERT INTO `settings` VALUES (23,14,'cuper','1');
INSERT INTO `settings` VALUES (24,15,'fiber','1');
INSERT INTO `settings` VALUES (25,16,'cuper','1');
INSERT INTO `settings` VALUES (26,17,'wifi','1');
INSERT INTO `settings` VALUES (27,17,'cuper','2');
INSERT INTO `settings` VALUES (28,1,'port_formers','');
INSERT INTO `settings` VALUES (29,28,'cable','bynode');
INSERT INTO `settings` VALUES (30,28,'patchpanel','byporttype');
INSERT INTO `settings` VALUES (31,1,'min_ports','');
INSERT INTO `settings` VALUES (32,31,'onu','2');
INSERT INTO `settings` VALUES (33,31,'mconverter','2');
INSERT INTO `settings` VALUES (34,31,'wifi','2');
INSERT INTO `settings` VALUES (35,31,'server','1');
UNLOCK TABLES;
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;

--
-- Table structure for table `claimperform`
--

DROP TABLE IF EXISTS `claimperform`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `claimperform` (
  `unique_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cid` int(10) unsigned NOT NULL,
  `woid` int(10) unsigned NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `begintime` datetime DEFAULT NULL,
  `endtime` datetime DEFAULT NULL,
  `tmlike` tinyint(1) NOT NULL DEFAULT '0',
  `note` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`unique_id`),
  KEY `cid` (`cid`),
  KEY `woid` (`woid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `claims`
--

DROP TABLE IF EXISTS `claims`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `claims` (
  `unique_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(2) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `user` varchar(64) NOT NULL DEFAULT '',
  `uid` smallint(5) NOT NULL DEFAULT 0,
  `rid` int(5) unsigned NOT NULL DEFAULT '1',
  `location` varchar(64) NOT NULL DEFAULT '',
  `woid` int(10) unsigned DEFAULT NULL,
  `claimtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fio` varchar(128) NOT NULL DEFAULT '',
  `phone` varchar(64) NOT NULL DEFAULT '',
  `address` varchar(64) NOT NULL DEFAULT '',
  `operator` varchar(32) NOT NULL DEFAULT '',
  `content` text NOT NULL DEFAULT '',
  `perform_note` varchar(255) NOT NULL DEFAULT '',
  `perform_operator` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`unique_id`),
  KEY `claimtime` (`claimtime`),
  KEY `user` (`user`),
  KEY `status` (`status`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `currency`
--

DROP TABLE IF EXISTS `currency`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `currency` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL DEFAULT '',
  `short` varchar(10) NOT NULL DEFAULT '',
  `blocked` smallint(5) NOT NULL DEFAULT '0',
  `rate` double(12,6) NOT NULL DEFAULT '1.000000',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `currency`
--

LOCK TABLES `currency` WRITE;
/*!40000 ALTER TABLE `currency` DISABLE KEYS */;
INSERT INTO `currency` VALUES (1,'Гривна','грн',0,2.000000);
INSERT INTO `currency` VALUES (2,'Рубль','руб',0,1.000000);
INSERT INTO `currency` VALUES (3,'Доллар','$',0,70.000000);
/*!40000 ALTER TABLE `currency` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `devices`
--

DROP TABLE IF EXISTS `devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `devices` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(32) NOT NULL DEFAULT 'unknown',
  `subtype` varchar(32) NOT NULL DEFAULT '',
  `login` varchar(32) NOT NULL DEFAULT '',
  `password` varchar(32) NOT NULL DEFAULT '',
  `ssid` varchar(32) NOT NULL DEFAULT '',
  `psk` varchar(32) NOT NULL DEFAULT '',
  `ip` varchar(15) NOT NULL DEFAULT '',
  `community` varchar(32) NOT NULL DEFAULT '',
  `object` int(10) unsigned DEFAULT NULL,
  `name` varchar(128) NOT NULL DEFAULT '',
  `firmname` varchar(32) NOT NULL DEFAULT '',
  `colorscheme` varchar(32) NOT NULL DEFAULT '',
  `node1` int(10) unsigned DEFAULT NULL,
  `node2` int(10) unsigned DEFAULT NULL,
  `numports` int(3) NOT NULL DEFAULT '0',
  `bandleports` int(10) unsigned DEFAULT '0',
  `macaddress` varchar(17) DEFAULT '',
  `note` varchar(255) NOT NULL DEFAULT '',
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `object` (`object`),
  KEY `node1` (`node1`),
  KEY `node2` (`node2`),
  KEY `macaddress` (`macaddress`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `before_insert_device` BEFORE INSERT ON `devices`
FOR EACH ROW
BEGIN
	DECLARE type_id, cable, pos, min_ports int(10) UNSIGNED DEFAULT NULL;
	DECLARE msg varchar(255) DEFAULT '';
	SELECT s3.id INTO type_id FROM settings s1, settings s2, settings s3  WHERE s3.parent=s2.id AND s2.parent=s1.id AND s1.parent IS NULL AND s1.name='devices' AND s2.name='device_types' AND s3.name=NEW.type;
	SELECT s3.value INTO min_ports FROM settings s1, settings s2, settings s3  WHERE s3.parent=s2.id AND s2.parent=s1.id AND s1.parent IS NULL AND s1.name='devices' AND s2.name='min_ports' AND s3.name=NEW.type;
	IF type_id is NULL THEN
		SET msg=concat('device type "',NEW.type,'" not found in settings!');
		SIGNAL SQLSTATE '45000' SET message_text=msg;
	END IF;

	IF NEW.type in ('divisor','splitter') THEN
		IF NEW.type = 'divisor' THEN
			SET NEW.numports = 3;
			IF NEW.subtype IS NULL THEN
				SET NEW.subtype = '50/50';
			END IF;
		ELSE
			SET pos = locate('x',NEW.subtype);
			IF pos > 0 THEN
				SET NEW.numports = cast(substr(NEW.subtype,pos+1) as int)+1;
			ELSE
				SET NEW.subtype = '1x2';
				SET NEW.numports = 3;
			END IF;
		END IF;
	END IF;

	IF min_ports > 0 AND NEW.numports < min_ports THEN
		SET NEW.numports = min_ports;
	END IF;
	
	IF NEW.type = 'cable' AND NEW.object IS NOT NULL THEN
		IF NEW.bandleports = 0 OR NEW.bandleports IS NULL THEN SET NEW.bandleports = 24; END IF;
		SELECT id INTO cable FROM `devices` WHERE `object`=NEW.object;
		IF cable IS NOT NULL THEN
			SET msg=concat('Cable object ',NEW.object,' alredy exists!');
			SIGNAL SQLSTATE '45000' SET message_text=msg;
		END IF;
	END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `after_insert_device` AFTER INSERT ON `devices`
FOR EACH ROW
BEGIN
	CALL CreatePorts(NEW.id,NEW.type,NEW.subtype,NEW.numports,NEW.node1,NEW.node2,NEW.bandleports,NEW.colorscheme);
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `update_device` BEFORE UPDATE ON `devices`
FOR EACH ROW
BEGIN
	DECLARE cable, pos int(10) UNSIGNED DEFAULT NULL;
	DECLARE msg varchar(255) DEFAULT '';
    IF OLD.type != NEW.type THEN
        SET msg=concat('Trying to change type of device ',NEW.type);
        SIGNAL SQLSTATE '45000' SET message_text=msg;
    END IF;
    IF NEW.type = 'cable' AND NEW.node1 = NEW.node2 THEN
        SET msg=concat('for ',NEW.type,' begin and end nodes cannot by equivalent!');
        SIGNAL SQLSTATE '45000' SET message_text=msg;
    END IF;
    IF NEW.type != 'cable' AND NEW.node2 IS NOT NULL THEN
        SET msg=concat('for ',NEW.type,' node2 must be null!');
        SIGNAL SQLSTATE '45000' SET message_text=msg;
    END IF;
	IF NEW.type = 'cable' AND NEW.object IS NOT NULL AND NEW.object != OLD.object THEN
		SELECT id INTO cable FROM `devices` WHERE `object`=NEW.object;
		IF cable IS NOT NULL THEN
			SET msg=concat('Cable object ',NEW.object,' alredy exists!');
			SIGNAL SQLSTATE '45000' SET message_text=msg;
		END IF;
	END IF;
	IF (NEW.type = 'divisor' OR NEW.type = 'splitter') AND NEW.subtype IS NULL THEN
		SET msg=concat('No subtype ',type,' ',NEW.id);
		SIGNAL SQLSTATE '45000' SET message_text=msg;
	END IF;
	IF NEW.type = 'cable' AND NEW.bandleports = 0 OR NEW.bandleports IS NULL THEN
		SET NEW.bandleports = 24;
	END IF;

	IF OLD.subtype != NEW.subtype THEN
		IF NEW.type = 'divisor' THEN
			SET NEW.numports = 3;
		END IF;
		IF NEW.type = 'splitter' THEN
			SET pos = locate('x',NEW.subtype);
			IF pos > 0 THEN
				SET NEW.numports = substr(NEW.subtype,locate('x',NEW.subtype)+1)+1;
			ELSE
				SET NEW.subtype = '1x2';
				SET NEW.numports = 3;
			END IF;
		END IF;
	END IF;

	IF OLD.bandleports != NEW.bandleports OR OLD.colorscheme != NEW.colorscheme THEN
		IF NEW.colorscheme != '' AND NEW.bandleports > 0 THEN
			UPDATE devports p
				LEFT OUTER JOIN devprofiles as dp ON dp.name=NEW.colorscheme AND dp.port=mod(p.number-1,NEW.bandleports)+1
				LEFT OUTER JOIN devprofiles as dps ON dps.name=NEW.colorscheme AND dps.port=mod(p.number-2,NEW.bandleports)+1
				LEFT OUTER JOIN devprofiles as dp1 ON dp1.name=NEW.colorscheme AND dp1.port=floor((p.number-1)/NEW.bandleports)+1
			SET
				p.color=if(NEW.type!='splitter',if(dp.color IS NULL,'',dp.color),if(p.number=1,'white',if(dps.color IS NULL,'',dps.color))), 
				p.bandle=if(NEW.numports<=NEW.bandleports OR NEW.bandleports=0 OR NEW.type!='cable','',if(dp1.color IS NULL,'',dp1.color)), 
				p.coloropt=if(NEW.type!='splitter',if(dp.option IS NULL,'',dp.option),'solid')
			WHERE
				p.device=NEW.id;
		ELSE
			UPDATE devports p SET color='', coloropt='solid', bandle='' WHERE device=NEW.id;
		END IF;
	END IF;

    IF OLD.numports > NEW.numports THEN
		UPDATE `devports` SET link=NULL WHERE id IN (SELECT link FROM (SELECT * FROM `devports` WHERE `device`=OLD.id AND link IS NOT NULL AND `number`>NEW.numports) p);
		DELETE FROM `devports` WHERE `device`=OLD.id AND `number`>NEW.numports;
		CALL updateDivide(NEW.id,NEW.type,NEW.subtype);
    END IF;

	IF OLD.type != 'cable' AND (OLD.node1 != NEW.node1 OR (OLD.node1 IS NULL AND NEW.node1 IS NOT NULL) OR (OLD.node1 IS NOT NULL AND NEW.node1 IS NULL)) THEN
		UPDATE `devports` SET link=NULL WHERE id IN (SELECT link FROM (SELECT * FROM `devports` WHERE `device`=OLD.id AND link IS NOT NULL) p);
		UPDATE `devports` SET node=NEW.node1 WHERE `device`=NEW.id;
	END IF;

	IF OLD.type='cable' AND (OLD.node1 != NEW.node1 OR (OLD.node1 IS NULL AND NEW.node1 IS NOT NULL) OR (OLD.node1 IS NOT NULL AND NEW.node1 IS NULL)) THEN
		UPDATE `devports` SET link=NULL WHERE id IN (SELECT link FROM (SELECT d1.link FROM `devports` d1, `devports` d2 WHERE d1.`device`=d2.`device` AND d1.`number`=d2.`number` AND d1.id<d2.id AND d1.`device`=OLD.id) p);
		UPDATE `devports` d1, `devports` d2 SET d1.node=NEW.node1, d1.link=NULL WHERE d1.`device`=d2.`device` AND d1.`number`=d2.`number` AND d1.id<d2.id AND d1.`device`=OLD.id;
	END IF;

	IF OLD.type='cable' AND (OLD.node2 != NEW.node2 OR (OLD.node2 IS NULL AND NEW.node2 IS NOT NULL) OR (OLD.node2 IS NOT NULL AND NEW.node2 IS NULL)) THEN
		UPDATE `devports` SET link=NULL WHERE id IN (SELECT link FROM (SELECT d1.link FROM `devports` d1, `devports` d2 WHERE d1.`device`=d2.`device` AND d1.`number`=d2.`number` AND d1.id>d2.id AND d1.`device`=OLD.id) p);
		UPDATE `devports` d1, `devports` d2 SET d1.node=NEW.node2 WHERE d1.`device`=d2.`device` AND d1.`number`=d2.`number` AND d1.id>d2.id AND d1.`device`=OLD.id;
	END IF;

	IF OLD.numports < NEW.numports THEN
		CALL CreatePorts(NEW.id,NEW.type,NEW.subtype,NEW.numports,NEW.node1,NEW.node2,NEW.bandleports,NEW.colorscheme);
		CALL updateDivide(NEW.id,NEW.type,NEW.subtype);
	END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `delete_device` BEFORE DELETE ON `devices`
FOR EACH ROW
BEGIN
	UPDATE `devports` SET link=NULL WHERE id IN (SELECT link FROM (SELECT * FROM `devports` WHERE `device`=OLD.id AND link IS NOT NULL) p);
    DELETE FROM `devports` WHERE `device` = OLD.id;
END */;;
DELIMITER ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `devports`
--

DROP TABLE IF EXISTS `devports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `devports` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device` int(10) unsigned NOT NULL,
  `number` int(3) NOT NULL DEFAULT '0',
  `node` int(10) unsigned DEFAULT NULL,
  `link` int(10) unsigned DEFAULT NULL,
  `link1` int(10) unsigned DEFAULT NULL,
  `snmp_id` int(8) DEFAULT NULL,
  `name` varchar(32) NOT NULL DEFAULT '',
  `porttype` varchar(32) NOT NULL DEFAULT 'unknown',
  `module` varchar(32) DEFAULT NULL,
  `color` varchar(32) NOT NULL DEFAULT '',
  `coloropt` varchar(32) NOT NULL DEFAULT 'solid',
  `bandle` varchar(32) NOT NULL DEFAULT '',
  `note` varchar(255) NOT NULL DEFAULT '',
  `divide` double(6,2) DEFAULT '0.00',
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `dev` (`device`),
  KEY `link` (`link`),
  KEY `node` (`node`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `delete_devport` AFTER DELETE ON `devports`
FOR EACH ROW
BEGIN
	DECLARE mytable varchar(128) DEFAULT NULL;
	SELECT table_name INTO mytable FROM INFORMATION_SCHEMA.tables WHERE table_schema='radius' AND table_name='vlans';
	IF mytable = 'vlans' THEN
		DELETE FROM `vlans` WHERE `port` = OLD.id;
	END IF;
END */;;
DELIMITER ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `devprofiles`
--

DROP TABLE IF EXISTS `devprofiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `devprofiles` (
  `id` int(4) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL DEFAULT '',
  `port` int(3) NOT NULL DEFAULT '0',
  `rucolor` varchar(32) NOT NULL DEFAULT '',
  `color` varchar(32) NOT NULL DEFAULT '',
  `option` varchar(32) NOT NULL DEFAULT '',
  `htmlcolor` varchar(16) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `devprofiles`
--

LOCK TABLES `devprofiles` WRITE;
/*!40000 ALTER TABLE `devprofiles` DISABLE KEYS */;
INSERT INTO `devprofiles` VALUES (61,'южкабель',9,'голубой','deepskyblue','solid','#bef');
INSERT INTO `devprofiles` VALUES (5,'южкабель',1,'красный','red','solid','#f88');
INSERT INTO `devprofiles` VALUES (6,'южкабель',2,'зеленый','green','solid','#9f9');
INSERT INTO `devprofiles` VALUES (7,'южкабель',4,'синий','blue','solid','#99f');
INSERT INTO `devprofiles` VALUES (8,'южкабель',3,'желтый','yellow','solid','#ff9');
INSERT INTO `devprofiles` VALUES (9,'южкабель',5,'белый','white','solid','#fff');
INSERT INTO `devprofiles` VALUES (10,'южкабель',6,'серый','gray','solid','#ddd');
INSERT INTO `devprofiles` VALUES (11,'южкабель',7,'коричневый','brown','solid','#eb9');
INSERT INTO `devprofiles` VALUES (12,'южкабель',8,'фиолетовый','purple','solid','#e7e');
INSERT INTO `devprofiles` VALUES (75,'южкабель',23,'розовый','deeppink','dashed','#fce');
INSERT INTO `devprofiles` VALUES (72,'южкабель',20,'фиолетовый','purple','dashed','#e7e');
INSERT INTO `devprofiles` VALUES (70,'южкабель',18,'серый','gray','dashed','#ddd');
INSERT INTO `devprofiles` VALUES (69,'южкабель',17,'белый','white','dashed','#fff');
INSERT INTO `devprofiles` VALUES (67,'южкабель',15,'желтый','yellow','dashed','#ff9');
INSERT INTO `devprofiles` VALUES (65,'южкабель',13,'красный','red','dashed','#f88');
INSERT INTO `devprofiles` VALUES (37,'Lancore',1,'синий','blue','solid','#99f');
INSERT INTO `devprofiles` VALUES (38,'Lancore',2,'оранжевый','orange','solid','#fd4');
INSERT INTO `devprofiles` VALUES (39,'Lancore',3,'зеленый','green','solid','#9f9');
INSERT INTO `devprofiles` VALUES (40,'Lancore',4,'коричневый','brown','solid','#eb9');
INSERT INTO `devprofiles` VALUES (41,'Lancore',5,'серый','gray','solid','#ddd');
INSERT INTO `devprofiles` VALUES (42,'Lancore',6,'белый','white','solid','#fff');
INSERT INTO `devprofiles` VALUES (43,'Lancore',7,'красный','red','solid','#f88');
INSERT INTO `devprofiles` VALUES (44,'Lancore',8,'черный','black','solid','#999');
INSERT INTO `devprofiles` VALUES (45,'Lancore',9,'желтый','yellow','solid','#ff9');
INSERT INTO `devprofiles` VALUES (46,'Lancore',10,'фиолетовый','purple','solid','#e7e');
INSERT INTO `devprofiles` VALUES (47,'Lancore',11,'розовый','deeppink','solid','#fce');
INSERT INTO `devprofiles` VALUES (48,'Lancore',12,'голубой','deepskyblue','solid','#bef');
INSERT INTO `devprofiles` VALUES (49,'Lancore',13,'синий','blue','dashed','#99f');
INSERT INTO `devprofiles` VALUES (50,'Lancore',14,'оранжевый','orange','dashed','#fd4');
INSERT INTO `devprofiles` VALUES (51,'Lancore',15,'зеленый','green','dashed','#9f9');
INSERT INTO `devprofiles` VALUES (52,'Lancore',16,'коричневый','brown','dashed','#eb9');
INSERT INTO `devprofiles` VALUES (53,'Lancore',17,'серый','gray','dashed','#ddd');
INSERT INTO `devprofiles` VALUES (54,'Lancore',18,'белый','white','dashed','#fff');
INSERT INTO `devprofiles` VALUES (55,'Lancore',19,'красный','red','dashed','#f88');
INSERT INTO `devprofiles` VALUES (56,'Lancore',20,'черный','black','dashed','#999');
INSERT INTO `devprofiles` VALUES (57,'Lancore',21,'желтый','yellow','dashed','#ff9');
INSERT INTO `devprofiles` VALUES (58,'Lancore',22,'фиолетовый','purple','dashed','#e7e');
INSERT INTO `devprofiles` VALUES (59,'Lancore',23,'розовый','deeppink','dashed','#fce');
INSERT INTO `devprofiles` VALUES (60,'Lancore',24,'голубой','deepskyblue','dashed','#bef');
INSERT INTO `devprofiles` VALUES (62,'южкабель',10,'черный','black','solid','#999');
INSERT INTO `devprofiles` VALUES (73,'южкабель',21,'голубой','deepskyblue','dashed','#bef');
INSERT INTO `devprofiles` VALUES (66,'южкабель',14,'зеленый','green','dashed','#9f9');
INSERT INTO `devprofiles` VALUES (68,'южкабель',16,'синий','blue','dashed','#99f');
INSERT INTO `devprofiles` VALUES (74,'южкабель',22,'черный','black','dashed','#999');
INSERT INTO `devprofiles` VALUES (76,'южкабель',24,'оранжевый','orange','dashed','#fd4');
INSERT INTO `devprofiles` VALUES (71,'южкабель',19,'коричневый','brown','dashed','#eb9');
INSERT INTO `devprofiles` VALUES (63,'южкабель',11,'розовый','deeppink','solid','#fce');
INSERT INTO `devprofiles` VALUES (64,'южкабель',12,'оранжевый','orange','solid','#fd4');
INSERT INTO `devprofiles` VALUES (79,'ТКО',1,'синий','blue','solid','#99f');
INSERT INTO `devprofiles` VALUES (80,'ТКО',2,'оранжевый','orange','solid','#fd4');
INSERT INTO `devprofiles` VALUES (81,'ТКО',3,'зеленый','green','solid','#9f9');
INSERT INTO `devprofiles` VALUES (82,'ТКО',4,'коричневый','brown','solid','#eb9');
INSERT INTO `devprofiles` VALUES (83,'ТКО',5,'серый','gray','solid','#ddd');
INSERT INTO `devprofiles` VALUES (84,'ТКО',6,'белый','white','solid','#fff');
INSERT INTO `devprofiles` VALUES (85,'ТКО',7,'красный','red','solid','#f88');
INSERT INTO `devprofiles` VALUES (86,'ТКО',8,'черный','black','solid','#999');
INSERT INTO `devprofiles` VALUES (87,'ТКО',9,'желтый','yellow','solid','#ff9');
INSERT INTO `devprofiles` VALUES (88,'ТКО',10,'фиолетовый','purple','solid','#e7e');
INSERT INTO `devprofiles` VALUES (89,'ТКО',11,'розовый','deeppink','solid','#fce');
INSERT INTO `devprofiles` VALUES (90,'ТКО',12,'голубой','deepskyblue','solid','#bef');
INSERT INTO `devprofiles` VALUES (91,'ТКО',13,'синий','blue','dashed','#99f');
INSERT INTO `devprofiles` VALUES (92,'ТКО',14,'оранжевый','orange','dashed','#fd4');
INSERT INTO `devprofiles` VALUES (93,'ТКО',15,'зеленый','green','dashed','#9f9');
INSERT INTO `devprofiles` VALUES (94,'ТКО',16,'коричневый','brown','dashed','#eb9');
INSERT INTO `devprofiles` VALUES (95,'ТКО',17,'серый','gray','dashed','#ddd');
INSERT INTO `devprofiles` VALUES (96,'ТКО',18,'белый','white','dashed','#fff');
INSERT INTO `devprofiles` VALUES (97,'ТКО',19,'красный','red','dashed','#f88');
INSERT INTO `devprofiles` VALUES (98,'ТКО',20,'черный','black','dashed','#999');
INSERT INTO `devprofiles` VALUES (99,'ТКО',21,'желтый','yellow','dashed','#ff9');
INSERT INTO `devprofiles` VALUES (100,'ТКО',22,'фиолетовый','purple','dashed','#e7e');
INSERT INTO `devprofiles` VALUES (101,'ТКО',23,'розовый','deeppink','dashed','#fce');
INSERT INTO `devprofiles` VALUES (102,'ТКО',24,'голубой','deepskyblue','dashed','#bef');
INSERT INTO `devprofiles` VALUES (103,'nkt',1,'красный','red','solid','#99f');
INSERT INTO `devprofiles` VALUES (104,'nkt',2,'зеленый','green','solid','#9f9');
INSERT INTO `devprofiles` VALUES (105,'nkt',3,'желтый','yellow','solid','#ff9');
INSERT INTO `devprofiles` VALUES (106,'nkt',4,'синий','blue','solid','#99f');
INSERT INTO `devprofiles` VALUES (107,'nkt',5,'нейтральный','neutral','solid','#fef');
INSERT INTO `devprofiles` VALUES (108,'nkt',6,'серый','gray','solid','#ddd');
INSERT INTO `devprofiles` VALUES (109,'nkt',7,'коричневый','brown','solid','#eb9');
INSERT INTO `devprofiles` VALUES (110,'nkt',8,'фиолетовый','purple','solid','#e7e');
INSERT INTO `devprofiles` VALUES (111,'nkt',9,'бирюзовый','turquoise','solid','#3сс');
INSERT INTO `devprofiles` VALUES (112,'nkt',10,'белый','white','solid','#fff');
INSERT INTO `devprofiles` VALUES (113,'nkt',11,'розовый','deeppink','solid','#fce');
INSERT INTO `devprofiles` VALUES (114,'nkt',12,'оранжевый','orange','solid','#fd4');
INSERT INTO `devprofiles` VALUES (115,'nkt',13,'красный','red','dashed','#99f');
INSERT INTO `devprofiles` VALUES (116,'nkt',14,'зеленый','green','dashed','#9f9');
INSERT INTO `devprofiles` VALUES (117,'nkt',15,'желтый','yellow','dashed','#ff9');
INSERT INTO `devprofiles` VALUES (118,'nkt',16,'синий','blue','dashed','#99f');
INSERT INTO `devprofiles` VALUES (119,'nkt',17,'нейтральный','neutral','dashed','#fef');
INSERT INTO `devprofiles` VALUES (120,'nkt',18,'серый','gray','dashed','#ddd');
INSERT INTO `devprofiles` VALUES (121,'nkt',19,'коричневый','brown','dashed','#eb9');
INSERT INTO `devprofiles` VALUES (122,'nkt',20,'фиолетовый','purple','dashed','#e7e');
INSERT INTO `devprofiles` VALUES (123,'nkt',21,'бирюзовый','turquoise','dashed','#3сс');
INSERT INTO `devprofiles` VALUES (124,'nkt',22,'белый','white','dashed','#fff');
INSERT INTO `devprofiles` VALUES (125,'nkt',23,'розовый','deeppink','dashed','#fce');
INSERT INTO `devprofiles` VALUES (126,'nkt',24,'оранжевый','orange','dashed','#fd4');
INSERT INTO `devprofiles` VALUES (127,'FinMark',1,'синий','blue','solid','#99f');
INSERT INTO `devprofiles` VALUES (128,'FinMark',2,'оранжевый','orange','solid','#fd4');
INSERT INTO `devprofiles` VALUES (129,'FinMark',3,'зеленый','green','solid','#9f9');
INSERT INTO `devprofiles` VALUES (130,'FinMark',4,'коричневый','brown','solid','#eb9');
INSERT INTO `devprofiles` VALUES (131,'FinMark',5,'серый','gray','solid','#ddd');
INSERT INTO `devprofiles` VALUES (132,'FinMark',6,'белый','white','solid','#fff');
INSERT INTO `devprofiles` VALUES (133,'FinMark',7,'красный','red','solid','#f88');
INSERT INTO `devprofiles` VALUES (134,'FinMark',8,'черный','black','solid','#999');
INSERT INTO `devprofiles` VALUES (135,'FinMark',9,'желтый','yellow','solid','#ff9');
INSERT INTO `devprofiles` VALUES (136,'FinMark',10,'фиолетовый','purple','solid','#e7e');
INSERT INTO `devprofiles` VALUES (137,'FinMark',11,'розовый','deeppink','solid','#fce');
INSERT INTO `devprofiles` VALUES (138,'FinMark',12,'бирюзовый','turquoise','solid','#3сс');
INSERT INTO `devprofiles` VALUES (139,'FinMark',13,'синий','blue','dashed','#99f');
INSERT INTO `devprofiles` VALUES (140,'FinMark',14,'оранжевый','orange','dashed','#fd4');
INSERT INTO `devprofiles` VALUES (141,'FinMark',15,'зеленый','green','dashed','#9f9');
INSERT INTO `devprofiles` VALUES (142,'FinMark',16,'коричневый','brown','dashed','#eb9');
INSERT INTO `devprofiles` VALUES (143,'FinMark',17,'серый','gray','dashed','#ddd');
INSERT INTO `devprofiles` VALUES (144,'FinMark',18,'белый','white','dashed','#fff');
INSERT INTO `devprofiles` VALUES (145,'FinMark',19,'красный','red','dashed','#f88');
INSERT INTO `devprofiles` VALUES (146,'FinMark',20,'неокрашенный','unpainted','dashed','#999');
INSERT INTO `devprofiles` VALUES (147,'FinMark',21,'желтый','yellow','dashed','#ff9');
INSERT INTO `devprofiles` VALUES (148,'FinMark',22,'фиолетовый','purple','dashed','#e7e');
INSERT INTO `devprofiles` VALUES (149,'FinMark',23,'розовый','deeppink','dashed','#fce');
INSERT INTO `devprofiles` VALUES (150,'FinMark',24,'бирюзовый','turquoise','dashed','#3сс');
INSERT INTO `devprofiles` VALUES (151,'ОдесКабель',1,'красный','red','solid','#f88');
INSERT INTO `devprofiles` VALUES (152,'ОдесКабель',2,'зеленый','green','solid','#9f9');
INSERT INTO `devprofiles` VALUES (153,'ОдесКабель',3,'голубой','deepskyblue','solid','#bef');
INSERT INTO `devprofiles` VALUES (154,'ОдесКабель',4,'желтый','yellow','solid','#ff9');
INSERT INTO `devprofiles` VALUES (155,'ОдесКабель',5,'белый','white','solid','#fff');
INSERT INTO `devprofiles` VALUES (156,'ОдесКабель',6,'серый','gray','solid','#ddd');
INSERT INTO `devprofiles` VALUES (157,'ОдесКабель',7,'коричневый','brown','solid','#eb9');
INSERT INTO `devprofiles` VALUES (158,'ОдесКабель',8,'фиолетовый','purple','solid','#e7e');
INSERT INTO `devprofiles` VALUES (159,'ОдесКабель',9,'оранжевый','orange','solid','#fd4');
INSERT INTO `devprofiles` VALUES (160,'ОдесКабель',10,'черный','black','solid','#999');
INSERT INTO `devprofiles` VALUES (161,'ОдесКабель',11,'розовый','deeppink','solid','#fce');
INSERT INTO `devprofiles` VALUES (162,'ОдесКабель',12,'аквамарин','aquamarine','solid','#6ас');
INSERT INTO `devprofiles` VALUES (163,'ОдесКабель',13,'красный','red','dashed','#f88');
INSERT INTO `devprofiles` VALUES (164,'ОдесКабель',14,'зеленый','green','dashed','#9f9');
INSERT INTO `devprofiles` VALUES (165,'ОдесКабель',15,'голубой','deepskyblue','dashed','#bef');
INSERT INTO `devprofiles` VALUES (166,'ОдесКабель',16,'желтый','yellow','dashed','#ff9');
INSERT INTO `devprofiles` VALUES (167,'ОдесКабель',17,'белый','white','dashed','#fff');
INSERT INTO `devprofiles` VALUES (168,'ОдесКабель',18,'серый','gray','dashed','#ddd');
INSERT INTO `devprofiles` VALUES (169,'ОдесКабель',19,'коричневый','brown','dashed','#eb9');
INSERT INTO `devprofiles` VALUES (170,'ОдесКабель',20,'фиолетовый','purple','dashed','#e7e');
INSERT INTO `devprofiles` VALUES (171,'ОдесКабель',21,'оранжевый','orange','dashed','#fd4');
INSERT INTO `devprofiles` VALUES (172,'ОдесКабель',22,'неокрашенный','unpainted','dashed','#999');
INSERT INTO `devprofiles` VALUES (173,'ОдесКабель',23,'розовый','deeppink','dashed','#fce');
INSERT INTO `devprofiles` VALUES (174,'ОдесКабель',24,'аквамарин','aquamarine','dashed','#6ас');
INSERT INTO `devprofiles` VALUES (175,'NNM',1,'красный','red','solid','#f88');
INSERT INTO `devprofiles` VALUES (176,'NNM',2,'оранжевый','orange','solid','#fd4');
INSERT INTO `devprofiles` VALUES (177,'NNM',3,'желтый','yellow','solid','#ff9');
INSERT INTO `devprofiles` VALUES (178,'NNM',4,'зеленый','green','solid','#9f9');
INSERT INTO `devprofiles` VALUES (179,'NNM',5,'голубой','deepskyblue','solid','#bef');
INSERT INTO `devprofiles` VALUES (180,'NNM',6,'синий','blue','solid','#99f');
INSERT INTO `devprofiles` VALUES (181,'NNM',7,'фиолетовый','purple','solid','#e7e');
INSERT INTO `devprofiles` VALUES (182,'NNM',8,'розовый','deeppink','solid','#fce');
INSERT INTO `devprofiles` VALUES (183,'NNM',9,'белый','white','solid','#fff');
INSERT INTO `devprofiles` VALUES (184,'NNM',10,'серый','gray','solid','#ddd');
INSERT INTO `devprofiles` VALUES (185,'NNM',11,'коричневый','brown','solid','#eb9');
INSERT INTO `devprofiles` VALUES (186,'NNM',12,'черный','black','solid','#999');
INSERT INTO `devprofiles` VALUES (187,'NNM',13,'красный','red','dashed','#f88');
INSERT INTO `devprofiles` VALUES (188,'NNM',14,'оранжевый','orange','dashed','#fd4');
INSERT INTO `devprofiles` VALUES (189,'NNM',15,'желтый','yellow','dashed','#ff9');
INSERT INTO `devprofiles` VALUES (190,'NNM',16,'зеленый','green','dashed','#9f9');
INSERT INTO `devprofiles` VALUES (191,'NNM',17,'голубой','deepskyblue','dashed','#bef');
INSERT INTO `devprofiles` VALUES (192,'NNM',18,'синий','blue','dashed','#99f');
INSERT INTO `devprofiles` VALUES (193,'NNM',19,'фиолетовый','purple','dashed','#e7e');
INSERT INTO `devprofiles` VALUES (194,'NNM',20,'розовый','deeppink','dashed','#fce');
INSERT INTO `devprofiles` VALUES (195,'NNM',21,'белый','white','dashed','#fff');
INSERT INTO `devprofiles` VALUES (196,'NNM',22,'серый','gray','dashed','#ddd');
INSERT INTO `devprofiles` VALUES (197,'NNM',23,'коричневый','brown','dashed','#eb9');
INSERT INTO `devprofiles` VALUES (199,'NNM',24,'неокрашенный','unpainted','dashed','#999');
INSERT INTO `devprofiles` VALUES (200,'ОранжевоКоричневый',1,'оранжевый','orange','solid','#fd4');
INSERT INTO `devprofiles` VALUES (201,'ОранжевоКоричневый',2,'коричневый','brown','solid','#eb9');
INSERT INTO `devprofiles` VALUES (202,'ЖёлтоКрасный',2,'красный','red','solid','#f88');
INSERT INTO `devprofiles` VALUES (203,'ЖёлтоКрасный',1,'желтый','yellow','solid','#ff9');
INSERT INTO `devprofiles` VALUES (204,'shortLancore',1,'синий','blue','solid','#99f');
INSERT INTO `devprofiles` VALUES (205,'shortLancore',2,'оранжевый','orange','solid','#fd4');
INSERT INTO `devprofiles` VALUES (206,'shortLancore',3,'зеленый','green','solid','#9f9');
INSERT INTO `devprofiles` VALUES (207,'shortLancore',4,'коричневый','brown','solid','#eb9');
INSERT INTO `devprofiles` VALUES (208,'shortLancore',5,'красный','red','solid','#f88');
INSERT INTO `devprofiles` VALUES (209,'shortLancore',6,'черный','black','solid','#999');
INSERT INTO `devprofiles` VALUES (210,'shortLancore',7,'желтый','yellow','solid','#ff9');
INSERT INTO `devprofiles` VALUES (211,'shortLancore',8,'фиолетовый','purple','solid','#e7e');
/*!40000 ALTER TABLE `devprofiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `docdata`
--

DROP TABLE IF EXISTS `docdata`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `docdata` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `document` int(10) unsigned NOT NULL DEFAULT '0',
  `field` varchar(64) NOT NULL DEFAULT '',
  `value` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `document` (`document`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `documents`
--

DROP TABLE IF EXISTS `documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `documents` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `val` int(10) unsigned NOT NULL DEFAULT '0',
  `type` varchar(24) NOT NULL DEFAULT '',
  `created` date NOT NULL DEFAULT '0000-00-00',
  `operator` int(10) unsigned NOT NULL DEFAULT '0',
  `note` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `created` (`created`),
  KEY `val` (`val`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `employers`
--

DROP TABLE IF EXISTS `employers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employers` (
  `eid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `blocked` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `fio` varchar(128) NOT NULL DEFAULT 'noname',
  `address` varchar(128) NOT NULL DEFAULT '',
  `seat` varchar(64) NOT NULL DEFAULT '',
  `category` varchar(32) NOT NULL DEFAULT '',
  `photo` varchar(128) NOT NULL DEFAULT '',
  `homephone` varchar(32) NOT NULL DEFAULT '',
  `workphone` varchar(32) NOT NULL DEFAULT '',
  `workphone1` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`eid`),
  KEY `fio` (`fio`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `entrances`
--

DROP TABLE IF EXISTS `entrances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `entrances` (
  `id` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `home` int(8) unsigned NOT NULL DEFAULT '0',
  `entrance` tinyint(2) NOT NULL DEFAULT '0',
  `floors` smallint(3) unsigned DEFAULT '1',
  `apartinit` smallint(3) unsigned DEFAULT '1',
  `apartfinal` smallint(3) unsigned DEFAULT '1',
  `onroof` tinyint(1) unsigned DEFAULT '0',
  `keytype` varchar(64) DEFAULT '',
  `roofkeyplace` varchar(64) DEFAULT '',
  `note` varchar(255) DEFAULT '',
  `boxtype` varchar(64) DEFAULT '500x500x400',
  PRIMARY KEY (`id`),
  KEY `home` (`home`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `homes`
--

DROP TABLE IF EXISTS `homes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `homes` (
  `id` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `address` varchar(64) NOT NULL DEFAULT '',
  `object` int(8) unsigned NOT NULL DEFAULT '0',
  `floors` tinyint(2) unsigned DEFAULT '1',
  `entrances` tinyint(2) unsigned DEFAULT '1',
  `apartments` smallint(3) unsigned DEFAULT '1',
  `boxplace` tinyint(2) unsigned DEFAULT '0',
  `note` varchar(255) DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `object` (`object`),
  KEY `address` (`address`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kassa`
--

DROP TABLE IF EXISTS `kassa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kassa` (
  `kid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL DEFAULT '',
  `computers` varchar(255) NOT NULL DEFAULT '',
  `createtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `balance` double(14,6) NOT NULL DEFAULT '0.000000',
  `longname` varchar(64) NOT NULL DEFAULT '',
  PRIMARY KEY (`kid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kassa`
--

LOCK TABLES `kassa` WRITE;
/*!40000 ALTER TABLE `kassa` DISABLE KEYS */;
INSERT INTO `kassa` VALUES (1,'Главный офис','',now(),0.0,'ул.Ленина 1');
/*!40000 ALTER TABLE `kassa` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leases`
--

DROP TABLE IF EXISTS `leases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `leases` (
  `id` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `object` varchar(64) NOT NULL DEFAULT '',
  `contract` varchar(24) NOT NULL DEFAULT '',
  `owner` varchar(64) NOT NULL DEFAULT '',
  `address` varchar(64) NOT NULL DEFAULT '',
  `rayon` int(8) NOT NULL DEFAULT '0',
  `amount` int(10) unsigned DEFAULT '0',
  `camount` int(10) unsigned DEFAULT '0',
  `note` varchar(255) DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `contract` (`contract`),
  KEY `address` (`address`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `log`
--

DROP TABLE IF EXISTS `log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log` (
  `unique_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `admin` varchar(64) NOT NULL DEFAULT '',
  `user` varchar(64) NOT NULL DEFAULT '',
  `uid` int(10) unsigned NOT NULL DEFAULT 0,
  `action` varchar(255) NOT NULL DEFAULT '',
  `content` varchar(1024) NOT NULL DEFAULT '',
  PRIMARY KEY (`unique_id`),
  KEY `uid` (`uid`),
  KEY `date` (`date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `map`
--

DROP TABLE IF EXISTS `map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `map` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `name` varchar(64) NOT NULL DEFAULT '',
  `type` varchar(64) NOT NULL DEFAULT '',
  `subtype` varchar(32) NOT NULL DEFAULT '',
  `rayon` smallint(3) NOT NULL DEFAULT '0',
  `address` varchar(64) NOT NULL DEFAULT '',
  `gtype` varchar(32) NOT NULL DEFAULT '',
  `length` int(8) DEFAULT '0',
  `hostname` varchar(32) NOT NULL DEFAULT '',
  `service` varchar(64) NOT NULL DEFAULT '',
  `mrtg` varchar(255) NOT NULL DEFAULT '',
  `connect` int(10) DEFAULT NULL,
  `note` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `address` (`address`),
  KEY `modified` (`modified`),
  KEY `type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `insert_map` AFTER INSERT ON `map`
FOR EACH ROW
BEGIN
	DECLARE cable int(10) UNSIGNED DEFAULT NULL;
	DECLARE msg, myname varchar(255) DEFAULT '';
	IF NEW.type = 'cable' THEN
		SELECT id INTO cable FROM `devices` WHERE `object`=NEW.id;
		IF cable IS NULL THEN
			INSERT INTO `devices` (`type`,`subtype`,`name`,`numports`,`object`) VALUES (NEW.type, NEW.subtype, NEW.name, 0, NEW.id);
		END IF;
	END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `delete_map` AFTER DELETE ON `map`
FOR EACH ROW
BEGIN
    DELETE FROM `map_xy` WHERE `object` = OLD.id;
    DELETE FROM `devices` WHERE `object` = OLD.id;

    IF OLD.type = 'node' OR OLD.type = 'client' THEN
        DELETE FROM `devices` WHERE `type`!='cable' AND `node1` = OLD.id OR `node2` = OLD.id;
        UPDATE `devices` SET `node1` = NULL WHERE `type`='cable' AND `node1` = OLD.id;
        UPDATE `devices` SET `node2` = NULL WHERE `type`='cable' AND `node2` = OLD.id;
    END IF;
END */;;
DELIMITER ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `map_xy`
--

DROP TABLE IF EXISTS `map_xy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `map_xy` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `object` int(12) NOT NULL DEFAULT '0',
  `slice` int(8) NOT NULL DEFAULT '0',
  `num` int(8) NOT NULL DEFAULT '0',
  `x` double(18,16) NOT NULL DEFAULT '0.0000000000000000',
  `y` double(18,16) NOT NULL DEFAULT '0.0000000000000000',
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `object` (`object`),
  KEY `modified` (`modified`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `messages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `status` tinyint(2) NOT NULL DEFAULT '0',
  `send` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `recive` datetime DEFAULT NULL,
  `sender` varchar(64) NOT NULL DEFAULT '',
  `to` varchar(64) NOT NULL DEFAULT '',
  `message` varchar(1024) NOT NULL DEFAULT '',
  `guid` varchar(40) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `send` (`send`),
  KEY `to` (`to`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nas`
--

DROP TABLE IF EXISTS `nas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nas` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `nasname` varchar(128) NOT NULL DEFAULT '',
  `shortname` varchar(32) DEFAULT NULL,
  `type` varchar(30) DEFAULT 'other',
  `nasipaddress` varchar(15) NOT NULL DEFAULT '',
  `nastype` varchar(32) NOT NULL DEFAULT '',
  `ports` int(5) DEFAULT NULL,
  `secret` varchar(60) NOT NULL DEFAULT 'secret',
  `community` varchar(50) DEFAULT NULL,
  `description` varchar(200) DEFAULT 'RADIUS Client',
  `ippool` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `nasname` (`nasname`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nas`
--

LOCK TABLES `nas` WRITE;
/*!40000 ALTER TABLE `nas` DISABLE KEYS */;
INSERT INTO `nas` VALUES (31,'10.0.0.1','nas1','other','10.0.0.1','linux',NULL,'',NULL,'test',NULL);
/*!40000 ALTER TABLE `nas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `news`
--

DROP TABLE IF EXISTS `news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `news` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expired` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `name` varchar(255) NOT NULL DEFAULT '',
  `content` text,
  `operator` int(10) unsigned NOT NULL DEFAULT '0',
  `uid` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `created` (`created`),
  KEY `expired` (`expired`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `operators`
--

DROP TABLE IF EXISTS `operators`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `operators` (
  `unique_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `status` smallint(5) NOT NULL DEFAULT '0',
  `groups` varchar(128) NOT NULL DEFAULT '',
  `blocked` smallint(5) NOT NULL DEFAULT '0',
  `login` varchar(64) NOT NULL DEFAULT '',
  `fio` varchar(128) NOT NULL DEFAULT '',
  `photo` varchar(128) NOT NULL DEFAULT '',
  `pass` varchar(64) NOT NULL DEFAULT '',
  `document` varchar(32) DEFAULT NULL,
  `homephone` varchar(32) NOT NULL DEFAULT '',
  `workphone` varchar(32) NOT NULL DEFAULT '',
  `workphone1` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`unique_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `operators`
--

LOCK TABLES `operators` WRITE;
/*!40000 ALTER TABLE `operators` DISABLE KEYS */;
INSERT INTO `operators` VALUES (1,5,'',0,'admin','Ник Админ Рутович','3','admin','0','','','');
INSERT INTO `operators` VALUES (2,2,'',1,'CLIENT','клиент','','*','22914','','','');
INSERT INTO `operators` VALUES (3,2,'',1,'ADMIN','SYSTEM','','dygfgdsnlqw',NULL,'','','');
/*!40000 ALTER TABLE `operators` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `oid` bigint(10) unsigned NOT NULL AUTO_INCREMENT,
  `operator` varchar(32) NOT NULL DEFAULT '',
  `kassa` tinyint(3) NOT NULL DEFAULT '0',
  `open` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `close` datetime DEFAULT NULL,
  `acceptor` varchar(32) NOT NULL DEFAULT '',
  `accept` datetime DEFAULT NULL,
  `summa` double(12,6) NOT NULL DEFAULT '0.000000',
  PRIMARY KEY (`oid`),
  KEY `operator` (`operator`),
  KEY `open` (`open`),
  KEY `accept` (`accept`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `packets`
--

DROP TABLE IF EXISTS `packets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `packets` (
  `pid` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `num` smallint(5) NOT NULL DEFAULT '0',
  `name` varchar(128) NOT NULL DEFAULT '',
  `tos` tinyint(1) NOT NULL DEFAULT '0',
  `direction` tinyint(1) NOT NULL DEFAULT '0',
  `groupname` varchar(64) NOT NULL DEFAULT '',
  `fixed` tinyint(1) NOT NULL DEFAULT '0',
  `period` smallint(5) NOT NULL DEFAULT '0',
  `fixed_cost` double(20,6) NOT NULL DEFAULT '0.000000',
  `su` smallint(5) NOT NULL DEFAULT '1',
  `hg` varchar(64) NOT NULL DEFAULT '',
  `switched` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `switchedout` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`pid`),
  KEY `pid` (`pid`),
  KEY `groupname` (`groupname`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `packets`
--

LOCK TABLES `packets` WRITE;
/*!40000 ALTER TABLE `packets` DISABLE KEYS */;
INSERT INTO `packets` VALUES (1,0,'Халява 100M',0,0,'admins',0,0,0.000000,1,'',0,0);
INSERT INTO `packets` VALUES (2,1,'Базовый',2,1,'speedlimit-100M',0,1,0.000000,1,'',0,0);
/*!40000 ALTER TABLE `packets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pay`
--

DROP TABLE IF EXISTS `pay`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pay` (
  `unique_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user` varchar(64) NOT NULL DEFAULT '',
  `pid` smallint(5) NOT NULL DEFAULT '0',
  `uid` smallint(5) NOT NULL,
  `acttime` datetime NOT NULL DEFAULT '2003-01-01 00:00:00',
  `service` varchar(32) DEFAULT 'internet',
  `money` double(12,6) NOT NULL DEFAULT '0.000000',
  `currency` smallint(5) NOT NULL DEFAULT '1',
  `summ` double(12,6) NOT NULL DEFAULT '0.000000',
  `paytime` datetime DEFAULT NULL,
  `card` varchar(30) NOT NULL DEFAULT '',
  `from` varchar(32) NOT NULL DEFAULT 'unknown',
  `note` varchar(128) NOT NULL DEFAULT '',
  `povod_id` int(5) unsigned NOT NULL DEFAULT '0',
  `rid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `oid` int(10) NOT NULL DEFAULT '0',
  `kid` smallint(5) NOT NULL DEFAULT '0',
  PRIMARY KEY (`unique_id`),
  KEY `acttime` (`acttime`),
  KEY `user` (`user`),
  KEY `oid` (`oid`),
  KEY `from` (`from`),
  KEY `card` (`card`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `photo`
--

DROP TABLE IF EXISTS `photo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `photo` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `image` blob NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `povod`
--

DROP TABLE IF EXISTS `povod`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `povod` (
  `povod_id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) DEFAULT '',
  `povod` varchar(64) NOT NULL DEFAULT '',
  `calculate` int(11) NOT NULL DEFAULT '1',
  `kassa` tinyint(1) NOT NULL DEFAULT '1',
  `diagram` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `private` tinyint(1) DEFAULT '1',
  `typeofpay` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `period` tinyint(2) DEFAULT '0',
  PRIMARY KEY (`povod_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `povod`
--

LOCK TABLES `povod` WRITE;
/*!40000 ALTER TABLE `povod` DISABLE KEYS */;
INSERT INTO `povod` VALUES (1,'','Оплата по карточке',1,1,1,1,2,0);
INSERT INTO `povod` VALUES (2,'','Пополнение лицевого счета',1,1,1,1,2,0);
INSERT INTO `povod` VALUES (3,'','Оплата подключения',1,0,0,1,0,0);
INSERT INTO `povod` VALUES (4,'','Замена оборудования',1,0,0,1,0,0);
INSERT INTO `povod` VALUES (5,'','Оплата настройки сети',0,1,0,1,0,0);
INSERT INTO `povod` VALUES (7,'','ошибка ввода',1,1,0,1,0,0);
INSERT INTO `povod` VALUES (9,'','Обнуление счета',1,0,0,1,0,0);
INSERT INTO `povod` VALUES (10,'','Перерасчет',1,0,0,1,0,0);
INSERT INTO `povod` VALUES (11,'','За услуги клиента',1,0,0,1,0,0);
INSERT INTO `povod` VALUES (12,'','Премия За Общественнополезные работы',1,0,0,1,0,0);
INSERT INTO `povod` VALUES (13,'','Переход на пакет',0,1,0,1,0,0);
INSERT INTO `povod` VALUES (14,'','Пополнение счета Б/Н',1,0,1,1,3,0);
INSERT INTO `povod` VALUES (17,'','ПОДАРОК!',1,0,0,1,0,0);
INSERT INTO `povod` VALUES (24,'','TERMINAL',1,0,1,1,1,0);
/*!40000 ALTER TABLE `povod` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `prices`
--

DROP TABLE IF EXISTS `prices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prices` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(5) unsigned NOT NULL DEFAULT '0',
  `service` varchar(32) NOT NULL DEFAULT 'none',
  `begintime` time NOT NULL DEFAULT '00:00:00',
  `endtime` time NOT NULL DEFAULT '00:00:00',
  `cost` double(12,6) NOT NULL DEFAULT '0.000000',
  PRIMARY KEY (`id`),
  KEY `service` (`service`),
  KEY `begintime` (`begintime`),
  KEY `endtime` (`endtime`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prices`
--

LOCK TABLES `prices` WRITE;
/*!40000 ALTER TABLE `prices` DISABLE KEYS */;
INSERT INTO `prices` VALUES (1,2,'traffic','00:00:00','23:59:59',0.010000);
/*!40000 ALTER TABLE `prices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `radacct`
--

DROP TABLE IF EXISTS `radacct`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `radacct` (
  `radacctid` bigint(21) NOT NULL AUTO_INCREMENT,
  `acctsessionid` varchar(32) NOT NULL DEFAULT '',
  `acctuniqueid` varchar(32) NOT NULL DEFAULT '',
  `username` varchar(64) NOT NULL DEFAULT '',
  `groupname` varchar(64) NOT NULL DEFAULT '',
  `uid` int(10) NOT NULL DEFAULT '0',
  `pid` int(5) unsigned NOT NULL DEFAULT '0',
  `credit` double(20,6) NOT NULL DEFAULT '0.000000',
  `before_billing` double(20,6) NOT NULL DEFAULT '0.000000',
  `billing_minus` double(20,6) NOT NULL DEFAULT '0.000000',
  `realm` varchar(64) DEFAULT '',
  `nasipaddress` varchar(15) NOT NULL DEFAULT '',
  `nasportid` varchar(15) DEFAULT NULL,
  `nasporttype` varchar(32) DEFAULT NULL,
  `acctstarttime` datetime DEFAULT NULL,
  `acctstoptime` datetime DEFAULT NULL,
  `acctsessiontime` int(12) DEFAULT NULL,
  `acctauthentic` varchar(32) DEFAULT NULL,
  `connectinfo_start` varchar(128) NOT NULL DEFAULT '',
  `connectinfo_stop` varchar(50) DEFAULT NULL,
  `inputgigawords` bigint(20) DEFAULT '0',
  `acctinputoctets` bigint(20) DEFAULT NULL,
  `outputgigawords` bigint(20) DEFAULT '0',
  `acctoutputoctets` bigint(20) DEFAULT NULL,
  `calledstationid` varchar(50) NOT NULL DEFAULT '',
  `callingstationid` varchar(50) NOT NULL DEFAULT '',
  `acctterminatecause` varchar(32) NOT NULL DEFAULT '',
  `servicetype` varchar(32) DEFAULT NULL,
  `framedprotocol` varchar(32) DEFAULT NULL,
  `framedipaddress` varchar(15) NOT NULL DEFAULT '',
  `acctstartdelay` int(12) DEFAULT NULL,
  `acctstopdelay` int(12) DEFAULT NULL,
  `xascendsessionsvrkey` varchar(10) DEFAULT NULL,
  `dropped` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`radacctid`),
  KEY `username` (`username`),
  KEY `framedipaddress` (`framedipaddress`),
  KEY `acctsessionid` (`acctsessionid`),
  KEY `acctuniqueid` (`acctuniqueid`),
  KEY `acctstarttime` (`acctstarttime`),
  KEY `acctstoptime` (`acctstoptime`),
  KEY `nasipaddress` (`nasipaddress`),
  KEY `callingstationid` (`callingstationid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `expand_pid` BEFORE INSERT ON `radacct`
FOR EACH ROW
BEGIN

DECLARE new_pid, new_uid, my_interval int DEFAULT 0;
DECLARE new_bf, new_cr, current_cost_traf, current_cost_time, tmp_sum, inbytes, outbytes, inbytes_, outbytes_ double(20,6) DEFAULT 0.000000;
DECLARE mygroup, grp, csid varchar(32) DEFAULT '';
DECLARE tos_, direct int DEFAULT 0;

IF NEW.servicetype IS NOT NULL THEN 
	IF INET_ATON(NEW.framedipaddress)<INET_ATON('192.168.192.0') OR INET_ATON(NEW.framedipaddress)>=INET_ATON('192.168.224.0') THEN
		UPDATE users SET last_connection=NEW.acctstarttime WHERE user=NEW.username;
	END IF;

	SELECT u.pid, u.deposit, u.credit, u.uid, p.groupname INTO new_pid, new_bf, new_cr, new_uid, grp FROM users as u, packets as p WHERE u.pid=p.pid and user=NEW.username;
	IF NEW.acctsessiontime IS NULL THEN 
		SET my_interval=-300; 
	ELSE 
		SET my_interval=-300-NEW.acctsessiontime; 
	END IF;

	IF NEW.groupname IS NULL OR NEW.groupname='' THEN
		SET NEW.groupname='unspecified';
	END IF;
	SET mygroup=NEW.groupname;

	IF new_pid is NULL THEN 
		SET new_pid=0;
		SET new_uid=0;
		SET new_bf=0.00;
		SET new_cr=0.00;
		SET mygroup='';
		SET grp='';
	END IF;

	IF grp!=mygroup THEN 
		SET new_pid=0; 
	END IF;

	SET NEW.uid=new_uid;
	SET NEW.pid=new_pid;
	SET NEW.credit=new_cr;
	SET NEW.before_billing=new_bf;
	SET NEW.groupname=mygroup;

	IF NEW.acctsessiontime is not NULL AND NEW.acctstoptime is NULL THEN
		SELECT callingstationid INTO csid FROM radippool WHERE framedipaddress=NEW.framedipaddress;
		IF csid is not NULL AND csid!='' AND NEW.callingstationid!=csid THEN
			INSERT INTO raddropuser (acctsessionid,uid,username,framedipaddress,nasipaddress,nasportid) VALUES (NEW.acctsessionid, NEW.uid, NEW.username, NEW.framedipaddress, NEW.nasipaddress, NEW.nasportid);
		END IF;
	END IF;

	IF NEW.acctstoptime is not NULL and NEW.acctsessiontime>0 THEN
		SELECT tos,direction INTO tos_,direct FROM packets WHERE pid=NEW.pid;

		SET inbytes=NEW.inputgigawords << 32 | NEW.acctinputoctets;
		SET outbytes=NEW.outputgigawords << 32 | NEW.acctoutputoctets;
		SET inbytes_=0;
		SET outbytes_=0;

		IF tos_=NULL or direct=NULL THEN 
			SET tos_=0; 
			SET direct=0; 
		END IF;

		
		IF tos_=1 and NEW.pid>0 THEN
			SELECT cost INTO current_cost_time FROM prices WHERE pid=NEW.pid and service='time' and time(now())>=begintime and time(now())<=endtime LIMIT 1;
			IF current_cost_time is NULL THEN 
				SET current_cost_time=0.00; 
			END IF;
			SET NEW.billing_minus=NEW.billing_minus+(NEW.acctsessiontime)/3600*current_cost_time;
		END IF;

		
		IF tos_=2 and NEW.pid>0 THEN
			SELECT cost INTO current_cost_traf FROM prices WHERE pid=NEW.pid and service='traffic' and  time(now())>=begintime and time(now())<=endtime LIMIT 1;
			IF current_cost_traf=NULL THEN 
				SET current_cost_traf=0.00; 
			END IF;
			IF DIRECT!=0 THEN
				IF DIRECT=1 THEN
					SET NEW.billing_minus=NEW.acctinputoctets/1048576*current_cost_traf;
				END IF;
				IF direct=2 THEN
					SET NEW.billing_minus=NEW.acctoutputoctets/1048576*current_cost_traf;
				END IF;
				IF direct=3 THEN
					SET NEW.billing_minus=(NEW.acctinputoctets+NEW.acctoutputoctets)/1048576*current_cost_traf;
				END IF;
			END IF;
		END IF;

		
		IF tos_=3 and NEW.pid>0 THEN
			SELECT sum(IF(service='traffic',cost,0)), sum(IF(service='time',cost,0))  INTO current_cost_traf, current_cost_time FROM prices WHERE pid=NEW.pid and (service='traffic' or service='time') and  time(now())>=begintime and time(now())<=endtime LIMIT 1;
			IF current_cost_traf=NULL THEN 
				SET current_cost_traf=0.00; 
			END IF;
			IF current_cost_time=NULL THEN 
				SET current_cost_time=0.00; 
			END IF;
			SET tmp_sum=(NEW.acctsessiontime)/3600*current_cost_time;
			IF direct!=0 THEN
				IF direct=1 THEN
					SET tmp_sum=tmp_sum+(inbytes - inbytes_)/1048576*current_cost_traf;
				END IF;
				IF direct=2 THEN
					SET tmp_sum=tmp_sum+(outbytes - outbytes_)/1048576*current_cost_traf;
				END IF;
				IF direct=3 THEN
					SET tmp_sum=tmp_sum+((inbytes - inbytes_)+(outbytes - outbytes_))/1048576*current_cost_traf;
				END IF;
			END IF;
			SET NEW.billing_minus=NEW.billing_minus+tmp_sum;
		END IF;
	END IF;
END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `update_acct` BEFORE UPDATE ON `radacct`
FOR EACH ROW
BEGIN
DECLARE tos_,direct int DEFAULT 0;
DECLARE current_cost_traf, current_cost_time, tmp_sum, inbytes, outbytes, inbytes_, outbytes_ double(20,6) DEFAULT 0.000000;
DECLARE in_, out_ bigint(15) DEFAULT 0;
DECLARE timeon int(12) DEFAULT NULL;
DECLARE mystoptime, mytime datetime DEFAULT NULL;

IF NEW.acctsessiontime-OLD.acctsessiontime>0 THEN
    SET mystoptime=OLD.acctstoptime;
    IF OLD.acctstoptime is not NULL THEN
        SET mystoptime=NULL;
    END IF;

    IF NEW.acctinputoctets<OLD.acctinputoctets THEN
        SET NEW.inputgigawords=OLD.inputgigawords+1;
    END IF;

    IF NEW.acctoutputoctets<OLD.acctoutputoctets THEN
        SET NEW.outputgigawords=OLD.outputgigawords+1;
    END IF;

    SET inbytes=NEW.inputgigawords << 32 | NEW.acctinputoctets;
    SET outbytes=NEW.outputgigawords << 32 | NEW.acctoutputoctets;
    SET inbytes_=OLD.inputgigawords << 32 | OLD.acctinputoctets;
    SET outbytes_=OLD.outputgigawords << 32 | OLD.acctoutputoctets;

    SET mytime=FROM_UNIXTIME(floor(UNIX_TIMESTAMP(now())/300)*300);
    SELECT time_on, in_bytes, out_bytes INTO timeon, in_, out_ FROM traffic WHERE `when`=mytime and `user`=OLD.username LIMIT 1;
    IF timeon is NULL THEN
        INSERT INTO traffic (`user`, `when`, `time_on`, `in_bytes`, `out_bytes`) values (OLD.username, mytime, NEW.acctsessiontime, inbytes, outbytes);
    ELSE
        IF in_<=inbytes AND out_<=outbytes THEN
            UPDATE traffic SET `time_on`=NEW.acctsessiontime, `in_bytes`=inbytes, `out_bytes`=outbytes WHERE `user`=NEW.username AND `when`=mytime LIMIT 1;
        END IF;
    END IF;

    IF mystoptime is NULL THEN
        
        SELECT tos,direction INTO tos_,direct FROM packets WHERE pid=OLD.pid;
        IF tos_ is NULL or direct is NULL THEN 
            SET tos_=0; 
            SET direct=0; 
        END IF;

        
        IF tos_=1 and OLD.pid>0 THEN
            SELECT cost INTO current_cost_time FROM prices WHERE pid=OLD.pid and service='time' and time(now())>=begintime and time(now())<=endtime LIMIT 1;
            IF current_cost_time is NULL THEN 
                SET current_cost_time=0.00; 
            END IF;
            SET NEW.billing_minus=OLD.billing_minus+(NEW.acctsessiontime - OLD.acctsessiontime)/3600*current_cost_time;
        END IF;

        
        IF tos_=2 and OLD.pid>0 THEN
            SELECT cost INTO current_cost_traf FROM prices WHERE pid=OLD.pid and service='traffic' and  time(now())>=begintime and time(now())<=endtime LIMIT 1;
            IF current_cost_traf=NULL THEN 
                SET current_cost_traf=0.00; 
            END IF;
            IF direct!=0 THEN
                IF direct=1 THEN
                    SET NEW.billing_minus=OLD.billing_minus+(inbytes - inbytes_)/1048576*current_cost_traf;
                END IF;
                IF direct=2 THEN
                    SET NEW.billing_minus=OLD.billing_minus+(outbytes - outbytes_)/1048576*current_cost_traf;
                END IF;
                IF direct=3 THEN
                    SET NEW.billing_minus=OLD.billing_minus+((inbytes - inbytes_)+(outbytes - outbytes_))/1048576*current_cost_traf;
                END IF;
            END IF;
        END IF;

        
        IF tos_=3 and OLD.pid>0 THEN
            SELECT sum(IF(service='traffic',cost,0)), sum(IF(service='time',cost,0))  INTO current_cost_traf, current_cost_time FROM prices WHERE pid=OLD.pid and (service='traffic' or service='time') and  time(now())>=begintime and time(now())<=endtime LIMIT 1;
            IF current_cost_traf=NULL THEN 
                SET current_cost_traf=0.00; 
            END IF;
            IF current_cost_time=NULL THEN 
                SET current_cost_time=0.00; 
            END IF;
            SET tmp_sum=(NEW.acctsessiontime - OLD.acctsessiontime)/3600*current_cost_time;
            IF direct!=0 THEN
                IF direct=1 THEN
                    SET tmp_sum=tmp_sum+(inbytes - inbytes_)/1048576*current_cost_traf;
                END IF;
                IF direct=2 THEN
                    SET tmp_sum=tmp_sum+(outbytes - outbytes_)/1048576*current_cost_traf;
                END IF;
                IF direct=3 THEN
                    SET tmp_sum=tmp_sum+((inbytes - inbytes_)+(outbytes - outbytes_))/1048576*current_cost_traf;
                END IF;
            END IF;
            SET NEW.billing_minus=OLD.billing_minus+tmp_sum;
        END IF;

        IF tos_>0 and OLD.before_billing+OLD.credit-NEW.billing_minus<=0 and OLD.dropped=0 THEN
            INSERT INTO raddropuser (acctsessionid,uid,username,framedipaddress,nasipaddress,nasportid) VALUES (OLD.acctsessionid, OLD.uid, OLD.username, OLD.framedipaddress, OLD.nasipaddress, OLD.nasportid);
            SET NEW.dropped=1;
        END IF;
    END IF;

    IF mystoptime is NULL and NEW.acctstoptime is not NULL THEN
        IF OLD.dropped!=0 THEN 
            DELETE FROM raddropuser WHERE uid=NEW.uid; 
        END IF;
        IF NEW.billing_minus>0.00 THEN
            UPDATE users SET deposit=OLD.before_billing-NEW.billing_minus WHERE uid=OLD.uid;
        END IF;
    END IF;

    IF OLD.acctstarttime != NEW.acctstarttime THEN
        SET NEW.acctstarttime=OLD.acctstarttime;
    END IF;
    IF OLD.acctstoptime is not NULL and NEW.acctstoptime=OLD.acctstoptime and mystoptime is NULL THEN
        IF (UNIX_TIMESTAMP() - (UNIX_TIMESTAMP(NEW.acctstarttime)+IF(NEW.acctsessiontime is null,0,NEW.acctsessiontime))) > 360 THEN
            SET NEW.acctstarttime = FROM_UNIXTIME(UNIX_TIMESTAMP()-IF(NEW.acctsessiontime is null,0,NEW.acctsessiontime));
        END IF;
        SET NEW.acctstoptime=mystoptime;
    END IF;
END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `radcheck`
--

DROP TABLE IF EXISTS `radcheck`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `radcheck` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL DEFAULT '',
  `attribute` varchar(32) NOT NULL DEFAULT '',
  `op` char(2) NOT NULL DEFAULT '==',
  `value` varchar(253) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `username` (`username`(32))
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `raddropuser`
--

DROP TABLE IF EXISTS `raddropuser`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raddropuser` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `acctsessionid` varchar(32) NOT NULL DEFAULT '',
  `uid` int(10) NOT NULL DEFAULT '0',
  `username` varchar(64) NOT NULL DEFAULT '',
  `framedipaddress` varchar(15) NOT NULL DEFAULT '',
  `nasipaddress` varchar(15) NOT NULL DEFAULT '',
  `nasportid` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `username` (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `radgroupcheck`
--

DROP TABLE IF EXISTS `radgroupcheck`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `radgroupcheck` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `groupname` varchar(64) NOT NULL DEFAULT '',
  `attribute` varchar(32) NOT NULL DEFAULT '',
  `op` char(2) NOT NULL DEFAULT '==',
  `value` varchar(253) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `groupname` (`groupname`(32))
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `radgroupcheck`
--

LOCK TABLES `radgroupcheck` WRITE;
/*!40000 ALTER TABLE `radgroupcheck` DISABLE KEYS */;
INSERT INTO `radgroupcheck` VALUES (91,'simultaneous1','Auth-Type',':=','Accept');
INSERT INTO `radgroupcheck` VALUES (90,'simultaneous1','User-Name','==','`{sql:SELECT IF(count(*)>0,username,\'nixt\') FROM radacct WHERE nasipaddress!=\'%{NAS-IP-Address}\' AND callingstationid=\'%{Calling-Station-Id}\' AND username=\'%{User-Name}\' AND acctstoptime is null}`');
INSERT INTO `radgroupcheck` VALUES (89,'simultaneous','Pool-Name',':=','simulpool');
INSERT INTO `radgroupcheck` VALUES (88,'simultaneous','Auth-Type',':=','Accept');
INSERT INTO `radgroupcheck` VALUES (87,'simultaneous','User-Name','==','`%{sql:SELECT IF(count(*)>=p.su,a.username,\'HaX\') FROM radacct a,packets p,users u WHERE u.pid=p.pid AND u.user=a.username AND a.username=\'%{User-Name}\' AND a.acctstoptime is null AND a.callingstationid!=\'%{Calling-Station-Id}\' AND a.pid!=0}`');
INSERT INTO `radgroupcheck` VALUES (42,'speedlimit-4Mb','User-Name','==','`%{sql:SELECT u.user FROM users u, packets p WHERE u.pid=p.pid and user=\'%{User-Name}\' and (((p.tos!=0 or (p.fixed!=0 and p.fixed!=10)) and u.deposit+u.credit>0) or (p.fixed=10 and u.expired>now() and u.deposit+u.credit>=0) or (p.tos=0 and p.fixed=0))}`');
INSERT INTO `radgroupcheck` VALUES (43,'speedlimit-4Mb','Cleartext-Password',':=','`%{sql:SELECT password FROM users WHERE user=\'%{User-Name}\'}`');
INSERT INTO `radgroupcheck` VALUES (86,'badsimultaneous','User-Name','==','`%{sql:SELECT IF(count(*)>1 || \'%{User-Name}\'=\'\',\'%{User-Name}\',concat(\'x\',\'%{User-Name}\')) as user FROM radacct WHERE acctstoptime is null AND callingstationid=\'%{Calling-Station-Id}\' AND username=\'%{User-Name}\'}`');
INSERT INTO `radgroupcheck` VALUES (85,'badsimultaneous','Auth-Type',':=','Reject');
INSERT INTO `radgroupcheck` VALUES (84,'wrongpass','User-Password','!=','`%{sql:SELECT password FROM users WHERE user=BINARY(\'%{User-Name}\')}`');
INSERT INTO `radgroupcheck` VALUES (83,'wrongpass','Auth-Type','==','PAP');
INSERT INTO `radgroupcheck` VALUES (81,'unknown','Auth-Type',':=','Accept');
INSERT INTO `radgroupcheck` VALUES (82,'wrongpass','Pool-Name',':=','wrpasspool');
INSERT INTO `radgroupcheck` VALUES (46,'speedlimit-1Mb','User-Name','==','`%{sql:SELECT u.user FROM users u, packets p WHERE u.pid=p.pid and user=\'%{User-Name}\' and (((p.tos!=0 or (p.fixed!=0 and p.fixed!=10)) and u.deposit+u.credit>0) or (p.fixed=10 and u.expired>now() and u.deposit+u.credit>=0) or (p.tos=0 and p.fixed=0))}`');
INSERT INTO `radgroupcheck` VALUES (47,'speedlimit-1Mb','Cleartext-Password',':=','`%{sql:SELECT password FROM users WHERE user=\'%{User-Name}\'}`');
INSERT INTO `radgroupcheck` VALUES (54,'speedlimit-10Mb','User-Name','==','`%{sql:SELECT u.user FROM users u, packets p WHERE u.pid=p.pid and user=\'%{User-Name}\' and (((p.tos!=0 or (p.fixed!=0 and p.fixed!=10)) and u.deposit+u.credit>0) or (p.fixed=10 and u.expired>now() and u.deposit+u.credit>=0) or (p.tos=0 and p.fixed=0))}`');
INSERT INTO `radgroupcheck` VALUES (55,'speedlimit-10Mb','Cleartext-Password',':=','`%{sql:SELECT password FROM users WHERE user=\'%{User-Name}\'}`');
INSERT INTO `radgroupcheck` VALUES (66,'speedlimit-20Mb','User-Name','==','`%{sql:SELECT u.user FROM users u, packets p WHERE u.pid=p.pid and user=\'%{User-Name}\' and (((p.tos!=0 or (p.fixed!=0 and p.fixed!=10)) and u.deposit+u.credit>0) or (p.fixed=10 and u.expired>now() and u.deposit+u.credit>=0) or (p.tos=0 and p.fixed=0))}`');
INSERT INTO `radgroupcheck` VALUES (67,'speedlimit-20Mb','Cleartext-Password',':=','`%{sql:SELECT password FROM users WHERE user=\'%{User-Name}\'}`');
INSERT INTO `radgroupcheck` VALUES (70,'speedlimit-2M-512k','User-Name','==','`%{sql:SELECT u.user FROM users u, packets p WHERE u.pid=p.pid and user=\'%{User-Name}\' and (((p.tos!=0 or (p.fixed!=0 and p.fixed!=10)) and u.deposit+u.credit>0) or (p.fixed=10 and u.expired>now() and u.deposit+u.credit>=0) or (p.tos=0 and p.fixed=0))}`');
INSERT INTO `radgroupcheck` VALUES (71,'speedlimit-2M-512k','Cleartext-Password',':=','`%{sql:SELECT password FROM users WHERE user=\'%{User-Name}\'}`');
INSERT INTO `radgroupcheck` VALUES (80,'unknown','User-Name','==','`%{sql:SELECT IF(count(*)=0 and \'%{User-Name}\'!=\'\',\'%{User-Name}\',concat(\'x\',\'%{User-Name}\')) FROM users WHERE user=BINARY(\'%{User-Name}\')}`');
INSERT INTO `radgroupcheck` VALUES (79,'unknown','Pool-Name',':=','offlinepool');
INSERT INTO `radgroupcheck` VALUES (92,'simultaneous1','Pool-Name',':=','simulpool');
INSERT INTO `radgroupcheck` VALUES (93,'unauthorized','User-Name','==','`%{sql:SELECT IF((hg!=\'\' && hg!=\'%{Huntgroup-Name}\')||(csid!=\'\' && csid!=\'%{Calling-Station-Id}\'), user,concat(\'x\',\'%{User-Name}\')) FROM users u, packets p WHERE u.pid=p.pid AND user=\'%{User-Name}\'}`');
INSERT INTO `radgroupcheck` VALUES (94,'unauthorized','Auth-Type',':=','Accept');
INSERT INTO `radgroupcheck` VALUES (95,'unauthorized','Pool-Name',':=','unautorizepool');
INSERT INTO `radgroupcheck` VALUES (96,'blocked','Auth-Type',':=','Accept');
INSERT INTO `radgroupcheck` VALUES (97,'blocked','Pool-Name',':=','blockedpool');
INSERT INTO `radgroupcheck` VALUES (98,'blocked','User-Name','==','`%{sql:SELECT user FROM users WHERE user=\'%{User-Name}\' and (blocked=1 or disabled=1)}`');
INSERT INTO `radgroupcheck` VALUES (99,'debtors','User-Name','==','`%{sql:SELECT u.user FROM users u, packets p WHERE u.pid=p.pid and user=\'%{User-Name}\' and (((p.tos!=0 or (p.fixed!=0 and p.fixed<10)) and u.deposit+u.credit<=0) or (p.fixed>9 and u.expired<=now() or u.deposit+u.credit<0))}`');
INSERT INTO `radgroupcheck` VALUES (100,'debtors','Cleartext-Password',':=','`%{sql:SELECT password FROM users WHERE user=\'%{User-Name}\'}`');
INSERT INTO `radgroupcheck` VALUES (101,'debtors','Pool-Name',':=','debtorspool');
INSERT INTO `radgroupcheck` VALUES (102,'admins','Cleartext-Password',':=','`%{sql:SELECT password FROM users WHERE user=\'%{User-Name}\'}`');
INSERT INTO `radgroupcheck` VALUES (103,'admins','User-Name','==','`%{sql:SELECT u.user FROM users u, packets p WHERE u.pid=p.pid and user=\'%{User-Name}\' and p.tos=0 and p.fixed=0}`');
INSERT INTO `radgroupcheck` VALUES (104,'basetraffic','User-Name','==','`%{sql:SELECT u.user FROM users u, packets p WHERE u.pid=p.pid and user=\'%{User-Name}\' and (((p.tos!=0 or (p.fixed!=0 and p.fixed!=10)) and u.deposit+u.credit>0) or (p.fixed=10 and u.expired>now() and u.deposit+u.credit>=0) or (p.tos=0 and p.fixed=0))}`');
INSERT INTO `radgroupcheck` VALUES (105,'basetraffic','Cleartext-Password',':=','`%{sql:SELECT password FROM users WHERE user=\'%{User-Name}\'}`');
INSERT INTO `radgroupcheck` VALUES (111,'speedlimit-50Mb','Cleartext-Password',':=','`%{sql:SELECT password FROM users WHERE user=\'%{User-Name}\'}`');
INSERT INTO `radgroupcheck` VALUES (110,'speedlimit-50Mb','User-Name','==','`%{sql:SELECT u.user FROM users u, packets p WHERE u.pid=p.pid and user=\'%{User-Name}\' and (((p.tos!=0 or (p.fixed!=0 and p.fixed!=10)) and u.deposit+u.credit>0) or (p.fixed=10 and u.expired>now() and u.deposit+u.credit>=0) or (p.tos=0 and p.fixed=0))}`');
INSERT INTO `radgroupcheck` VALUES (119,'basetraffic','Pool-Name',':=','`%{sql:SELECT IF(max(nasname) is NULL,\'\',ippool) FROM nas WHERE nasname=\'%{NAS-IP-Address}\' }`');
INSERT INTO `radgroupcheck` VALUES (120,'speedlimit-10Mb','Pool-Name',':=','`%{sql:SELECT IF(max(nasname) is NULL,\'\',ippool) FROM nas WHERE nasname=\'%{NAS-IP-Address}\' }`');
INSERT INTO `radgroupcheck` VALUES (121,'speedlimit-20Mb','Pool-Name',':=','`%{sql:SELECT IF(max(nasname) is NULL,\'\',ippool) FROM nas WHERE nasname=\'%{NAS-IP-Address}\' }`');
INSERT INTO `radgroupcheck` VALUES (122,'speedlimit-50Mb','Pool-Name',':=','`%{sql:SELECT IF(max(nasname) is NULL,\'\',ippool) FROM nas WHERE nasname=\'%{NAS-IP-Address}\' }`');
INSERT INTO `radgroupcheck` VALUES (125,'admins','Pool-Name',':=','`%{sql:SELECT IF(max(nasname) is NULL,\'\',ippool) FROM nas WHERE nasname=\'%{NAS-IP-Address}\' }`');
INSERT INTO `radgroupcheck` VALUES (127,'speedlimit-100M','Cleartext-Password',':=','`%{sql:SELECT password FROM users WHERE user=\'%{User-Name}\'}`');
INSERT INTO `radgroupcheck` VALUES (128,'speedlimit-100M','User-Name','==','`%{sql:SELECT u.user FROM users u, packets p WHERE u.pid=p.pid and user=\'%{User-Name}\' and (((p.tos!=0 or (p.fixed!=0 and p.fixed<10)) and u.deposit+u.credit>0) or (p.fixed>9 and u.expired>now() and u.deposit+u.credit>=0) or (p.tos=0 and p.fixed=0))}`');
INSERT INTO `radgroupcheck` VALUES (129,'speedlimit-100M','Pool-Name',':=','`%{sql:SELECT IF(max(nasname) is NULL,\'\',ippool) FROM nas WHERE nasname=\'%{NAS-IP-Address}\' }`');
INSERT INTO `radgroupcheck` VALUES (134,'speedlimit-30Mb','Cleartext-Password',':=','`%{sql:SELECT password FROM users WHERE user=\'%{User-Name}\'}`');
INSERT INTO `radgroupcheck` VALUES (135,'speedlimit-30Mb','Pool-Name',':=','`%{sql:SELECT IF(max(nasname) is NULL,\'\',ippool) FROM nas WHERE nasname=\'%{NAS-IP-Address}\' }`');
INSERT INTO `radgroupcheck` VALUES (133,'speedlimit-30Mb','User-Name','==','`%{sql:SELECT u.user FROM users u, packets p WHERE u.pid=p.pid and user=\'%{User-Name}\' and (((p.tos!=0 or (p.fixed!=0 and p.fixed!=10)) and u.deposit+u.credit>0) or (p.fixed=10 and u.expired>now() and u.deposit+u.credit>=0) or (p.tos=0 and p.fixed=0))}`');
INSERT INTO `radgroupcheck` VALUES (138,'speedlimit-40Mb','Cleartext-Password',':=','`%{sql:SELECT password FROM users WHERE user=\'%{User-Name}\'}`');
INSERT INTO `radgroupcheck` VALUES (139,'speedlimit-40Mb','Pool-Name',':=','`%{sql:SELECT IF(max(nasname) is NULL,\'\',ippool) FROM nas WHERE nasname=\'%{NAS-IP-Address}\' }`');
INSERT INTO `radgroupcheck` VALUES (140,'speedlimit-40Mb','User-Name','==','`%{sql:SELECT u.user FROM users u, packets p WHERE u.pid=p.pid and user=\'%{User-Name}\' and (((p.tos!=0 or (p.fixed!=0 and p.fixed!=10)) and u.deposit+u.credit>0) or (p.fixed=10 and u.expired>now() and u.deposit+u.credit>=0) or (p.tos=0 and p.fixed=0))}`');
INSERT INTO `radgroupcheck` VALUES (141,'speedlimit-60Mb','Cleartext-Password',':=','`%{sql:SELECT password FROM users WHERE user=\'%{User-Name}\'}`');
INSERT INTO `radgroupcheck` VALUES (142,'speedlimit-60Mb','Pool-Name',':=','`%{sql:SELECT IF(max(nasname) is NULL,\'\',ippool) FROM nas WHERE nasname=\'%{NAS-IP-Address}\' }`');
INSERT INTO `radgroupcheck` VALUES (143,'speedlimit-60Mb','User-Name','==','`%{sql:SELECT u.user FROM users u, packets p WHERE u.pid=p.pid and user=\'%{User-Name}\' and (((p.tos!=0 or (p.fixed!=0 and p.fixed!=10)) and u.deposit+u.credit>0) or (p.fixed=10 and u.expired>now() and u.deposit+u.credit>=0) or (p.tos=0 and p.fixed=0))}`');
/*!40000 ALTER TABLE `radgroupcheck` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `radgroupreply`
--

DROP TABLE IF EXISTS `radgroupreply`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `radgroupreply` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `groupname` varchar(64) NOT NULL DEFAULT '',
  `attribute` varchar(32) NOT NULL DEFAULT '',
  `op` char(2) NOT NULL DEFAULT '=',
  `value` varchar(253) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `groupname` (`groupname`(32))
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `radgroupreply`
--

LOCK TABLES `radgroupreply` WRITE;
/*!40000 ALTER TABLE `radgroupreply` DISABLE KEYS */;
INSERT INTO `radgroupreply` VALUES (363,'basetraffic','Service-Type','=','Framed-User');
INSERT INTO `radgroupreply` VALUES (362,'basetraffic','Reply-Message',':=','Your tarif is basetraffic!');
INSERT INTO `radgroupreply` VALUES (361,'basetraffic','Port-Limit',':=','1');
INSERT INTO `radgroupreply` VALUES (360,'basetraffic','Framed-Protocol','=','PPP');
INSERT INTO `radgroupreply` VALUES (359,'basetraffic','Class',':=','basetraffic');
INSERT INTO `radgroupreply` VALUES (358,'basetraffic','Acct-Interim-Interval','=','60');
INSERT INTO `radgroupreply` VALUES (357,'admins','Service-Type','=','Framed-User');
INSERT INTO `radgroupreply` VALUES (356,'admins','Framed-Protocol','=','PPP');
INSERT INTO `radgroupreply` VALUES (355,'admins','Acct-Interim-Interval','=','300');
INSERT INTO `radgroupreply` VALUES (354,'blocked','Service-Type','=','Framed-User');
INSERT INTO `radgroupreply` VALUES (353,'blocked','PPPD-Upstream-Speed-Limit',':=','64');
INSERT INTO `radgroupreply` VALUES (352,'blocked','PPPD-Downstream-Speed-Limit',':=','64');
INSERT INTO `radgroupreply` VALUES (351,'blocked','mpd-limit','+=','out#1=all rate-limit 64000 10000 20000');
INSERT INTO `radgroupreply` VALUES (350,'blocked','mpd-limit','+=','in#1=all rate-limit 64000 10000 20000');
INSERT INTO `radgroupreply` VALUES (349,'blocked','Mikrotik-Rate-Limit',':=','64000/64000');
INSERT INTO `radgroupreply` VALUES (348,'blocked','Framed-Protocol','=','PPP');
INSERT INTO `radgroupreply` VALUES (345,'simultaneous1','Service-Type','=','Framed-User');
INSERT INTO `radgroupreply` VALUES (346,'blocked','Acct-Interim-Interval','=','\n300');
INSERT INTO `radgroupreply` VALUES (347,'blocked','Class',':=','blocked');
INSERT INTO `radgroupreply` VALUES (344,'simultaneous1','PPPD-Upstream-Speed-Limit',':=','64');
INSERT INTO `radgroupreply` VALUES (343,'simultaneous1','PPPD-Downstream-Speed-Limit',':=','64');
INSERT INTO `radgroupreply` VALUES (342,'simultaneous1','mpd-limit','+=','out#1=all rate-limit 64000 10000 20000');
INSERT INTO `radgroupreply` VALUES (341,'simultaneous1','mpd-limit','+=','in#1=all rate-limit 64000 10000 20000');
INSERT INTO `radgroupreply` VALUES (340,'simultaneous1','Mikrotik-Rate-Limit',':=','64000/64000');
INSERT INTO `radgroupreply` VALUES (339,'simultaneous1','Framed-Protocol','=','PPP');
INSERT INTO `radgroupreply` VALUES (338,'simultaneous1','Class',':=','simultaneous1');
INSERT INTO `radgroupreply` VALUES (496,'speedlimit-40Mb','Mikrotik-Rate-Limit',':=','40000000/40000000');
INSERT INTO `radgroupreply` VALUES (497,'speedlimit-40Mb','Framed-Compression','=','Van-Jacobson-TCP-IP');
INSERT INTO `radgroupreply` VALUES (337,'simultaneous1','Acct-Interim-Interval',':=','300');
INSERT INTO `radgroupreply` VALUES (336,'simultaneous','Service-Type',':=','Framed-User');
INSERT INTO `radgroupreply` VALUES (335,'simultaneous','Reply-Message',':=','Simultaneous connection not allow!');
INSERT INTO `radgroupreply` VALUES (334,'simultaneous','PPPD-Upstream-Speed-Limit',':=','64');
INSERT INTO `radgroupreply` VALUES (333,'simultaneous','PPPD-Downstream-Speed-Limit',':=','64');
INSERT INTO `radgroupreply` VALUES (332,'simultaneous','mpd-limit','+=','out#1=all rate-limit 64000 10000 20000');
INSERT INTO `radgroupreply` VALUES (331,'simultaneous','mpd-limit','+=','in#1=all rate-limit 64000 10000 20000');
INSERT INTO `radgroupreply` VALUES (330,'simultaneous','Mikrotik-Rate-Limit',':=','64000/64000');
INSERT INTO `radgroupreply` VALUES (329,'simultaneous','Framed-Protocol','=','PPP');
INSERT INTO `radgroupreply` VALUES (328,'simultaneous','Class',':=','simultaneous');
INSERT INTO `radgroupreply` VALUES (327,'simultaneous','Acct-Interim-Interval',':=','300');
INSERT INTO `radgroupreply` VALUES (326,'badsimultaneous','Reply-Message',':=','Bad simultaneous connetct rejected. Maybe faulty equipment!!');
INSERT INTO `radgroupreply` VALUES (325,'unauthorized','Service-Type','=','Framed-User');
INSERT INTO `radgroupreply` VALUES (324,'unauthorized','Reply-Message',':=','Your device is unauthorized, not access!');
INSERT INTO `radgroupreply` VALUES (323,'unauthorized','PPPD-Upstream-Speed-Limit',':=','64');
INSERT INTO `radgroupreply` VALUES (322,'unauthorized','PPPD-Downstream-Speed-Limit',':=','64');
INSERT INTO `radgroupreply` VALUES (321,'unauthorized','mpd-limit','+=','out#1=all rate-limit 64000 10000 20000');
INSERT INTO `radgroupreply` VALUES (320,'unauthorized','mpd-limit','+=','in#1=all rate-limit 64000 10000 20000');
INSERT INTO `radgroupreply` VALUES (319,'unauthorized','Mikrotik-Rate-Limit',':=','64000/64000');
INSERT INTO `radgroupreply` VALUES (318,'unauthorized','Framed-Protocol','=','PPP');
INSERT INTO `radgroupreply` VALUES (317,'unauthorized','Class',':=','unauthorized');
INSERT INTO `radgroupreply` VALUES (316,'unauthorized','Acct-Interim-Interval','=','300');
INSERT INTO `radgroupreply` VALUES (315,'wrongpass','Service-Type','=','Framed-User');
INSERT INTO `radgroupreply` VALUES (105,'speedlimit-1Mb','Service-Type','=','Framed-User');
INSERT INTO `radgroupreply` VALUES (106,'speedlimit-1Mb','Framed-Protocol','=','PPP');
INSERT INTO `radgroupreply` VALUES (107,'speedlimit-1Mb','Framed-Compression','=','Van-Jacobson-TCP-IP');
INSERT INTO `radgroupreply` VALUES (108,'speedlimit-1Mb','Acct-Interim-Interval',':=','300');
INSERT INTO `radgroupreply` VALUES (110,'speedlimit-1Mb','Mikrotik-Rate-Limit',':=','1024000/1024000');
INSERT INTO `radgroupreply` VALUES (129,'speedlimit-10Mb','Service-Type','=','Framed-User');
INSERT INTO `radgroupreply` VALUES (130,'speedlimit-10Mb','Framed-Protocol','=','PPP');
INSERT INTO `radgroupreply` VALUES (131,'speedlimit-10Mb','Framed-Compression','=','Van-Jacobson-TCP-IP');
INSERT INTO `radgroupreply` VALUES (132,'speedlimit-10Mb','Acct-Interim-Interval',':=','300');
INSERT INTO `radgroupreply` VALUES (134,'speedlimit-10Mb','Mikrotik-Rate-Limit',':=','10240000/10240000');
INSERT INTO `radgroupreply` VALUES (135,'speedlimit-10Mb','mpd-limit','+=','in#1=all rate-limit 10240000 300000 600000');
INSERT INTO `radgroupreply` VALUES (136,'speedlimit-10Mb','mpd-limit','+=','out#1=all rate-limit 10240000 300000 600000');
INSERT INTO `radgroupreply` VALUES (139,'speedlimit-1Mb','mpd-limit','+=','in#1=all rate-limit 1024000 30000 60000');
INSERT INTO `radgroupreply` VALUES (140,'speedlimit-1Mb','mpd-limit','+=','out#1=all rate-limit 1024000 30000 60000');
INSERT INTO `radgroupreply` VALUES (142,'speedlimit-1Mb','Session-Timeout',':=','`%{sql:SELECT sessiontimeout(\'%{User-Name}\')}`');
INSERT INTO `radgroupreply` VALUES (521,'admins','PPPD-Upstream-Speed-Limit',':=','0');
INSERT INTO `radgroupreply` VALUES (197,'speedlimit-10Mb','PPPD-Upstream-Speed-Limit',':=','10000');
INSERT INTO `radgroupreply` VALUES (198,'speedlimit-10Mb','PPPD-Downstream-Speed-Limit',':=','10000');
INSERT INTO `radgroupreply` VALUES (201,'speedlimit-1Mb','PPPD-Upstream-Speed-Limit',':=','1000');
INSERT INTO `radgroupreply` VALUES (202,'speedlimit-1Mb','PPPD-Downstream-Speed-Limit',':=','1000');
INSERT INTO `radgroupreply` VALUES (314,'wrongpass','Reply-Message',':=','Your password is wrong! Please reconnect with correct password.');
INSERT INTO `radgroupreply` VALUES (313,'wrongpass','PPPD-Upstream-Speed-Limit',':=','64');
INSERT INTO `radgroupreply` VALUES (312,'wrongpass','PPPD-Downstream-Speed-Limit',':=','64');
INSERT INTO `radgroupreply` VALUES (311,'wrongpass','Port-Limit',':=','1');
INSERT INTO `radgroupreply` VALUES (310,'wrongpass','mpd-limit','+=','out#1=all rate-limit 64000 10000 20000');
INSERT INTO `radgroupreply` VALUES (213,'speedlimit-20Mb','Service-Type','=','Framed-User');
INSERT INTO `radgroupreply` VALUES (214,'speedlimit-20Mb','Framed-Protocol','=','PPP');
INSERT INTO `radgroupreply` VALUES (215,'speedlimit-20Mb','Framed-Compression','=','Van-Jacobson-TCP-IP');
INSERT INTO `radgroupreply` VALUES (216,'speedlimit-20Mb','Acct-Interim-Interval',':=','300');
INSERT INTO `radgroupreply` VALUES (450,'admins','Connect-Info',':=','`%{User-Name}`');
INSERT INTO `radgroupreply` VALUES (221,'speedlimit-20Mb','Session-Timeout',':=','`%{sql:SELECT sessiontimeout(\'%{User-Name}\')}`');
INSERT INTO `radgroupreply` VALUES (519,'speedlimit-100M','PPPD-Upstream-Speed-Limit',':=','100000');
INSERT INTO `radgroupreply` VALUES (309,'wrongpass','mpd-limit','+=','in#1=all rate-limit 64000 10000 20000');
INSERT INTO `radgroupreply` VALUES (248,'speedlimit-10Mb','Session-Timeout',':=','`%{sql:SELECT sessiontimeout(\'%{User-Name}\')}`');
INSERT INTO `radgroupreply` VALUES (308,'wrongpass','Mikrotik-Rate-Limit',':=','64000/64000');
INSERT INTO `radgroupreply` VALUES (307,'wrongpass','Framed-Protocol','=','PPP');
INSERT INTO `radgroupreply` VALUES (306,'wrongpass','Class',':=','wrongpass');
INSERT INTO `radgroupreply` VALUES (305,'wrongpass','Acct-Interim-Interval','=','300');
INSERT INTO `radgroupreply` VALUES (304,'unknown','Service-Type','=','Framed-User');
INSERT INTO `radgroupreply` VALUES (264,'speedlimit-1Mb','Class',':=','speedlimit-1Mb');
INSERT INTO `radgroupreply` VALUES (268,'speedlimit-10Mb','Class',':=','speedlimit-10Mb');
INSERT INTO `radgroupreply` VALUES (303,'unknown','Reply-Message',':=','Entered username not found in database!');
INSERT INTO `radgroupreply` VALUES (302,'unknown','PPPD-Upstream-Speed-Limit',':=','256');
INSERT INTO `radgroupreply` VALUES (301,'unknown','PPPD-Downstream-Speed-Limit',':=','256');
INSERT INTO `radgroupreply` VALUES (300,'unknown','mpd-limit','+=','out#1=all rate-limit 256000 10000 20000');
INSERT INTO `radgroupreply` VALUES (299,'unknown','mpd-limit','+=','in#1=all rate-limit 256000 10000 20000');
INSERT INTO `radgroupreply` VALUES (298,'unknown','Mikrotik-Rate-Limit',':=','256000/256000');
INSERT INTO `radgroupreply` VALUES (297,'unknown','Framed-Protocol','=','PPP');
INSERT INTO `radgroupreply` VALUES (296,'unknown','Class',':=','unknown');
INSERT INTO `radgroupreply` VALUES (295,'unknown','Acct-Interim-Interval',':=','300');
INSERT INTO `radgroupreply` VALUES (388,'speedlimit-1M-512k','PPPD-Downstream-Speed-Limit',':=','512');
INSERT INTO `radgroupreply` VALUES (387,'speedlimit-1M-512k','PPPD-Upstream-Speed-Limit',':=','1024');
INSERT INTO `radgroupreply` VALUES (364,'speedlimit-2Mb','Acct-Interim-Interval','=','300');
INSERT INTO `radgroupreply` VALUES (365,'speedlimit-2Mb','Class',':=','speedlimit-2Mb');
INSERT INTO `radgroupreply` VALUES (366,'speedlimit-2Mb','Framed-Protocol','=','PPP');
INSERT INTO `radgroupreply` VALUES (367,'speedlimit-2Mb','Mikrotik-Rate-Limit',':=','2000000/2000000');
INSERT INTO `radgroupreply` VALUES (368,'speedlimit-2Mb','mpd-limit','+=','in#1=all rate-limit 2048000 300000 600000');
INSERT INTO `radgroupreply` VALUES (369,'speedlimit-2Mb','mpd-limit','+=','out#1=all rate-limit 2048000 300000 600000');
INSERT INTO `radgroupreply` VALUES (370,'speedlimit-2Mb','PPPD-Downstream-Speed-Limit',':=','2000');
INSERT INTO `radgroupreply` VALUES (371,'speedlimit-2Mb','PPPD-Upstream-Speed-Limit',':=','2000');
INSERT INTO `radgroupreply` VALUES (495,'speedlimit-40Mb','PPPD-Upstream-Speed-Limit',':=','30000');
INSERT INTO `radgroupreply` VALUES (373,'speedlimit-2Mb','Service-Type','=','Framed-User');
INSERT INTO `radgroupreply` VALUES (374,'speedlimit-2Mb','Session-Timeout',':=','`%{sql:SELECT sessiontimeout(\'%{User-Name}\')}`');
INSERT INTO `radgroupreply` VALUES (375,'debtors','Acct-Interim-Interval','=','300');
INSERT INTO `radgroupreply` VALUES (376,'debtors','Class',':=','debtors');
INSERT INTO `radgroupreply` VALUES (377,'debtors','Framed-Protocol','=','PPP');
INSERT INTO `radgroupreply` VALUES (378,'debtors','Mikrotik-Rate-Limit','=','64000/64000');
INSERT INTO `radgroupreply` VALUES (379,'debtors','mpd-limit','+=','in#1=all rate-limit 64000 10000 20000');
INSERT INTO `radgroupreply` VALUES (380,'debtors','mpd-limit','+=','out#1=all rate-limit 64000 10000 20000');
INSERT INTO `radgroupreply` VALUES (381,'debtors','PPPD-Downstream-Speed-Limit',':=','64');
INSERT INTO `radgroupreply` VALUES (382,'debtors','PPPD-Upstream-Speed-Limit',':=','64');
INSERT INTO `radgroupreply` VALUES (383,'debtors','Reply-Message',':=','You are dont enought sum on deposite.');
INSERT INTO `radgroupreply` VALUES (384,'debtors','Service-Type','=','Framed-User');
INSERT INTO `radgroupreply` VALUES (385,'speedlimit-20Mb','Class',':=','speedlimit-20Mb');
INSERT INTO `radgroupreply` VALUES (412,'speedlimit-50Mb','Session-Timeout',':=','`%{sql:SELECT UNIX_TIMESTAMP(u.expired)-UNIX_TIMESTAMP(now())+10 FROM users as u, packets as p WHERE u.pid=p.pid and u.user=\'%{User-Name}\'}`');
INSERT INTO `radgroupreply` VALUES (409,'speedlimit-50Mb','Mikrotik-Rate-Limit',':=','50000000/50000000');
INSERT INTO `radgroupreply` VALUES (407,'speedlimit-50Mb','Acct-Interim-Interval',':=','300');
INSERT INTO `radgroupreply` VALUES (404,'speedlimit-50Mb','Service-Type','=','Framed-User');
INSERT INTO `radgroupreply` VALUES (405,'speedlimit-50Mb','Framed-Protocol','=','PPP');
INSERT INTO `radgroupreply` VALUES (406,'speedlimit-50Mb','Framed-Compression','=','Van-Jacobson-TCP-IP');
INSERT INTO `radgroupreply` VALUES (413,'speedlimit-50Mb','Class',':=','speedlimit-50Mb');
INSERT INTO `radgroupreply` VALUES (447,'speedlimit-20Mb','Connect-Info',':=','`%{User-Name}`');
INSERT INTO `radgroupreply` VALUES (448,'speedlimit-50Mb','Connect-Info',':=','`%{User-Name}`');
INSERT INTO `radgroupreply` VALUES (449,'speedlimit-10Mb','Connect-Info',':=','`%{User-Name}`');
INSERT INTO `radgroupreply` VALUES (451,'speedlimit-1Mb','Connect-Info',':=','`%{User-Name}`');
INSERT INTO `radgroupreply` VALUES (453,'speedlimit-4Mb','Connect-Info',':=','`%{User-Name}`');
INSERT INTO `radgroupreply` VALUES (454,'admins','Class',':=','admins');
INSERT INTO `radgroupreply` VALUES (455,'speedlimit-100M','Session-Timeout',':=','`%{sql:SELECT sessiontimeout(\'%{User-Name}\')}`');
INSERT INTO `radgroupreply` VALUES (494,'speedlimit-40Mb','PPPD-Downstream-Speed-Limit',':=','30000');
INSERT INTO `radgroupreply` VALUES (457,'speedlimit-100M','Acct-Interim-Interval',':=','300');
INSERT INTO `radgroupreply` VALUES (458,'speedlimit-100M','Service-Type','=','Framed-User');
INSERT INTO `radgroupreply` VALUES (459,'speedlimit-100M','Framed-Protocol','=','PPP');
INSERT INTO `radgroupreply` VALUES (460,'speedlimit-100M','Framed-Compression','=','Van-Jacobson-TCP-IP');
INSERT INTO `radgroupreply` VALUES (461,'speedlimit-100M','Class',':=','speedlimit-100M');
INSERT INTO `radgroupreply` VALUES (462,'speedlimit-100M','Connect-Info',':=','`%{User-Name}`');
INSERT INTO `radgroupreply` VALUES (478,'speedlimit-30Mb','PPPD-Downstream-Speed-Limit',':=','30000');
INSERT INTO `radgroupreply` VALUES (477,'speedlimit-30Mb','PPPD-Upstream-Speed-Limit',':=','30000');
INSERT INTO `radgroupreply` VALUES (476,'speedlimit-30Mb','Mikrotik-Rate-Limit',':=','30000000/30000000');
INSERT INTO `radgroupreply` VALUES (473,'speedlimit-30Mb','Framed-Compression','=','Van-Jacobson-TCP-IP');
INSERT INTO `radgroupreply` VALUES (474,'speedlimit-30Mb','Acct-Interim-Interval',':=','300');
INSERT INTO `radgroupreply` VALUES (472,'speedlimit-30Mb','Framed-Protocol','=','PPP');
INSERT INTO `radgroupreply` VALUES (471,'speedlimit-30Mb','Service-Type','=','Framed-User');
INSERT INTO `radgroupreply` VALUES (479,'speedlimit-30Mb','Session-Timeout',':=','`%{sql:SELECT sessiontimeout(\'%{User-Name}\')}`');
INSERT INTO `radgroupreply` VALUES (480,'speedlimit-30Mb','Class',':=','speedlimit-30Mb');
INSERT INTO `radgroupreply` VALUES (522,'admins','PPPD-Downstream-Speed-Limit',':=','0');
INSERT INTO `radgroupreply` VALUES (491,'wrongpass','Session-Timeout',':=','900');
INSERT INTO `radgroupreply` VALUES (498,'speedlimit-40Mb','Acct-Interim-Interval',':=','300');
INSERT INTO `radgroupreply` VALUES (500,'speedlimit-40Mb','Framed-Protocol','=','PPP');
INSERT INTO `radgroupreply` VALUES (501,'speedlimit-40Mb','Service-Type','=','Framed-User');
INSERT INTO `radgroupreply` VALUES (502,'speedlimit-40Mb','Session-Timeout',':=','`%{sql:SELECT UNIX_TIMESTAMP(expired)-UNIX_TIMESTAMP(now()) FROM users WHERE user=\'%{User-Name}\'}`');
INSERT INTO `radgroupreply` VALUES (503,'speedlimit-40Mb','Class',':=','speedlimit-40Mb');
INSERT INTO `radgroupreply` VALUES (506,'speedlimit-40Mb','PPPD-Downstream-Speed-Limit',':=','40000');
INSERT INTO `radgroupreply` VALUES (505,'speedlimit-40Mb','PPPD-Upstream-Speed-Limit',':=','40000');
INSERT INTO `radgroupreply` VALUES (507,'speedlimit-50Mb','PPPD-Upstream-Speed-Limit',':=','50000');
INSERT INTO `radgroupreply` VALUES (508,'speedlimit-50Mb','PPPD-Downstream-Speed-Limit',':=','50000');
INSERT INTO `radgroupreply` VALUES (509,'speedlimit-60Mb','PPPD-Downstream-Speed-Limit',':=','60000');
INSERT INTO `radgroupreply` VALUES (510,'speedlimit-60Mb','PPPD-Upstream-Speed-Limit',':=','60000');
INSERT INTO `radgroupreply` VALUES (511,'speedlimit-60Mb','Mikrotik-Rate-Limit',':=','60000000/60000000');
INSERT INTO `radgroupreply` VALUES (512,'speedlimit-60Mb','Framed-Compression','=','Van-Jacobson-TCP-IP');
INSERT INTO `radgroupreply` VALUES (513,'speedlimit-60Mb','Acct-Interim-Interval',':=','300');
INSERT INTO `radgroupreply` VALUES (515,'speedlimit-60Mb','Framed-Protocol','=','PPP');
INSERT INTO `radgroupreply` VALUES (516,'speedlimit-60Mb','Service-Type','=','Framed-User');
INSERT INTO `radgroupreply` VALUES (517,'speedlimit-60Mb','Session-Timeout',':=','`%{sql:SELECT sessiontimeout(\'%{User-Name}\')}`');
INSERT INTO `radgroupreply` VALUES (520,'speedlimit-100M','PPPD-Downstream-Speed-Limit',':=','100000');
INSERT INTO `radgroupreply` VALUES (518,'speedlimit-60Mb','Class',':=','speedlimit-60Mb');
/*!40000 ALTER TABLE `radgroupreply` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `radippool`
--

DROP TABLE IF EXISTS `radippool`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `radippool` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pool_name` varchar(30) NOT NULL DEFAULT '',
  `framedipaddress` varchar(15) NOT NULL DEFAULT '',
  `nasipaddress` varchar(15) NOT NULL DEFAULT '',
  `calledstationid` varchar(30) NOT NULL DEFAULT '',
  `callingstationid` varchar(30) NOT NULL DEFAULT '',
  `expiry_time` datetime DEFAULT NULL,
  `username` varchar(64) NOT NULL DEFAULT '',
  `pool_key` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `callingstationid` (`callingstationid`),
  KEY `framedipaddress` (`framedipaddress`),
  KEY `pool_name` (`pool_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `radpostauth`
--

DROP TABLE IF EXISTS `radpostauth`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `radpostauth` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL DEFAULT '',
  `uid` int(10) NOT NULL DEFAULT '0',
  `pass` varchar(64) NOT NULL DEFAULT '',
  `reply` varchar(32) NOT NULL DEFAULT '',
  `authdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `radgroup` varchar(64) NOT NULL DEFAULT '',
  `nasip` varchar(15) NOT NULL DEFAULT '',
  `nasport` int(10) NOT NULL DEFAULT '0',
  `callingstationid` varchar(32) NOT NULL DEFAULT '',
  `connectinfo` varchar(128) NOT NULL DEFAULT '',
  `replymessage` varchar(64) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `username` (`username`),
  KEY `nasip` (`nasip`),
  KEY `nasport` (`nasport`),
  KEY `authdate` (`authdate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `expand_group` BEFORE INSERT ON `radpostauth`
FOR EACH ROW
BEGIN
IF NEW.radgroup=NULL or NEW.radgroup='' THEN
    SET NEW.radgroup='not_defined_Class';
END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `radreply`
--

DROP TABLE IF EXISTS `radreply`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `radreply` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL DEFAULT '',
  `attribute` varchar(32) NOT NULL DEFAULT '',
  `op` char(2) NOT NULL DEFAULT '=',
  `value` varchar(253) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `username` (`username`(32))
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `radusergroup`
--

DROP TABLE IF EXISTS `radusergroup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `radusergroup` (
  `username` varchar(64) NOT NULL DEFAULT '',
  `groupname` varchar(64) NOT NULL DEFAULT '',
  `priority` int(11) NOT NULL DEFAULT '1',
  KEY `username` (`username`(32))
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `radusergroup`
--

LOCK TABLES `radusergroup` WRITE;
/*!40000 ALTER TABLE `radusergroup` DISABLE KEYS */;
INSERT INTO `radusergroup` VALUES ('ALL','blocked',7);
INSERT INTO `radusergroup` VALUES ('ALL','unauthorized',4);
INSERT INTO `radusergroup` VALUES ('ALL','simultaneous1',6);
INSERT INTO `radusergroup` VALUES ('ALL','simultaneous',5);
INSERT INTO `radusergroup` VALUES ('ALL','badsimultaneous',3);
INSERT INTO `radusergroup` VALUES ('ALL','unknown',1);
INSERT INTO `radusergroup` VALUES ('ALL','wrongpass',2);
INSERT INTO `radusergroup` VALUES ('USER','admins',8);
INSERT INTO `radusergroup` VALUES ('USER','basetraffic',8);
INSERT INTO `radusergroup` VALUES ('USER','speedlimit-1Mb',8);
INSERT INTO `radusergroup` VALUES ('USER','speedlimit-10Mb',8);
INSERT INTO `radusergroup` VALUES ('USER','speedlimit-20Mb',8);
INSERT INTO `radusergroup` VALUES ('USER','speedlimit-30Mb',8);
INSERT INTO `radusergroup` VALUES ('USER','speedlimit-40Mb',8);
INSERT INTO `radusergroup` VALUES ('USER','speedlimit-50Mb',8);
INSERT INTO `radusergroup` VALUES ('USER','speedlimit-60Mb',8);
INSERT INTO `radusergroup` VALUES ('USER','speedlimit-100M',8);
INSERT INTO `radusergroup` VALUES ('ALL','debtors',9);
/*!40000 ALTER TABLE `radusergroup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rayon`
--

DROP TABLE IF EXISTS `rayon`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rayon` (
  `rid` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `r_name` varchar(64) NOT NULL DEFAULT '',
  `latitude` double(12,10) DEFAULT NULL,
  `longitude` double(12,10) DEFAULT NULL,
  `zoom` tinyint(2) DEFAULT '15',
  UNIQUE KEY `rid` (`rid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rayon`
--

LOCK TABLES `rayon` WRITE;
/*!40000 ALTER TABLE `rayon` DISABLE KEYS */;
INSERT INTO `rayon` VALUES (1,'не определен',NULL,NULL,15);
/*!40000 ALTER TABLE `rayon` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rayon_packet`
--

DROP TABLE IF EXISTS `rayon_packet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rayon_packet` (
  `unique_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rid` smallint(5) NOT NULL DEFAULT '0',
  `gid` smallint(5) NOT NULL DEFAULT '0',
  PRIMARY KEY (`unique_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rayon_packet`
--

LOCK TABLES `rayon_packet` WRITE;
/*!40000 ALTER TABLE `rayon_packet` DISABLE KEYS */;
INSERT INTO `rayon_packet` VALUES (1,1,1);
INSERT INTO `rayon_packet` VALUES (2,1,2);
/*!40000 ALTER TABLE `rayon_packet` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sms`
--

DROP TABLE IF EXISTS `sms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sms` (
  `unique_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `op` varchar(64) NOT NULL DEFAULT '',
  `uid` smallint(5) unsigned DEFAULT '0',
  `phone` varchar(16) NOT NULL DEFAULT '',
  `message` varchar(255) NOT NULL DEFAULT '',
  `status` tinyint(3) NOT NULL DEFAULT '0',
  `smsresult` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`unique_id`),
  KEY `created` (`created`),
  KEY `status` (`status`),
  KEY `phone` (`phone`),
  KEY `updated` (`updated`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `update_sms` BEFORE UPDATE ON `sms`
FOR EACH ROW
BEGIN
    SET NEW.updated=now();
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `traffic`
--

DROP TABLE IF EXISTS `traffic`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `traffic` (
  `unique_id` bigint(16) unsigned NOT NULL AUTO_INCREMENT,
  `user` varchar(64) NOT NULL DEFAULT '',
  `uid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `when` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `time_on` int(12) DEFAULT NULL,
  `in_bytes` bigint(15) DEFAULT NULL,
  `out_bytes` bigint(15) DEFAULT NULL,
  PRIMARY KEY (`unique_id`),
  KEY `when` (`when`),
  KEY `who` (`user`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `uid` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `pid` smallint(5) unsigned NOT NULL DEFAULT '1',
  `blocked` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `disabled` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `user` varchar(64) NOT NULL DEFAULT '',
  `fio` varchar(128) NOT NULL DEFAULT '',
  `psp` varchar(128) NOT NULL DEFAULT '',
  `password` varchar(64) NOT NULL DEFAULT '',
  `opt82` varchar(128) NOT NULL DEFAULT '',
  `deposit` double(20,6) NOT NULL DEFAULT '0.000000',
  `credit` double(20,6) NOT NULL DEFAULT '0.000000',
  `expired` date NOT NULL DEFAULT '0000-00-00',
  `phone` varchar(128) NOT NULL DEFAULT '',
  `address` varchar(128) NOT NULL DEFAULT '',
  `csid` varchar(32) NOT NULL DEFAULT '',
  `rid` smallint(5) unsigned NOT NULL DEFAULT '1',
  `add_date` date NOT NULL DEFAULT '0000-00-00',
  `last_connection` date NOT NULL DEFAULT '0000-00-00',
  `late_payment` date NOT NULL DEFAULT '0000-00-00',
  `prev_pid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `next_pid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `note` varchar(254) NOT NULL DEFAULT '',
  `contract` int(6) DEFAULT NULL,
  `email` varchar(64) NOT NULL DEFAULT '',
  `source` varchar(16) NOT NULL DEFAULT 'netline',
  PRIMARY KEY (`uid`),
  KEY `user` (`user`),
  KEY `csid` (`csid`),
  KEY `contract` (`contract`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `usrontime`
--

DROP TABLE IF EXISTS `usrontime`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usrontime` (
  `id` bigint(16) unsigned NOT NULL AUTO_INCREMENT,
  `when` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `counter` int(8) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `when` (`when`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `vlans`
--

DROP TABLE IF EXISTS `vlans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vlans` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `port` int(10) unsigned NOT NULL,
  `tagged` tinyint(1) DEFAULT '0',
  `vlan` int(6) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `port` (`port`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `workdays`
--

DROP TABLE IF EXISTS `workdays`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `workdays` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `eid` int(10) unsigned NOT NULL DEFAULT '0',
  `date` date NOT NULL DEFAULT '0000-00-00',
  `work` tinyint(1) NOT NULL DEFAULT '0',
  `worktime` smallint(5) NOT NULL DEFAULT '0',
  `overtime` smallint(5) NOT NULL DEFAULT '0',
  `note` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `date` (`date`),
  KEY `eid` (`eid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `workorders`
--

DROP TABLE IF EXISTS `workorders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `workorders` (
  `woid` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `createtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `prescribe` date DEFAULT NULL,
  `performed` datetime DEFAULT NULL,
  `type` varchar(32) DEFAULT '',
  `worktype` varchar(32) DEFAULT '',
  `manager` int(8) unsigned NOT NULL DEFAULT '0',
  `operator` varchar(32) NOT NULL DEFAULT '',
  `note` varchar(255) DEFAULT '',
  PRIMARY KEY (`woid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `workpeople`
--

DROP TABLE IF EXISTS `workpeople`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `workpeople` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `worder` int(10) unsigned NOT NULL DEFAULT '0',
  `employer` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `worder` (`worder`),
  KEY `employer` (`employer`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

--
-- Dumping routines for database 'radius'
--
/*!50003 DROP FUNCTION IF EXISTS `distance` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` FUNCTION `distance`(lng1 DOUBLE(10,7), lat1 DOUBLE(10,7), lng2 DOUBLE(10,7), lat2 DOUBLE(10,7)) RETURNS int(10)
    DETERMINISTIC
    SQL SECURITY INVOKER
BEGIN
DECLARE lat_1, lat_2, lng_1, lng_2, cl1, cl2, sl1, sl2, delta, cdelta, sdelta, x, y, ad double(20,8) DEFAULT 0.0;
DECLARE dist, EARTH_RAD int(10) DEFAULT 0;

SET EARTH_RAD = 6372795;

SET lat_1 = lat1 * PI() / 180;
SET lat_2 = lat2 * PI() / 180;
SET lng_1 = lng1 * PI() / 180;
SET lng_2 = lng2 * PI() / 180;

SET cl1 = COS(lat_1);
SET cl2 = COS(lat_2);
SET sl1 = SIN(lat_1);
SET sl2 = SIN(lat_2);

SET delta = lng_2 - lng_1;
SET cdelta = COS(delta);
SET sdelta = SIN(delta);

SET y = SQRT(POWER(cl2 * sdelta, 2) + POWER(cl1 * sl2 - sl1 * cl2 * cdelta, 2));
SET x = sl1 * sl2 + cl1 * cl2 * cdelta;

SET ad = ATAN2(y, x);
SET dist = ad * EARTH_RAD;

RETURN dist;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `getDivide` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` FUNCTION `getDivide`( subtype varchar(32), port int(10) ) RETURNS double
    DETERMINISTIC
    SQL SECURITY INVOKER
BEGIN
    DECLARE mydivide double(6,2) DEFAULT NULL;
    DECLARE pos int(10) DEFAULT NULL;
    IF subtype IS NOT NULL THEN
		SET pos = locate('/',subtype);
		IF pos > 0 THEN
			IF port = 2 THEN
				SET mydivide = 100/substr(subtype,1,pos-1);
			ELSE IF port = 3 THEN
				SET mydivide = 100/substr(subtype,pos+1);
			END IF; END IF;
		ELSE
			SET pos = locate('x',subtype);
			IF pos > 0 AND port > 1 THEN
				SET mydivide = 100/substr(subtype,pos+1);
			END IF;
		END IF;
	END IF;
	RETURN mydivide;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `getusergroup` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = koi8r */ ;
/*!50003 SET character_set_results = koi8r */ ;
/*!50003 SET collation_connection  = koi8r_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` FUNCTION `getusergroup`(usr varchar(64), mac varchar(32)) RETURNS varchar(32) CHARSET utf8
    DETERMINISTIC
    SQL SECURITY INVOKER
BEGIN
DECLARE dep,cr,balance double DEFAULT 0.0;
DECLARE fix,mytos,mydirect,blck,simul,countsimul int DEFAULT 0;
DECLARE exp date DEFAULT '0000-00-00';
DECLARE result,grp1,grp2,mymac varchar(32) DEFAULT '';
SELECT u.deposit, u.credit, p.fixed, u.expired, p.tos, p.direction, u.groupname, p.groupname, u.blocked, p.su,u.csid INTO dep,cr,fix,exp,mytos,mydirect,grp1,grp2,blck,simul,mymac FROM users as u, packets as p WHERE u.pid=p.pid and u.user = usr;
IF simul>0 THEN
SELECT count(*) INTO countsimul FROM radacct WHERE username = usr and callingstationid!=mac and acctstoptime IS NULL;
END IF;
IF (grp1='' and grp2='') or grp1=NULL or grp2=NULL THEN
SET result='DEFAULT';
RETURN result;
END IF;
IF blck!=0 THEN
SET result='blocked';
RETURN result;
END IF;
IF simul>0 and countsimul>=simul THEN
SET result='simultaneous';
RETURN result;
END IF;
IF mymac!='' and mymac!=mac THEN
SET result='unauthorized';
RETURN result;
END IF;
IF grp1='' THEN SET grp1=grp2; END IF;
IF fix>0 or (mytos>0 and mydirect>0) THEN
SET balance=dep+cr;
IF (exp!='0000-00-00' and exp<=now()) or balance<=0 THEN 
SET result='debtors';
ELSE SET result=grp1;
END IF;
ELSE
SET result=grp1;
END IF;
RETURN result;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `sessiontimeout` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` FUNCTION `sessiontimeout`(usr varchar(64)) RETURNS int(10)
    DETERMINISTIC
    SQL SECURITY INVOKER
BEGIN
DECLARE d,cr,fc double DEFAULT 0.0;
DECLARE tos_, fixed_, result int DEFAULT 0;
DECLARE expired_ date DEFAULT '0000-00-00';

SELECT tos, fixed, fixed_cost, expired, deposit, credit INTO tos_, fixed_, fc, expired_, d, cr FROM  users as u, packets as p WHERE u.pid=p.pid and u.user=usr;

IF tos_ IS NULL THEN
	RETURN 0;
END IF;

IF (fixed_ = 1) AND (d + cr > 0) THEN
	SET result = UNIX_TIMESTAMP(DATE(DATE_ADD(now(), interval 1 day)))-UNIX_TIMESTAMP(now())+60;
	RETURN result;
END IF;

IF (fixed_ = 7) AND (d + cr > 0) THEN
	SET result = UNIX_TIMESTAMP(DATE(DATE_ADD(now(), INTERVAL floor((d+cr)/fc+0.99) DAY)))-UNIX_TIMESTAMP(now())+60;
	RETURN result;
END IF;

IF (tos_=0 AND fixed_=0) OR (fixed_ = 8 AND d + cr > 0) THEN
	SET result = UNIX_TIMESTAMP(DATE_FORMAT(DATE_ADD(now(), interval 1 month),'%Y-%m-01'))-UNIX_TIMESTAMP(now())+60;
	RETURN result;
END IF;

IF (fixed_ = 10) AND (d + cr >= 0) AND expired_ > now() THEN
	SET result = UNIX_TIMESTAMP(expired_)-UNIX_TIMESTAMP(now())+60;
	RETURN result;
END IF;

SET result = 900;
RETURN result;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `CreatePorts` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `CreatePorts`(
	dev int(10),
    type varchar(32),
    subtype varchar(32),
    port_num int(10),
    node1 int(10),
    node2 int(10),
    bandleports int(10),
    colorscheme varchar(32)
)
    SQL SECURITY INVOKER
BEGIN
    
    DECLARE counter, slice, mynode, max_slice, min_ports, slices, config_id, port_formers_id, devtypes_id, devtype_id, porttypes_id int(10) DEFAULT 0;
    DECLARE port_color, port_bandle varchar(32) DEFAULT '';
    DECLARE port_option varchar(32) DEFAULT 'solid';
    DECLARE port_exists int(10) DEFAULT NULL;
    DECLARE mydivide double(6,2) DEFAULT NULL;
    DECLARE port_former, myporttype, div1, div2 varchar(32) DEFAULT NULL;
	DECLARE msg varchar(255) DEFAULT '';

	SELECT id INTO config_id FROM `settings` WHERE parent is NULL AND name='devices';

    IF config_id > 0 THEN
		SELECT id INTO devtypes_id FROM `settings` WHERE name='device_types' AND parent=config_id;
		SELECT id INTO port_formers_id FROM `settings` WHERE name='port_formers' AND parent=config_id;
    END IF;

    IF devtypes_id > 0 THEN
		SELECT id INTO porttypes_id FROM `settings` WHERE `parent` = devtypes_id AND name = type;
    END IF;

    IF port_formers_id > 0 THEN
		SELECT `value` INTO port_former FROM `settings` WHERE `parent` = port_formers_id AND name = type;
    END IF;

    IF porttypes_id > 0 THEN
		SELECT count(*) INTO max_slice FROM `settings` WHERE `parent` = porttypes_id;
    END IF;

    IF max_slice > 0 AND port_num > 0 THEN
		SET slice = 1;
		SET slices = max_slice;
		IF port_former IS NULL THEN
			SET slices = 1;
		END IF;
		WHILE slice <= slices DO
			SET counter = 1;
			SET myporttype = NULL;
			SET mynode = node1;
			IF port_former IS NOT NULL THEN
				SELECT `name` INTO myporttype FROM `settings` WHERE `parent` = porttypes_id AND `value` = cast(slice as CHAR);
			END IF;
			IF port_former = 'bynode' THEN
				IF slice > 1 THEN
					SET mynode = node2;
				END IF;
			END IF;
			WHILE counter <= port_num DO
				SET mydivide = getDivide(subtype,counter);
				IF port_former IS NULL THEN
					IF counter <= max_slice THEN
						SELECT `name` INTO myporttype FROM `settings` WHERE `parent` = porttypes_id AND `value` = cast(counter as CHAR);
					END IF;
				END IF;
				IF myporttype IS NULL THEN
					SET msg=concat('Trying to insert port ',counter,' device ',dev,' with null as porttype!');
					SIGNAL SQLSTATE '45000' SET message_text=msg;
				END IF;
				SET port_exists = NULL;
				SELECT id INTO port_exists FROM `devports`  WHERE `device` = dev AND `number` = counter AND `node` = mynode AND `porttype` = myporttype;
				IF port_exists IS NULL THEN
					IF colorscheme != '' AND bandleports > 0 THEN
						IF type = 'splitter' THEN
							SET port_color = '', port_option = 'solid', port_bandle = '';
							IF counter = 1 THEN
								SET port_color = 'white';
							ELSE
								SELECT `color` INTO port_color FROM devprofiles WHERE name = colorscheme AND port = mod(counter-2,bandleports)+1;
							END IF;
						ELSE
							SELECT `color`, `option` INTO port_color, port_option FROM devprofiles WHERE name = colorscheme AND port = mod(counter-1,bandleports)+1;
							IF type = 'cable' AND port_num > bandleports THEN
								SELECT `color` INTO port_bandle FROM devprofiles WHERE name = colorscheme AND port = floor((counter-1)/bandleports)+1;
							END IF;
						END IF;
					END IF;
					INSERT INTO `devports` (`device`,`number`,`node`,`porttype`,`color`,`coloropt`,`bandle`,`divide`) values (dev,counter,mynode,myporttype,port_color,port_option,port_bandle,mydivide);
				END IF;
				SET counter = counter + 1;
			END WHILE;
			SET slice = slice + 1;
		END WHILE;
    END IF;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `updateDivide` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `updateDivide`(
	dev int(10),
    type varchar(32),
    subtype varchar(32)
)
    SQL SECURITY INVOKER
BEGIN
	IF type = 'divisor' OR type = 'splitter' THEN
		UPDATE `devports` SET divide = getDivide(subtype,`number`) WHERE device=dev;
	END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2020-09-25 12:32:34
