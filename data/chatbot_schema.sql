
-- Chatbot Schema

CREATE TABLE IF NOT EXISTS `chatbots` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `active` int(1) NOT NULL DEFAULT 0,
  `name` varchar(100) NOT NULL,
  `description` text NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`user_id`),
  UNIQUE KEY (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;


CREATE TABLE IF NOT EXISTS `site_chatbot` (
  `site_url` varchar(255) NOT NULL,
  `chatbot_id` int(11) NOT NULL,
  `active` int(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`site_url`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;


INSERT INTO `chatbots` (`user_id`, `active`, `name`, `description`) VALUES (38, 1, 'UNLChatbot', 'UNLChatbot Prototype');

INSERT INTO `site_chatbot` (`site_url`, `chatbot_id`, `active`) VALUES ('http://iimjsturek.unl.edu/', 1, 1);