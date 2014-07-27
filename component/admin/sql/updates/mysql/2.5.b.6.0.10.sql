ALTER TABLE `#__redevent_organizations` DROP column `b2b_admin_notification_mailflow`;
ALTER TABLE `#__redevent_organizations` DROP column `b2b_disable_all_attendee_notifications`;

ALTER TABLE `#__redevent_organizations` ADD column `b2b_attendee_notification_mailflow` tinyint(4) NOT NULL DEFAULT '0';
ALTER TABLE `#__redevent_organizations` ADD column `b2b_attendee_notification_mailflow_orgadmin_confirmation_tag` VARCHAR(30);
ALTER TABLE `#__redevent_organizations` ADD column `b2b_attendee_notification_mailflow_orgadmin_cancellation_tag` VARCHAR(30);
