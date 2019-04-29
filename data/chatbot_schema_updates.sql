ALTER TABLE conversations MODIFY method enum('CHAT','CHATBOT','EMAIL');

insert into users (name, date_created, date_updated, max_chats) values ('UNLChatbot', now(), now(), 0);

