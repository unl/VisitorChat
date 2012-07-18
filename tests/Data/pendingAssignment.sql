-- phpMyAdmin SQL Dump
-- version 3.3.7
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: May 24, 2012 at 01:10 PM
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
  PRIMARY KEY (`id`),
  KEY `fk_assignments_users1` (`users_id`),
  KEY `fk_assignments_conversations1` (`conversations_id`),
  KEY `fk_assignments_Invitations1` (`invitations_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Dumping data for table `assignments`
--

INSERT INTO `assignments` (`id`, `conversations_id`, `users_id`, `date_created`, `status`, `date_updated`, `answering_site`, `invitations_id`, `date_finished`, `date_accepted`) VALUES
(1, 1, 2, '0000-00-00 00:00:00', 'PENDING', '2012-05-24 13:09:43', 'unl.edu', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00');

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
  `close_status` ENUM('OPERATOR', 'CLIENT', 'IDLE') NULL ,
  `closer_id` INT NULL ,
  `method` enum('CHAT','EMAIL') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'CHAT' COMMENT 'The method of the conversation.  Either chat or email, depending on what the user wants.',
  PRIMARY KEY (`id`),
  KEY `fk_conversations_users` (`users_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `conversations`
--

INSERT INTO `conversations` (`id`, `users_id`, `date_created`, `date_updated`, `date_closed`, `initial_url`, `initial_pagetitle`, `user_agent`, `status`, `emailed`, `email_fallback`, `method`) VALUES
(1, 3, '2012-05-14 14:46:01', '2012-05-14 14:46:01', NULL, 'www.visitorchattest.com', 'Visitor Chat Test', NULL, 'CHATTING', NULL, NULL, 'CHAT');

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
  KEY `fk_Invitations_conversations1` (`conversations_id`),
  KEY `fk_invitations_users1` (`users_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Dumping data for table `invitations`
--

INSERT INTO `invitations` (`id`, `conversations_id`, `invitee`, `status`, `date_created`, `date_updated`, `users_id`, `date_finished`) VALUES
(1, 1, 'test_operator', 'SEARCHING', '2012-05-24 13:09:43', '2012-05-24 13:09:43', 1, '0000-00-00 00:00:00');

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
  `ip` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_updated` datetime DEFAULT NULL,
  `type` enum('operator','client') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Must be either client or operator',
  `uid` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'UNL id to associate accounts',
  `max_chats` int(11) NOT NULL COMMENT 'The max amount of chats that the user (operator) can handle at any given time.',
  `status` enum('AVAILABLE','BUSY') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'BUSY' COMMENT 'Current status.  Set to busy by default.  System will assign chats when set to available\n',
  `Invitations_id` int(11) DEFAULT NULL,
  `last_active` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid_UNIQUE` (`uid`),
  KEY `fk_users_Invitations1` (`Invitations_id`)
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

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `ip`, `date_created`, `date_updated`, `type`, `uid`, `max_chats`, `status`) VALUES
(1, 'System', NULL, NULL, '2012-05-14 14:44:02', '2012-05-14 14:44:02', 'operator', NULL, 0, 'BUSY'),
(2, 'Test Operator', '', NULL, '2012-05-14 14:44:33', '2012-05-14 14:44:33', NULL, 'test_operator', 3, 'AVAILABLE'),
(3, 'test client', '', NULL, '2012-05-14 14:45:17', '2012-05-14 14:45:17', 'client', NULL, 0, 'BUSY');

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
  ADD CONSTRAINT `fk_conversations_users1` FOREIGN KEY (`closer_id`) REFERENCES `visitorchatapp`.`users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `invitations`
--
ALTER TABLE `invitations`
  ADD CONSTRAINT `fk_Invitations_conversations1` FOREIGN KEY (`conversations_id`) REFERENCES `conversations` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_invitations_users1` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `fk_messages_users1` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_messages_conversations1` FOREIGN KEY (`conversations_id`) REFERENCES `conversations` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
