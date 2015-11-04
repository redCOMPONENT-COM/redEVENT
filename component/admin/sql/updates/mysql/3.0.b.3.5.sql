ALTER TABLE `#__redevent_event_venue_xref`
   ADD `created` datetime NOT NULL,
   ADD `created_by` int(11) unsigned NOT NULL default '0',
   ADD `modified` datetime NOT NULL,
   ADD `modified_by` int(11) unsigned NOT NULL default '0',
   ADD `checked_out` int(11) NOT NULL default '0',
   ADD `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00';
