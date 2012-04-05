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
  PRIMARY KEY (`id`) ,
  INDEX `fk_conversations_users` (`users_id` ASC) ,
  CONSTRAINT `fk_conversations_users`
    FOREIGN KEY (`users_id` )
    REFERENCES `visitorchatapp`.`users` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
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
-- Table `visitorchatapp`.`assignments`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `visitorchatapp`.`assignments` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `conversations_id` INT(10) NOT NULL ,
  `users_id` INT(10) NOT NULL ,
  `date_created` DATETIME NOT NULL ,
  `status` ENUM('PENDING','REJECTED','ACCEPTED','EXPIRED','COMPLETED','LEFT') NOT NULL DEFAULT 'PENDING' COMMENT 'The status of the assignment.' ,
  `date_updated` DATETIME NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_assignments_users1` (`users_id` ASC) ,
  INDEX `fk_assignments_conversations1` (`conversations_id` ASC) ,
  CONSTRAINT `fk_assignments_users1`
    FOREIGN KEY (`users_id` )
    REFERENCES `visitorchatapp`.`users` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_assignments_conversations1`
    FOREIGN KEY (`conversations_id` )
    REFERENCES `visitorchatapp`.`conversations` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;



SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
