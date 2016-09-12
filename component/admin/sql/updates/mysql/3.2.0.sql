SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;

CREATE TABLE IF NOT EXISTS `#__redevent_bundle` (
  `id` int(11) UNSIGNED NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `alias` varchar(100) NOT NULL default '',
  `description` text NOT NULL,
  `published` tinyint(1) NOT NULL default '0',
  `checked_out` int(11) NOT NULL default '0',
  `checked_out_time` datetime NOT NULL,
  `access` int(11) unsigned NOT NULL default '0',
  `ordering` int(11) NOT NULL default '0',
  `language` char(7) NOT NULL,
  `params` TEXT NOT NULL,
  `asset_id` int(10) NOT NULL DEFAULT '0',
PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__redevent_bundle_event` (
  `id` int(11) UNSIGNED NOT NULL auto_increment,
  `bundle_id` INT(11) UNSIGNED NOT NULL,
  `event_id` INT(11) UNSIGNED NOT NULL,
  `all_dates` tinyint(1) NOT NULL default '0',
  `params` TEXT NOT NULL,
  PRIMARY KEY  (`id`),
  CONSTRAINT `#__re_bundle_ev_fk1`
  FOREIGN KEY (`bundle_id`) REFERENCES `#__redevent_bundle` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `#__re_bundle_ev_fk2`
  FOREIGN KEY (`event_id`) REFERENCES `#__redevent_events` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__redevent_bundle_event_session` (
  `id` int(11) UNSIGNED NOT NULL auto_increment,
  `bundle_event_id` INT(11) UNSIGNED NOT NULL,
  `session_id` INT(11) UNSIGNED NOT NULL,
  `params` TEXT NOT NULL,
  PRIMARY KEY  (`id`),
  CONSTRAINT `#__re_bundle_ev_session_fk1`
  FOREIGN KEY (`bundle_event_id`) REFERENCES `#__redevent_bundle_event` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `#__re_bundle_ev_session_fk2`
  FOREIGN KEY (`session_id`) REFERENCES `#__redevent_event_venue_xref` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
