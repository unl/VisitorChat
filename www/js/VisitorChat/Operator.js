//TODO:  Simply attach the getUserData function to the end of the loop function.
var VisitorChat_Chat = VisitorChat_ChatBase.extend({
  currentRequest    : false,  //The current request ID.
  requestLoopID     : false,
  operatorStatus    : false,
  unreadMessages    : new Array(), //The total number of messages for all open conversations
  requestExpireDate : new Array(),
  invitationsHTML   : false, //Holds a copy of the latest invitations html
  
  initWindow: function() {
    WDN.jQuery("#toggleOperatorStatus").click(function(){
        if (VisitorChat.operatorStatus == 'AVAILABLE') {
          VisitorChat.checkOperatorCountBeforeStatusChange();
        } else {
          VisitorChat.toggleOperatorStatus();
        }
        return false;
    });

    this._super();
  },
  
  showBrightBox: function() {
    var mouse_is_inside = false;
  
      //Navigation needs to be under back-drop
      WDN.jQuery("#wdn_navigation_wrapper").css({'z-index': '1'});
      
      //Add in the back-drop and show brightBox
      WDN.jQuery("body").append("<div id='visitorChat_backDrop'></div>");
      WDN.jQuery('#visitorChat_brightBox').fadeIn("fast");

      //Track mouse position
      WDN.jQuery('#visitorChat_brightBox').hover(function() {
        mouse_is_inside = true;
      }, function() {
        mouse_is_inside = false;
      });

      //Click outside container to close
      WDN.jQuery("#visitorChat_backDrop").mouseup(function() {
        if (!mouse_is_inside) {
          WDN.jQuery("#visitorChat_backDrop").remove();
          WDN.jQuery('#visitorChat_brightBox').fadeOut(100);
          WDN.jQuery("#wdn_navigation_wrapper").css({'z-index': 'auto'});
        }
    });
  },
  
  initWatchers: function() {
    //Remove old elvent handlers
    WDN.jQuery('.conversationLink, .closeConversation, #shareConversation').unbind();
  
    //Watch coversation link clicks.  Loads up the conversation all ajaxy
    WDN.jQuery('.conversationLink').click(function(){
      //Empty out the current chat.
    VisitorChat.clearChat();
      
      //reset the chat status.
      VisitorChat.chatStatus = false;
      
      //Load the chat.
      VisitorChat.updateChat(this);
      
      return false;
    });
    
    WDN.jQuery('#closeConversation').click(function() {
      if (confirm("Are you sure you want to end the conversation?")) {
        VisitorChat.changeConversationStatus("CLOSED");
      }
    });
    
    WDN.jQuery('#shareConversation').click(function() {
      VisitorChat.openShareWindow();
    });
    
    this._super();
  },
  
  clearChat: function() {
    WDN.jQuery('#clientChat').empty();
    WDN.jQuery('#clientChat_Invitations').empty();
    this.invitationsHTML = "";
  },
  
  init: function()
  {
    this.loadStyles();
    this.initWindow();
    this.initWatchers();
    
    if (window.webkitNotifications && window.webkitNotifications.checkPermission()) {
      WDN.jQuery('#notificationOptions').show();
    }
    
    //Request permission for notifications.
    WDN.jQuery('#requestNotifications').click(function() {
      if (!window.webkitNotifications) {
        return false;
      }
      
      window.webkitNotifications.requestPermission(function() {
        if (!window.webkitNotifications.checkPermission()) {
          WDN.jQuery('#notificationOptions').hide();
        }
      });
      return false
    });
  },
  
  run: function() {
    this.updateUserInfo();
    this._super();
  },
  
  start: function() {
      //load the conversation list.
      this._super();
  },
  
  openShareWindow: function() {
    //Update the Client List
    WDN.jQuery.ajax({
      url: this.serverURL + "conversation/" + this.conversationID + "/share?format=partial",
      xhrFields: {
          withCredentials: true
      },
      success: WDN.jQuery.proxy(function(data) {
        
        WDN.jQuery("#visitorChat_brightBox").html(data);
        this.showBrightBox();
        this.loadShareWatchers();
        //start a new dialog box.
      }, this),
    });
  },
  
  loadShareWatchers: function() {
    WDN.jQuery('#shareForm input[type="radio"][name="to"]').change(WDN.jQuery.proxy(function() {
      this.confirmShare();
    }, this));
  },
  
  confirmShare: function() {
    var toHTML = WDN.jQuery('input[name=to]:checked', '#shareForm').parent().text();
    var to     = WDN.jQuery('input[name=to]:checked', '#shareForm').val();
    
    //Clean to as it may contain lots of whitepsace
    toHTML = WDN.jQuery.trim(toHTML);
    
    var method     = WDN.jQuery('input[name=method]:checked', '#shareForm').val();
    var methodHTML = method;
    
    if (confirm('Are sure you want to ' + methodHTML + ' ' + toHTML + '?')) {
        this.share(method, to);
    }
  },
  
  share: function(method, to) {
    WDN.jQuery.ajax({
      type: "POST",
      url: this.serverURL + "conversation/" + this.conversationID + "/share?format=json",
      data: "method=" + method + "&to=" + to
    }).error(function(msg) {
      alert('There was an error sharing, please try back later.');
    });
  },
  
  updateConversationListWithUnreadMessages: function()
  {
    //Do we need to display a notice?
    for (conversation in this.unreadMessages) {
      var html = "";
      if (this.unreadMessages[conversation]) {
        html = this.unreadMessages[conversation];
      }
      WDN.jQuery("#visitorChat_UnreadMessages_" + conversation).html(html);
    }
  },
  
  updateUnreadMessages: function(newTotals) {
    var currentConversations = "";
    var oldConversations = "";
    
    for (conversation in this.unreadMessages) {
      oldConversations += conversation + ",";
    }
    
    for (conversation in newTotals) {
      currentConversations += conversation + ",";
    }
    
    //Do we need to update the conversationList?
    if (currentConversations !== oldConversations) {
      this.updateConversationList();
    }
    
    for (conversation in newTotals) {
      //Check to see if this is a new conversation.
      if (this.unreadMessages[conversation] == undefined) {
        //Set it to -1 so that an alert will fire.
        this.unreadMessages[conversation] = -1;
      }
      
      //Do we need to alert?
      if (newTotals[conversation] > this.unreadMessages[conversation]) {
        //We should only alert once.
        this.alert();
        break;
      }
    }
    
    //Update the conversation list with unread amounts.
    this.unreadMessages = newTotals;
    this.updateConversationListWithUnreadMessages();
  },
  
  updateUserInfo: function() {
    if (this.operatorStatus == 'BUSY') {
      return false;
    }
    
    this._super();
  },
  
  handleUserDataResponse: function(data) {
      this.operatorStatus = data['userStatus'];
      
      this.updateOperatorStatus(this.operatorStatus);
      
      this._super(data);
      
      //Alert if there are new and unread messages.
      this.updateUnreadMessages(data['unreadMessages']);
      
      this.totalMessages  = data['totalMessages'];
      
      //1. Check for any pending conversations.
      if (data['pendingAssignment'] == false || data['pendingDate'] == false) {
          return true;
      }
      
      //Start the alert.
      this.alert('assignment');
      
      var date = new Date(data['pendingDate']);
      
      //3. Alert the user.
      if (this.currentRequest != data['pendingAssignment']) {
        //start a new dialog box.
        WDN.jQuery("#chatRequest").dialog({
          resizable: false,
          height:140,
          modal: true,
          buttons: {
            "Reject": WDN.jQuery.proxy(function() {
              WDN.jQuery("#chatRequest").dialog("close");
              this.sendChatRequestResponse(this.currentRequest, 'REJECTED');
              this.clearAlert();
              clearTimeout(VisitorChat.requestLoopID);
            }, this),
            "Accept": WDN.jQuery.proxy(function() {
               WDN.jQuery("#chatRequest").dialog("close");
               this.sendChatRequestResponse(this.currentRequest, 'ACCEPTED');
               clearTimeout(VisitorChat.requestLoopID);
               this.clearAlert();
            }, this),
          }
        });
        
        this.currentRequest = data['pendingAssignment'];
        this.startRequestLoop(data['pendingAssignment'], data['pendingDate'], data['serverTime']);
      }
  },
  
  generateChatURL: function() {
    var conversation = ""
    if (this.conversationID) {
      conversation = "&conversation_id=" + this.conversationID;
    }
    return this.serverURL + "conversation?format=json&last=" + this.latestMessageId + conversation + "&PHPSESSID=" + this.phpsessid;
  },
  
  sendChatRequestResponse: function(id, response) {
    WDN.jQuery.ajax({
      type: "POST",
      url: this.serverURL + "assignment/" + id + "/edit?format=json&PHPSESSID=" + this.phpsessid,
      data: "status=" + response
    }).done(function(msg) {
      if(response == "REJECTED") {
        return true;
      }
    });
  },
  
  startRequestLoop: function(id, startDate, serverTime) {
    startDate   = Date.parse(new Date(Date.parse(startDate)).toUTCString());
    serverTime  = Date.parse(new Date(Date.parse(serverTime)).toUTCString());
    currentDate = Date.parse(new Date().toUTCString());
    
    var offset  = currentDate - serverTime;
    var startDate = startDate + offset;
    
    this.requestExpireDate[id] = new Date(startDate + <?php echo \UNL\VisitorChat\Controller::$chatRequestTimeout; ?>);
    
    this.requestLoop(id);
  },
  
  requestLoop: function(id) {
    currentDate = new Date();
    difference = Math.round((VisitorChat.requestExpireDate[id] - currentDate.getTime())/1000);
    WDN.jQuery("#chatRequestCountDown").html(difference);
    
    if (currentDate.getTime() >= VisitorChat.requestExpireDate[id]) {
        WDN.jQuery("#chatRequest").dialog("close"); //Remove the dialog box.
        clearTimeout(VisitorChat.requestLoopID); //Clear the timeout.
        this.clearAlert();
    }
    
    VisitorChat.requestLoopID = setTimeout("VisitorChat.requestLoop(" + id + ")", 1000);
  },
  
  updateChat: function(url) {
    if (this.conversationID == false && url == undefined) {
      return false;
    }
    
    this._super(url);
  },
  
  updateChatWithData: function(data) {
    if (data['invitations_html'] !== undefined && data['invitations_html']) {
      this.updateInvitationsListWithHTML(data['invitations_html']);
    }
    
    return this._super(data);
  },
  
  updateInvitationsListWithHTML: function(html) {
    if (this.invitationsHTML != html) {
      this.invitationsHTML = html;
      WDN.jQuery("#clientChat_Invitations").html(html);
    }
  },
  
  onConversationStatus_Chatting: function(data)
  {
    if (data['html'] == undefined) {
      return true;
    }
    
    //Does the message box current exist?  If it does, only replace the message list.
    if (WDN.jQuery("#visitorChat_chatBox").length !== 0) {
        //Replace the header
       this.updateChatContainerWithHTML("#visitorChat_conversation_header", WDN.jQuery(data['html']).find("#visitorChat_conversation_header").html());
        
        //Replace conversation.
        this.updateChatContainerWithHTML("#visitorChat_chatBox", WDN.jQuery(data['html']).find("#visitorChat_chatBox").html());
        
        //remove the loading class.
        WDN.jQuery("#visitorChat_chatBox").removeClass('visitorChat_loading');
    } else {
        //Load all of it.
        this.updateChatContainerWithHTML("#clientChat", data['html']);
    }
  },
  
  onConversationStatus_Closed: function(data) {
	    //Disable the input message input.
	    WDN.jQuery("visitorChat_messageBox").attr("disabled", "disabled");
	    
	    //Display a closed message.
	    var html = "<div class='chat_notify' id='visitorChat_closed'>This conversation has been closed.</div>";
	    html = WDN.jQuery("#visterChat_conversation").prepend(html);
	    this.updateChatContainerWithHTML("#clientChat", html);
	    
	    //Fade out everything BUT closed message

			//loop through all the children in #items		 
				//set the opacity of all siblings
				WDN.jQuery('#visitorChat_closed').siblings().css({'opacity': '0.1'})
				//set the opacity of current item to full, and add the effect class
				WDN.jQuery('#visitorChat_closed').css({'opacity': '1.0'});   
			 
				//reset all the opacity to full and remove effect class
				//WDN.jQuery(this).removeClass('effect');
				//WDN.jQuery(this).siblings().fadeTo('fast', '1.0')  
	  },
  
  updateConversationList: function() {
    //Update the Client List
    WDN.jQuery.ajax({
      url: this.serverURL + "conversations?format=partial",
      xhrFields: {
          withCredentials: true
      },
      success: WDN.jQuery.proxy(function(data) {
          WDN.jQuery("#clientList").html(data);
          this.initWatchers();
      }, this)
    });
  },
  
  checkOperatorCountBeforeStatusChange: function() {
    WDN.jQuery.ajax({
      type: "GET",
      url: this.serverURL + "user/sites?format=json",
      success: WDN.jQuery.proxy(function(data) {
        var offline = new Array();
        
        for (url in data) {
          if ((data[url]['total_available'] - 1) < 1) {
            offline[url] = data[url]['title'];
          }
        }
        
        if (offline.length == 0) {
            this.displayStatusChangeAlert(offline);
        }
        
      }, this),
      error: WDN.jQuery.proxy(function(data) {
        this.toggleOperatorStatus();
    }, this)
    });
  },
  
  displayStatusChangeAlert: function(offline)
  {
    var html = "You are the last person online for the following sites.  If you go offline now, these sites will have chat functionality turned off. <ul id='visitorChat_sitesWarning'>";
    
    for (site in offline) {
      html += "<li>" + offline[site] + "</li>";
    }
    
    html += "</ul>";
    
    WDN.jQuery("#alert").html(html);
    
    //start a new dialog box.
    WDN.jQuery("#alert").dialog({
      resizable: false,
      modal: true,
      buttons: {
        "Go Offline Anyway": WDN.jQuery.proxy(function() {
          WDN.jQuery("#alert").dialog("close");
          this.toggleOperatorStatus();
        }, this),
        "Nevermind": WDN.jQuery.proxy(function() {
           WDN.jQuery("#alert").dialog("close");
        }, this),
      }
    });
  },
  
  toggleOperatorStatus: function() {
    var status = "BUSY";
    
    if (this.operatorStatus == "BUSY") {
      status = "AVAILABLE";
    }
    
    if (!this.userID) {
      return false;
    }
    
    WDN.jQuery.ajax({
      type: "POST",
      url: this.serverURL + "users/" + this.userID + "/edit?format=json",
      data: "status=" + status,
      success: WDN.jQuery.proxy(function(data) {
        this.updateOperatorStatus(status);
      }, this)
    });
  },
  
  updateOperatorStatus: function(newStatus) {
	  var formatStatus = 'Busy';
	  
	if (newStatus == 'BUSY') {
      WDN.jQuery("#toggleOperatorStatus").addClass("closed");
      WDN.jQuery("#toggleOperatorStatus").removeClass("open");
    } else {
      WDN.jQuery("#toggleOperatorStatus").addClass("open");
      WDN.jQuery("#toggleOperatorStatus").removeClass("closed");
      formatStatus = 'Available';
    }
    
    WDN.jQuery("#currentOperatorStatus").html(formatStatus);
    
    this.operatorStatus = newStatus;
  }
  
});

WDN.jQuery(function(){
  VisitorChat = new VisitorChat_Chat();
  VisitorChat.start();
});