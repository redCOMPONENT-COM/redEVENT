ALTER TABLE `#__redevent_sessions_pricegroups` ADD `currency` VARCHAR(10) NOT NULL;
ALTER TABLE `#__redevent_register` ADD `sessionpricegroup_id` int(11) NOT NULL default '0';

UPDATE #__redevent_register AS r
	INNER JOIN #__redevent_sessions_pricegroups AS sp ON sp.pricegroup_id = r.pricegroup_id AND sp.xref = r.xref
	SET r.sessionpricegroup_id = sp.id;
