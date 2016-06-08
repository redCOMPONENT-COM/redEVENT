ALTER TABLE `#__redevent_event_venue_xref`
   ADD `allday` tinyint(1) NOT NULL default '0' AFTER `enddates`;

UPDATE `#__redevent_event_venue_xref` SET allday = 1 WHERE times = 0;
