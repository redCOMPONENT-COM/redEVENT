CREATE TABLE IF NOT EXISTS `#__redevent_organizations` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `organization_id` int(11) NOT NULL,
  `b2b_attendee_notification_mailflow` tinyint(4) NOT NULL DEFAULT '0',
  `b2b_orgadmin_mailflow_confirmation_subject_tag` VARCHAR(30),
  `b2b_orgadmin_mailflow_cancellation_subject_tag` VARCHAR(30),
  `b2b_orgadmin_mailflow_confirmation_body_tag` VARCHAR(30),
  `b2b_orgadmin_mailflow_cancellation_body_tag` VARCHAR(30),
  `b2b_cancellation_period` tinyint(4) NOT NULL DEFAULT '15',
  `checked_out` int(11) NOT NULL default '0',
  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `organization_id` (`organization_id`),
  CONSTRAINT `fk_organization1` FOREIGN KEY (`organization_id`) REFERENCES `#__redmember_organization` (`id`) ON DELETE CASCADE
) DEFAULT CHARSET=utf8;
