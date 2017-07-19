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

ALTER TABLE `#__redevent_attachments`
   CHANGE `added` `added` datetime NOT NULL;

ALTER TABLE `#__redevent_bundle`
   CHANGE `checked_out_time` `checked_out_time` datetime NULL default NULL;

UPDATE `#__redevent_bundle`
   SET `checked_out_time` = NULL WHERE `checked_out_time` = 0;

ALTER TABLE `#__redevent_categories`
   CHANGE `checked_out_time` `checked_out_time` datetime NULL default NULL;

UPDATE `#__redevent_categories`
   SET `checked_out_time` = NULL WHERE `checked_out_time` = 0;

ALTER TABLE `#__redevent_event_venue_xref`
   CHANGE `checked_out_time` `checked_out_time` datetime NULL default NULL;

UPDATE `#__redevent_event_venue_xref`
   SET `checked_out_time` = NULL WHERE `checked_out_time` = 0;

ALTER TABLE `#__redevent_events`
   CHANGE `checked_out_time` `checked_out_time` datetime NULL default NULL;

UPDATE `#__redevent_events`
   SET `checked_out_time` = NULL WHERE `checked_out_time` = 0;

ALTER TABLE `#__redevent_event_template`
   CHANGE `checked_out_time` `checked_out_time` datetime NULL default NULL;

UPDATE `#__redevent_event_template`
   SET `checked_out_time` = NULL WHERE `checked_out_time` = 0;

ALTER TABLE `#__redevent_fields`
   CHANGE `checked_out_time` `checked_out_time` datetime NULL default NULL;

UPDATE `#__redevent_fields`
   SET `checked_out_time` = NULL WHERE `checked_out_time` = 0;

ALTER TABLE `#__redevent_organizations`
   CHANGE `checked_out_time` `checked_out_time` datetime NULL default NULL;

UPDATE `#__redevent_organizations`
   SET `checked_out_time` = NULL WHERE `checked_out_time` = 0;

ALTER TABLE `#__redevent_pricegroups`
   CHANGE `checked_out_time` `checked_out_time` datetime NULL default NULL;

UPDATE `#__redevent_pricegroups`
   SET `checked_out_time` = NULL WHERE `checked_out_time` = 0;

ALTER TABLE `#__redevent_register`
   CHANGE `checked_out_time` `checked_out_time` datetime NULL default NULL,
   CHANGE `payment_reminder_sent` `payment_reminder_sent` datetime NULL default NULL;

UPDATE `#__redevent_register`
   SET `checked_out_time` = NULL WHERE `checked_out_time` = 0;

UPDATE `#__redevent_register`
   SET `payment_reminder_sent` = NULL WHERE `payment_reminder_sent` = 0;

ALTER TABLE `#__redevent_roles`
   CHANGE `checked_out_time` `checked_out_time` datetime NULL default NULL;

UPDATE `#__redevent_roles`
   SET `checked_out_time` = NULL WHERE `checked_out_time` = 0;

ALTER TABLE `#__redevent_textlibrary`
   CHANGE `checked_out_time` `checked_out_time` datetime NULL default NULL;

UPDATE `#__redevent_textlibrary`
   SET `checked_out_time` = NULL WHERE `checked_out_time` = 0;

ALTER TABLE `#__redevent_venues`
   CHANGE `checked_out_time` `checked_out_time` datetime NULL default NULL;

UPDATE `#__redevent_venues`
   SET `checked_out_time` = NULL WHERE `checked_out_time` = 0;

ALTER TABLE `#__redevent_venues_categories`
   CHANGE `checked_out_time` `checked_out_time` datetime NULL default NULL;

UPDATE `#__redevent_venues_categories`
   SET `checked_out_time` = NULL WHERE `checked_out_time` = 0;

SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
