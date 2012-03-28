/**
 * TODO: Add support for handling cookies. IE does not support passing session data back
 * to the server via CORS when you are on a different domain than the server.  To work around
 * this we need to get the session ID from the server, save it in a cookie, and use that 
 * cookie to remember who you are as you move across websites in the UNL templates.
 * 
 * TODO: The process of logging in and out COULD take a little bit.  So we need to display to the user 
 * that we are logging them in and out.
 */

/*
 * The base Chat class.  This class can be extended.
 * However, the application is built so that ONLY one
 * instance of it is allowed at a time.  And that instance
 * MUST be a variable called VisitorChat.
 */
var VisitorChat_ChatBase = Class.extend({
  //The id of the latest message for this conversation on the server.
  latestMessageId: 0,
  
  //The current chat status, ie: login, searching, chatting, closed.
  chatStatus     : false,
  
  //The chat sever url.
  serverURL      : "<?php echo \UNL\VisitorChat\Controller::$url;?>",
  
  //The refresh rate of the chat.
  refreshRate    : <?php echo \UNL\VisitorChat\Controller::$refreshRate;?>,
  
  //The original site title of the current web page.
  siteTitle      : document.title,
  
  //The php session ID as determined by the server.  This passed due to IE not handling sessions with ajax and CORS.
  phpsessid      : false,
  
  //True if the window that the chat is in is visible.
  windowVisible  : true,
  
  //The timer ID for the looping process of the main chat.
  loopID         : false,
  
  //The timer ID for the looping proccess of the alert notification.
  alertID        : false,
  
  //Is the chat currently open?  Is true when the chat has been started, false when stopped.
  chatOpened     : false,
  
  //The current conversationID for the user.
  conversationID : false,
  
  userID         : false,
  
  operatorsChecked: false,
  
  operatorsAvailable: false,
  
  notifications: new Array(),
  
  /**
   * Constructor function.
   */
  init: function() {
    this.initWindow();
    this.updateUserInfo();
    this.loadStyles();
    this.initWatchers();
  },
  
  /**
   * Initalize event watchers related to the current window.
   */
  initWindow: function(){
    WDN.jQuery([window, document]).blur(function(){
      
      VisitorChat.windowVisible = false;
    });
    
    WDN.jQuery([window, document]).focus(function(){
      VisitorChat.windowVisible = true;
      VisitorChat.clearAlert();
      document.title = VisitorChat.siteTitle;
    });
  },
  
  /**
   * Start function.  This function starts the application
   * flow of the chat.
   */
  start: function() {
    this.chatOpened = true;

    clearTimeout(VisitorChat.loopID);
    
    VisitorChat_Timer_ID = VisitorChat.loop();
  },
  
  /**
   * Run function.  Tells the chat to update.  This is called 
   * on every heartbeat of the application.
   */
  run: function() {
    this.updateChat();
  },
  
  /**
   * Generates the current chat URL.
   */
  generateChatURL: function() {
    return this.serverURL + "conversation?format=json&last=" + this.latestMessageId + "&PHPSESSID=" + this.phpsessid;
  },
  
  /**
   * loadStyles loads all of the required styles for the chat.
   */
  loadStyles: function() {
  },

  /**
   * updateUserInfo grabs data about the current user from the 
   * chat sever, this data includes session id.
   */
  updateUserInfo: function() {
    var checkOperators = "";
    if (!this.operatorsChecked) {
      checkOperators = "&checkOperators=" + escape(document.URL);
    }
    
    //Start the chat.
    WDN.jQuery.ajax({
      url: this.serverURL + "user/info?format=json&PHPSESSID=" + this.phpsessid + checkOperators,
      xhrFields: {
        withCredentials: true
      },
      dataType: "json",
      success: WDN.jQuery.proxy(function(data, textStatus, jqXHR) {
        this.handleUserDataResponse(data);
      }, this)
    });
  },
  
  handleUserDataResponse: function(data) {
    this.userID = data['userID'];

    this.updatePHPSESSID(data['phpssid']);

    if (!this.operatorsChecked) {
      this.operatorsAvailable = data['operatorsAvailable'];
    }
    this.operatorsChecked = true;
  },

  updatePHPSESSID: function(phpsessid) {
    this.phpsessid = phpsessid;
  },
  
  /**
   * updateChat function.  This function will only fire when
   * the user is chatting, the session is set and the chat is open.
   * 
   * Grabs current chat data from the server.  If new data is present 
   * the chat will be updated.
   */
  updateChat: function(url, force) {
    //Check if we should not update.
    if ((this.chatStatus == 'LOGIN' 
        || this.chatStatus == 'CLOSED'
        || this.chatStatus == 'OPERATOR_LOOKUP_FAILED'
        || this.chatStatus == 'EMAILED'
        || this.chatOpened == false
        || this.phpsessid == false)
        && force != true) {
      return false;
    }
    
    if (url == undefined) {
      url = this.generateChatURL();
    }
    
    WDN.jQuery.ajax({
      url: url,
      xhrFields: {
        withCredentials: true
      },
      dataType: "json",
      success: WDN.jQuery.proxy(function(data, textStatus, jqXHR) {
        this.updateChatWithData(data);
      }, this)
    });
  },
  
  /**
   * updateChatWithData updates the chat with data grabbed by 
   * ajax functions.  This function actually looks at the returned
   * conversation status and fires off a related function.
   */
  updateChatWithData: function(data) {
    if (data['latest_message_id'] !== undefined) {
      this.latestMessageId = data['latest_message_id'];
    }
    
    if (data['status'] !== undefined) {
      this.chatStatus = data['status'];
    }
  
    if (data['conversation_id'] !== undefined) {
      this.conversationID = data['conversation_id'];
    }
    
    switch(this.chatStatus) {
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
    }
    
    return true;
  },
  
  /**
   * onConversationStatus_OperatorLookupFailed
   * Related status code: OPERATOR_LOOKUP_FAILED
   * Details: This function will be called when an operator can not be found.
   * This will be called when no operator can be found and an email has not been sent.
   */
  onConversationStatus_OperatorLookupFailed: function(data) {
    var html = '<div class="chat_notify">We could not find an operator to help you.  Please try back later.</div>';
    this.updateChatContainerWithHTML("#visitorChat_container", html);
  },
  
  /**
   * onConversationStatus_Emailed
   * Related status code: EMAILED
   * Details: This function will be called when a converstation
   * falls back to an email.  This means that an operator was not available
   * but an email could be sent.
   */
  onConversationStatus_Emailed: function(data) {
     clearTimeout(VisitorChat.loopID);
     var html = '<div class="chat_notify" id="visitorChat_emailed">Your message has been emailed.</div>';
     this.updateChatContainerWithHTML("#visitorChat_container", html);
  },
  
  /**
   * onConversationStatus_Chatting
   * Related status code: CHATTING
   * Details: This function will be called when an operator was found
   * and the operator accepted the conversation.  We are now chatting.
   * HTML will be sent along with the data parm if new updates were found.
   */
  onConversationStatus_Chatting: function(data) {
    if (data['html'] == undefined) {
      return true;
    }
    
    this.updateChatContainerWithHTML("#visitorChat_container", data['html']);
  },
  
  /**
   * onConversationStatus_Closed
   * Related status code: CLOSED
   * Details: This function will be called when a conversation has been closed.
   * A close event happens when a client logs out or an operator closes the chat
   * from their end or the current operator logs out.
   */
  onConversationStatus_Closed: function(data) {
    clearTimeout(VisitorChat.loopID);
    var html = '<div class="chat_notify" id="visitorChat_closed">The conversation has been closed.</div>';
    this.updateChatContainerWithHTML("#visitorChat_container", html);
  },
  
  /**
   * onConversationStatus_OperatorPendingApproval
   * Related status code: OPERATOR_PENDING_APPROVAL
   * Details: This function will be called when a conversation is currently
   * pending approval by an operator.  This means that an operator was found
   * but we are waiting on them to accept or reject the request.
   */
  onConversationStatus_OperatorPendingApproval: function(data) {
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
  onConversationStatus_Searching: function(data) {
    var html = '<div class="chat_notify visitorChat_loading">Please wait while we find someone to help you.</div>';
    this.updateChatContainerWithHTML("#visitorChat_container", html);
  },
  
  /**
   * onConversationStatus_Login
   * Related status code: LOGIN
   * Details: This function is called when the server is waiting for 
   * the client to log in.  HTML data of the login form is sent in the data param.
   */
  onConversationStatus_Login: function(data) {
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
  updateChatContainerWithHTML: function(selector, html) {
    //Should we alert the user?
    if (WDN.jQuery(selector).html() !== html) {
      this.clearAlert();
      this.alert();
    }
    
    //Update the html
    WDN.jQuery(selector).html(html);
    
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
  initWatchers: function() {
    //Remove old elvent handlers
    WDN.jQuery('#visitorChat_messageBox').unbind();
    
    WDN.jQuery('#visitorChat_messageBox').keypress(function(e){
      if(e.which == 13 && !e.shiftKey){
        e.preventDefault();
        if (VisitorChat.chatStatus == 'LOGIN') {
          WDN.jQuery('#visitorchat_clientLogin').submit();
        } else {
          WDN.jQuery('#visitorChat_messageForm').submit();
        }
      }
    });
    
    this.initAjaxForms();
  },
  
  /**
   * scroll is used to scroll the current chat to the bottom of the chat div.
   */
  scroll: function() {
    WDN.jQuery("#visitorChat_chatBox").scrollTop(WDN.jQuery("#visitorChat_chatBox").prop('scrollHeight'));
  },
  
  /**
   * initAjaxForms initalizes ajax forms used by the chat.
   */
  initAjaxForms: function()
  {
    var options = { 
          clearForm: true,
          timeout:   3000,
          dataType: "json",
          complete: WDN.jQuery.proxy(function(data, textStatus, jqXHR) {
            this.handleAjaxResponse(data, textStatus);
            //this.updateChatWithData(data);
          }, this),
          beforeSubmit: WDN.jQuery.proxy(function(arr, $form, options) {
              return this.ajaxBeforeSubmit(arr, $form, options);
          }, this),
          crossDomain: true,
          xhrFields: {
            withCredentials: true
          }
      };
      
    var action = WDN.jQuery('.unl_visitorchat_form').attr('action');
      
    if (action !== undefined && action.indexOf("format=json") == -1) {
      WDN.jQuery('.unl_visitorchat_form').attr('action', WDN.jQuery.proxy(function(i, val) {
        return val + '?format=json&PHPSESSID=' + this.phpsessid;
      }, this));
    }
      
    //bind form using 'ajaxForm' 
    WDN.jQuery('.unl_visitorchat_form').ajaxForm(options);
    
    if (this.windowVisible) {
      WDN.jQuery('#visitorChat_messageBox').focus();
    }
  },
  
  ajaxBeforeSubmit: function(arr, $form, options) {
    var html = "<div class='visitorChat_loading'></div>";
    if (VisitorChat.chatStatus == 'LOGIN') {
      WDN.jQuery('#visitorChat_container').html(html);
    } else {
      WDN.jQuery('#visitorChat_chatBox').addClass("visitorChat_loading");
    }
    
    return true;
  },
  
  /**
   * The alert function will be called to alert the user of a new notification
   * when the window containing the chat is not in focus.  By default it will 
   * just flash the page title.
   */
  alert: function(alertType) {
    //1. do not continue if the window is currently focued.
    if (this.windowVisible) {
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
        }
      
      document.title = message + VisitorChat.siteTitle;
    } else {
      document.title = VisitorChat.siteTitle;
    }
    
    //Play a sound only on first alert.
    if (!VisitorChat.alertID) {
      this.playSound(alertType);
      this.showNotification(alertType);
    }
    
    //3. flash the document title.
    VisitorChat.alertID = setTimeout('VisitorChat.alert()', 2000);
  },
  
  showNotification: function(alertType) {
    //are notifications supported?
    if (!window.webkitNotifications) {
      return false;
    }
    
    // do we have permission?
    if (window.webkitNotifications.checkPermission()) {
      return false;
    }
    
    var message = "You recieved a new Alert";
    switch (alertType) {
      case 'newMessage':
        message = "You have new messages!";
        break;
      case 'assignment':
        message = "You have a new pending assignment!";
        break;
    }
    
    notification = window.webkitNotifications.createNotification(this.serverURL + 'images/alert.gif', 'UNL VisitorChat Alert', message);
    notification.show();
    this.notifications.push(notification);
  },
  
  clearAlert: function()
  {
    if (VisitorChat.alertID) {
      clearTimeout(VisitorChat.alertID);
    }
    //Set the alertID to false so that we no there are no current alerts.
    VisitorChat.alertID = false;
    
    for (id in this.notifications) {
      this.notifications[id].cancel();
    }
  },
  
  playSound: function(alertType) {
    var audioTagSupport = !!(document.createElement('audio').canPlayType);
    
    if (!audioTagSupport) {
      return false;
    }
    
    var file = 'message.wav';
    switch(alertType) {
      case 'assignment':
        file = 'alert.wav';
        break;
      case 'newMessage':
        file = 'message.wav';
        break;
    }
    
    var sound = false;
    
    if (sound = document.getElementById('visitorChat_sound')) {
      sound.src = this.serverURL + "audio/" + file;
      sound.play();
    }
  },
  
  /**
   * HandleAjaxresponse will handle responses form ajax functions.
   */
  handleAjaxResponse: function(data, textStatus)
  {
    if (data["responseText"] !== undefined) {
      data = WDN.jQuery.parseJSON(data["responseText"]);
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
  
  /**
   * stop will stop the chat by logging the user out, removing the chat box
   * and reseting chat variables.
   */
  stop: function() {
    //TODO: Show that we are logging out.
    //1. stop server updates.
    clearTimeout(VisitorChat.loopID);
    this.chatOpened = false;
    
    //2. Close the chatbox.
    WDN.jQuery("#visitorChat_container").remove();
    
    //3. logout
    WDN.jQuery.ajax({
        url: this.serverURL + "logout" + "?PHPSESSID=" + this.phpsessid,
        xhrFields: {
            withCredentials: true
        },
        dataType: "html",
        complete: function(jqXHR, textStatus) {
            //TODO: close chat.
        }
    });
    
    //4. clear vars.
    this.latestMessageId = 0;
    this.chatStatus      = false;
  },
  
  /**
   * the loop function is used to loop the main process of the chat application.
   */
  loop: function() {
    VisitorChat.run();
    VisitorChat.loopID = setTimeout("VisitorChat.loop()", VisitorChat.refreshRate);
  }
});
