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
            $.extend(this.config, visitorchat_config);
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

        $(document).ready($.proxy(function(){
            this.updateUserInfo();
            this.initWatchers();
        }, this));


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
                $.xhrPool.push(jqXHR);
            },
            complete: function(jqXHR) {
                var index = $.xhrPool.indexOf(jqXHR);
                if (index > -1) {
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
        $([window, document]).blur(function () {

            VisitorChat.windowVisible = false;
        });

        $([window, document]).focus(function () {
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
        $.ajax({
            url:this.serverURL + "user/info?format=json" + this.getURLSessionParam() + checkOperators + checkChatbots,
            xhrFields:{
                withCredentials:true
            },
            dataType:"json",
            success:$.proxy(function (data, textStatus, jqXHR) {
                this.handleUserDataResponse(data);
            }, this),
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
            success:$.proxy(function (data, textStatus, jqXHR) {
                this.updateChatWithData(data);
                this.pendingChatAJAX = false;
            }, this)
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

        if (action = $('.unl_visitorchat_form').attr('action')) {
            action = action.replace(/last=(\d)*/g, "last=" + latest);
            $('.unl_visitorchat_form').attr('action', action);
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
            if ($('#visitorChat_message_' + id).length != 0) {
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
        $("#visitorChat_chatBox ul").append("<li id='visitorChat_message_" + id + "' class='" + message['class'] + "'>" + message['message'] +
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

        $("#visitorChat_container").append("<div class='visitorChat_center'></div>");

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
        if ($(selector).html() === $.parseHTML(html)[0].outerHTML) {
            //Contents are the same, nothing to be done here.
            return;
        }

        //Should we alert the user?
        if (sendAlerts != false) {
            this.clearAlert();
            this.alert();
        }

        //Update the html
        var $container = $(selector);
        $container.html(html);
        //Send focus to the first input or child for a11y (notify of change)
        //Contents should be wrapped in their own container div or element, so we need to focus that.
        var $first_input = $('input[type="text"],textarea', $container);
        var $first_child = $(':first-child', $container);

        if ($first_input.length) {
            //focus first input
            $first_input.eq(0).focus();
        } else if ($first_child.length) {
            //focus first child
            $first_child.eq(0).attr('tabindex', '-1').focus();
        } else {
            //focus the container
            $container.attr('tabindex', '-1').focus();
        }

        //Scroll if we can.
        this.scroll();

        //Reinitalize the watcher functions.
        this.initWatchers();
    },

    /**
     * initWatchers sets up watcher functions for events related to chatting.
     * this function is called whenever the chat is updated to ensure that the correct
     * watcher functions are in place.  Be sure to always unbind before you
     * add a new watcher.
     */
    initWatchers:function () {
        $('#visitorChat_messageBox').keypress(function (e) {
            if (VisitorChat.chatStatus == false) {
                return true;
            }

            if (VisitorChat.chatStatus == 'LOGIN') {
                return true;
            }

            VisitorChat.handleIsTyping();

            if (e.which == 13 && !e.shiftKey) {
                e.preventDefault();
                if (VisitorChat.chatStatus == 'LOGIN') {
                  $('#visitorchat_clientLogin').submit();
                } else if(VisitorChat.chatStatus != false) {
                  $('#visitorChat_messageForm').submit();
                  $('#visitorChat_messageBox').val('');
                }
            }
        });

        $('#visitorChat_messageForm, #visitorchat_clientLogin').on('submit', function() {
          var chatbotIntentMessage = $('#visitorChatbot_intent').val();
          var chatbotIntentDefaults = $('#visitorChatbot_intent_defaults').val();

          // Handle chatbot intent message as a message from user
          if (chatbotIntentMessage && chatbotIntentMessage.trim().length > 0) {
            $('#visitorChat_messageBox').val(chatbotIntentMessage);
            if (chatbotIntentDefaults && chatbotIntentDefaults.trim().length > 0) {
              VisitorChat.sessionAttributes = JSON.parse(chatbotIntentDefaults);
            }
          }

          var message = $('#visitorChat_messageBox').val();

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
            $('#visitorChat_message_submit').disabled = true;
            $('#visitorChat_message_submit').attr('disabled', 'disabled');
            VisitorChat.chatbotClientMessage = message.trim();
          }
        });

        this.initAjaxForms();
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
        success:$.proxy(function (data, textStatus, jqXHR) {
          //console.log('recordChatbotError data', data);
          this.handleAjaxResponse(data, textStatus);
          $('#visitorChat_chatBox').removeClass('visitorChat_loading');
        }, this)
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
        success:$.proxy(function (data, textStatus, jqXHR) {
          //console.log('recordChatbotResponse data', data);
          this.handleAjaxResponse(data, textStatus);
          $('#visitorChat_chatBox').removeClass('visitorChat_loading');
        }, this)
      });
    },

    handleIsTyping:function () {
        //empty, operator and client will need to implement this differently
    },

    /**
     * scroll is used to scroll the current chat to the bottom of the chat div.
     */
    scroll:function () {
        $("#visitorChat_chatBox").scrollTop($("#visitorChat_chatBox").prop('scrollHeight'));
    },

    /**
     * initAjaxForms initalizes ajax forms used by the chat.
     */
    initAjaxForms:function () {
        var options = {
            clearForm:true,
            timeout: 10000,
            dataType:"json",
            success:$.proxy(function (data, textStatus, jqXHR) {
                this.handleAjaxResponse(data, textStatus);

                // handle chatbot message if set
                if (VisitorChat.chatbotClientMessage) {
                  VisitorChat.sendChatbotMessage(VisitorChat.chatbotClientMessage);
                  $('#visitorChat_message_submit').disabled = false;
                  $('#visitorChat_message_submit').removeAttr("disabled");
                  VisitorChat.chatbotClientMessage = false;
                }

                $('#visitorChat_chatBox').removeClass('visitorChat_loading');
            }, this),
            error:$.proxy(function (data, textStatus, jqXHR) {
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
                  $('#visitorChat_container').text(errorMessage);

                } else {
                  console.log('reloading chat...');
                  // Reset chat so does not hang
                  this.updateChat(this.generateChatURL(), true);
                }
            }, this),
            beforeSubmit:$.proxy(function (arr, $form, options) {
                return this.ajaxBeforeSubmit(arr, $form, options);
            }, this),
            crossDomain:true,
            xhrFields:{
                withCredentials:true
            }
        };

        var action = $('.unl_visitorchat_form').attr('action');

        if (action !== undefined && action.indexOf("format=json") == -1) {
            $('.unl_visitorchat_form').attr('action', $.proxy(function (i, val) {
                return val + '?format=json&' + this.getURLSessionParam();
            }, this));
        }

        //bind form using 'ajaxForm'
        $('.unl_visitorchat_form').ajaxForm(options);
        //this.myAjaxForm(options);
    },

    onLogin:function () {
        var html = "<div class='visitorChat_loading'></div>";
        ('#visitorChat_container').html(html);
    },

    ajaxBeforeSubmit:function (arr, $form, options) {
        if (VisitorChat.chatStatus == 'LOGIN') {
            VisitorChat.onLogin();
        } else {
            if (VisitorChat.chatStatus != 'CLOSED') {
                $('#visitorChat_chatBox').addClass("visitorChat_loading");
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
            data = $.parseJSON(data["responseText"]);
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
        
    //Toan's code for the conversion , has not been tested yer , only implemented 
    ready: function(fn) {
        if (document.readyState != 'loading'){
          fn();
        } else {
          document.addEventListener('DOMContentLoaded', fn);
        }
    },

    myAjaxForm: function(options){
        // in jQuery 1.3+ we can fix mistakes with the ready state
        if (this.length === 0) {
            var o = { s: this.selector, c: this.context };
            if (!$.isReady && o.s) {
                log('DOM not ready, queuing ajaxForm');
                (function() {
                    (o.s,o.c).myAjaxForm(options);
                });
                return this;
            }
            // is your DOM ready?  http://docs.jquery.com/Tutorials:Introducing_$(document).ready()
            log('terminating; zero elements found by selector' + ($.isReady ? '' : ' (DOM not ready)'));
            return this;
        }
    
        return this.ajaxFormUnbind().bind('submit.form-plugin', function(e) {
            if (!e.isDefaultPrevented()) { // if event has been canceled, don't proceed
                e.preventDefault();
                this.myAjaxSubmit(options);
            }
        }).bind('click.form-plugin', function(e) {
            var el = e.target;
            if (!(el.is(":submit,input:image"))) {
                // is this a child element of the submit el?  (ex: a span within a button)
                var t = document.querySelector(el.closest(':submit'));
                if (t.length == 0) {
                    return;
                }
                target = t[0];
            }
            var form = this;
            form.clk = target;
            if (target.type == 'image') {
                if (e.offsetX != undefined) {
                    form.clk_x = e.offsetX;
                    form.clk_y = e.offsetY;
                } else if (typeof $.fn.offset == 'function') { // try to use dimensions plugin
                    var offset = $el.offset();
                    form.clk_x = e.pageX - offset.left;
                    form.clk_y = e.pageY - offset.top;
                } else {
                    form.clk_x = e.pageX - target.offsetLeft;
                    form.clk_y = e.pageY - target.offsetTop;
                }
            }
            // clear form vars
            setTimeout(function() { form.clk = form.clk_x = form.clk_y = null; }, 100);
        });
    },

    ajaxFormUnbind: function(){
        return this.removeEventListener('submit.form-plugin click.form-plugin');
    },

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
    },

    myAjaxSubmit:function (options){
        // fast fail if nothing selected (http://dev.jquery.com/ticket/2752)
     if (!this.length) {
         log('ajaxSubmit: skipping submit process - no element selected');
         return this;
     }
     
     var method, action, url, form = this;
 
     if (typeof options == 'function') {
         options = { success: options };
     }
 
     method = this.getAttribute('method');
     action = this.getAttribute('action');
     url = (typeof action === 'string') ? action.trim : '';
     url = url || window.location.href || '';
     if (url) {
         // clean url (don't include hash vaue)
         url = (url.match(/^([^#]+)/)||[])[1];
     }
 
     // This jquery need to be update somehow
     var obj1 = {
         url:  url,
         success: ajaxSettings.success,
         type: method || 'GET',
         iframeSrc: /^https/i.test(window.location.href || '') ? 'javascript:false' : 'about:blank'
     };

     deepExtend({}, obj1 , options);
     
     // hook for manipulating the form data before it is extracted;
     // convenient for use with rich editors like tinyMCE or FCKEditor
     var veto = {};
     //this.trigger('form-pre-serialize', [this, options, veto]);
     this.dispatchEvent(new Event('form-pre-serialize' , { detail  : [this, options, veto]}));
    
     if (veto.veto) {
         log('ajaxSubmit: submit vetoed via form-pre-serialize trigger');
         return this;
     }
 
     // provide opportunity to alter form data before it is serialized
     if (options.beforeSerialize && options.beforeSerialize(this, options) === false) {
         log('ajaxSubmit: submit aborted via beforeSerialize callback');
         return this;
     }
 
     var traditional = options.traditional;
     if ( traditional === undefined ) {
         traditional = ajaxSettings.traditional;
     }
     
     var qx,n,v,a = this.myFormToArray(options.semantic);
     if (options.data) {
         options.extraData = options.data;
         const URLparams = new URLSearchParams(Object.entries(options.data, traditional));
         qx = URLparams.toString();
     }
 
     // give pre-submit callback an opportunity to abort the submit
     // April 15 , what even is a beforeSubmit function ?
     if (options.beforeSubmit && options.beforeSubmit(a, this, options) === false) {
         log('ajaxSubmit: submit aborted via beforeSubmit callback');
         return this;
     }
 
     // fire vetoable 'validate' event
     this.dispatchEvent(new Event('form-submit-validate',{ detail : [a, this, options, veto]}));
     if (veto.veto) {
         log('ajaxSubmit: submit vetoed via form-submit-validate trigger');
         return this;
     }
 
     //unsure
     const aParams = new URLSearchParams(Object.entries(a, traditional));
     let q = aParams.toString();
 
     if (qx) {
         q = ( q ? (q + '&' + qx) : qx );
     }   
     if (options.type.toUpperCase() == 'GET') {
         options.url += (options.url.indexOf('?') >= 0 ? '&' : '?') + q;
         options.data = null;  // data is null for 'get'
     }
     else {
         options.data = q; // data is the query string for 'post'
     }
 
     var callbacks = [];
     if (options.resetForm) {
         //is this how the code works ?
         callbacks.push(function() { form.myResetForm(); });
     }
     if (options.clearForm) {
         callbacks.push(function() { form.clearForm(options.includeHidden); });
     }
 
     // perform a load on the target only if dataType is not provided
     if (!options.dataType && options.target) {
         var oldSuccess = options.success || function(){};
         callbacks.push(function(data) {
             var fn = options.replaceTarget ? 'replaceWith' : 'html';
             //unsure
             Array.prototype.forEach.call(document.querySelectorAll((options.target)[fn](data)),(oldSuccess, arguments));
         });
     }
     else if (options.success) {
         callbacks.push(options.success);
     }
 
     options.success = function(data, status, xhr) { // jQuery 1.4+ passes xhr as 3rd arg
         var context = options.context || options;   // jQuery 1.4+ supports scope context 
         for (var i=0, max=callbacks.length; i < max; i++) {
             callbacks[i].apply(context, [data, status, xhr || form, form]);
         }
     };
 
     // are there files to upload?
     var fileInputs = $('input:file:enabled[value]', this); // [value] (issue #113)
     var hasFileInputs = fileInputs.length > 0;
     var mp = 'multipart/form-data';
     var multipart = (form.getAttribute('enctype') == mp || form.getAttribute('encoding') == mp);
 
     var fileAPI = !!(hasFileInputs && fileInputs.get(0).files && window.FormData);
     log("fileAPI :" + fileAPI);
     var shouldUseFrame = (hasFileInputs || multipart) && !fileAPI;
 
     // options.iframe allows user to force iframe mode
     // 06-NOV-09: now defaulting to iframe mode if file input is detected
     if (options.iframe !== false && (options.iframe || shouldUseFrame)) {
         // hack to fix Safari hang (thanks to Tim Molendijk for this)
         // see:  http://groups.google.com/group/jquery-dev/browse_thread/thread/36395b7ab510dd5d
         if (options.closeKeepAlive) {
             // $.get(options.closeKeepAlive, function() {
             //     fileUploadIframe(a);
             // });
                 var request = new XMLHttpRequest();
                 request.open('GET', options.closeKeepAlive, true);
                 request.onload = function() {
                 if (this.status >= 200 && this.status < 400) {
                         // Success!
                         fileUploadIframe(a);
                 } else {
                         // We reached our target server, but it returned an error
                 }
                 };
                 request.send();
         }
         else {
             fileUploadIframe(a);
         }
     }
     else if ((hasFileInputs || multipart) && fileAPI) {
         options.progress = options.progress || $.noop;
         fileUploadXhr(a);
     }
     else {
         var request = new XMLHttpRequest();
         request.open('POST', options.url , true);
         request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
         request.send(options.data);
     }
 
      // fire 'notify' event
      this.dispatchEvent(new Event('form-submit-notify' , { detail  : [this, options]}));
      return this;
 
      // XMLHttpRequest Level 2 file uploads (big hat tip to francois2metz)
     function fileUploadXhr(a) {
         var formdata = new FormData();
 
         for (var i=0; i < a.length; i++) {
             if (a[i].type == 'file')
                 continue;
             formdata.append(a[i].name, a[i].value);
         }
 
         form.find('input:file:enabled').each(function(){
             var name = this.getAttribute('name'), files = this.files;
             if (name) {
                 for (var i=0; i < files.length; i++)
                     formdata.append(name, files[i]);
             }
         });
 
         if (options.extraData) {
             for (var k in options.extraData)
                 formdata.append(k, options.extraData[k])
         }
 
         options.data = null;
         //unsure
         var obj1 = {
             contentType: false,
             processData: false,
             cache: false,
             type: 'POST'
         };
         deepExtend({} , ajaxSettings, options , obj1);
 
       s.context = s.context || s;
 
       s.data = null;
       var beforeSend = s.beforeSend;
       s.beforeSend = function(xhr, o) {
           o.data = formdata;
           if(xhr.upload) { // unfortunately, jQuery doesn't expose this prop (http://bugs.jquery.com/ticket/10190)
               xhr.upload.onprogress = function(event) {
                   o.progress(event.position, event.total);
               };
           }
           if(beforeSend)
               beforeSend.call(o, xhr, options);
       };
       //I really can't tell what this do :(
       $.ajax(s);
    }
 
     // private function for handling file uploads (hat tip to YAHOO!)
     function fileUploadIframe(a) {
         var form = form[0], el, i, s, g, id, $io, io, xhr, sub, n, timedOut, timeoutHandle;
         var useProp = !!$.fn.prop;
 
         if (a) {
             if ( useProp ) {
                 // ensure that every serialized input is still enabled
                 for (i=0; i < a.length; i++) {
                     form[a[i].name].querySelector('.input').disabled = false;
                 }
             } else {
                 for (i=0; i < a.length; i++) {
                     form[a[i].name].removeAttribute('disabled');
                 }
             };
         }
 
         if ($(':input[name=submit],:input[id=submit]', form).length) {
             // if there is an input with a name or id of 'submit' then we won't be
             // able to invoke the submit fn on the form (at least not x-browser)
             alert('Error: Form elements must not have name or id of "submit".');
             return;
         }
         // unsure extend
         deepExtend({}, ajaxSettings, options);
         s.context = s.context || s;
         id = 'jqFormIO' + (new Date().getTime());
         if (s.iframeTarget) {
             $io = s.iframeTarget;
             n = $io.getAttribute('name');
             if (n == null)
                 $io.getAttribute('name', id);
             else
                 id = n;
         }
         else {
             $io = ('<iframe name="' + id + '" src="'+ s.iframeSrc +'" />');
             $io.css({ position: 'absolute', top: '-1000px', left: '-1000px' });
         }
         io = io[0];
 
 
         xhr = { // mock object
             aborted: 0,
             responseText: null,
             responseXML: null,
             status: 0,
             statusText: 'n/a',
             getAllResponseHeaders: function() {},
             getResponseHeader: function() {},
             setRequestHeader: function() {},
             abort: function(status) {
                 var e = (status === 'timeout' ? 'timeout' : 'aborted');
                 log('aborting upload... ' + e);
                 this.aborted = 1;
                 io.getAttribute('src', s.iframeSrc); // abort op in progress
                 xhr.error = e;
                 s.error && s.error.call(s.context, xhr, e, status);
                 //g && $.event.trigger("ajaxError", [xhr, s, e]);
                 g && event.dispatchEvent(new Event("ajaxError" , { detail  : {xhr, s, e}}));
                 s.complete && s.complete.call(s.context, xhr, e);
             }
         };
 
         g = s.global;
         // trigger ajax global events so that activity/block indicators work like normal
         if (g && ! $.active++) {
             //$.event.trigger("ajaxStart");
             event.dispatchEvent(new Event("ajaxStart"));
         }
         if (g) {
             //$.event.trigger("ajaxSend", [xhr, s]);
             var e = new Event("ajaxSend" , { detail  : {xhr, s}});
             event.dispatchEvent(e);
         }
 
         if (s.beforeSend && s.beforeSend.call(s.context, xhr, s) === false) {
             if (s.global) {
                 $.active--;
             }
             return;
         }
         if (xhr.aborted) {
             return;
         }
 
         // add submitting element to data if we know it
         sub = form.clk;
         if (sub) {
             n = sub.name;
             if (n && !sub.disabled) {
                 s.extraData = s.extraData || {};
                 s.extraData[n] = sub.value;
                 if (sub.type == "image") {
                     s.extraData[n+'.x'] = form.clk_x;
                     s.extraData[n+'.y'] = form.clk_y;
                 }
             }
         }
         
         var CLIENT_TIMEOUT_ABORT = 1;
         var SERVER_ABORT = 2;
 
         function getDoc(frame) {
             var doc = frame.contentWindow ? frame.contentWindow.document : frame.contentDocument ? frame.contentDocument : frame.document;
             return doc;
         }
         
         // Rails CSRF hack (thanks to Yvan Barthelemy)
         var csrf_token = $('meta[name=csrf-token]').getAttribute('content');
         var csrf_param = $('meta[name=csrf-param]').getAttribute('content');
         if (csrf_param && csrf_token) {
             s.extraData = s.extraData || {};
             s.extraData[csrf_param] = csrf_token;
         }
 
         // take a breath so that pending repaints get some cpu time before the upload starts
         function doSubmit() {
             // make sure form attrs are set
             var t = form.getAttribute('target'), a = form.getAttribute('action');
 
             // update form attrs in IE friendly way
             form.setAttribute('target',id);
             if (!method) {
                 form.setAttribute('method', 'POST');
             }
             if (a != s.url) {
                 form.setAttribute('action', s.url);
             }
 
             // ie borks in some cases when setting encoding
             if (! s.skipEncodingOverride && (!method || /post/i.test(method))) {
                 form.getAttribute({
                     encoding: 'multipart/form-data',
                     enctype:  'multipart/form-data'
                 });
             }
 
             // support timout
             if (s.timeout) {
                 timeoutHandle = setTimeout(function() { timedOut = true; cb(CLIENT_TIMEOUT_ABORT); }, s.timeout);
             }
             
             // look for server aborts
             function checkState() {
                 try {
                     var state = getDoc(io).readyState;
                     log('state = ' + state);
                     if (state.toLowerCase() == 'uninitialized')
                         setTimeout(checkState,50);
                 }
                 catch(e) {
                     log('Server abort: ' , e, ' (', e.name, ')');
                     cb(SERVER_ABORT);
                     timeoutHandle && clearTimeout(timeoutHandle);
                     timeoutHandle = undefined;
                 }
             }
 
             // add "extra" data to form if provided in options
             var extraInputs = [];
             try {
                 if (s.extraData) {
                     for (var n in s.extraData) {
                         extraInputs.push(
                             $('<input type="hidden" name="'+n+'">').getAttribute('value',s.extraData[n]).appendTo(form)[0]);
                     }
                 }
 
                 if (!s.iframeTarget) {
                     // add iframe to doc and submit the form
                     io.appendTo('body');
                     io.attachEvent ? io.attachEvent('onload', cb) : io.addEventListener('load', cb, false);
                 }
                 setTimeout(checkState,15);
                 form.submit();
             }
             finally {
                 // reset attrs and remove "extra" input elements
                 form.setAttribute('action',a);
                 if(t) {
                     form.setAttribute('target', t);
                 } else {
                     form.removeAttribute('target');
                 }
                 //$(extraInputs).remove();
                 if (extraInputs.parentNode !== null) {
                     extraInputs.parentNode.removeChild(extraInputs);
                 }
             }
         }
 
         if (s.forceSync) {
             doSubmit();
         }
         else {
             setTimeout(doSubmit, 10); // this lets dom updates render
         }
 
         var data, doc, domCheckCount = 50, callbackProcessed;
 
         function cb(e) {
             if (xhr.aborted || callbackProcessed) {
                 return;
             }
             try {
                 doc = getDoc(io);
             }
             catch(ex) {
                 log('cannot access response document: ', ex);
                 e = SERVER_ABORT;
             }
             if (e === CLIENT_TIMEOUT_ABORT && xhr) {
                 xhr.abort('timeout');
                 return;
             }
             else if (e == SERVER_ABORT && xhr) {
                 xhr.abort('server abort');
                 return;
             }
 
             if (!doc || doc.location.href == s.iframeSrc) {
                 // response not received yet
                 if (!timedOut)
                     return;
             }
             io.detachEvent ? io.detachEvent('onload', cb) : io.removeEventListener('load', cb, false);
 
             var status = 'success', errMsg;
             try {
                 if (timedOut) {
                     throw 'timeout';
                 }
 
                 var isXml = s.dataType == 'xml' || doc.XMLDocument || $.isXMLDoc(doc);
                 log('isXml='+isXml);
                 if (!isXml && window.opera && (doc.body == null || doc.body.innerHTML == '')) {
                     if (--domCheckCount) {
                         // in some browsers (Opera) the iframe DOM is not always traversable when
                         // the onload callback fires, so we loop a bit to accommodate
                         log('requeing onLoad callback, DOM not available');
                         setTimeout(cb, 250);
                         return;
                     }
                     // let this fall through because server response could be an empty document
                     //log('Could not access iframe DOM after mutiple tries.');
                     //throw 'DOMException: not available';
                 }
 
                 //log('response detected');
                 var docRoot = doc.body ? doc.body : doc.documentElement;
                 xhr.responseText = docRoot ? docRoot.innerHTML : null;
                 xhr.responseXML = doc.XMLDocument ? doc.XMLDocument : doc;
                 if (isXml)
                     s.dataType = 'xml';
                     xhr.getResponseHeader = function(header){
                     var headers = {'content-type': s.dataType};
                     return headers[header];
                 };
                 // support for XHR 'status' & 'statusText' emulation :
                 if (docRoot) {
                     xhr.status = Number( docRoot.getAttribute('status') ) || xhr.status;
                     xhr.statusText = docRoot.getAttribute('statusText') || xhr.statusText;
                 }
 
                 var dt = (s.dataType || '').toLowerCase();
                 var scr = /(json|script|text)/.test(dt);
                 if (scr || s.textarea) {
                     // see if user embedded response in textarea
                     var ta = doc.getElementsByTagName('textarea')[0];
                     if (ta) {
                         xhr.responseText = ta.value;
                         // support for XHR 'status' & 'statusText' emulation :
                         xhr.status = Number( ta.getAttribute('status') ) || xhr.status;
                         xhr.statusText = ta.getAttribute('statusText') || xhr.statusText;
                     }
                     else if (scr) {
                         // account for browsers injecting pre around json response
                         var pre = doc.getElementsByTagName('pre')[0];
                         var b = doc.getElementsByTagName('body')[0];
                         if (pre) {
                             xhr.responseText = pre.textContent ? pre.textContent : pre.innerText;
                         }
                         else if (b) {
                             xhr.responseText = b.textContent ? b.textContent : b.innerText;
                         }
                     }
                 }
                 else if (dt == 'xml' && !xhr.responseXML && xhr.responseText != null) {
                     xhr.responseXML = toXml(xhr.responseText);
                 }
 
                 try {
                     data = httpData(xhr, dt, s);
                 }
                 catch (e) {
                     status = 'parsererror';
                     xhr.error = errMsg = (e || status);
                 }
             }
             catch (e) {
                 log('error caught: ',e);
                 status = 'error';
                 xhr.error = errMsg = (e || status);
             }
 
             if (xhr.aborted) {
                 log('upload aborted');
                 status = null;
             }
 
             if (xhr.status) { // we've set xhr.status
                 status = (xhr.status >= 200 && xhr.status < 300 || xhr.status === 304) ? 'success' : 'error';
             }
 
             // ordering of these callbacks/triggers is odd, but that's how $.ajax does it
             if (status === 'success') {
                 s.success && s.success.call(s.context, data, 'success', xhr);
                 g && $.event.trigger("ajaxSuccess", [xhr, s]);
             }
             else if (status) {
                 if (errMsg == undefined)
                     errMsg = xhr.statusText;
                 s.error && s.error.call(s.context, xhr, status, errMsg);
                 g && event.dispatchEvent(new Event("ajaxError", { detail : [xhr, s, errMsg]}));
             }
 
             g && event.dispatchEvent(new Event("ajaxComplete",{detail : [xhr, s]}));
             
             if (g && ! --$.active) {
                 event.dispatchEvent(new Event("ajaxStop"));
             }
 
             s.complete && s.complete.call(s.context, xhr, status);
 
             callbackProcessed = true;
             if (s.timeout)
                 clearTimeout(timeoutHandle);
 
             // clean up
             setTimeout(function() {
                 if (!s.iframeTarget){
                     //$io.remove();
                     if (io.parentNode !== null) {
                         io.parentNode.removeChild(io);
                     }
                 }
                     
                 xhr.responseXML = null;
             }, 100);
         }
 
         var toXml = $.parseXML || function(s, doc) { // use parseXML if available (jQuery 1.5+)
             if (window.ActiveXObject) {
                 doc = new ActiveXObject('Microsoft.XMLDOM');
                 doc.async = 'false';
                 doc.loadXML(s);
             }
             else {
                 doc = (new DOMParser()).parseFromString(s, 'text/xml');
             }
             return (doc && doc.documentElement && doc.documentElement.nodeName != 'parsererror') ? doc : null;
         };
         var parseJSON = $.parseJSON || function(s) {
             return window['eval']('(' + s + ')');
         };
 
         var httpData = function( xhr, type, s ) { // mostly lifted from jq1.4.4
 
             var ct = xhr.getResponseHeader('content-type') || '',
                 xml = type === 'xml' || !type && ct.indexOf('xml') >= 0,
                 data = xml ? xhr.responseXML : xhr.responseText;
 
             if (xml && data.documentElement.nodeName === 'parsererror') {
                 $.error && $.error('parsererror');
             }
             if (s && s.dataFilter) {
                 data = s.dataFilter(data, type);
             }
             if (typeof data === 'string') {
                 if (type === 'json' || !type && ct.indexOf('json') >= 0) {
                     data = parseJSON(data);
                 } else if (type === "script" || !type && ct.indexOf("javascript") >= 0) {
                     //unsure
                     $.globalEval(data);
                 }
             }
             return data;
         };
     }
    },

    myFormToArray:function (semantic){
        var a = [];
        if (this.length === 0) {
            return a;
        }

        var form = this[0];
        var els = semantic ? form.getElementsByTagName('*') : form.elements;
        if (!els) {
            return a;
        }

        var i,j,n,v,el,max,jmax;
        for(i=0, max=els.length; i < max; i++) {
            el = els[i];
            n = el.name;
            if (!n) {
                continue;
            }

            if (semantic && form.clk && el.type == "image") {
                // handle image inputs on the fly when semantic == true
                if(!el.disabled && form.clk == el) {
                    a.push({name: n, value: Object.values(el), type: el.type });
                    a.push({name: n+'.x', value: form.clk_x}, {name: n+'.y', value: form.clk_y});
                }
                continue;
            }

            v = this.myFieldValue(el, true);
            if (v && v.constructor == Array) {
                for(j=0, jmax=v.length; j < jmax; j++) {
                    a.push({name: n, value: v[j]});
                }
            }
            else if (v !== null && typeof v != 'undefined') {
                a.push({name: n, value: v, type: el.type});
            }
        }

        if (!semantic && form.clk) {
            // input type=='image' are not found in elements array! handle it here
            var input = (form.clk), input = input[0];
            n = input.name;
            if (n && !input.disabled && input.type == 'image') {
                a.push({name: n, value: Object.values(input)});
                a.push({name: n+'.x', value: form.clk_x}, {name: n+'.y', value: form.clk_y});
            }
        }
        return a;
    },

    myFieldValue:function (el,successful){
        var n = el.name, t = el.type, tag = el.tagName.toLowerCase();
        if (successful === undefined) {
            successful = true;
        }
    
        if (successful && (!n || el.disabled || t == 'reset' || t == 'button' ||
            (t == 'checkbox' || t == 'radio') && !el.checked ||
            (t == 'submit' || t == 'image') && el.form && el.form.clk != el ||
            tag == 'select' && el.selectedIndex == -1)) {
                return null;
        }
    
        if (tag == 'select') {
            var index = el.selectedIndex;
            if (index < 0) {
                return null;
            }
            var a = [], ops = el.options;
            var one = (t == 'select-one');
            var max = (one ? index+1 : ops.length);
            for(var i=(one ? index : 0); i < max; i++) {
                var op = ops[i];
                if (op.mySelected) {
                    var v = op.value;
                    if (!v) { // extra pain for IE...
                        v = (op.attributes && op.attributes['value'] && !(op.attributes['value'].specified)) ? op.text : op.value;
                    }
                    if (one) {
                        return v;
                    }
                    a.push(v);
                }
            }
            return a;
        }
        return Objects.values(el);
    },

    // My own clearForm, kinda messy
    clearForm:function (includeHidden){
     return Array.prototype.forEach.call(this , function(){
         $('input,select,textarea', this).myClearFields(includeHidden);
     }); 
    },

    myClearFields:function(includeHidden) {
        var re = /^(?:color|date|datetime|email|month|number|password|range|search|tel|text|time|url|week)$/i; // 'hidden' is not in this list
        return this.each(function() {
            var t = this.type, tag = this.tagName.toLowerCase();
            if (re.test(t) || tag == 'textarea' || (includeHidden && /hidden/.test(t)) ) {
                this.value = '';
            }
            else if (t == 'checkbox' || t == 'radio') {
                this.checked = false;
            }
            else if (tag == 'select') {
                this.selectedIndex = -1;
            }
        });
    },

    myResetForm: function(){
     return Array.prototype.forEach.call(this, function() {
         // guard against an input with the name of 'reset'
         // note that IE reports the reset function as an 'object'
         if (typeof this.reset == 'function' || (typeof this.reset == 'object' && !this.reset.nodeType)) {
             this.reset();
         }
     });
    },

    getParents: function(e) {
     var result = [];
     for (var p = e && e.parentElement; p; p = p.parentElement) {
       result.push(p);
     }
     return result;
    },

    mySelected: function(select){
     if (select === undefined) {
         select = true;
     }
     return this.each(function() {
         var t = this.type;
         if (t == 'checkbox' || t == 'radio') {
             this.checked = select;
         }
         else if (this.tagName.toLowerCase() == 'option') {
             var sel = getParents('select');
             if (select && sel[0] && sel[0].type == 'select-one') {
                 // deselect all other options
                 sel.querySelectorAll('option').mySelected(false);
             }
             this.selected = select;
         }
     });
    }
 
});
