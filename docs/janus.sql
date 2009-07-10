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
  `entityid` text,
  `revisionid` int(11) default NULL,
  `system` text,
  `state` text,
  `type` text,
  `expiration` char(25) default NULL,
  `metadataurl` text,
  `allowedall` char(3) NOT NULL default 'yes',
  `allowedlist` text,
  `authcontext` int(11) default NULL,
  `created` char(25) default NULL,
  `ip` char(15) default NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `janus__hasEntity`
--

DROP TABLE IF EXISTS `janus__hasEntity`;
CREATE TABLE `janus__hasEntity` (
  `uid` int(11) NOT NULL,
  `entityid` text,
  `created` char(25) default NULL,
  `ip` char(15) default NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `janus__metadata`
--

DROP TABLE IF EXISTS `janus__metadata`;
CREATE TABLE `janus__metadata` (
  `entityid` text NOT NULL,
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
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `janus__user`
--

DROP TABLE IF EXISTS `janus__user`;
CREATE TABLE `janus__user` (
  `uid` int(11) NOT NULL auto_increment,
  `type` text,
  `email` varchar(320) default NULL,
  `update` char(25) default NULL,
  `created` char(25) default NULL,
  `ip` char(15) default NULL,
  `data` text,
  PRIMARY KEY  (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
