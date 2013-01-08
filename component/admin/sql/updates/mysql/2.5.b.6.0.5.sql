ALTER TABLE `#__redevent_venues` ADD `params` TEXT NOT NULL;

ALTER TABLE `#__redevent_event_venue_xref` ADD `session_code` varchar(100) NOT NULL;
ALTER TABLE `#__redevent_venues` ADD `venue_code` varchar(100) NOT NULL;
