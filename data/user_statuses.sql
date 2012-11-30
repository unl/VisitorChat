-- -----------------------------------------------------
-- Table `visitorchatapp`.`user_statuses`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `visitorchatapp`.`user_statuses` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `users_id` INT(10) NOT NULL ,
  `date_created` DATETIME NOT NULL ,
  `status` ENUM('AVAILABLE','BUSY') NOT NULL DEFAULT "BUSY" COMMENT 'Current status.  Set to busy by default.  System will assign chats when set to available\n' ,
  `status_reason` ENUM('USER', 'SERVER_IDLE', 'CLIENT_IDLE', 'EXPIRED_REQUEST') NULL DEFAULT "USER" ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_user_statuses_users` (`users_id` ASC) ,
  CONSTRAINT `fk_users_statuses_users`
    FOREIGN KEY (`users_id` )
    REFERENCES `visitorchatapp`.`users` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE 
)
ENGINE = InnoDB;