ALTER TABLE  `#__redevent_event_venue_xref` ADD  `icaldetails` text NOT NULL;

ALTER TABLE  `#__redevent_pricegroups` ADD  `adminonly` tinyint(1) NOT NULL default '0';

ALTER TABLE  `#__redevent_event_venue_xref` ADD  `title` varchar(255) default NULL,
  ADD `alias` varchar(255) default NULL;
