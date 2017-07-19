SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;

ALTER TABLE `#__redevent_event_venue_xref`
   CHANGE `dates` `dates` date NULL DEFAULT NULL,
   CHANGE `enddates` `enddates` date NULL DEFAULT NULL,
   CHANGE `times` `times` time NULL DEFAULT NULL,
   CHANGE `endtimes` `endtimes` time NULL DEFAULT NULL,
   CHANGE `registrationend` `registrationend` datetime NULL DEFAULT NULL;

UPDATE `#__redevent_event_venue_xref`
   SET `dates` = NULL WHERE `dates` = 0;

UPDATE `#__redevent_event_venue_xref`
   SET `enddates` = NULL WHERE `enddates` = 0;

UPDATE `#__redevent_event_venue_xref`
   SET `times` = NULL WHERE `times` = 0;

UPDATE `#__redevent_event_venue_xref`
   SET `endtimes` = NULL WHERE `endtimes` = 0;

UPDATE `#__redevent_event_venue_xref`
   SET `registrationend` = NULL WHERE `registrationend` = 0;

SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
