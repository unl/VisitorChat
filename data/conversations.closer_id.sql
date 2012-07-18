ALTER TABLE `conversations` ADD `closer_id` int NULL;

ALTER TABLE conversations
ADD FOREIGN KEY (closer_id)
REFERENCES users(id);