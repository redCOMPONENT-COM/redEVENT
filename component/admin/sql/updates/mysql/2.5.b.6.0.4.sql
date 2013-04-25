ALTER TABLE `#__redevent_event_venue_xref` ADD `language` char(7) NOT NULL;

UPDATE `#__redevent_event_venue_xref` AS x 
INNER JOIN `#__redevent_events` AS e ON e.id = x.eventid
SET x.language = e.language; 
