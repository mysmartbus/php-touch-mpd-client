-- MySQL dump 10.13  Distrib 5.5.60, for debian-linux-gnu (armv8l)
--
-- Host: localhost    Database: Songlyrics
-- ------------------------------------------------------
-- Server version	5.5.60-0+deb8u1-log

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
-- Current Database: `Songlyrics`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `Songlyrics` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `Songlyrics`;

--
-- Table structure for table `Albums`
--

DROP TABLE IF EXISTS `Albums`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Albums` (
  `AlbumID` smallint(6) NOT NULL AUTO_INCREMENT,
  `ArtistID` smallint(5) unsigned DEFAULT '0',
  `Name` tinytext,
  `ShortName` tinytext,
  `ReleaseDate` date DEFAULT '0000-00-00',
  `Wikipedia` tinytext,
  `CoverArtFront` tinytext,
  `CoverArtBack` tinytext,
  `DateAdded` date DEFAULT '0000-00-00',
  `AddedBy` int(10) unsigned DEFAULT '0',
  PRIMARY KEY (`AlbumID`)
) ENGINE=MyISAM AUTO_INCREMENT=3050 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Artists`
--

DROP TABLE IF EXISTS `Artists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Artists` (
  `ArtistID` smallint(6) NOT NULL AUTO_INCREMENT,
  `Name` tinytext,
  `ShortName` tinytext,
  `Website` tinytext,
  `Bio` text,
  `Wikipedia` tinytext,
  `DateAdded` date DEFAULT '0000-00-00',
  `AddedBy` int(10) unsigned DEFAULT '0',
  PRIMARY KEY (`ArtistID`)
) ENGINE=MyISAM AUTO_INCREMENT=872 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `GenreIDs`
--

DROP TABLE IF EXISTS `GenreIDs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `GenreIDs` (
  `GenreID` smallint(6) NOT NULL DEFAULT '0',
  `Name` tinytext,
  PRIMARY KEY (`GenreID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Lyrics`
--

DROP TABLE IF EXISTS `Lyrics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Lyrics` (
  `SongID` smallint(6) NOT NULL AUTO_INCREMENT,
  `Name` tinytext,
  `ShortName` tinytext,
  `Lyrics` text,
  `AlbumID` smallint(6) DEFAULT '0',
  `TrackNumber` tinyint(3) DEFAULT '0',
  `Writers` tinytext,
  `TrackMinutes` tinyint(2) DEFAULT '0',
  `TrackSeconds` tinyint(2) DEFAULT '0',
  `ArtistID` smallint(6) DEFAULT '0',
  `GenreID` smallint(5) unsigned DEFAULT '0',
  `DateAdded` date DEFAULT '0000-00-00',
  `AddedBy` int(10) unsigned DEFAULT '0',
  `volume` int(3) DEFAULT '-1',
  PRIMARY KEY (`SongID`),
  FULLTEXT KEY `Lyrics` (`Lyrics`)
) ENGINE=MyISAM AUTO_INCREMENT=459 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Settings`
--

DROP TABLE IF EXISTS `Settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Settings` (
  `VersionDB` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`VersionDB`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping events for database 'Songlyrics'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-09-21  2:24:03
