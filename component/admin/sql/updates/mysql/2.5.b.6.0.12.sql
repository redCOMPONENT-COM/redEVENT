ALTER TABLE `#__redevent_organizations` ADD column `b2b_orgadmin_mailflow_confirmation_subject_tag` VARCHAR(30);
ALTER TABLE `#__redevent_organizations` ADD column `b2b_orgadmin_mailflow_cancellation_subject_tag` VARCHAR(30);
ALTER TABLE `#__redevent_organizations`
	CHANGE column `b2b_attendee_notification_mailflow_orgadmin_confirmation_tag` `b2b_orgadmin_mailflow_confirmation_body_tag` VARCHAR(30);
ALTER TABLE `#__redevent_organizations`
	CHANGE column `b2b_attendee_notification_mailflow_orgadmin_cancellation_tag` `b2b_orgadmin_mailflow_cancellation_body_tag` VARCHAR(30);
