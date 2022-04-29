<?php
require_once(\UNL\VisitorChat\Controller::$applicationDir . "/www/js/VisitorChat/5.0" . "/SimpleJavaScriptInheritance.js");
require_once(\UNL\VisitorChat\Controller::$applicationDir . "/www/js/VisitorChat/5.0" . "/form.js");
?>

/*
 * The base Chat class.  This class can be extended.
 * However, the application is built so that ONLY one
 * instance of it is allowed at a time.  And that instance
 * MUST be a variable called VisitorChat.
 *
 * @author Michael Fairchild <mfairchild365@gmail.com>
 * @author Caleb Wiedel
 */
var VisitorChat_ChatBase = Class.extend({
    //The id of the latest message for this conversation on the server.
    latestMessageId:0,

    //The current chat status, ie: login, searching, chatting, closed.
    chatStatus:false,

    //The chat sever url.
    serverURL:false,

    //The refresh rate of the chat.
    refreshRate:2000,

    //The original site title of the current web page.
    siteTitle:document.title,

    //The php session ID as determined by the server.  This passed due to IE not handling sessions with ajax and CORS.
    phpsessid:false,

    //True if the window that the chat is in is visible.
    windowVisible:true,

    //The timer ID for the looping process of the main chat.
    loopID:false,

    //The timer ID for the looping proccess of the alert notification.
    alertID:false,

    //Is the chat currently open?  Is true when the chat has been started, false when stopped.
    chatOpened:false,

    //The current conversationID for the user.
    conversationID:false,

    //The current user id.
    userID:false,

    //The chatbot user id.
    chatbotUserID:false,

    chatbotClientMessage: false,

    // 'TEST' or 'PROD'
    chatbotEnv: 'PROD',

    blocked:false,

    //True if operators have been checked (so that they will only be checked once)
    operatorsChecked:false,

    //True if chatbots have been checked (so that they will only be checked once)
    chatbotsChecked:false,

    //True if there are operators currently available
    operatorsAvailable:false,

    //An array of current notifications
    notifications:new Array(),

    //true if there are any pending updateChat Ajax connections
    pendingChatAJAX:false,

    //true if there are any pending updateUserInfo Ajax connections
    pendingUserAJAX:false,

    //Should large popup windows be displayed for notifications?
    popupNotifications:false,

    version: 5.0,

    nonCORSDomain: 'unl.edu',

    config: {},

    //timeout for the is_typing status
    isTypingTimeout:false,

    /**
     * Constructor function.
     */
    init:function (serverURL, refreshRate) {
        //set vars
        this.serverURL = serverURL;

        if (typeof visitorchat_config == "object") {
            this.config = Object.assign(this.config, visitorchat_config);
        }

        //Change to https if we need to.
        if ('https:' == document.location.protocol) {
            this.serverURL = serverURL.replace('http://', 'https://');
        }

        this.refreshRate = refreshRate;

        this.initAjaxPool();

        //Start the chat
        this.loadStyles();
        this.initWindow();

        // This needs more work , hasn't finished yet
        $(document).ready(function(){
            this.updateUserInfo();
            this.initWatchers();
        }.bind(this));
        
        


    },

    // Ajax related, I don't know if I can fix this
    // Eric recommended this , will work around this
    xhrPool:[],
    xhrAbortAll: function () {
        this.xhrPool.forEach( (idx, jqXHR) => {
            jqXHR.abort();
        });
    },

    xhrAjaxSetup: function(){
        this.xhrPool[xhrPool.length] = jqXHR;
    },

    initAjaxPool: function()
    {
        $.xhrPool = [];
        $.xhrPool.abortAll = function() {
            $(this).each(function(idx, jqXHR) {
                jqXHR.abort();
            });
            $.xhrPool.length = 0
        };

        $.ajaxSetup({
            beforeSend: function(jqXHR) {
                //VisitorChat.xhrPool[xhrPool.length] = jqXHR;
                $.xhrPool.push = jqXHR;
            },
            complete: function(jqXHR) {
                var index = $.xhrPool.indexOf(jqXHR);
                if (index > -1) {
                    //VisitorChat.xhrPool.splice(index, 1);
                    $.xhrPool.splice(index, 1);
                }
            }
        });
    },

    usePhpSessIdCookie: function() {
      var domainName = window.location.hostname;
      var nonCORSDomainLength = this.nonCORSDomain.length;
      // Use cookie if MSIE browser or CORSDomain
      return navigator.userAgent.indexOf("MSIE") !== -1 || (domainName.length >= nonCORSDomainLength && domainName.substr(domainName.length-nonCORSDomainLength, nonCORSDomainLength) != this.nonCORSDomain);
    },

    getURLSessionParam: function() {
        if (!this.usePhpSessIdCookie()) {
            return '';
        }

        return '&PHPSESSID=' + this.phpsessid;
    },

    /**
     * Initalize event watchers related to the current window.
     */
    initWindow:function () {
        // Add blur for both document + window
        document.addEventListener('blur', function () {

             VisitorChat.windowVisible = false;
        });

        window.addEventListener('blur', function () {

            VisitorChat.windowVisible = false;
       });

       // Add focus 
        document.addEventListener('focus', function () {
            VisitorChat.windowVisible = true;
            VisitorChat.clearAlert();
            document.title = VisitorChat.siteTitle;
        });
        
        window.addEventListener('focus', function () {
            VisitorChat.windowVisible = true;
            VisitorChat.clearAlert();
            document.title = VisitorChat.siteTitle;
        });
    },

    /**
     * Start function.  This function starts the application
     * flow of the chat.
     */
    start:function () {
        this.initWatchers();

        this.chatOpened = true;

        clearTimeout(VisitorChat.loopID);

        VisitorChat_Timer_ID = VisitorChat.loop();
    },

    /**
     * Run function.  Tells the chat to update.  This is called
     * on every heartbeat of the application.
     */
    run:function () {
        this.updateChat();
    },

    /**
     * Generates the current chat URL.
     */
    generateChatURL:function () {
        return this.serverURL + "conversation?format=json&last=" + this.latestMessageId + "&" + this.getURLSessionParam();
    },

    /**
     * loadStyles loads all of the required styles for the chat.
     */
    loadStyles:function () {
    },

    /**
     * updateUserInfo grabs data about the current user from the
     * chat sever, this data includes session id.
     */
    updateUserInfo:function () {
        //Don't flood the server
        if (this.pendingUserAJAX) {
            return false;
        }

        this.pendingUserAJAX = true;

        var checkOperators = "";
        if (!this.operatorsChecked) {
            checkOperators = "&checkOperators=" + escape(document.URL);
        }

        var checkChatbots = "";
        if (!this.chatbotsChecked) {
          sessionStorage.removeItem('chatbotID');
          sessionStorage.removeItem('chatbotName');
          checkChatbots = "&checkChatbots=" + escape(document.URL);
        }

        //Start the chat.
        // var request = new XMLHttpRequest();
        // request.open('POST' , this.serverURL + "user/info?format=json" + this.getURLSessionParam() + checkOperators + checkChatbots, true);
        // request.withCredentials = true;
        // request.dataType = "json";

        $.ajax({
            url:this.serverURL + "user/info?format=json" + this.getURLSessionParam() + checkOperators + checkChatbots,
            xhrFields:{
                withCredentials:true
            },
            dataType:"json",
            success:(function (data, textStatus, jqXHR) {
                this.handleUserDataResponse(data);
            }.bind(this)),
            complete:function(data, textStatus, jqXHR)
            {
                VisitorChat.pendingUserAJAX = false;
            }
        });
    },

    handleUserDataResponse:function (data) {
        if (typeof data['userID'] !== 'undefined') {
          this.userID = data['userID'];
        }

        this.updatePHPSESSID(data['phpssid']);

        if (!this.operatorsChecked) {
            this.operatorsAvailable = data['operatorsAvailable'];
        }

        if ((typeof data['chatbotID'] !== 'undefined') &&
            (data['chatbotID'] !== null) &&
            (typeof data['chatbotName'] !== 'undefined') &&
            (data['chatbotName'] !== null)) {
          sessionStorage.setItem('chatbotID', parseInt(data['chatbotID']));
          sessionStorage.setItem('chatbotName', data['chatbotName']);
        }

        this.blocked = data['blocked'];

        this.operatorsChecked = true;
        this.chatbotsChecked = true;

        if (data['popupNotifications'] != undefined) {
            this.popupNotifications = data['popupNotifications'];
        }
    },

    updatePHPSESSID:function (phpsessid) {
        this.phpsessid = phpsessid;
    },

    /**
     * updateChat function.  This function will only fire when
     * the user is chatting, the session is set and the chat is open.
     *
     * Grabs current chat data from the server.  If new data is present
     * the chat will be updated.
     */
    updateChat:function (url, force) {
        //Check if we should not update.
        if ((this.chatStatus == 'LOGIN'
            || this.chatStatus == 'CLOSED'
            || this.chatStatus == 'OPERATOR_LOOKUP_FAILED'
            || this.chatStatus == 'EMAILED'
            || this.chatStatus == 'CAPTCHA'
            || this.chatOpened == false
            || this.phpsessid == false
            || this.pendingChatAJAX == true)
            && force != true) {
            return false;
        }

        if (url == undefined) {
            url = this.generateChatURL();
        }

        this.pendingChatAJAX = true;

        $.ajax({
            url:url,
            xhrFields:{
                withCredentials:true
            },
            dataType:"json",
            error: function(jqXHR, textStatus, errorThrown) {
                //alert('test: ' + textStatus);
            },
            success:(function (data, textStatus, jqXHR) {
                this.updateChatWithData(data);
                this.pendingChatAJAX = false;
            }.bind(this))
        });
    },

    /**
     * updateChatWithData updates the chat with data grabbed by
     * ajax functions.  This function actually looks at the returned
     * conversation status and fires off a related function.
     */
    updateChatWithData:function (data) {
        if (data['status'] !== undefined) {
            this.chatStatus = data['status'];
        }

        if (data['phpssid'] !== undefined) {
            this.updatePHPSESSID(data['phpssid']);
        }

        if (data['conversation_id'] !== undefined) {
            this.conversationID = data['conversation_id'];
        }

        switch (this.chatStatus) {
            case 'OPERATOR_LOOKUP_FAILED':
                this.onConversationStatus_OperatorLookupFailed(data);
                break;
            case 'CHATTING':
                this.onConversationStatus_Chatting(data);
                break;
            case 'CLOSED':
                this.onConversationStatus_Closed(data);
                break;
            case 'OPERATOR_PENDING_APPROVAL':
                this.onConversationStatus_OperatorPendingApproval(data);
                break;
            case 'SEARCHING':
                this.onConversationStatus_Searching(data);
                break;
            case 'LOGIN':
                this.onConversationStatus_Login(data);
                break;
            case 'EMAILED':
                this.onConversationStatus_Emailed(data);
                break;
            case 'CAPTCHA':
                this.onConversationStatus_Captcha(data);
                break;
        }

        return true;
    },

    updateLatestMessageId:function (latest) {
        this.latestMessageId = latest;
        var e = document.querySelector('.unl_visitorchat_form');
        if (action = e.getAttribute('action')) {
            action = action.replace(/last=(\d)*/g, "last=" + latest);
            e.setAttribute('action', action);         
        }
    },

    /**
     * onConversationStatus_OperatorLookupFailed
     * Related status code: OPERATOR_LOOKUP_FAILED
     * Details: This function will be called when an operator can not be found.
     * This will be called when no operator can be found and an email has not been sent.
     */
    onConversationStatus_OperatorLookupFailed:function (data) {
        var html = '<div class="chat_notify" tabindex="-1">We could not find an operator to help you.  Please try back later.</div>';
        this.updateChatContainerWithHTML("#visitorChat_container", html);
    },

    onConversationStatus_Captcha:function (data) {
    },

    /**
     * onConversationStatus_Emailed
     * Related status code: EMAILED
     * Details: This function will be called when a converstation
     * falls back to an email.  This means that an operator was not available
     * but an email could be sent.
     */
    onConversationStatus_Emailed:function (data) {
        clearTimeout(VisitorChat.loopID);
        var html = '<div class="chat_notify" id="visitorChat_emailed" tabindex="-1">Your message has been emailed.</div>';
        this.updateChatContainerWithHTML("#visitorChat_container", html);
    },

    /**
     * onConversationStatus_Chatting
     * Related status code: CHATTING
     * Details: This function will be called when an operator was found
     * and the operator accepted the conversation.  We are now chatting.
     * HTML will be sent along with the data parm if new updates were found.
     */
    onConversationStatus_Chatting:function (data) {
        if (this.latestMessageId == 0) {
            if (data['html'] == undefined) {
                return false;
            }

            this.updateChatContainerWithHTML("#visitorChat_container", data['html']);
        }

        if (data['messages'] == undefined) {
            return true;
        }

        this.appendMessages(data['messages']);
    },

    /**
     * onOperatorMessage
     *
     * Fired when a message by an operator is received.
     */
    onOperatorMessage:function (message) {
    },

    /**
     * onClientMessage
     *
     * Fired when a message by a client is received.
     */
    onClientMessage:function (message) {
    },

    /**
     * AppendMessages
     * Used to append messages to the current conversation.
     * The messages param should be a json formmated array of messages.
     */
    appendMessages:function (messages) {
        if (messages.length == 0) {
            return true;
        }

        for (id in messages) {
            //skip if a message with this id already exists
            if (document.querySelectorAll('#visitorChat_message_' + id).length != 0) {
                continue;
            }

            this.appendMessage(id, messages[id]);

            if (messages[id]['poster']['type'] == 'operator') {
                this.onOperatorMessage(messages[id]);
            } else {
                this.onClientMessage(messages[id]);
            }

            id = parseInt(id)
            if (id > this.latestMessageId) {
                this.updateLatestMessageId(id);
            }
        }

        //alert
        this.clearAlert();
        this.alert();

        //Scroll if we can.
        this.scroll();
        this.initWatchers();
    },

    /**
     * appendMessage
     * Appends a single message to the conversation.
     */
    appendMessage:function (id, message) {
        var e = document.querySelector("#visitorChat_chatBox ul");
        e.insertAdjacentHTML('beforeend',"<li id='visitorChat_message_" + id + "' class='" + message['class'] + "'>" + message['message'] +
        "<div class='dcf-d-flex dcf-jc-between dcf-mt-1 dcf-txt-xs unl-dark-gray'><span class='stamp'>from " + message['poster']['name'] + "</span><span class='dcf-sr-only'> at </span><span class='timestamp'>" + message['date'] + "</span></div>" +
        "</li>");
    },

    /**
     * onConversationStatus_Closed
     * Related status code: CLOSED
     * Details: This function will be called when a conversation has been closed.
     * A close event happens when a client logs out or an operator closes the chat
     * from their end or the current operator logs out.
     */
    onConversationStatus_Closed:function (data) {
        if (data['html'] != undefined) {
            this.updateChatContainerWithHTML("#visitorChat_container", data['html']);
        }

        var e = document.getElementById("visitorChat_container");
        e.insertAdjacentHTML('beforeend',"<div class='visitorChat_center'></div>");
        clearTimeout(VisitorChat.loopID);

        var html = '<div class="chat_notify" id="visitorChat_closed" tabindex="-1"><p class="dcf-mb-1">This conversation has ended.</p></div>';
        this.updateChatContainerWithHTML(".visitorChat_center", html);

        if (data['messages'] == undefined) {
            return true;
        }

        this.appendMessages(data['messages']);
    },

    /**
     * onConversationStatus_OperatorPendingApproval
     * Related status code: OPERATOR_PENDING_APPROVAL
     * Details: This function will be called when a conversation is currently
     * pending approval by an operator.  This means that an operator was found
     * but we are waiting on them to accept or reject the request.
     */
    onConversationStatus_OperatorPendingApproval:function (data) {
        this.onConversationStatus_Searching(data);
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
        var html = '<div class="chat_notify visitorChat_loading" tabindex="-1">Please wait while we find someone to help you.</div>';
        this.updateChatContainerWithHTML("#visitorChat_container", html);
    },

    /**
     * onConversationStatus_Login
     * Related status code: LOGIN
     * Details: This function is called when the server is waiting for
     * the client to log in.  HTML data of the login form is sent in the data param.
     */
    onConversationStatus_Login:function (data) {
        if (data['html'] == undefined) {
            return true;
        }

        //Update the chat container.
        this.updateChatContainerWithHTML("#visitorChat_container", data['html']);
    },

    /**
     * updateChatContainerWithHTML will update a given html container
     * with html and then scroll that container and initalize any watcher
     * functions.
     */
    updateChatContainerWithHTML:function (selector, html, sendAlerts) {
        //$.parseHTML(html)[0].outerHTML is used to compare the rendered html (the browser can change quotes, etc)
        //It just makes the comparison more accurate (at the cost of a little speed)
        if (document.querySelector(selector).innerHTML === this.htmlParse((html)[0].outerHTML)) {
            //Contents are the same, nothing to be done here.
            return;
        }

        //Should we alert the user?
        if (sendAlerts != false) {
            this.clearAlert();
            this.alert();
        }

        //Update the html
        var container = document.querySelector(selector);
        container.innerHTML = html;
        //Send focus to the first input or child for a11y (notify of change)
        //Contents should be wrapped in their own container div or element, so we need to focus that.
        var first_input = document.querySelector('input[type="text"],textarea', container);
        //This doesn't seems to work
        var first_child = document.querySelector(':first-child', container);

        if (first_input.length) {
            //focus first input
            first_input[0].focus();
        } else if (first_child.length) {
            //focus first child
            first_child[0].setAttribute('tabindex', '-1').focus();
        } else {
            //focus the container
            container.setAttribute('tabindex', '-1');
        }

        //Scroll if we can.
        this.scroll();

        //Reinitalize the watcher functions.
        this.initWatchers();
    },

    //Toan function to dealt with parseHtml
    htmlParse: function(str) {
        var tmp = document.implementation.createHTMLDocument("");
        tmp.body.innerHTML = str;
        return tmp.body.children;
    },
    /**
     * initWatchers sets up watcher functions for events related to chatting.
     * this function is called whenever the chat is updated to ensure that the correct
     * watcher functions are in place.  Be sure to always unbind before you
     * add a new watcher.
     */
    initWatchers:function () {
         var e = document.querySelector('#visitorChat_messageBox') !== null;
         if(e){
            document.querySelector('#visitorChat_messageBox').addEventListener('keypress' ,(function (e) {
                if (VisitorChat.chatStatus == false) {
                    return true;
                }
    
                if (VisitorChat.chatStatus == 'LOGIN') {
                    return true;
                }
    
                VisitorChat.handleIsTyping();
    
                if (e.which == 13 && !e.shiftKey) {
                    e.preventDefault();
                    // submit() and val('') is a jquery function imo
                    if (VisitorChat.chatStatus == 'LOGIN') {
                       document.querySelector('#visitorchat_clientLogin').submit();
                    } else if(VisitorChat.chatStatus != false) {
                        document.querySelector('#visitorchat_clientLogin').submit();
                        document.querySelector('#visitorChat_messageBox').value = ' ';
                    }
                }
            }));
    
        var el = document.querySelector('#visitorChat_messageForm, #visitorchat_clientLogin') !== null;
        if(el){
            document.querySelector('#visitorChat_messageForm, #visitorchat_clientLogin').addEventListener('submit', function() {
                var chatbotIntentMessage = document.querySelectorAll('#visitorChatbot_intent').value;
                var chatbotIntentDefaults = document.querySelectorAll('#visitorChatbot_intent_defaults').value;
              
                // Handle chatbot intent message as a message from user
                if (chatbotIntentMessage && chatbotIntentMessage.trim().length > 0) {
                    document.querySelectorAll('#visitorChat_messageBox').value = chatbotIntentMessage;
                if (chatbotIntentDefaults && chatbotIntentDefaults.trim().length > 0) {
                    VisitorChat.sessionAttributes = JSON.parse(chatbotIntentDefaults);
                    }
                }
              
                      
                var message = document.querySelector('#visitorChat_messageBox').value;
              
                if (message.trim().length == 0) {
                    // ignore empty messages
                    return false;
                }
              
                // check if chatting with chatbot and capture client message
                if (VisitorChat.method == 'chatbot') {
                    // set chatbot message to be sent once processed by VisitorChat
                if (VisitorChat.userID === false) {
                    VisitorChat.updateUserInfo();
                }
                    document.querySelector('#visitorChat_message_submit').disabled = true;
                    document.querySelector('#visitorChat_message_submit').setAttribute('disabled', 'disabled');
                    VisitorChat.chatbotClientMessage = message.trim();
                }
            });
              
                this.initAjaxForms();
            }
         }
    },

    generateUUID: function() { // Public Domain/MIT
      var d = new Date().getTime();
      if (typeof performance !== 'undefined' && typeof performance.now === 'function'){
        d += performance.now(); //use high-precision timer if available
      }
      return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
        var r = (d + Math.random() * 16) % 16 | 0;
        d = Math.floor(d / 16);
        return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
      });
    },

    getChatbotID: function() {
      if (sessionStorage.chatbotID) {
        return sessionStorage.getItem('chatbotID');
      } else {
        return 0;
      }
    },

    getChatbotName: function() {
      if (sessionStorage.chatbotName) {
        return sessionStorage.getItem('chatbotName');
      } else {
        return false;
      }
    },

    getChatbotUserID: function() {
      if (sessionStorage.chatbotUserID) {
        return sessionStorage.getItem('chatbotUserID');
      } else {
        var chatbotUserID = this.generateUUID();
        sessionStorage.setItem('chatbotUserID', chatbotUserID);
        return chatbotUserID;
      }
    },

  sendChatbotMessage: function(message) {

      WDN.jQuery('#visitorChat_is_typing').text("The chatbot is processing.").show(500);

      if (VisitorChat.chatbotUserID === false) {
        VisitorChat.chatbotUserID = VisitorChat.getChatbotUserID();
      }

      if (VisitorChat.userID) {
        VisitorChat.sessionAttributes.userID = VisitorChat.userID;
      }

      if (VisitorChat.name) {
        VisitorChat.sessionAttributes.name = VisitorChat.name;
      }

      if (VisitorChat.email) {
        VisitorChat.sessionAttributes.email = VisitorChat.email;
      }

      // send it to the Lex runtime
      var params = {
        botAlias: VisitorChat.getChatbotName() + '_' + VisitorChat.chatbotEnv,
        botName: VisitorChat.getChatbotName(),
        inputText: message,
        userId: VisitorChat.chatbotUserID,
        sessionAttributes: VisitorChat.sessionAttributes
      };
      //console.log('sendChatbotMessage params', params);

      VisitorChat.lexruntime.postText(params, function(err, data) {
        if (err) {
          VisitorChat.recordChatbotError(err);
        }
        if (data) {
          // capture the sessionAttributes for the next cycle
          VisitorChat.sessionAttributes = data.sessionAttributes;
          VisitorChat.recordChatbotResponse(data);
        }
      });

      WDN.jQuery('#visitorChat_is_typing').hide(500);
    },

    recordChatbotError: function (err) {
      //console.log('Error sending message to AWS', err.stack);
      var message = 'There was an error processing message to chatbot, please try message again.';
      var data = {
        'users_id': this.getChatbotID(),
        'conversations_id': this.conversationID,
        'message': message,
        '_class': 'UNL\\VisitorChat\\Message\\Edit'
      }

      //Send a post response.
      $.ajax({
        type:"POST",
        url: this.generateChatURL(),
        xhrFields:{
          withCredentials:true
        },
        data: data,
        success:(function (data, textStatus, jqXHR) {
          //console.log('recordChatbotError data', data);
          this.handleAjaxResponse(data, textStatus);
          document.querySelector('#visitorChat_chatBox').classList.remove('visitorChat_loading');
        }.bind(this))
      });
    },

    recordChatbotResponse: function(lexResponse) {
      var message = lexResponse.message;
      var data = {
        'users_id': this.getChatbotID(),
        'conversations_id': this.conversationID,
        'message': message,
        '_class': 'UNL\\VisitorChat\\Message\\Edit'
      }

      //Send a post response.
      $.ajax({
        type:"POST",
        url: this.generateChatURL(),
        xhrFields:{
          withCredentials:true
        },
        data: data,
        success:(function (data, textStatus, jqXHR) {
          //console.log('recordChatbotResponse data', data);
          this.handleAjaxResponse(data, textStatus);
          document.querySelector('#visitorChat_chatBox').classList.remove('visitorChat_loading');
        }.bind(this))
      });
    },

    handleIsTyping:function () {
        //empty, operator and client will need to implement this differently
    },

    /**
     * scroll is used to scroll the current chat to the bottom of the chat div.
     */
    scroll:function () {
        // Check if it's null or not, only execute when it isn't
        var el = document.querySelector("#visitorChat_chatBox") !== null;
        if(el){
            document.querySelector("#visitorChat_chatBox").scrollTo(0, document.querySelector("#visitorChat_chatBox").scrollHeight);
        }
    },

    /**
     * initAjaxForms initalizes ajax forms used by the chat.
     */
    initAjaxForms:function () {
        var options = {
            clearForm:true,
            timeout: 10000,
            dataType:"json",
            success:(function (data, textStatus, jqXHR) {
                this.handleAjaxResponse(data, textStatus);

                // handle chatbot message if set
                if (VisitorChat.chatbotClientMessage) {
                  VisitorChat.sendChatbotMessage(VisitorChat.chatbotClientMessage);
                  document.querySelector('#visitorChat_message_submit').disabled = true;
                  document.querySelector('#visitorChat_message_submit').removeAttribute("disabled");
                  VisitorChat.chatbotClientMessage = false;
                }

                document.querySelector('#visitorChat_chatBox').classList.remove('visitorChat_loading');
            }.bind(this)),

            error:(function (data, textStatus, jqXHR) {
                // Temp logging to help debug error
                console.log('initAjaxForms fail data', data);
                console.log('initAjaxForms fail textStatus', textStatus);
                console.log('initAjaxForms fail jqXHR', jqXHR);

                if (VisitorChat.chatStatus == 'LOGIN') {

                  var errorMessage = 'An error occurred. Please try again.';

                  if (textStatus == 'error') {
                    errorMessage = jqXHR;
                  } else if (textStatus == 'timeout') {
                    errorMessage = 'A timeout error occurred. Close and try again. If the error occurs repeatedly, please again try later.';
                  }

                  //display word filter error (and other errors during login)
                  document.querySelector('#visitorChat_container').textContent = errorMessage;

                } else {
                  console.log('reloading chat...');
                  // Reset chat so does not hang
                  this.updateChat(this.generateChatURL(), true);
                }
            }.bind(this)),

            beforeSubmit:(function (arr, $form, options) {
                return this.ajaxBeforeSubmit(arr, $form, options);
            }.bind(this)),
            crossDomain:true,
            xhrFields:{
                withCredentials:true
            }
        };

        //Keep jquery due to conflict with form.js
        var action = $('.unl_visitorchat_form').attr('action');

        if (action !== undefined && action.indexOf("format=json") == -1) {
            $('.unl_visitorchat_form').attr('action',(function (i, val) {
                return val + '?format=json&' + this.getURLSessionParam();
            }.bind(this)));
        }
            // Need to fix this I think
            $('.unl_visitorchat_form').ajaxForm(options);
          
    },

    onLogin:function () {
        var html = "<div class='visitorChat_loading'></div>";
        var el = document.getElementById('visitorChat_container');
        el.innerHTML = html;
    },

    ajaxBeforeSubmit:function (arr, $form, options) {
        if (VisitorChat.chatStatus == 'LOGIN') {
            VisitorChat.onLogin();
        } else {
            if (VisitorChat.chatStatus != 'CLOSED') {
                var el = document.getElementById('visitorChat_chatBox');
                el.classList.add("visitorChat_loading");
            }
        }

        return true;
    },

    /**
     * The alert function will be called to alert the user of a new notification
     * when the window containing the chat is not in focus.  By default it will
     * just flash the page title.
     */
    alert:function (alertType, force) {
        //1. do not continue if the window is currently focued.
        if (this.windowVisible && force == undefined) {
            return false;
        }

        if (alertType == undefined) {
            alertType = "newMessage";
        }

        //2. update the document title.
        if (document.title == VisitorChat.siteTitle) {
            var message = "New message! ";

            switch (alertType) {
                case 'newMessage':
                    message = "New message! ";
                    break;
                case 'assignment':
                    message = "New assignment! ";
                    break;
                case 'idle':
                    message = "Idle!";
            }

            document.title = message + " " + VisitorChat.siteTitle;
        } else {
            document.title = VisitorChat.siteTitle;
        }

        //Play a sound only on first alert.
        if (!VisitorChat.alertID) {
            this.playSound(alertType);
            this.showNotification(alertType);
        }

        //3. flash the document title.
        VisitorChat.alertID = setTimeout("VisitorChat.alert('"+ alertType +"')", 2000);
    },

    showNotification:function (alertType) {
        //are notifications supported?
        if (!("Notification" in window)) {
            return false;
        }

        // do we have permission?
        if (Notification.permission != 'granted') {
            return false;
        }

        var message = "You received a new Alert!";
        switch (alertType) {
            case 'newMessage':
                message = "You have new messages!";
                break;
            case 'assignment':
                message = "You have a new pending assignment!";
                break;
            case 'idle':
                message = "You have been set to Idle!";
        }

        var notification = new Notification(
            'UNL VisitorChat Alert', {
                body: message,
                icon: VisitorChat.serverURL + 'images/alert.gif'
            }
        );

        notification.onclick = function() {
            //Focus the window.
            window.focus();
            VisitorChat.clearAlert();
        };

        notification.onclose = function() {
            //Focus the window.
            window.focus();
            VisitorChat.clearAlert();
        };

        notifyWindow = undefined;

        if (this.popupNotifications) {
            //Create a notification window.
            notifyWindow = window.open(this.serverURL + 'notifications/notification.php?message='+message,'_blank','width=850,height=650,menubar=no,location=no')
            notifyWindow.focus();
            var timer = setInterval(function() {
                if(notifyWindow.closed) {
                    clearInterval(timer);
                    window.focus();
                    VisitorChat.clearAlert();
                }
            }, 50);
        }

        item = new Array();
        item['notification'] = notification;
        item['window']       = notifyWindow;
        this.notifications.push(item);
    },

    clearAlert:function () {
        if (VisitorChat.alertID) {
            clearTimeout(VisitorChat.alertID);
        }

        document.title = VisitorChat.siteTitle;

        //Set the alertID to false so that we no there are no current alerts.
        VisitorChat.alertID = false;

        for (var id = 0; id<this.notifications.length; id++) {
            if (this.notifications[id]['window'] != undefined) {
                this.notifications[id]['window'].close();
            }
        }

        if (window.webkitNotifications) {
            for (var id = 0; id<this.notifications.length; id++) {
                this.notifications[id]['notification'].cancel();
            }
        }
    },

    playSound:function (alertType) {
        var audioTagSupport = !!(document.createElement('audio').canPlayType);

        if (!audioTagSupport) {
            return false;
        }

        var file = 'message.wav';
        switch (alertType) {
            case 'assignment':
                file = 'alert.wav';
                break;
            case 'newMessage':
                file = 'message.wav';
                break;
            case 'idle':
                file = 'alert.wav';
                break;
        }

        var $soundContainer = $('#visitorChat_sound_container');
        if ($soundContainer.length) {
            var audio = $('<audio />', {
                'src': this.serverURL + "audio/" + file,
                'autoplay': true,
                'aria-hidden': 'true'
            });

            audio.on('ended', function() {
                audio.remove();
            });
            $soundContainer.append(audio);
        }
    },

    /**
     * HandleAjaxresponse will handle responses form ajax functions.
     */
    handleAjaxResponse:function (data, textStatus) {
        if (data["responseText"] !== undefined) {
            data = JSON.parse(data["responseText"]);
        }

        if (textStatus == 'error') {
            //Update the chatbox
            return this.updateChat(this.generateChatURL(), true);
        }

        if (data['phpssid'] !== undefined) {
            this.updatePHPSESSID(data['phpssid']);
        }

        return this.updateChatWithData(data);
    },

    changeConversationStatus:function (status, callback) {
        if (!this.conversationID || this.conversationID == undefined) {
            return false;
        }

        if (callback == undefined) {
            callback = function() {
                VisitorChat.updateChat(VisitorChat.generateChatURL(), true);
            }
        }

        //Send a post response.
        $.ajax({
            type:"POST",
            url:this.serverURL + "conversation/" + this.conversationID + "/edit?format=json&" + this.getURLSessionParam(),
            xhrFields:{
                withCredentials:true
            },
            data:"status=" + status
        }).done(callback);
    },

    /**
     * stop will stop the chat by logging the user out, removing the chat box
     * and reseting chat variables.
     */
    stop:function () {
        //1. stop server updates.
        clearTimeout(VisitorChat.loopID);
        this.chatOpened = false;

        //2. logout
        $.ajax({
            url:this.serverURL + "logout" + "?format=json&" + this.getURLSessionParam(),
            xhrFields:{
                withCredentials:true
            },
            dataType:"json",
            complete:function (jqXHR, textStatus) {
                $.xhrPool.abortAll();
                //this.xhrAbortAll();
            }
        });

        //3. clear vars.
        sessionStorage.removeItem('chatbotUserID');
        this.chatbotUserID = false;

        this.latestMessageId = 0;
        this.chatStatus = false;
    },

    /**
     * the loop function is used to loop the main process of the chat application.
     */
    loop:function () {
        VisitorChat.run();
        VisitorChat.loopID = setTimeout("VisitorChat.loop()", VisitorChat.refreshRate);
    },

    // Toan's code from form.js
    deepExtend: function (out) {
        out = out || {};
      
        for (var i = 1; i < arguments.length; i++) {
          var obj = arguments[i];
      
          if (!obj) continue;
      
          for (var key in obj) {
            if (obj.hasOwnProperty(key)) {
              if (typeof obj[key] === "object" && obj[key] !== null) {
                if (obj[key] instanceof Array) out[key] = obj[key].slice(0);
                else out[key] = deepExtend(out[key], obj[key]);
              } else out[key] = obj[key];
            }
          }
        }
      
        return out;
    }
    
 
});
