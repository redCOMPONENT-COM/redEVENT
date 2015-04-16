ALTER TABLE `#__redevent_categories` ADD `language` VARCHAR( 7 ) NOT NULL;
ALTER TABLE `#__redevent_venues` ADD `language` VARCHAR( 7 ) NOT NULL;
ALTER TABLE `#__redevent_events` ADD `language` VARCHAR( 7 ) NOT NULL;
ALTER TABLE `#__redevent_roles` ADD `language` VARCHAR( 7 ) NOT NULL;
ALTER TABLE `#__redevent_pricegroups` ADD `language` VARCHAR( 7 ) NOT NULL;
ALTER TABLE `#__redevent_venues_categories` ADD `language` VARCHAR( 7 ) NOT NULL;
ALTER TABLE `#__redevent_fields` ADD `language` VARCHAR( 7 ) NOT NULL;
ALTER TABLE `#__redevent_textlibrary` ADD `language` VARCHAR( 7 ) NOT NULL;

ALTER TABLE `#__redevent_categories` ADD INDEX `idx_language` (`language`);
ALTER TABLE `#__redevent_venues` ADD INDEX `idx_language` (`language`);
ALTER TABLE `#__redevent_events` ADD INDEX `idx_language` (`language`);
ALTER TABLE `#__redevent_roles` ADD INDEX `idx_language` (`language`);
ALTER TABLE `#__redevent_pricegroups` ADD INDEX `idx_language` (`language`);
ALTER TABLE `#__redevent_venues_categories` ADD INDEX `idx_language` (`language`);
ALTER TABLE `#__redevent_fields` ADD INDEX `idx_language` (`language`);
ALTER TABLE `#__redevent_textlibrary` ADD INDEX `idx_language` (`language`);

UPDATE `#__redevent_categories` SET language = '*';
UPDATE `#__redevent_venues` SET language = '*';
UPDATE `#__redevent_events` SET language = '*';
UPDATE `#__redevent_roles` SET language = '*';
UPDATE `#__redevent_pricegroups` SET language = '*';
UPDATE `#__redevent_venues_categories` SET language = '*';
UPDATE `#__redevent_fields` SET language = '*';
UPDATE `#__redevent_textlibrary` SET language = '*';