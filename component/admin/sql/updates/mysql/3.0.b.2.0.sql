ALTER TABLE `#__redevent_sessions_pricegroups` CHANGE `price` `price` DECIMAL(10,2) NOT NULL default '0',
	ADD `vatrate` DECIMAL(10,2) NOT NULL default '0';
