require(['jquery', 'idm', 'analytics'], function($, idm, analytics) {
    <?php
    require_once(__DIR__ . "/ChatBase.js.php");
    // https://sdk.amazonaws.com/builder/js/#
    // Currently only need AWS.CongnitoIdentity and AWS.LexRuntime
    require_once(__DIR__ . "/aws-sdk-2.493.0.min.js");
    ?>

    // Initialize the Amazon Cognito credentials provider
    AWS.config.region = 'us-east-1'; // Region
    AWS.config.credentials = new AWS.CognitoIdentityCredentials({
      IdentityPoolId: 'us-east-1:b409ceff-f0a1-4fcb-b52f-5c7ec94c7e23',
    });

    var VisitorChat_Client = VisitorChat_ChatBase.extend({
        loginHTML: false,
        clientName: false,
        initialMessage: false,
        name:'',
        email:'',
        confirmationHTML: false,
        userType: 'client',
        method: 'chat',
        config: {
            email_required: false,
            name_required: false,
            site_title: false
        },
        widgetIsOpen: false,
        lexruntime: new AWS.LexRuntime(),
        sessionAttributes: {},

        setSessionCookie: function(name, value, expiresInSeconds, path) {
          WDN.setCookie(name, value, expiresInSeconds, path, null, 'none', true);
        },

        isChatbotAvailable: function() {
          return this.lexruntime && this.getChatbotID() && this.getChatbotName();
        },

        startEmail:function () {
            this.method = 'email';
            this.displaySiteAvailability(false);
            this.launchEmailContainer();
            this.start();

            var title = this.getSiteTitle();

            document.querySelector('#visitorChat_footerHeader').innerHTML = 'Send ' + title + ' a message';

            document.querySelector('#visitorChat_footerHeader').innerText = "Send a comment or ask us a question";
            //Require email if we need to.
            if (this.config.email_required) {
                document.querySelector('label[for="visitorChat_email"]').innerText = "Email (Required)";
                document.querySelector('#visitorChat_email').classList.add('required-entry');
            }

            if (this.config.name_required) {
                document.querySelector('label[for="visitorChat_name"]').innerText = "Name (Required)";
                document.querySelector('#visitorChat_name').classList.add('required-entry');
            }

            VisitorChat.displayWelcomeMessage();

            if (VisitorChat.operatorsAvailable) {
                //$('#visitorChat_container').append("<div id='visitorChat_methods'> or <button id='visitorChat_methods_chat'>chat with us</button> </div>");
                var el = document.querySelector('#visitorChat_container');
                el.insertAdjacentHTML('beforeend',"<div id='visitorChat_methods'> or <button id='visitorChat_methods_chat'>chat with us</button> </div>");

                document.querySelector('#visitorChat_methods_chat').addEventListener('click', function() {
                    VisitorChat.stop(function(){
                        VisitorChat.startChat();
                        document.querySelector('#visitorChat_messageBox').addEventListener("keyup",  function() {});
                    });

                    return false;
                }, {once : true});

            } else if (this.isChatbotAvailable()) {
                //$('#visitorChat_container').append("<div id='visitorChat_methods'> or <button id='visitorChat_methods_chat'>chat with us</button> </div>");
                var el = document.querySelector('#visitorChat_container');
                el.insertAdjacentHTML('beforeend',"<div id='visitorChat_methods'> or <button id='visitorChat_methods_chat'>chat with us</button> </div>");

                document.querySelector('#visitorChat_methods_chat').addEventListener('click', function() {
                   VisitorChat.stop(function(){
                      VisitorChat.startChatBot();
                      document.querySelector('#visitorChat_messageBox').addEventListener("keyup",  function() {});
                    });
                    
                    return false;
                }, {once : true});
            }
        },

        startChat:function (chatInProgress) {
            this.method = 'chat';
            this.displaySiteAvailability();
            this.launchChatContainer();

            if (chatInProgress && this.chatStatus == "LOGIN") {
                this.chatStatus = "CHATTING";
                return this.start();
            }
            var e = document.querySelector('#visitorChat_container #visitorChat_email_fallback_text');
            if(e){
                e.innerHTML = "If no operators are available,&nbsp;I would like to receive an email.";
            }


            this.start();

            var title = this.getSiteTitle();

            document.querySelector('#visitorChat_footerHeader').innerHTML = 'Chat with ' + title;

            document.querySelector('label[for="visitorChat_messageBox"]').innerText = "How can we assist you?";
            //Submit as chat
            document.querySelector('#visitorChat_login_chatmethod').value = "CHAT";
            
            var el = document.querySelector('#visitorChat_container');
            el.insertAdjacentHTML('beforeend',"<div id='visitorChat_methods'> or <button id='visitorChat_methods_email' >email us</button></div>");

            VisitorChat.displayWelcomeMessage();

            document.querySelector('#visitorChat_methods_email').addEventListener('click', function() {
                VisitorChat.stop(function(){
                    VisitorChat.startEmail();
                    document.querySelector('#visitorChat_messageBox').addEventListener("keyup",  function() {});
                });
                return false;
            }, {once : true});

        },

        startChatBot:function (chatInProgress) {
          this.method = 'chatbot';
          this.displaySiteAvailability();
          this.launchChatContainer();

          if (chatInProgress && this.chatStatus == "LOGIN") {
            this.chatStatus = "CHATING";
            return this.start();
          }

          var e = document.querySelector('#visitorChat_container #visitorChat_email_fallback_text');
        if(e){
                e.innerHTML = "If no operators are available,&nbsp;I would like to receive an email.";
        }


          this.start();

          var title = this.getSiteTitle();

          var testNotice = '';
          if (VisitorChat.chatbotEnv != 'PROD') {
            testNotice = ' (Test Env)';
          }

          document.querySelector('#visitorChat_footerHeader').innerHTML = 'Chat with ' + title + ' Chatbot ' + testNotice;

          document.querySelector('label[for="visitorChat_messageBox"]').innerHTML = "How can we assist you?";

          //Submit as chat
          document.querySelector('#visitorChat_login_chatmethod').value = "CHATBOT";

          var el = document.querySelector('#visitorChat_container');
          el.insertAdjacentHTML('beforeend',"<div id='visitorChat_methods'> or <button id='visitorChat_methods_email' >email us</button></div>");

          VisitorChat.displayWelcomeMessage();

          document.querySelector('#visitorChat_methods_email').addEventListener('click', function() {
            VisitorChat.stop(function(){
              VisitorChat.startEmail();
              document.querySelector('#visitorChat_messageBox').addEventListener("keyup",  function() {});
            });
            return false;
          }, {once : true});
        },

      startChatBotWithIntent:function (introMsg, intentMsg, intentSessionAttributes, displayChatMethods) {
        // Default parameters
        var introMsg = introMsg || '';
        var intentMsg = intentMsg || '';
        var intentSessionAttributes = intentSessionAttributes || {};
        var displayChatMethods = displayChatMethods || false;
        //console.log('starting chatbot intent: ' + VisitorChat.chatbotUserID);
        //console.log('starting chatbot: ' + VisitorChat.getChatbotUserID())
        if (VisitorChat.operatorsAvailable || !this.isChatbotAvailable() || introMsg.trim().length == 0 || intentMsg.trim().length == 0) {
          // invalid intent info so launch chatbot without intent instead
          if (VisitorChat.operatorsAvailable) {
            this.startChatBot();
            document.querySelector('#visitorChat_messageBox').addEventListener("keyup",  function() {});
          } else {
            VisitorChat.startEmail();
            document.querySelector('#visitorChat_messageBox').addEventListener("keyup",  function() {});
          }
          return false;
        }

        this.method = 'chatbot';
        this.displaySiteAvailability();
        this.launchChatContainer();

        var el = document.querySelector('#visitorChat_container #visitorChat_email_fallback_text');
        el.insertAdjacentHTML('beforeend',"If no operators are available,&nbsp;I would like to receive an email.");

        this.start();

        var title = this.getSiteTitle();

        var testNotice = '';
        if (VisitorChat.chatbotEnv != 'PROD') {
          testNotice = ' (Test Env)';
        }
        document.querySelector('#visitorChat_footerHeader').innerHTML = 'Chat with ' + title + ' Chatbot ' + testNotice;
        //Submit as chat
        document.querySelector('#visitorChat_login_chatmethod').value = "CHATBOT";

        VisitorChat.displayWelcomeMessage();

        // setup intent display
        document.querySelector('#visitorChat').classList.add('visitorChat_open');
        document.querySelector('#visitorChat_header, #dcf-mobile-toggle-chat').
        setAttribute('aria-label', 'Close the Let\'s Chat widget').
        setAttribute('aria-expanded', 'true');
        document.querySelector('.dcf-nav-toggle-label-chat').innerText = 'Close';
        document.querySelector('#visitorChatbot_intent').value = intentMsg;
        document.querySelector('#visitorChatbot_intent_defaults').value = JSON.stringify(intentSessionAttributes);
        document.querySelector('#visitorChat_messageBox').addEventListener("keyup",  function() {});
        document.querySelector('#visitorChatbot_messageBoxContainer').style.display = 'none';
        document.querySelector('#visitorChatbot_intent_message').innerText = introMsg;
        document.querySelector('#visitorChatbot_intent_message').style.display = '';

        if (displayChatMethods === true) {
          if (VisitorChat.operatorsAvailable) {
            var el = document.querySelector('#visitorChat_container');
            el.insertAdjacentHTML('beforeend',"<div id='visitorChat_methods'> or <button id='visitorChat_methods_chat'>chat</button> or <button id='visitorChat_methods_email'>email us</button></div>");

            document.querySelector('#visitorChat_methods_chat').addEventListener('click', function () {
              VisitorChat.stop(function () {
                VisitorChat.startChat();
                document.querySelector('#visitorChat_messageBox').addEventListener("keyup",  function() {});
              });

              return false;
            }, {once : true});
          } else if (this.isChatbotAvailable()) {

            var el = document.querySelector('#visitorChat_container');
            el.insertAdjacentHTML('beforeend',"<div id='visitorChat_methods'> or <button id='visitorChat_methods_chat'>chat</button> or <button id='visitorChat_methods_email'>email us</button></div>");

            document.querySelector('#visitorChat_methods_chat').addEventListener('click', function () {
              VisitorChat.stop(function () {
                VisitorChat.startChatBot();
                document.querySelector('#visitorChat_messageBox').addEventListener("keyup",  function() {});
              });

              return false;
            }, {once : true});

          } else {
              // This code causes problem, will have to look into it
              var el = document.querySelector('#visitorChat_container');
              el.insertAdjacentHTML('afterend',"<div id='visitorChat_methods'> or <button id='visitorChat_methods_email'>email us</button></div>");
          }
        }

        document.querySelector('#visitorChat_methods_email').addEventListener('click', function() {
          VisitorChat.stop(function(){
            VisitorChat.startEmail();
                document.querySelector('#visitorChat_messageBox').addEventListener("keyup",  function() {});
          });

          return false;
        }, {once : true});

      },

        displayWelcomeMessage: function() {
            if (typeof visitorchat_config !== 'undefined' && typeof visitorchat_config.chat_welcome_message !== 'undefined') {
                $('#visitorchat_clientLogin').prepend($('<p>', {'class':'welcome-message'}).html(visitorchat_config.chat_welcome_message));
            }
        },

        getSiteTitle: function() {
            var title = this.config.site_title;
            if (false == title) {
                if ($('#dcf-site-title abbr').length) {
                    title = document.querySelector('#dcf-site-title abbr').getAttribute('title');
                } else {
                    title =document.querySelector('#dcf-site-title').textContent;
                }
            }

            title.trim();
            return title;
        },

        start:function () {
            if (this.blocked) {
                var html = "Your IP address has been blocked.  If you feel that this is an error, please contact operator@unl.edu";
                this.updateChatContainerWithHTML("#visitorChat_container", html, false);
                return;
            }

            //Always show the chat if we are logged in as an operator.  Otherwise only show if someone is available.
            if (this.userType == 'operator') {
                html = "<div class='chat_notify'>You are currently logged in as an operator and can not start a client conversation from this browser.  If you want to start a conversation, please either log out or do so in another web browser.</div>";
                this.updateChatContainerWithHTML("#visitorChat_container", html, false);
            } else {
                this.updateChatContainerWithHTML("#visitorChat_container", this.loginHTML, false);
            }

            this._super();
        },

        onOperatorMessage:function (message) {
            //Fire an analytics event on first response.  set cookie for cross domain.
            if (!WDN.getCookie('UNL_Visitorchat_FirstOperatorResponse') && WDN.getCookie('UNL_Visitorchat_Start')) {
                start = WDN.getCookie('UNL_Visitorchat_Start');
                date = new Date();
                date = Math.round(date.getTime() / 1000);
                difference = date - start;

                analytics.callTrackEvent('WDN Chat', 'Response', 'Received', difference);

                //Set a cookie so that we don't call this if we have to reload the chat (page refresh or move to another page).
                this.setSessionCookie('UNL_Visitorchat_FirstOperatorResponse', difference, null, '/');
            }
        },

        launchEmailContainer:function() {
            this.chatStatus = "LOGIN";
            
            //Remove an old one if it is there.
            $('#visitorChat_container').remove();
            //set up a container.
            $('#visitorChat').append(
                "<div class='dcf-relative dcf-mr-1 dcf-mb-1 dcf-ml-1 dcf-p-4 dcf-rounded unl-bg-lightest-gray' id='visitorChat_container' tabindex='-1'>" +
                "<div class='chat_notify visitorChat_loading'>Initializing, please wait.</div>" +
                "</div>"
            );

            //set up a container.
            var html = "<div class='dcf-relative dcf-mr-1 dcf-mb-1 dcf-ml-1 dcf-p-4 dcf-rounded unl-bg-lightest-gray' id='visitorChat_container'>Please wait...</div>";
            var e = document.querySelector('#visitorchat_clientLogin') !== null;
            if(e){
                document.querySelector('#visitorchat_clientLogin').outerHTML = "<div class='dcf-relative dcf-mb-2 dcf-pt-3 dcf-pr-4 dcf-pb-3 dcf-pl-4 unl-bg-lightest-gray' id='visitorChat_container'></div>";
            }
               document.querySelector('#visitorChat_container').style.display = '';
        },

        launchChatContainer:function () {
            //Remove an old one if it is there.
            $('#visitorChat_container').remove();
            // var el = document.querySelector('#visitorChat_container');
            // if(el.parentNode !== null){
            //     el.parentNode.removeChild(el);
            // }

            //set up a container.
            $('#visitorChat').append(
                "<div class='dcf-relative dcf-mr-1 dcf-mb-1 dcf-ml-1 dcf-p-4 dcf-rounded unl-bg-lightest-gray' id='visitorChat_container' tabindex='-1'>" +
                    "<div class='chat_notify visitorChat_loading'>Initializing, please wait.</div>" +
                    "</div>"
            );

            this.chatStatus = "LOGIN";

            $('#visitorchat_clientLogin').parent().html("Disabled");

            //document.querySelector('#visitorchat_clientLogin').parentNode.innerHTML = "Disabled";

            //Display and set the name (if found).
            $('#visitorChat_container').delay(10).slideDown(320, function() {
                if (idm.getDisplayName()) {
                    //$('#visitorChat_name').val(idm.getDisplayName());
                    document.querySelector('#visitorChat_name').value = idm.getDisplayName();
                }
            });
        },

        confirmClose:function (id) {
            if (this.chatStatus == 'CLOSED') {
                return true;
            }

            if (confirm("End your chat?")) {
                return true;
            }

            return false;
        },

        //Require confirmation that a comment is to be submitted anon.
        confirmAnonSubmit: function() {
            var email = document.querySelector('#visitorChat_email').value;
            //If the email is empty, don't submit and append a warning to the form, otherwise continue on.
            if (email != '') {
                return true;
            }

            //Check if they are confirming anon...
            if (document.querySelector('#visitorChat_login_submit').value == 'Yes, I do not need a response') {
                    //Reset to say 'submit'.
                    document.querySelector('#visitorChat_login_submit').value = "Submit";
                    return true;
                }

            //Display error and request confirmation before continuing.
            var html = "<div id='visitorchat_clientLogin_anonwaning'>Since you didn't enter an email, we won't be able to respond. Is this OK? Type your email if you want a respond instead </div>";

            // $('#visitorChat_login_submit').before(html);
            // $('#visitorChat_login_submit').val("Yes, I do not need a response");
            var e = document.querySelector('#visitorChat_login_submit');
            e.insertAdjacentHTML('beforebegin', html);
            e.value = "Yes, I do not need a response";
            this.initWatchers();

            //remove the warning if they start to enter an email
            document.querySelector('#visitorChat_email').addEventListener('keyup',function () {
                $('#visitorchat_clientLogin_anonwaning').remove();
                document.querySelector('#visitorChat_login_submit').value = "Submit";
            });

            return false;
        },

        ajaxBeforeSubmit:function (arr, $form, options) {
            //Start an email convo now if need be.
            for (var key = 0; key<arr.length; key++) {
                if (arr[key]['name'] == 'method' && arr[key]['value'] == 'EMAIL') {
                    if (!this.confirmAnonSubmit()) return false;
                }
            }

            return this._super(arr, $form, options);
        },

        initValidation: function() {
            $.validation.addMethod('validate-require-if-question',
                'An email address is required if you ask a question so that we can respond.',
                function(value, object) {
                    var message = document.querySelector('#visitorChat_messageBox').value;
                    if (message.indexOf("?") != -1 && value == "") {
                        return false;
                    }

                    return true;
                });

            //Remove the vaildation binding so that validation does not stack and is always called before ajax submit.
            $('#visitorchat_clientLogin').data('validation', false);
            $('#visitorChat_confirmationEmailForm').data('validation', false);

            //Require email for questions submitted via the footer comment form.
            $('#visitorChat_footercontainer #visitorChat_email').addClass('validate-require-if-question');

            //Validator
            $('#visitorchat_clientLogin, #visitorChat_confirmationEmailForm').validation();
        },

        initWatchers:function () {
            /* This method is called several times thoughout
             * executation.  Thus in order to stop the stacking
             * of watch functions, we should always unbind previous
             * watch functions before applying the new ones.
             */
            $('#visitorChat_container, ' +
                '#visitorChat_email_fallback, ' +
                '#visitorChat_logout, ' +
                '#visitorChat_login_submit, ' +
                '#visitorChat_header, ' +
                '#dcf-mobile-toggle-chat, ' +
                '#visitorChat_chatBox > ul > li,' +
                '#visitorChat_messageBox,' +
                '#visitorChat_email,' +
                '#visitorChat_confiramtionEmail,' +
                '#visitorChat_failedOptions_yes,' +
                '#visitorChat_failedOptions_no,' +
                '#visitorChat_sendAnotherConfirmation,' +
                '#visitorChat_name,' +
                '#visitorChat_footercontainer #visitorchat_clientLogin,' +
                '#visitorchat_clientLogin,' +
                '.unl_visitorchat_form,' +
                '#visitorChat_confirmationEmailForm').unbind();

            this.initValidation();

            $('#visitorChat_footercontainer #visitorchat_clientLogin').bind('validate-form', function (event, result) {
                //document.querySelector('#visitorchat_clientLogin_anonwaning').remove();
                $('#visitorchat_clientLogin_anonwaning').remove();
                if (document.querySelector('#visitorChat_footercontainer #visitorChat_login_submit').value == 'Yes, no response needed'
                    && document.querySelector('#visitorChat_email').value != '') {
                    document.querySelector('#visitorChat_footercontainer #visitorChat_login_submit').value = "Submit";
                }

                return true;
            });

            $('#visitorChat_confirmationEmailForm').bind('validate-form', function (event, result) {
                if (result) {
                    document.querySelector('#visitorChat_confirmationContainer').innerHTML = ("<p class='dcf-txt-xs'>The email transcript has been sent to " + document.querySelector('#visitorChat_confiramtionEmail').value + ".</p><button class='dcf-btn dcf-btn-secondary' id='visitorChat_sendAnotherConfirmation'>Send another one</button>").focus();

                    $().unbind('#visitorChat_sendAnotherConfirmation');
                    
                    document.querySelector('#visitorChat_sendAnotherConfirmation').addEventListener('click' , function(){
                        //$('#visitorChat_confirmationContainer').html($(VisitorChat.confirmationHTML).filter('#visitorChat_confirmationContainer').html()).focus();
                        document.querySelector('#visitorChat_confirmationContainer').innerHTML
                        = Array.prototype.filter.call( document.querySelectorAll(VisitorChat.confirmationHTML), document.querySelector('#visitorChat_confirmationContainer').innerHTML).focus();
                        VisitorChat.initWatchers();
                        return false;
                    });
                }

                return false;
            });

            //Call the parent
            this._super();

            //Click header or mobile toolbar button to open up Chat
            // document.querySelector('#visitorChat_header, #dcf-mobile-toggle-chat').
            // addEventListener('click keypress', function (event) {
            $('#visitorChat_header, #dcf-mobile-toggle-chat').on('click keypress', function (event) {
                if (event.type == 'keypress' && ([32,13].indexOf(event.which) == -1)) {
                    //Must be space or enter to continue
                    return;
                }
                var a = document.querySelector('#visitorChat_container');
                if(!Object.is(a,':visible')) {
                    //Open the container
                    VisitorChat.widgetIsOpen = true;
                    document.querySelector('#visitorChat').classList.add('visitorChat_open');
                    $('#visitorChat_container').slideDown(320);
                    document.querySelector('#dcf-nav-toggle-icon-open-chat').classList.add('dcf-d-none');
                    document.querySelector('#dcf-nav-toggle-icon-close-chat').classList.remove('dcf-d-none');
                } else {
                    //Close the container
                    VisitorChat.widgetIsOpen = false;
                    $('#visitorChat_container').slideUp(320);
                    document.querySelector('#dcf-nav-toggle-icon-open-chat').classList.remove('dcf-d-none');
                    document.querySelector('#dcf-nav-toggle-icon-close-chat').classList.add('dcf-d-none');

                    if (VisitorChat.chatStatus == "LOGIN") {
                        //If the user hasn't done anything yet, simply stop everything and exit early
                        VisitorChat.stop();
                        return false;
                    }
                }

                if (VisitorChat.chatOpened) {
                    //If we are chatting or pending approval, don't do anything
                    if (VisitorChat.chatStatus == 'CHATTING' || VisitorChat.chatStatus == 'OPERATOR_PENDING_APPROVAL') {
                        return false;
                    }
                } else {
                    //Otherwise start chat/email forms
                    if (VisitorChat.method == 'chat') {
                        VisitorChat.startChat();
                    } else if (VisitorChat.method == 'chatbot') {
                      VisitorChat.startChatBot();
                    } else {
                        VisitorChat.startEmail();
                    }

                }
                return false;
            });

            //Logout function
            $('#visitorChat_logout').on('click keypress', (function (event) {
            //document.querySelector('#visitorChat_logout').addEventListener('click keypress', (function (event) {
                if (event.type == 'keypress' && ([32,13].indexOf(event.which) == -1)) {
                    //Must be space or enter to continue
                    return;
                }

                if (this.chatStatus == 'CHATTING' && !VisitorChat.confirmClose()) {
                    return false;
                }

                if (this.chatStatus == 'CHATTING') {
                    VisitorChat.changeConversationStatus("CLOSED");
                    return false;
                }

                VisitorChat.stop();
                return false;
            }.bind(this)));

            if (VisitorChat.chatStatus == "LOGIN" || VisitorChat.chatStatus == false) {
                //if email_fallback is checked, make sure that the email is required.
                //document.querySelector('#visitorChat_email_fallback').addEventListener('click' , function () {
                $('#visitorChat_email_fallback').click(function () {
                    if(Object.is(this,':checked') || this.config.email_required) {
                        document.querySelector('label[for="visitorChat_email"]').innerText = "Email (Required)";
                        document.querySelector('#visitorChat_email').classList.add('required-entry');
                    } else {
                        document.querySelector('label[for="visitorChat_email"]').innerText = "Email (Optional)";
                        document.querySelector('#visitorChat_email').classList.add('required-entry');
                    }
                });
            }

            //This will slide down the Name and Email fields, plus the Ask button
            $('#visitorChat_messageBox').one('keyup', function () {
                if (idm.getDisplayName()) {
                    document.querySelector('#visitorChat_name').value = idm.getDisplayName();
                }
                if (idm.getEmailAddress()) {
                    //$('#visitorChat_email').val(idm.getEmailAddress());
                    document.querySelector('#visitorChat_email').value = idm.getEmailAddress();
                }

                $('.visitorChat_info, #visitorChat_login_submit').slideDown('fast', function(){
                    
                    if (VisitorChat.initialMessage && !Object.is(document.querySelector('#visitorChat_messageBox'),':focus')) {
                        document.querySelector('#visitorChat_email').focus();
                    }
                });
            });

            $('#visitorChat_failedOptions_yes').click(function() {
                VisitorChat.stop(function(){
                    VisitorChat.startEmail();
                    if (VisitorChat.initialMessage) {
                        document.querySelector('#visitorChat_messageBox').value = VisitorChat.initialMessage;
                    }

                    document.querySelector('#visitorChat_name').value = VisitorChat.name;
                    document.querySelector('#visitorChat_email').value = VisitorChat.email;

                    document.querySelector('#visitorChat_email').focus();
                    document.querySelector('#visitorChat_messageBox').addEventListener("keyup", function (){
                        //testing for the keyup function , unsure this even works but it's ok for now
                        console.log("this works ?");
                    });
                });

                return true;
            });
            var el = document.querySelector('#visitorChat_confirmationEmail') !== null;
            if(el){
                document.querySelector('#visitorChat_confirmationEmail').addEventListener('keypress', function (e) {
                    if (e.which == 13) {
                        e.preventDefault();
    
                        document.querySelector('#visitorChat_confirmationEmailForm').submit();
                    }
                });
            }
            

            $('#visitorChat_failedOptions_no').click(function() {
                VisitorChat.stop();

                return true;
            });

            if (this.chatStatus) {
                $('#visitorChat_logout').css({'display':'inline-block'});
                // document.querySelectorAll('#visitorChat_logout').forEach(function(el){
                //     el.style.display = "incline-block";
                // });
                $('#visitorChat_header_text').css({'margin-right':'1.777em'});
            } else {
                $('#visitorChat_logout').css({'display':'none'});
                // document.querySelectorAll('#visitorChat_logout').forEach(function(el){
                //     el.style.display = "none";
                // });
                $('#visitorChat_header_text').css({'margin-right':'0'});
            }

            //set the for_url
            $('#initial_url').val(document.URL);
            $('#initial_pagetitle').val($(document).attr('title'));
        },

        handleIsTyping:function () {
            if (VisitorChat.isTypingTimeout == false) {
                VisitorChat.sendIsTypingStatus('YES');

                VisitorChat.isTypingTimeout = setTimeout(function(){
                    VisitorChat.isTypingTimeout = false;
                    VisitorChat.sendIsTypingStatus('NO');

                }, 5000);
            }
        },

        sendIsTypingStatus:function(newStatus) {
            $.ajax({
                type:"POST",
                url:this.serverURL + "conversation/" + this.conversationID + "/edit?format=json&" + this.getURLSessionParam(),
                xhrFields:{
                    withCredentials:true
                },

                data:"client_is_typing=" + newStatus
            })
        },

        /**
         * onConversationStatus_Searching
         * Related status code: SEARCHING
         * Details: This function means that the client is waiting for
         * the server to find an operator.  Please note that this status
         * will hardly ever be returned. Most often we will either be pending
         * approval during this stage.  Thus, OperatorPendingApproval and this
         * function are closely related and could probably share the same logic.
         */
        onConversationStatus_Searching:function (data) {
            if (this.method == 'chat') {
                var html = "<div class='chat_notify visitorChat_loading' tabindex='-1'>Please wait while we find someone to help you.</div>";
                this.updateChatContainerWithHTML('#visitorChat_container', html);
            } else {
                var html = "<div class='chat_notify visitorChat_loading' tabindex='-1'>Please wait while we process your request.</div>";
                this.updateChatContainerWithHTML('#visitorChat_container', html);
            }

        },

        /**
         * onConversationStatus_Emailed
         * Related status code: EMAILED
         * Details: This function will be called when a converstation
         * falls back to an email.  This means that an operator was not available
         * but an email could be sent.
         */
        onConversationStatus_Emailed:function (data) {
            this._super();

            //Make sure that the closed button is visible at this point.
            this.chatStatus = 'EMAILED';
            this.initWatchers();
        },

        onLogin:function () {
            this.clientName = document.querySelector('#visitorChat_name').value;
            this.initialMessage = document.querySelector('#visitorChat_messageBox').value;
            this.name = document.querySelector('#visitorChat_name').value;
            this.email = document.querySelector('#visitorChat_email').value;

            this._super();

            //Record a start event cookie (for analytics)
            VisitorChat.deleteAnalyticsCookies();

            //Set a cookie.
            date = new Date();
            this.setSessionCookie('UNL_Visitorchat_Start', (Math.round(date.getTime() / 1000)), null, '/');

            //Mark as started
            analytics.callTrackEvent('WDN Chat', 'Started');
        },


        onConversationStatus_Closed:function (data) {
            if (document.querySelectorAll('#visitorChat_confirmationContainer').length != 0) {
                return false;
            }

            this._super(data);

            this.confirmationHTML = data['confirmationHTML'];

            document.querySelector('#visitorChat_chatBox').style.height = '150px';

            $('#visitorChat_messageForm').remove();

            var $closed_container = $('#visitorChat_closed');
            var e = document.querySelector('#visitorChat_closed');

             //Object.assign(e , data['confirmationHTML'])
            //e.setAttribute('tabindex', '-1');
            $closed_container.append(data['confirmationHTML']);
            $closed_container.attr('tabindex', '-1');
            $('#visitorChat_confiramtionEmail').focus();

            this.initWatchers();

            $().unbind('visitorChat_header');

            this.scroll();
        },

        onConversationStatus_Chatting:function (data) {
            this._super(data);

            var is_typing = false;
            if (data['operators'] !== undefined) {
                for (operator in data['operators']) {
                    if (data['operators'][operator].is_typing == 'YES') {
                        is_typing = true;
                    }
                }
            }

            if (is_typing) {
                WDN.jQuery('#visitorChat_is_typing').text("The other party is typing.").show(500);
            } else {
                WDN.jQuery('#visitorChat_is_typing').hide(500);
            }

            $().unbind('visitorChat_header');
        },

        handleUserDataResponse:function (data) {
            this.conversationID = data['conversationID'];

            this.userType = data['userType'];

            //Call the parent logic.
            this._super(data);

            if (data['loginHTML'] !== undefined && data['loginHTML']) {
                this.loginHTML = data['loginHTML'];

                this.initWatchers();
            }

            //Handle the rest of the data.
            if (data['conversationID'] && this.chatStatus == false) {
                if (this.method == 'chat') {
                  this.startChat(true);
                } else {
                  this.startChatBot(true);
                }
            }

            this.displaySiteAvailability();
        },

        onConversationStatus_Captcha:function (data) {
            if (this.method == 'email') {
                this.launchChatContainer();
            }

            this.updateChatContainerWithHTML('#visitorChat_container', data['html']);
        },

        onConversationStatus_OperatorLookupFailed:function (data) {
            clearTimeout(VisitorChat.loopID);
            VisitorChat.operatorsAvailable = false;
            var html = "<div class='chat_notify'><p class='dcf-txt-sm'>Unfortunately all of our operators are currently busy. Would you like to send an email instead?</p>" +
                "<div class='dcf-d-flex dcf-jc-around dcf-mt-4' id='visitorChat_failedOptions'><button class='dcf-btn dcf-btn-secondary' id='visitorChat_failedOptions_yes'>Yes</button> <button class='dcf-btn dcf-btn-secondary' id='visitorChat_failedOptions_no'>No</button></div></div>";
            this.updateChatContainerWithHTML('#visitorChat_container', html);
        },

        updatePHPSESSID:function (phpsessid) {
            this.phpsessid = phpsessid;

            //set the cookie (IE ONLY) or CORS Domain.
            if (this.usePhpSessIdCookie()) {
                this.setSessionCookie('UNL_Visitorchat_Session', phpsessid, null, '/');
            }
        },

        loadStyles:function () {
            var stylesheet = this.serverURL + "assets/css?for=client&v=" + this.version;
            //load styling.
            if (document.createStyleSheet) {
                document.createStyleSheet(stylesheet);
            } else {
                var html = "<link rel='stylesheet' href='" + stylesheet + "' type='text/css' media='screen, print' />";
                document.getElementsByTagName('head')[0].insertAdjacentHTML('beforeend', html);
            }

            window.addEventListener("load", function () {
                VisitorChat.displaySiteAvailability();
            });

            this._super();
        },

        init:function (serverURL, refreshRate) {
            document.querySelector('#dcf-footer').insertAdjacentHTML('beforeend',
                    '' + '<div class="dcf-fixed dcf-d-none@print unl-font-sans offline" id="visitorChat">' +
                    '<div class="dcf-d-flex dcf-flex-nowrap dcf-ai-center dcf-jc-between dcf-w-100% dcf-lh-1" id="visitorChat_header" tabindex="0" aria-label="Open the Email Us widget">' +
                        '<span class="dcf-txt-xs dcf-pt-3 dcf-pr-5 dcf-pb-3 dcf-pl-5 dcf-uppercase" id="visitorChat_header_text">Email Us</span>' +
                        '<div id="visitor-chat-header-options">' +
                            '<button class="dcf-pl-4 dcf-pr-4 dcf-lh-1 dcf-b-0 dcf-bg-transparent" id="visitorChat_logout" aria-label="close and log out of chat">' +
                                '<svg class="dcf-d-block dcf-h-4 dcf-w-4 dcf-fill-current" aria-hidden="true" focusable="false" width="16" height="16" viewBox="0 0 24 24">' +
                                    '<path d="M20.5 4.2L4.2 20.5c-.2.2-.5.2-.7 0-.2-.2-.2-.5 0-.7L19.8 3.5c.2-.2.5-.2.7 0 .2.2.2.5 0 .7z"/><path d="M3.5 4.2l16.3 16.3c.2.2.5.2.7 0s.2-.5 0-.7L4.2 3.5c-.2-.2-.5-.2-.7 0-.2.2-.2.5 0 .7z"></path>' +
                                '</svg>' +
                            '</button>' +
                        '</div>' +
                    '</div>' +
                    '<div id="visitorChat_sound_container"></div>' +
                '</div>'
            );

            document.querySelector('#dcf-nav-toggle-group').insertAdjacentHTML('beforeend', 
               '' + '<button class="dcf-nav-toggle-btn dcf-nav-toggle-btn-chat dcf-d-flex dcf-flex-col dcf-ai-center dcf-jc-center dcf-flex-grow-1 dcf-h-9 dcf-p-0 dcf-b-0 dcf-bg-transparent unl-scarlet" id="dcf-mobile-toggle-chat" aria-expanded="false">' +
                    '<svg class="dcf-txt-sm dcf-h-6 dcf-w-6 dcf-fill-current" aria-hidden="true" focusable="false" width="16" height="16" viewBox="0 0 24 24">' +
                        '<g class="" id="dcf-nav-toggle-icon-open-chat">' +
                            '<path d="M1.4 23.2c-.1 0-.3-.1-.4-.2-.1-.2-.2-.4-.1-.6l2.4-4.8C1.2 15.9 0 13.5 0 10.9 0 5.4 5.4 1 12 1s12 4.4 12 9.9-5.4 9.9-12 9.9c-1.4 0-2.7-.2-4-.6l-6.4 3h-.2zM12 2C5.9 2 1 6 1 10.9c0 2.4 1.2 4.6 3.3 6.3.2.1.2.4.1.6l-1.9 3.9 5.3-2.5c.1-.1.2-.1.4 0 1.2.4 2.5.6 3.9.6 6.1 0 11-4 11-8.9S18.1 2 12 2z"></path>' +
                        '</g>' +
                        '<g class="dcf-d-none" id="dcf-nav-toggle-icon-close-chat">' +
                            '<path d="M20.5 4.2L4.2 20.5c-.2.2-.5.2-.7 0-.2-.2-.2-.5 0-.7L19.8 3.5c.2-.2.5-.2.7 0 .2.2.2.5 0 .7z"/><path d="M3.5 4.2l16.3 16.3c.2.2.5.2.7 0s.2-.5 0-.7L4.2 3.5c-.2-.2-.5-.2-.7 0-.2.2-.2.5 0 .7z"></path>' +
                        '</g>' +
                    '</svg>' +
                    '<span class="dcf-nav-toggle-label-chat dcf-mt-1 dcf-txt-2xs">Email Us</span>' +
                '</button>');

            //Handle cookies. (IE session handling);
            var phpsessid = WDN.getCookie('UNL_Visitorchat_Session');
            if (phpsessid != null) {
                this.phpsessid = phpsessid;
            }

            this._super(serverURL, refreshRate);
        },

        stop:function (callback) {
            if (this.userType == 'operator') {
                this.closeChatContainer();
                return true;
            }

            VisitorChat.xhrAbortAll();

            callbackSet = false;
            //Object.is(document.querySelector('#visitorChat_container'),':visible')
            if ($('#visitorChat_container').is(':visible')) {
                callbackSet = true;
                $('#visitorChat_container').slideUp(400,(function () {
                    if (callback) {
                        callback();
                    }
                }.bind(this)));
            }

            this._super();

            this.closeChatContainer();

            if (WDN.getCookie('UNL_Visitorchat_Start')) {
                date = new Date();
                date = Math.round(date.getTime() / 1000);
                difference = date - WDN.getCookie('UNL_Visitorchat_Start');

                analytics.callTrackEvent('WDN Chat', 'Ended', undefined, difference);
            }

            //Delete the current cookie.
            VisitorChat.deleteAnalyticsCookies();

            this.initWatchers();

            if (callback && !callbackSet) {
                callback();
            }
        },

        deleteAnalyticsCookies: function() {
            //Delete the current cookie.
            this.setSessionCookie('UNL_Visitorchat_Start', '0', -1, '/');
            this.setSessionCookie('UNL_Visitorchat_Session', '0', -1, '/');
            this.setSessionCookie('UNL_Visitorchat_FirstOperatorResponse', '0', -1, '/');
        },

        closeChatContainer: function() {
            document.querySelector('#visitorChat').classList.remove('visitorChat_open');
            $('#visitorChat_logout').css({'display':'none'});
            document.querySelector('#dcf-nav-toggle-icon-open-chat').classList.remove('dcf-d-none');
            document.querySelector('#dcf-nav-toggle-icon-close-chat').classList.add('dcf-d-none');
            this.widgetIsOpen = false;
            this.displaySiteAvailability();
        },

        displaySiteAvailability:function (available) {
            if (available == null) {
                available = VisitorChat.operatorsAvailable;
            }

            //var $widget = $('#visitorChat');
            var widget = document.querySelector('#visitorChat');
            var text = 'Email Us';

            if (available) {
                widget.classList.add('online');
                widget.classList.remove('offline');
                text = 'Let\'s Chat';
                VisitorChat.method = 'chat';
            } else if (this.isChatbotAvailable()) {
                widget.classList.add('online');
                widget.classList.remove('offline');
                text = 'Let\'s Chat';
                VisitorChat.method = 'chatbot';
            } else {
                widget.classList.add('offline');
                widget.classList.remove('online');
                VisitorChat.method = 'email';
            }

            //Update the text of the visible prompt
            document.querySelector('#visitorChat_header_text').innerText = text;

            //Set the aria attributes based on the action that will be performed when clicking
            if (this.widgetIsOpen) {
                $('#visitorChat_header, #dcf-mobile-toggle-chat').attr('aria-label', 'Close the ' + text + ' widget').attr('aria-expanded', 'true');
                document.querySelector('.dcf-nav-toggle-label-chat').innerText = 'Close';
            } else {
                $('#visitorChat_header, #dcf-mobile-toggle-chat').attr('aria-label', 'Open the ' + text + ' widget').attr('aria-expanded', 'false');
                document.querySelector('.dcf-nav-toggle-label-chat').innerText = text;
            }

            return true;
        },

        setHeight: function(el, val) {
            if (typeof val === "function") val = val();
            if (typeof val === "string") el.style.height = val;
            else el.style.height = val + "px";
        }
    });

    $(function(){

        WDN.initializePlugin('form_validation', [function() {
            if (VisitorChat == false) {
                VisitorChat = new VisitorChat_Client("<?php echo \UNL\VisitorChat\Controller::$url;?>", <?php echo \UNL\VisitorChat\Controller::$refreshRate;?>);
            }
        }]);
    });

});
