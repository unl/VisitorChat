ALTER TABLE conversations ADD ip_address VARCHAR(45) NULL;
UPDATE conversations SET ip_address = (SELECT ip FROM users WHERE conversations.users_id = users.id);
ALTER TABLE users DROP COLUMN ip;