SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;

ALTER TABLE `#__redevent_bundle`
   CHANGE `alias` `alias` varchar(255) NOT NULL default '';

ALTER TABLE `#__redevent_categories`
   CHANGE `alias` `alias` varchar(255) NOT NULL default '';

ALTER TABLE `#__redevent_events`
   CHANGE `title` `title` varchar(255) NOT NULL default ''
   CHANGE `alias` `alias` varchar(255) NOT NULL default '';

ALTER TABLE `#__redevent_event_template`
   CHANGE `name` `name` varchar(255) NOT NULL default '';

ALTER TABLE `#__redevent_pricegroups`
   CHANGE `name` `name` varchar(255) NOT NULL default ''
   CHANGE `alias` `alias` varchar(255) NOT NULL default '';

ALTER TABLE `#__redevent_roles`
   CHANGE `name` `name` varchar(255) NOT NULL default '';

ALTER TABLE `#__redevent_venues`
   CHANGE `venue` `venue` varchar(255) NOT NULL default ''
   CHANGE `alias` `alias` varchar(255) NOT NULL default '';

ALTER TABLE `#__redevent_venues_categories`
   CHANGE `name` `name` varchar(255) NOT NULL default ''
   CHANGE `alias` `alias` varchar(255) NOT NULL default '';

SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
