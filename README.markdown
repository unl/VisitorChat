UNLchat
---------------

UNLchat is a custom live chat solution built for UNL.  It is designed for a large organization with many sub-organizations such as a university.

When a chat request comes in, the chat is routed to the operators of closest site (as defined by a registry service) from where the chat originated.
For example, if a chat was started at http://www.unl.edu/WDN/index.php the chat would be routed to operators for http://www.unl.edu/WDN/.

The system aims to bring advanced functionality of a live chat solution while keeping the interface clean and intuitive.

The system also supports integration with AWS Lex chatbot. A site will always default to the live chat if an operator is available.  If chatbot is enable for a site url and no operator is available it will behave like live chat but interact with specified AWS Lex chatbot.

Requirements
------------
 - PHP 5.3+
 - MySQL 5+
 - Apache

Install
-------
 - Clone this repository
 - Run `composer install`
 - Create a database named `visitorchatapp` and user in MySQL for the application.
 - Copy config.sample.php to config.inc.php.
 - Edit config.inc.php to fit your own environment.
   - Follow the instructions inside the config.inc.php file to configure your site.
 - From command line run `php scripts/install.php` (This will install the database)
 - Copy the www/sample.htaccess to www/.htaccess and edit www/.htaccess to fit your own environment.
 - Download the "5.3 Dependents Only" from https://wdn.unl.edu/starting-site and put the wdn folder at `www/wdn`, or build the project from https://github.com/unl/wdntemplates and symlink the wdn folder.

Editing CSS Files
-----------------
 - `cd www; npm ci`
 - Edit files in www/less then run `grunt` in www

Using and Testing Your Dev Environment
-------------------------------------------------
You need a UNL website that is running the UNLedu Web Framework with your dev chat system URL built into it rather than the production ucommchat.unl.edu.

 - You'll need a unl.edu domain registered and pointed at your machine like yourname.unl.edu.
 - Register yourname.unl.edu at https://webaudit.unl.edu/
 - (An alternative to the above two steps would be to run a local version of https://github.com/UNLSiteMaster - or contact the programmer behind WebAudit and have them add yourname.unl.edu to WebAudit then set `127.0.0.1 yourname.unl.edu` in `/etc/hosts`.)
 - Build https://github.com/unl/wdntemplates with `grunt --rjs-flags="unlChatURl=//YOUR_CHAT_DEV_URL/assets/js"` replacing YOUR_CHAT_DEV_URL.
 - Login to your dev chat install, then in a separate browser visit http://yourname.unl.edu/wdntemplates/debug.shtml and you should be able to chat with yourself.

Chatbot Management
------------------
Two tables control whether a site url has chatbot as an option:
1. chatbots - defines the AWS chatbots available. The name must match the AWS Lex chatbot name.  There is also a chatbot alias which currently is chatbot name and either TEST or PROD version. The botalias used is controlled by the chatbotEnv variable defined in Client.js.php.  It should always be PROD unless in test environment and you need to point to test version of chatbot.
2. chatbot_sites - defines which sites have a chatbot and which chatbot.  The system currently only supports on chatbot per site url.

Access to the AWS chatbot is controlled via AWS Cognito.  This is currently hardcoded in the Client.js.php file.  The correct region and creditials need to be defined to allow access to the AWS Chatbot.

The AWS javascript need to run the chatbot is currently in aws-sdk-2.493.0.min.js and is referenced in Client.js.php.  This file was generated at https://sdk.amazonaws.com/builder/js/# and may need to be manually updated from time to time to get the current AWS SDK code to run the chatbot.  The only AWS services needed are AWS.CongnitoIdentity and AWS.LexRuntime.

https://git.unl.edu/dxg/aws-chatbot - repo for AWS lambda code.  Serves as a backup to AWS code.


BSD License Agreement
------------------------------------------------------------------------------------
The software accompanying this license is available to you under the BSD license, available here and within the LICENSE file accompanying this software.

Copyright (c) 2012, Regents of the University of Nebraska

All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are
permitted provided that the following conditions are met:

1. Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
2. Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
3. Neither the name of the University of Nebraska nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.

 THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY
 EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES
 OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT
 SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED
 TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR
 BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
 DAMAGE.
