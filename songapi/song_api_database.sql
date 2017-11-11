-- Generated on a MySQL 5.5 database server

CREATE DATABASE `Songlyrics` CHARACTER SET utf8 COLLATE utf8_general_ci;

USE `Songlyrics`;

DROP TABLE IF EXISTS `Albums`;
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `Artists`;
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `GenreIDs`;
CREATE TABLE `GenreIDs` (
  `GenreID` smallint(6) NOT NULL DEFAULT '0',
  `Name` tinytext,
  PRIMARY KEY (`GenreID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `Lyrics`;
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `Settings`;
CREATE TABLE `Settings` (
  `VersionDB` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`VersionDB`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

LOCK TABLES `Settings` WRITE;
INSERT INTO `Settings` VALUES (0.12);
UNLOCK TABLES;
