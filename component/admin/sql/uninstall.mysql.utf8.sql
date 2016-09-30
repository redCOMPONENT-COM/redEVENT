SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS
	`#__redevent_attachments`,
	`#__redevent_categories`,
	`#__redevent_countries`,
	`#__redevent_event_category_xref`,
	`#__redevent_event_venue_xref`,
	`#__redevent_events`,
	`#__redevent_fields`,
	`#__redevent_organizations`,
	`#__redevent_pricegroups`,
	`#__redevent_recurrences`,
	`#__redevent_register`,
	`#__redevent_repeats`,
	`#__redevent_roles`,
	`#__redevent_roles_redmember`,
	`#__redevent_sessions_pricegroups`,
	`#__redevent_sessions_roles`,
	`#__redevent_textlibrary`,
	`#__redevent_venue_category_xref`,
	`#__redevent_venues`,
	`#__redevent_venues_categories`
	;

SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
