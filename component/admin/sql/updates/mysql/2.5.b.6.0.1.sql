ALTER TABLE `#__redevent_categories` ADD `asset_id` INT(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__redevent_venues` ADD `asset_id` INT(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__redevent_venues_categories` ADD `asset_id` INT(10) NOT NULL DEFAULT '0';

ALTER TABLE `#__redevent_venues` ADD `access` INT(10) NOT NULL DEFAULT '1';
