ALTER TABLE `#__redevent_event_venue_xref` CHANGE `dates` `dates` date NOT NULL,
   CHANGE `enddates` `enddates` date NOT NULL,
   CHANGE `times` `times` time NOT NULL,
   CHANGE `endtimes` `endtimes` time NOT NULL,
   CHANGE `registrationend` `registrationend` datetime NOT NULL;
