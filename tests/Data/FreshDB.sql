-- phpMyAdmin SQL Dump
-- version 3.3.7
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: May 24, 2012 at 01:13 PM
-- Server version: 5.1.50
-- PHP Version: 5.3.10

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `visitorchattest`
--

-- --------------------------------------------------------

--
-- Table structure for table `assignments`
--

CREATE TABLE IF NOT EXISTS `assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `conversations_id` int(10) NOT NULL,
  `users_id` int(10) NOT NULL,
  `date_created` datetime NOT NULL,
  `status` enum('PENDING','REJECTED','ACCEPTED','EXPIRED','COMPLETED','LEFT') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'PENDING' COMMENT 'The status of the assignment.',
  `date_updated` datetime DEFAULT NULL,
  `answering_site` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'The site that is answering the chat.',
  `invitations_id` int(11) NOT NULL,
  `date_finished` datetime,
  `date_accepted` datetime,
  `is_typing` ENUM('YES','NO') NOT NULL DEFAULT 'NO',
  PRIMARY KEY (`id`),
  KEY `fk_assignments_users1` (`users_id`),
  KEY `fk_assignments_conversations1` (`conversations_id`),
  KEY `fk_assignments_Invitations1` (`invitations_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `assignments`
--


-- --------------------------------------------------------

--
-- Table structure for table `conversations`
--

CREATE TABLE IF NOT EXISTS `conversations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `users_id` int(10) NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` datetime NOT NULL,
  `date_closed` datetime DEFAULT NULL,
  `initial_url` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'The initial URL of the chat (IE: where the chat started)',
  `initial_pagetitle` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'The page title of the page were the chat was started.',
  `user_agent` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'The user agent of the client when the conversation was started.',
  `status` enum('SEARCHING','OPERATOR_PENDING_APPROVAL','OPERATOR_LOOKUP_FAILED','CHATTING','CLOSED','EMAILED') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'SEARCHING',
  `emailed` int(1) DEFAULT NULL COMMENT '0 - did not fall though to email, 1 - fell though to email.',
  `email_fallback` int(1) DEFAULT NULL,
  `auto_spam` int(1) DEFAULT 0,
  `close_status` ENUM('OPERATOR', 'CLIENT', 'IDLE') NULL ,
  `closer_id` INT NULL ,
  `ip_address` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `method` enum('CHAT','EMAIL') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'CHAT' COMMENT 'The method of the conversation.  Either chat or email, depending on what the user wants.',
  `client_is_typing` ENUM('YES','NO') NOT NULL DEFAULT 'NO',
  PRIMARY KEY (`id`),
  KEY `fk_conversations_users` (`users_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `conversations`
--


-- --------------------------------------------------------

--
-- Table structure for table `invitations`
--

CREATE TABLE IF NOT EXISTS `invitations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `conversations_id` int(11) NOT NULL COMMENT 'the conversation that this invitation belongs to',
  `invitee` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'The (url or person) to invite',
  `status` enum('SEARCHING','FAILED','COMPLETED') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'SEARCHING',
  `date_created` datetime NOT NULL COMMENT 'the date the invitation was created',
  `date_updated` datetime NOT NULL,
  `users_id` int(11) NOT NULL COMMENT 'The id of the user that created the invitation (if applicable)',
  `date_finished` datetime,
  PRIMARY KEY (`id`),
  KEY `fk_Invitations_conversations1` (`conversations_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `invitations`
--


-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE IF NOT EXISTS `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `conversations_id` int(10) NOT NULL,
  `users_id` int(10) NOT NULL COMMENT 'The id of the user account creating the message',
  `date_created` datetime NOT NULL,
  `message` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_messages_users1` (`users_id`),
  KEY `fk_messages_conversations1` (`conversations_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `messages`
--


-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) COLLATE utf8_unicode_ci DEFAULT '',
  `email` varchar(45) COLLATE utf8_unicode_ci DEFAULT '',
  `date_created` datetime DEFAULT NULL,
  `date_updated` datetime DEFAULT NULL,
  `type` enum('operator','client') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Must be either client or operator',
  `uid` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'UNL id to associate accounts',
  `max_chats` int(11) NOT NULL COMMENT 'The max amount of chats that the user (operator) can handle at any given time.',
  `status` enum('AVAILABLE','BUSY') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'BUSY' COMMENT 'Current status.  Set to busy by default.  System will assign chats when set to available\n',
  `last_active` datetime DEFAULT NULL,
  `status_reason` ENUM('USER', 'SERVER_IDLE', 'CLIENT_IDLE', 'EXPIRED_REQUEST', 'NEW_USER', 'MAINTENANCE', 'LOGIN', 'LOGOUT') NULL DEFAULT "USER",
  `popup_notifications` int(1) NULL default 0,
  `alias` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid_UNIQUE` (`uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

-- -----------------------------------------------------
-- Table `emails`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `emails` (
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
    REFERENCES `conversations` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_emails_users1`
    FOREIGN KEY (`users_id` )
    REFERENCES `users` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `user_statuses`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `user_statuses` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `users_id` INT(10) NOT NULL ,
  `date_created` DATETIME NOT NULL ,
  `status` ENUM('AVAILABLE','BUSY') NOT NULL DEFAULT "BUSY" COMMENT 'Current status.  Set to busy by default.  System will assign chats when set to available\n' ,
  `reason` ENUM('USER', 'SERVER_IDLE', 'CLIENT_IDLE', 'EXPIRED_REQUEST', 'NEW_USER', 'MAINTENANCE', 'LOGIN', 'LOGOUT') NULL DEFAULT "USER" ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_user_statuses_users` (`users_id` ASC) ,
  CONSTRAINT `fk_users_statuses_users`
    FOREIGN KEY (`users_id` )
    REFERENCES `users` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE 
)
ENGINE = InnoDB;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `date_created`, `date_updated`, `type`, `uid`, `max_chats`, `status`, `last_active`, `status_reason`, `popup_notifications`) VALUES
(1, 'System', NULL, '2012-05-10 10:06:40', '2012-05-10 10:06:40', 'operator', NULL, 0, 'BUSY', null, 'USER', 0),
(2, 'Operator1', NULL, '2012-05-10 10:06:40', '2012-05-10 10:06:40', 'operator', 'OP1', 2, 'AVAILABLE', null, 'USER', 0),
(3, 'Operator2', NULL, '2012-05-10 10:06:40', '2012-05-10 10:06:40', 'operator', 'OP2', 2, 'AVAILABLE', null, 'USER', 0),
(4, 'Operator3', NULL, '2012-05-10 10:06:40', '2012-05-10 10:06:40', 'operator', 'OP3', 2, 'AVAILABLE', null, 'USER', 0),
(5, 'Operator4', NULL, '2012-05-10 10:06:40', '2012-05-10 10:06:40', 'operator', 'OP4', 2, 'AVAILABLE', null, 'USER', 0),
(6, 'Client1', NULL, '2012-05-10 10:06:40', '2012-05-10 10:06:40', 'client', NULL, 0, 'BUSY', null, 'USER', 0);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assignments`
--
ALTER TABLE `assignments`
  ADD CONSTRAINT `fk_assignments_conversations1` FOREIGN KEY (`conversations_id`) REFERENCES `conversations` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_assignments_Invitations1` FOREIGN KEY (`invitations_id`) REFERENCES `invitations` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_assignments_users1` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `conversations`
--
ALTER TABLE `conversations`
  ADD CONSTRAINT `fk_conversations_users` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_conversations_users1` FOREIGN KEY (`closer_id`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `invitations`
--
ALTER TABLE `invitations`
  ADD CONSTRAINT `fk_Invitations_conversations1` FOREIGN KEY (`conversations_id`) REFERENCES `conversations` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `fk_messages_users1` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_messages_conversations1` FOREIGN KEY (`conversations_id`) REFERENCES `conversations` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;