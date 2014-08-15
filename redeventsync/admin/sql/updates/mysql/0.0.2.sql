CREATE TABLE IF NOT EXISTS `#__redeventsync_queuedmessages` (
  `redeventsync_queuedmessage_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queued` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `plugin` VARCHAR(50) NOT NULL,
  `message` text NOT NULL,
  PRIMARY KEY (`redeventsync_queuedmessage_id`)
) DEFAULT CHARSET=utf8;
