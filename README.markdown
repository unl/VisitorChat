UNL VisitorChat
---------------

This app will be integrated into the UNL templates.  The goal is to allow end users to chat with site support for whatever site they are viewing.  The chat system is linked with the WDN Registry so that it knows who to route chat requests to.

Install
-------
 - Clone this repository
 - Create a database and user in MySQL for the application.
 - Copy config.sample.php to config.inc.php.
 - Edit config.inc.php to fit your own environment.
   - Database Connection
   - Site URL
   - A unique session key
   - Any default operators (these are the people the chat will fall though to if no one else can be found)
 - From command line run scripts/install.php (This will install the database)
