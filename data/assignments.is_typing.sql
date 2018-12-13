ALTER TABLE `assignments` ADD COLUMN `is_typing` ENUM('YES','NO') NOT NULL DEFAULT 'NO' COMMENT 'Is this person currently typing for this conversation';

ALTER TABLE `conversations` ADD COLUMN `client_is_typing` ENUM('YES','NO') NOT NULL DEFAULT 'NO' COMMENT 'Is this client currently typing for this conversation';
