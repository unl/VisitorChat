UNLchat
---------------

UNLchat is a custom live chat solution built for UNL.  It is designed to be work for a large organization with many sub-organizations such as a university.

When a chat request comes in, the chat is routed to the operators of closest site (as defined by a registry service) from where the chat originated.
For example, if a chat was started at http://www.unl.edu/WDN/index.php the chat would be routed to operators for http://www.unl.edu/WDN/.

The system aims to bring advanced functionality of a live chat solution while keeping the interface clean and intuitive.

Requirements
------------
 - PHP 5+
 - MySQL 5+
 - Apache

Install
-------
 - Clone this repository
 - Create a database and user in MySQL for the application.
 - Copy config.sample.php to config.inc.php.
 - Edit config.inc.php to fit your own environment.
   - Follow the instructions inside the config.inc.php file to configure your site.
 - From command line run scripts/install.php (This will install the database)
 - Copy the www/sample.htaccess to www/.htaccess
 - Edit www/.htaccess to fit your own environment.
