/* add indexes to the assignments table */
ALTER TABLE `assignments` ADD INDEX `index_date_created` (`date_created`);
ALTER TABLE `assignments` ADD INDEX `index_status` (`status`);

/* add indexes to the conversations table */
ALTER TABLE `conversations` ADD INDEX `index_date_created` (`date_created`);
ALTER TABLE `conversations` ADD INDEX `index_status` (`status`);
ALTER TABLE `conversations` ADD INDEX `index_method` (`method`);

/* add indexes to the emails table */
ALTER TABLE `emails` ADD INDEX `index_date_sent` (`date_sent`);

/* add indexes to the invitations table */
ALTER TABLE `invitations` ADD INDEX `index_date_created` (`date_created`);
ALTER TABLE `invitations` ADD INDEX `index_status` (`status`);
ALTER TABLE `invitations` ADD INDEX `index_invitee` (`invitee`);

/* add indexes to the messages table */
ALTER TABLE `messages` ADD INDEX `index_date_created` (`date_created`);

/* add indexes to the users table */
ALTER TABLE `users` ADD INDEX `index_date_created` (`date_created`);
ALTER TABLE `users` ADD INDEX `index_status` (`status`);
ALTER TABLE `users` ADD INDEX `index_type` (`type`);
ALTER TABLE `users` ADD INDEX `index_last_active` (`last_active`);

/* add indexes to the users_statuses table */
ALTER TABLE `users_statuses` ADD INDEX `index_date_created` (`date_created`);
ALTER TABLE `users_statuses` ADD INDEX `index_status` (`status`);
ALTER TABLE `users_statuses` ADD INDEX `index_reason` (`reason`);
