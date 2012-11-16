ALTER TABLE `#__redevent_categories` ADD `language` VARCHAR( 7 ) NOT NULL;
ALTER TABLE `#__redevent_venues` ADD `language` VARCHAR( 7 ) NOT NULL;
ALTER TABLE `#__redevent_events` ADD `language` VARCHAR( 7 ) NOT NULL;
ALTER TABLE `#__redevent_roles` ADD `language` VARCHAR( 7 ) NOT NULL;
ALTER TABLE `#__redevent_pricegroups` ADD `language` VARCHAR( 7 ) NOT NULL;
ALTER TABLE `#__redevent_venues_categories` ADD `language` VARCHAR( 7 ) NOT NULL;
ALTER TABLE `#__redevent_fields` ADD `language` VARCHAR( 7 ) NOT NULL;

ALTER TABLE `#__redevent_categories` ADD INDEX `idx_language` (`language`);
ALTER TABLE `#__redevent_venues` ADD INDEX `idx_language` (`language`);
ALTER TABLE `#__redevent_events` ADD INDEX `idx_language` (`language`);
ALTER TABLE `#__redevent_roles` ADD INDEX `idx_language` (`language`);
ALTER TABLE `#__redevent_pricegroups` ADD INDEX `idx_language` (`language`);
ALTER TABLE `#__redevent_venues_categories` ADD INDEX `idx_language` (`language`);
ALTER TABLE `#__redevent_fields` ADD INDEX `idx_language` (`language`);
