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
                var parent = document.querySelector('#visitorchat_clientLogin');
                var child = document.querySelector('<p>', {'class':'welcome-message'}).innerHTML = visitorchat_config.chat_welcome_message;
                parent.insertBefore(child , parent.firstChild);

            }
        },

        getSiteTitle: function() {
            //checking fo config.site seems redundant , I'll might remove this
            var element = document.querySelector('#dcf-site-title abbr');
            if (typeof(element) != 'undefined' && element != null){
                title = document.querySelector('#dcf-site-title abbr').getAttribute('title');
                console.log('yes');
            } else {
                title = document.querySelector('#dcf-site-title').textContent;
                console.log('no');
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
            if(this.elementReady('#visitorChat_container')){
                document.querySelector('#visitorChat_container').
                parentNode.removeChild(document.querySelector('#visitorChat_container'));
            }
            //set up a container.
            var addon = "<div class='dcf-relative dcf-mr-1 dcf-mb-1 dcf-ml-1 dcf-p-4 dcf-rounded unl-bg-lightest-gray' id='visitorChat_container' tabindex='-1'>" +
            "<div class='chat_notify visitorChat_loading'>Initializing, please wait.</div>" +
            "</div>";
            document.querySelector('#visitorChat').insertAdjacentHTML('beforeend' , addon);

            //set up a container.
            var html = "<div class='dcf-relative dcf-mr-1 dcf-mb-1 dcf-ml-1 dcf-p-4 dcf-rounded unl-bg-lightest-gray' id='visitorChat_container'>Please wait...</div>";
            var e = document.querySelector('#visitorchat_clientLogin') !== null;
            if(e){
                document.querySelector('#visitorchat_clientLogin').outerHTML = html;
            }
               document.querySelector('#visitorChat_container').style.display = '';
        },

        launchChatContainer:function () {
            //Remove an old one if it is there.
            if(this.elementReady('#visitorChat_container')){
                document.querySelector('#visitorChat_container').
                parentNode.removeChild(document.querySelector('#visitorChat_container'));
            }

            var addon = "<div class='dcf-relative dcf-mr-1 dcf-mb-1 dcf-ml-1 dcf-p-4 dcf-rounded unl-bg-lightest-gray' id='visitorChat_container' tabindex='-1'>" +
            "<div class='chat_notify visitorChat_loading'>Initializing, please wait.</div>" +
            "</div>";
            document.querySelector('#visitorChat').insertAdjacentHTML('beforeend' , addon);
           

            this.chatStatus = "LOGIN";

            if (this.existInDom('#visitorchat_clientLogin'))
            {
                document.querySelector('#visitorchat_clientLogin').parentNode.innerHTML = "Disabled";
            }           
            

            //Display and set the name (if found).
            // setTimeout(function(){
            //     document.querySelector('#visitorChat_container').style.height = '320px';
            //     //if (idm.getDisplayName()) {
            //         document.querySelector('#visitorChat_name').value = idm.getDisplayName();
            //     //}
            // }, 10);

            $('#visitorChat_container').delay(10).slideDown(320, function() {
                if (idm.getDisplayName()) {
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

            var e = document.querySelector('#visitorChat_login_submit');
            e.insertAdjacentHTML('beforebegin', html);
            e.value = "Yes, I do not need a response";
            this.initWatchers();

            //remove the warning if they start to enter an email
            document.querySelector('#visitorChat_email').addEventListener('keyup',function () {
                document.querySelector('#visitorchat_clientLogin_anonwaning').parentNode.removeChild(document.querySelector('#visitorchat_clientLogin_anonwaning'));
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
            if(this.existInDom('#visitorchat_clientLogin')) 
                document.querySelector('#visitorchat_clientLogin').data = {'validation':false};
            if(this.existInDom('#visitorChat_confirmationEmailForm')) 
                document.querySelector('#visitorChat_confirmationEmailForm').data = {'validation':false};

            //Require email for questions submitted via the footer comment form.
            if(this.existInDom('#visitorChat_footercontainer #visitorChat_email')) 
                document.querySelector('#visitorChat_footercontainer #visitorChat_email').
                classList.add('validate-require-if-question');

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
            if(this.elementReady('#visitorChat_footercontainer #visitorchat_clientLogin')){
                document.querySelector('#visitorChat_footercontainer #visitorchat_clientLogin').bind('validate-form', function (event, result) {
                        document.querySelector('#visitorchat_clientLogin_anonwaning').
                        parentNode.removeChild(document.querySelector('#visitorchat_clientLogin_anonwaning'));
                        if (document.querySelector('#visitorChat_footercontainer #visitorChat_login_submit').value == 'Yes, no response needed'
                            && document.querySelector('#visitorChat_email').value != '') {
                            document.querySelector('#visitorChat_footercontainer #visitorChat_login_submit').value = "Submit";
                        }
        
                        return true;
                    });
        
            }
            if(this.existInDom('#visitorChat_confirmationEmailForm')){
                document.querySelector('#visitorChat_confirmationEmailForm').addEventListener('validate-form', function (event, result) {
                        if (result) {
                            document.querySelector('#visitorChat_confirmationContainer').innerHTML = "<p class='dcf-txt-xs'>The email transcript has been sent to " + document.querySelector('#visitorChat_confiramtionEmail').value + ".</p><button class='dcf-btn dcf-btn-secondary' id='visitorChat_sendAnotherConfirmation'>Send another one</button>";
        
                            $().unbind('#visitorChat_sendAnotherConfirmation');
                            
                            document.querySelector('#visitorChat_sendAnotherConfirmation').addEventListener('click' , function(){
                                // This kinda ugly
                                document.querySelector('#visitorChat_confirmationContainer').innerHTML
                                = Array.prototype.filter.call( document.querySelectorAll(VisitorChat.confirmationHTML), document.querySelector('#visitorChat_confirmationContainer').innerHTML).focus();
                                VisitorChat.initWatchers();
                                return false;
                            });
                        }
        
                        return false;
                    });
            }
            

            //Call the parent
            this._super();

            // Click header or mobile toolbar button to open up Chat
            // jquery mess a lot of things , need to check on thsi for now
            
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
                    var e = document.querySelector('#visitorChat_container') !== null;
                    if(e){
                        document.querySelector('#visitorChat_container').style.height = '320px';
                    }
                    document.querySelector('#dcf-nav-toggle-icon-open-chat').classList.add('dcf-d-none');
                    document.querySelector('#dcf-nav-toggle-icon-close-chat').classList.remove('dcf-d-none');
                } else {
                    //Close the container
                    VisitorChat.widgetIsOpen = false;
                    var e = document.querySelector('#visitorChat_container') !== null;
                    if(e){
                        document.querySelector('#visitorChat_container').style.height = '0px';
                    }
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

            if(this.elementReady('#visitorChat_logout')){
                $('#visitorChat_logout').on('click keypress', (function (event) {
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
            }

            // var someFunction = function(event){
            //     if (event.type == 'keypress' && ([32,13].indexOf(event.which) == -1)) {
            //         //Must be space or enter to continue
            //         return;
            //     }

            //     if (this.chatStatus == 'CHATTING' && !VisitorChat.confirmClose()) {
            //         return false;
            //     }

            //     if (this.chatStatus == 'CHATTING') {
            //         VisitorChat.changeConversationStatus("CLOSED");
            //         return false;
            //     }

            //     VisitorChat.stop();
            //     return false;
            // }.bind(this);

            // document.querySelector('#visitorChat_logout').addEventListener('click' , someFunction);
            // document.querySelector('#visitorChat_logout').addEventListener('keypress' , someFunction);

            if (VisitorChat.chatStatus == "LOGIN" || VisitorChat.chatStatus == false) {
                //if email_fallback is checked, make sure that the email is required.
                if(this.elementReady('#visitorChat_email_fallback')){
                    document.querySelector('#visitorChat_email_fallback').addEventListener('click' , function () {
                            if(Object.is(this,':checked') || this.config.email_required) {
                                document.querySelector('label[for="visitorChat_email"]').innerText = "Email (Required)";
                                document.querySelector('#visitorChat_email').classList.add('required-entry');
                            } else {
                                document.querySelector('label[for="visitorChat_email"]').innerText = "Email (Optional)";
                                document.querySelector('#visitorChat_email').classList.add('required-entry');
                            }
                        } , false);
                }
            }

            //This will slide down the Name and Email fields, plus the Ask button
            if(this.elementReady('#visitorChat_messageBox')){
                document.querySelector('#visitorChat_messageBox').addEventListener('keyup', function () {
                    if (idm.getDisplayName()) {
                        document.querySelector('#visitorChat_name').value = idm.getDisplayName();
                    }
                    if (idm.getEmailAddress()) {
                        document.querySelector('#visitorChat_email').value = idm.getEmailAddress();
                    }
    
                    //this.downSlide();
                    $('.visitorChat_info, #visitorChat_login_submit').slideDown('fast', function(){
                        
                        if (VisitorChat.initialMessage && !Object.is(document.querySelector('#visitorChat_messageBox'),':focus')) {
                            document.querySelector('#visitorChat_email').focus();
                        }
                    });
                } , {once : true});
            }
            

            //From down below is definietly not the best way to check for document ready , but this works for now
            // First checking if the query selector is null or not , then do the thing , might have to make a seperate function for it 
            if(this.elementReady('#visitorChat_failedOptions_yes')){
                document.querySelector('#visitorChat_failedOptions_yes').addEventListener('click', function() {
                        VisitorChat.stop(function(){
                            VisitorChat.startEmail();
                            if (VisitorChat.initialMessage) {
                                document.querySelector('#visitorChat_messageBox').value = VisitorChat.initialMessage;
                            }
                            document.querySelector('#visitorChat_name').value = VisitorChat.name;
                            document.querySelector('#visitorChat_email').value = VisitorChat.email;
                            document.querySelector('#visitorChat_email').focus();
                             //testing for the keyup function , unsure this even works but it's ok for now
                            document.querySelector('#visitorChat_messageBox').addEventListener("keyup", function (){
                                console.log("this works ?");
                            });
                        });
        
                        return true;
                    });
            }

            if(this.elementReady('#visitorChat_confirmationEmail')){
                document.querySelector('#visitorChat_confirmationEmail').addEventListener('keypress', function (e) {
                    if (e.which == 13) {
                        e.preventDefault();
                        document.querySelector('#visitorChat_confirmationEmailForm').submit();
                    }
                });
            }
            
            if(this.elementReady('#visitorChat_failedOptions_no')){
                document.querySelector('#visitorChat_failedOptions_no').addEventListener('click', function(){
                        VisitorChat.stop();
                        return true;
                });
            }

            if (this.chatStatus) {
                document.querySelector('#visitorChat_logout').style.display = 'inline-block';
                document.querySelector('#visitorChat_header_text').style.marginRight = '1.777em';
            } else {
                document.querySelector('#visitorChat_logout').style.display = 'none';
                document.querySelector('#visitorChat_header_text').style.marginRight = 0;
            }

            //set the for_url
            if(this.elementReady('#initial_url')){
                document.querySelector('#initial_url').value = document.URL;
                document.querySelector('#initial_pagetitle').value = document.title;
            }
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
            var request = new XMLHttpRequest();
            request.open('POST',this.serverURL + "conversation/" + this.conversationID + "/edit?format=json&" + this.getURLSessionParam(),true );
            request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
            request.withCredentials = true;
            request.send("client_is_typing=" + newStatus);
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

            if(this.existInDom('#visitorChat_messageForm')){
                document.querySelector('#visitorChat_messageForm').parentNode.removeChild(document.querySelector('#visitorChat_messageForm'));
            }
            var e = document.querySelector('#visitorChat_closed');

            e.insertAdjacentHTML('beforeend',data['confirmationHTML'] );
            e.setAttribute('tabindex', '-1');
            document.querySelector('#visitorChat_confiramtionEmail').focus();

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
                //document.querySelector('#visitorChat_is_typing').style.display = 'none';
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
            if (document.querySelector('#visitorChat_container').style.display != "none") {
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
            document.querySelector('#visitorChat_logout').style.display = 'none';
            document.querySelector('#dcf-nav-toggle-icon-open-chat').classList.remove('dcf-d-none');
            document.querySelector('#dcf-nav-toggle-icon-close-chat').classList.add('dcf-d-none');
            this.widgetIsOpen = false;
            this.displaySiteAvailability();
        },

        displaySiteAvailability:function (available) {
            if (available == null) {
                available = VisitorChat.operatorsAvailable;
            }

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
                // This kinda work I guess
                var e = document.querySelector('#visitorChat_header, #dcf-mobile-toggle-chat');
                e.setAttribute('aria-label', 'Close the ' + text + ' widget');
                e.setAttribute('aria-expanded', 'true');
                document.querySelector('.dcf-nav-toggle-label-chat').innerText = 'Close';
            } else {
                var e = document.querySelector('#visitorChat_header, #dcf-mobile-toggle-chat');
                e.setAttribute('aria-label', 'Open the ' + text + ' widget');
                e.setAttribute('aria-expanded', 'false');
                document.querySelector('.dcf-nav-toggle-label-chat').innerText = text;
            }

            return true;
        },

        setHeight: function(el, val) {
            if (typeof val === "function") val = val();
            if (typeof val === "string") el.style.height = val;
            else el.style.height = val + "px";
        },
        
        // This function check whenever the followin querySelector is loaded or not
        // This is how I check for domReady in Chatbase but should be fix in the future , since this is not the best way to check it
        elementReady :function(e){
            return document.querySelector(e) !== null;
        },

        //This might be a better elementReady , more testing needed
        existInDom : function(e){
            var element = document.querySelector(e);
            if (typeof(element) != 'undefined' && element != null)
            {
                return true;
            }
            return false;
        },

        // This repalce the 
        mobileOpen: function (event){
            if (event.type == 'keypress' && ([32,13].indexOf(event.which) == -1)) {
                //Must be space or enter to continue
                return;
            }
            var a = document.querySelector('#visitorChat_container');
            if(!Object.is(a,':visible')) {
                //Open the container
                VisitorChat.widgetIsOpen = true;
                document.querySelector('#visitorChat').classList.add('visitorChat_open');
                var e = document.querySelector('#visitorChat_container') !== null;
                if(e){
                    document.querySelector('#visitorChat_container').style.height = '320px';
                }
                document.querySelector('#dcf-nav-toggle-icon-open-chat').classList.add('dcf-d-none');
                document.querySelector('#dcf-nav-toggle-icon-close-chat').classList.remove('dcf-d-none');
            } else {
                //Close the container
                VisitorChat.widgetIsOpen = false;
                var e = document.querySelector('#visitorChat_container') !== null;
                if(e){
                    document.querySelector('#visitorChat_container').style.height = '0px';
                }
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
        },
        

        downSlide : function(){
            document.querySelector('.visitorChat_info, #visitorChat_login_submit').style.height = '320px';
            once(1 , function(){
               if (VisitorChat.initialMessage && !Object.is(document.querySelector('#visitorChat_messageBox'),':focus')) {
                    document.querySelector('#visitorChat_email').focus();
                }
            });
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
