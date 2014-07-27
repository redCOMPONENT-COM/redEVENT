CREATE TABLE IF NOT EXISTS `#__redevent_organizations` (
`id` int(11) unsigned NOT NULL auto_increment,
`organization_id` int(11) NOT NULL,
`b2b_admin_notification_mailflow` tinyint(4) NOT NULL DEFAULT '0',
`b2b_disable_all_attendee_notifications` tinyint(4) NOT NULL DEFAULT '0',
`b2b_cancellation_period` tinyint(4) NOT NULL DEFAULT '15',
`checked_out` int(11) NOT NULL default '0',
`checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;
