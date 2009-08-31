-- MySQL dump 10.11
--
-- Host: localhost    Database: jach_db
-- ------------------------------------------------------
-- Server version	5.0.32-Debian_7etch8-log

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
-- Table structure for table `janus__attribute`
--

DROP TABLE IF EXISTS `janus__attribute`;
CREATE TABLE `janus__attribute` (
  `entityid` text NOT NULL,
  `revisionid` int(11) NOT NULL,
  `key` text NOT NULL,
  `value` text NOT NULL,
  `created` char(25) NOT NULL,
  `ip` char(15) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `janus__blockedEntity`
--

DROP TABLE IF EXISTS `janus__blockedEntity`;
CREATE TABLE `janus__blockedEntity` (
  `entityid` text NOT NULL,
  `revisionid` int(11) NOT NULL,
  `remoteentityid` text NOT NULL,
  `created` char(25) NOT NULL,
  `ip` char(15) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `janus__entity`
--

DROP TABLE IF EXISTS `janus__entity`;
CREATE TABLE `janus__entity` (
  `eid` int(11) NOT NULL,
  `entityid` text NOT NULL,
  `revisionid` int(11) default NULL,
  `state` text,
  `type` text,
  `expiration` char(25) default NULL,
  `metadataurl` text,
  `allowedall` char(3) NOT NULL default 'yes',
  `created` char(25) default NULL,
  `ip` char(15) default NULL,
  `parent` int(11) default NULL,
  `revisionnote` text
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `janus__hasEntity`
--

DROP TABLE IF EXISTS `janus__hasEntity`;
CREATE TABLE `janus__hasEntity` (
  `uid` int(11) NOT NULL,
  `eid` int(11) default NULL,
  `created` char(25) default NULL,
  `ip` char(15) default NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `janus__metadata`
--

DROP TABLE IF EXISTS `janus__metadata`;
CREATE TABLE `janus__metadata` (
  `eid` int(11) NOT NULL,
  `revisionid` int(11) NOT NULL,
  `key` text NOT NULL,
  `value` text NOT NULL,
  `created` char(25) NOT NULL,
  `ip` char(15) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `janus__tokens`
--

DROP TABLE IF EXISTS `janus__tokens`;
CREATE TABLE `janus__tokens` (
  `id` int(11) NOT NULL auto_increment,
  `mail` varchar(320) NOT NULL,
  `token` varchar(255) NOT NULL,
  `notvalidafter` varchar(255) NOT NULL,
  `usedat` varchar(255) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `token` (`token`)
) ENGINE=MyISAM AUTO_INCREMENT=61 DEFAULT CHARSET=latin1;

--
-- Table structure for table `janus__user`
--

DROP TABLE IF EXISTS `janus__user`;
CREATE TABLE `janus__user` (
  `uid` int(11) NOT NULL auto_increment,
  `type` text,
  `email` varchar(320) default NULL,
  `active` char(3) default 'yes',
  `update` char(25) default NULL,
  `created` char(25) default NULL,
  `ip` char(15) default NULL,
  `data` text,
  PRIMARY KEY  (`uid`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;

--
-- Table structure for table `janus__userData`
--

DROP TABLE IF EXISTS `janus__userData`;
CREATE TABLE `janus__userData` (
  `uid` int(11) NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `update` char(25) NOT NULL,
  `created` char(25) NOT NULL,
  `ip` char(15) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2009-08-31 11:36:38
