# Web Service Browser
<p>Gathers web services and other types of files from IIS web servers and checks for access from specified networks</p>
<p>You need to create mysql db </p>
<p>Compatible with datables js library at www.datables.net</p>

## WEBSERVICES
Stores web service list.
<code>
CREATE TABLE `webservices` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `hostname` varchar(60) DEFAULT NULL,
  `IP` varchar(30) DEFAULT NULL,
  `folder` varchar(255) DEFAULT NULL,
  `file` varchar(255) DEFAULT NULL,
  `Appname` varchar(255) DEFAULT NULL,
  `IPRestrictionInIIS` tinyint(1) DEFAULT NULL,
  `fullurl` varchar(255) DEFAULT NULL,
  `YOUR_NETWORK_ZONE_NAME_1` tinyint(1) DEFAULT NULL,
  `YOUR_NETWORK_ZONE_NAME_2` tinyint(1) DEFAULT NULL,
  `internetaccess` tinyint(1) DEFAULT NULL,
  `domain` varchar(255) DEFAULT NULL,
  `YOUR_NETWORK_ZONE_NAME_3` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=116170 DEFAULT CHARSET=latin1;
</code>
## USERS
Stores users. Usernames are SHA256 hashed
<code>
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `aduser` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=latin1;
</code>

## ACCESSRES
<code>
CREATE TABLE `accessres` (
  `id` int(11) NOT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `endtime` datetime DEFAULT NULL,
  `username` varchar(45) DEFAULT NULL,
  `attempt` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username_UNIQUE` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='access restrictions';
</code>
