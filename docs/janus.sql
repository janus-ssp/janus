SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `janus_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `janus__arp`
--

DROP TABLE IF EXISTS `janus__arp`;
CREATE TABLE IF NOT EXISTS `janus__arp` (
  `aid` int(11) NOT NULL auto_increment,
  `name` text,
  `description` text,
  `attributes` text,
  `created` char(25) NOT NULL,
  `updated` char(25) NOT NULL,
  `ip` char(15) NOT NULL,
  PRIMARY KEY  (`aid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=56 ;

-- --------------------------------------------------------

--
-- Table structure for table `janus__attribute`
--

DROP TABLE IF EXISTS `janus__attribute`;
CREATE TABLE IF NOT EXISTS `janus__attribute` (
  `eid` int(11) NOT NULL,
  `revisionid` int(11) NOT NULL,
  `key` text NOT NULL,
  `value` text NOT NULL,
  `created` char(25) NOT NULL,
  `ip` char(15) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `janus__blockedEntity`
--

DROP TABLE IF EXISTS `janus__blockedEntity`;
CREATE TABLE IF NOT EXISTS `janus__blockedEntity` (
  `eid` int(11) NOT NULL,
  `revisionid` int(11) NOT NULL,
  `remoteentityid` text NOT NULL,
  `created` char(25) NOT NULL,
  `ip` char(15) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `janus__allowedEntity`
--

DROP TABLE IF EXISTS `janus__allowedEntity`;
CREATE TABLE IF NOT EXISTS `janus__allowedEntity` (
  `eid` int(11) NOT NULL,
  `revisionid` int(11) NOT NULL,
  `remoteentityid` text NOT NULL,
  `created` char(25) NOT NULL,
  `ip` char(15) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


-- --------------------------------------------------------

--
-- Table structure for table `janus__disableConsent`
--

DROP TABLE IF EXISTS `janus__disableConsent`;
CREATE TABLE IF NOT EXISTS `janus__disableConsent` (
  `eid` int(11) NOT NULL,
  `revisionid` int(11) NOT NULL,
  `remoteentityid` text NOT NULL,
  `created` char(25) NOT NULL,
  `ip` char(15) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `janus__entity`
--

DROP TABLE IF EXISTS `janus__entity`;
CREATE TABLE IF NOT EXISTS `janus__entity` (
  `eid` int(11) NOT NULL,
  `entityid` text NOT NULL,
  `revisionid` int(11) default NULL,
  `state` text,
  `type` text,
  `expiration` char(25) default NULL,
  `metadataurl` text,
  `allowedall` char(3) NOT NULL default 'yes',
  `arp` int(11) default NULL,
  `user` int(11) default NULL,
  `created` char(25) default NULL,
  `ip` char(15) default NULL,
  `parent` int(11) default NULL,
  `revisionnote` text,
  UNIQUE KEY `eid` (`eid`,`revisionid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE UNIQUE INDEX `janus__entity__eid_revisionid` ON `janus__entity`(`eid`, `revisionid`);
-- --------------------------------------------------------

--
-- Table structure for table `janus__hasEntity`
--

DROP TABLE IF EXISTS `janus__hasEntity`;
CREATE TABLE IF NOT EXISTS `janus__hasEntity` (
  `uid` int(11) NOT NULL,
  `eid` int(11) default NULL,
  `created` char(25) default NULL,
  `ip` char(15) default NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `janus__message`
--

DROP TABLE IF EXISTS `janus__message`;
CREATE TABLE IF NOT EXISTS `janus__message` (
  `mid` int(11) NOT NULL auto_increment,
  `uid` int(11) NOT NULL,
  `subject` text NOT NULL,
  `message` text,
  `from` int(11) NOT NULL,
  `subscription` text NOT NULL,
  `read` enum('yes','no') default 'no',
  `created` char(25) NOT NULL,
  `ip` char(15) default NULL,
  PRIMARY KEY  (`mid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=440 ;

-- --------------------------------------------------------

--
-- Table structure for table `janus__metadata`
--

DROP TABLE IF EXISTS `janus__metadata`;
CREATE TABLE IF NOT EXISTS `janus__metadata` (
  `eid` int(11) NOT NULL,
  `revisionid` int(11) NOT NULL,
  `key` text NOT NULL,
  `value` text NOT NULL,
  `created` char(25) NOT NULL,
  `ip` char(15) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE UNIQUE INDEX `janus__metadata__eid_revisionid_key` ON `janus__metadata`(`eid`, `revisionid`, `key`(50));
-- --------------------------------------------------------

--
-- Table structure for table `janus__subscription`
--

DROP TABLE IF EXISTS `janus__subscription`;
CREATE TABLE IF NOT EXISTS `janus__subscription` (
  `sid` int(11) NOT NULL auto_increment,
  `uid` int(11) NOT NULL,
  `subscription` text NOT NULL,
  `type` text,
  `created` char(25) default NULL,
  `ip` char(15) default NULL,
  PRIMARY KEY  (`sid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=35 ;

-- --------------------------------------------------------

--
-- Table structure for table `janus__tokens`
--

DROP TABLE IF EXISTS `janus__tokens`;
CREATE TABLE IF NOT EXISTS `janus__tokens` (
  `id` int(11) NOT NULL auto_increment,
  `mail` varchar(320) NOT NULL,
  `token` varchar(255) NOT NULL,
  `notvalidafter` varchar(255) NOT NULL,
  `usedat` varchar(255) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `token` (`token`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=32 ;

-- --------------------------------------------------------

--
-- Table structure for table `janus__user`
--

DROP TABLE IF EXISTS `janus__user`;
CREATE TABLE IF NOT EXISTS `janus__user` (
  `uid` int(11) NOT NULL auto_increment,
  `userid` text,
  `type` text,
  `email` varchar(320) default NULL,
  `active` char(3) default 'yes',
  `update` char(25) default NULL,
  `created` char(25) default NULL,
  `ip` char(15) default NULL,
  `data` text,
  `secret` text, 
  PRIMARY KEY  (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

--
-- Table structure for table `janus__userData`
--

DROP TABLE IF EXISTS `janus__userData`;
CREATE TABLE IF NOT EXISTS `janus__userData` (
  `uid` int(11) NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `update` char(25) NOT NULL,
  `created` char(25) NOT NULL,
  `ip` char(15) NOT NULL,
  UNIQUE KEY `uid` (`uid`,`key`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
