UNLchat
---------------

UNLchat is a custom live chat solution built for UNL.  It is designed to be work for a large organization with many sub-organizations such as a university.

When a chat request comes in, the chat is routed to the operators of closest site (as defined by a registry service) from where the chat originated.
For example, if a chat was started at http://www.unl.edu/WDN/index.php the chat would be routed to operators for http://www.unl.edu/WDN/.

The system aims to bring advanced functionality of a live chat solution while keeping the interface clean and intuitive.

Requirements
------------
 - PHP 5.3+
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
