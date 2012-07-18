SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

CREATE SCHEMA IF NOT EXISTS `visitorchatapp` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;
USE `visitorchatapp` ;

-- -----------------------------------------------------
-- Table `visitorchatapp`.`users`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `visitorchatapp`.`users` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(45) NULL DEFAULT '' ,
  `email` VARCHAR(45) NULL DEFAULT '' ,
  `ip` VARCHAR(45) NULL ,
  `date_created` DATETIME NULL ,
  `date_updated` DATETIME NULL ,
  `type` ENUM('operator','client') NULL COMMENT 'Must be either client or operator' ,
  `uid` VARCHAR(45) NULL COMMENT 'UNL id to associate accounts' ,
  `max_chats` INT NOT NULL COMMENT 'The max amount of chats that the user (operator) can handle at any given time.' ,
  `status` ENUM('AVAILABLE','BUSY') NOT NULL DEFAULT "BUSY" COMMENT 'Current status.  Set to busy by default.  System will assign chats when set to available\n' ,
  `last_active` DATETIME NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `uid_UNIQUE` (`uid` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `visitorchatapp`.`conversations`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `visitorchatapp`.`conversations` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `users_id` INT(10) NOT NULL ,
  `date_created` DATETIME NOT NULL ,
  `date_updated` DATETIME NOT NULL ,
  `date_closed` DATETIME NULL ,
  `initial_url` VARCHAR(128) NULL COMMENT 'The initial URL of the chat (IE: where the chat started)' ,
  `initial_pagetitle` VARCHAR(255) NOT NULL COMMENT 'The page title of the page were the chat was started.' ,
  `user_agent` VARCHAR(255) NULL COMMENT 'The user agent of the client when the conversation was started.' ,
  `status` ENUM('SEARCHING','OPERATOR_PENDING_APPROVAL','OPERATOR_LOOKUP_FAILED','CHATTING','CLOSED','EMAILED') NOT NULL DEFAULT 'SEARCHING' ,
  `emailed` INT(1) NULL COMMENT '0 - did not fall though to email, 1 - fell though to email.' ,
  `email_fallback` INT(1) NULL ,
  `method` ENUM('CHAT', 'EMAIL') NOT NULL DEFAULT 'CHAT' COMMENT 'The method of the conversation.  Either chat or email, depending on what the user wants.' ,
  `close_status` ENUM('OPERATOR', 'CLIENT', 'IDLE') NULL ,
  `closer_id` INT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_conversations_users` (`users_id` ASC) ,
  INDEX `fk_conversations_users1` (`closer_id` ASC) ,
  CONSTRAINT `fk_conversations_users`
    FOREIGN KEY (`users_id` )
    REFERENCES `visitorchatapp`.`users` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_conversations_users1`
    FOREIGN KEY (`closer_id` )
    REFERENCES `visitorchatapp`.`users` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `visitorchatapp`.`messages`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `visitorchatapp`.`messages` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `conversations_id` INT(10) NOT NULL ,
  `users_id` INT(10) NOT NULL COMMENT 'The id of the user account creating the message' ,
  `date_created` DATETIME NOT NULL ,
  `message` MEDIUMTEXT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_messages_users1` (`users_id` ASC) ,
  INDEX `fk_messages_conversations1` (`conversations_id` ASC) ,
  CONSTRAINT `fk_messages_users1`
    FOREIGN KEY (`users_id` )
    REFERENCES `visitorchatapp`.`users` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_messages_conversations1`
    FOREIGN KEY (`conversations_id` )
    REFERENCES `visitorchatapp`.`conversations` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `visitorchatapp`.`invitations`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `visitorchatapp`.`invitations` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `conversations_id` INT NOT NULL COMMENT 'the conversation that this invitation belongs to' ,
  `invitee` VARCHAR(255) NOT NULL COMMENT 'The (url or person) to invite' ,
  `status` ENUM('SEARCHING','FAILED','COMPLETED') NOT NULL DEFAULT 'SEARCHING' ,
  `date_created` DATETIME NOT NULL COMMENT 'the date the invitation was created' ,
  `date_updated` DATETIME NOT NULL ,
  `users_id` INT NOT NULL COMMENT 'The id of the user that created the invitation (if applicable)' ,
  `date_finished` DATETIME NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_Invitations_conversations1` (`conversations_id` ASC) ,
  INDEX `fk_invitations_users1` (`users_id` ASC) ,
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
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `visitorchatapp`.`assignments`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `visitorchatapp`.`assignments` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `conversations_id` INT(10) NOT NULL ,
  `users_id` INT(10) NOT NULL ,
  `date_created` DATETIME NOT NULL ,
  `status` ENUM('PENDING','REJECTED','ACCEPTED','EXPIRED','COMPLETED','LEFT') NOT NULL DEFAULT 'PENDING' COMMENT 'The status of the assignment.' ,
  `date_updated` DATETIME NULL ,
  `answering_site` VARCHAR(255) NOT NULL COMMENT 'The site that is answering the chat.' ,
  `invitations_id` INT NOT NULL ,
  `date_finished` DATETIME NULL ,
  `date_accepted` DATETIME NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_assignments_users1` (`users_id` ASC) ,
  INDEX `fk_assignments_conversations1` (`conversations_id` ASC) ,
  INDEX `fk_assignments_Invitations1` (`invitations_id` ASC) ,
  CONSTRAINT `fk_assignments_users1`
    FOREIGN KEY (`users_id` )
    REFERENCES `visitorchatapp`.`users` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_assignments_conversations1`
    FOREIGN KEY (`conversations_id` )
    REFERENCES `visitorchatapp`.`conversations` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_assignments_Invitations1`
    FOREIGN KEY (`invitations_id` )
    REFERENCES `visitorchatapp`.`invitations` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `visitorchatapp`.`emails`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `visitorchatapp`.`emails` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `to` VARCHAR(255) NOT NULL ,
  `from` VARCHAR(255) NOT NULL ,
  `subject` TEXT NOT NULL ,
  `date_sent` DATETIME NOT NULL ,
  `conversations_id` INT NOT NULL ,
  `reply_to` VARCHAR(255) NOT NULL ,
  `users_id` INT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_emails_conversations1` (`conversations_id` ASC) ,
  INDEX `fk_emails_users1` (`users_id` ASC) ,
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
ENGINE = InnoDB;



SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
