CREATE TABLE IF NOT EXISTS `#__redeventsync_logs` (
  `redeventsync_log_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `transactionid` int NOT NULL,
  `direction` tinyint NOT NULL,
  `type` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `debug` text NOT NULL,
  `status` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`redeventsync_log_id`),
  KEY `transactionid` (`transactionid`)
) DEFAULT CHARSET=utf8;
