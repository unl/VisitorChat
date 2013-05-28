SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

CREATE SCHEMA IF NOT EXISTS `visitorchatapp` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ;
USE `visitorchatapp` ;

-- -----------------------------------------------------
-- Table `visitorchatapp`.`users`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `visitorchatapp`.`users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(45) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL DEFAULT '' ,
  `email` VARCHAR(45) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL DEFAULT '' ,
  `date_created` DATETIME NULL DEFAULT NULL ,
  `date_updated` DATETIME NULL DEFAULT NULL ,
  `type` ENUM('operator','client') CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL DEFAULT NULL COMMENT 'Must be either client or operator' ,
  `uid` VARCHAR(45) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL DEFAULT NULL COMMENT 'UNL id to associate accounts' ,
  `max_chats` INT(11) NOT NULL COMMENT 'The max amount of chats that the user (operator) can handle at any given time.' ,
  `status` ENUM('AVAILABLE','BUSY') CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL DEFAULT 'BUSY' COMMENT 'Current status.  Set to busy by default.  System will assign chats when set to available\n' ,
  `last_active` DATETIME NULL DEFAULT NULL ,
  `status_reason` ENUM('USER', 'SERVER_IDLE', 'CLIENT_IDLE', 'EXPIRED_REQUEST', 'NEW_USER', 'MAINTENANCE', 'LOGIN', 'LOGOUT') CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL DEFAULT 'USER' ,
  `popup_notifications` INT(1) NULL DEFAULT '0' ,
  `alias` VARCHAR(64) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL DEFAULT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `uid_UNIQUE` (`uid` ASC) ,
  INDEX `index_date_created` (`date_created` ASC) ,
  INDEX `index_status` (`status` ASC) ,
  INDEX `index_type` (`type` ASC) ,
  INDEX `index_last_active` (`last_active` ASC) )
ENGINE = InnoDB
AUTO_INCREMENT = 712
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `visitorchatapp`.`conversations`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `visitorchatapp`.`conversations` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `users_id` INT(10) NOT NULL ,
  `date_created` DATETIME NOT NULL ,
  `date_updated` DATETIME NOT NULL ,
  `date_closed` DATETIME NULL DEFAULT NULL ,
  `initial_url` VARCHAR(128) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL DEFAULT NULL COMMENT 'The initial URL of the chat (IE: where the chat started)' ,
  `initial_pagetitle` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL COMMENT 'The page title of the page were the chat was started.' ,
  `user_agent` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL DEFAULT NULL COMMENT 'The user agent of the client when the conversation was started.' ,
  `status` ENUM('SEARCHING','OPERATOR_PENDING_APPROVAL','OPERATOR_LOOKUP_FAILED','CHATTING','CLOSED','EMAILED','CAPTCHA') CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL DEFAULT NULL ,
  `emailed` INT(1) NULL DEFAULT NULL COMMENT '0 - did not fall though to email, 1 - fell though to email.' ,
  `email_fallback` INT(1) NULL DEFAULT NULL ,
  `method` ENUM('CHAT','EMAIL') CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL DEFAULT 'CHAT' COMMENT 'The method of the conversation.  Either chat or email, depending on what the user wants.' ,
  `close_status` ENUM('CLIENT','OPERATOR','IDLE') CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL DEFAULT NULL ,
  `closer_id` INT(11) NULL DEFAULT NULL ,
  `auto_spam` INT(1) NULL DEFAULT '0' ,
  `ip_address` VARCHAR(45) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL DEFAULT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_conversations_users` (`users_id` ASC) ,
  INDEX `closer_id` (`closer_id` ASC) ,
  INDEX `index_date_created` (`date_created` ASC) ,
  INDEX `index_status` (`status` ASC) ,
  INDEX `index_method` (`method` ASC) ,
  CONSTRAINT `conversations_ibfk_1`
    FOREIGN KEY (`closer_id` )
    REFERENCES `visitorchatapp`.`users` (`id` ),
  CONSTRAINT `fk_conversations_users`
    FOREIGN KEY (`users_id` )
    REFERENCES `visitorchatapp`.`users` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
AUTO_INCREMENT = 700
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `visitorchatapp`.`invitations`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `visitorchatapp`.`invitations` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `conversations_id` INT(11) NOT NULL COMMENT 'the conversation that this invitation belongs to' ,
  `invitee` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL COMMENT 'The (url or person) to invite' ,
  `status` ENUM('SEARCHING','FAILED','COMPLETED') CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL DEFAULT 'SEARCHING' ,
  `date_created` DATETIME NOT NULL COMMENT 'the date the invitation was created' ,
  `date_updated` DATETIME NOT NULL ,
  `users_id` INT(11) NOT NULL COMMENT 'The id of the user that created the invitation (if applicable)' ,
  `date_finished` DATETIME NULL DEFAULT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_Invitations_conversations1` (`conversations_id` ASC) ,
  INDEX `fk_invitations_users1` (`users_id` ASC) ,
  INDEX `index_date_created` (`date_created` ASC) ,
  INDEX `index_status` (`status` ASC) ,
  INDEX `index_invitee` (`invitee` ASC) ,
  CONSTRAINT `fk_Invitations_conversations1`
    FOREIGN KEY (`conversations_id` )
    REFERENCES `visitorchatapp`.`conversations` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_invitations_users1`
    FOREIGN KEY (`users_id` )
    REFERENCES `visitorchatapp`.`users` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 486
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `visitorchatapp`.`assignments`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `visitorchatapp`.`assignments` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `conversations_id` INT(10) NOT NULL ,
  `users_id` INT(10) NOT NULL ,
  `date_created` DATETIME NOT NULL ,
  `status` ENUM('PENDING','REJECTED','ACCEPTED','EXPIRED','COMPLETED','LEFT','FAILED') CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL DEFAULT 'PENDING' COMMENT 'The status of the assignment.' ,
  `date_updated` DATETIME NULL DEFAULT NULL ,
  `answering_site` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL COMMENT 'The site that is answering the chat.' ,
  `invitations_id` INT(11) NOT NULL ,
  `date_finished` DATETIME NULL DEFAULT NULL ,
  `date_accepted` DATETIME NULL DEFAULT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_assignments_users1` (`users_id` ASC) ,
  INDEX `fk_assignments_conversations1` (`conversations_id` ASC) ,
  INDEX `fk_assignments_Invitations1` (`invitations_id` ASC) ,
  INDEX `id` (`id` ASC) ,
  INDEX `index_date_created` (`date_created` ASC) ,
  INDEX `index_status` (`status` ASC) ,
  CONSTRAINT `fk_assignments_conversations1`
    FOREIGN KEY (`conversations_id` )
    REFERENCES `visitorchatapp`.`conversations` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_assignments_Invitations1`
    FOREIGN KEY (`invitations_id` )
    REFERENCES `visitorchatapp`.`invitations` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_assignments_users1`
    FOREIGN KEY (`users_id` )
    REFERENCES `visitorchatapp`.`users` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
AUTO_INCREMENT = 456
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `visitorchatapp`.`emails`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `visitorchatapp`.`emails` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `to` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL ,
  `from` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL ,
  `subject` TEXT CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL ,
  `date_sent` DATETIME NOT NULL ,
  `conversations_id` INT(11) NOT NULL ,
  `reply_to` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL ,
  `users_id` INT(11) NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_emails_conversations1` (`conversations_id` ASC) ,
  INDEX `fk_emails_users1` (`users_id` ASC) ,
  INDEX `index_date_sent` (`date_sent` ASC) ,
  CONSTRAINT `fk_emails_conversations1`
    FOREIGN KEY (`conversations_id` )
    REFERENCES `visitorchatapp`.`conversations` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_emails_users1`
    FOREIGN KEY (`users_id` )
    REFERENCES `visitorchatapp`.`users` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 390
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `visitorchatapp`.`messages`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `visitorchatapp`.`messages` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `conversations_id` INT(10) NOT NULL ,
  `users_id` INT(10) NOT NULL COMMENT 'The id of the user account creating the message' ,
  `date_created` DATETIME NOT NULL ,
  `message` MEDIUMTEXT CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_messages_users1` (`users_id` ASC) ,
  INDEX `fk_messages_conversations1` (`conversations_id` ASC) ,
  INDEX `index_date_created` (`date_created` ASC) ,
  CONSTRAINT `fk_messages_conversations1`
    FOREIGN KEY (`conversations_id` )
    REFERENCES `visitorchatapp`.`conversations` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_messages_users1`
    FOREIGN KEY (`users_id` )
    REFERENCES `visitorchatapp`.`users` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
AUTO_INCREMENT = 1054
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `visitorchatapp`.`user_statuses`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `visitorchatapp`.`user_statuses` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `users_id` INT(10) NOT NULL ,
  `date_created` DATETIME NOT NULL ,
  `status` ENUM('AVAILABLE','BUSY') CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL DEFAULT 'BUSY' COMMENT 'Current status.  Set to busy by default.  System will assign chats when set to available\n' ,
  `reason` ENUM('USER', 'SERVER_IDLE', 'CLIENT_IDLE', 'EXPIRED_REQUEST', 'NEW_USER', 'MAINTENANCE', 'LOGIN', 'LOGOUT') CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL DEFAULT 'USER' ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_user_statuses_users` (`users_id` ASC) ,
  CONSTRAINT `fk_users_statuses_users`
    FOREIGN KEY (`users_id` )
    REFERENCES `visitorchatapp`.`users` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
AUTO_INCREMENT = 53
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `visitorchatapp`.`blocked_ips`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `visitorchatapp`.`blocked_ips` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `ip_address` VARCHAR(45) NOT NULL ,
  `users_id` INT NOT NULL ,
  `block_start` DATETIME NOT NULL ,
  `block_end` DATETIME NOT NULL ,
  `status` ENUM('ENABLED', 'DISABLED') NOT NULL DEFAULT 'ENABLED' ,
  `date_created` DATETIME NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_blocked_ips_users1` (`users_id` ASC) ,
  CONSTRAINT `fk_blocked_ips_users1`
    FOREIGN KEY (`users_id` )
    REFERENCES `visitorchatapp`.`users` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;



SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
