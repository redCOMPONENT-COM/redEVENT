CREATE TABLE IF NOT EXISTS `#__redeventsync_archive` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `transactionid` int(11) NOT NULL,
  `direction` tinyint(4) NOT NULL,
  `type` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `status` varchar(50) NOT NULL,
  `debug` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `transactionid` (`transactionid`)
) DEFAULT CHARSET=utf8;
