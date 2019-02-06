CREATE TABLE `stats_visits` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(15) DEFAULT NULL,
  `appid` int(11) unsigned DEFAULT NULL,
  `language` varchar(255) DEFAULT NULL,
  `filters` text,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ip` (`ip`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

CREATE TABLE `stats_users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `fk_stats_visits_id` int(11) unsigned NOT NULL,
  `steamid` bigint(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_stats_visits_id` (`fk_stats_visits_id`),
  KEY `steamid` (`steamid`),
  CONSTRAINT `fk_stats_visits_id` FOREIGN KEY (`fk_stats_visits_id`) REFERENCES `stats_visits` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;


