//TODO:  Simply attach the getUserData function to the end of the loop function.
var VisitorChat_Chat = VisitorChat_ChatBase.extend({
  currentRequest    : false,  //The current request ID.
  requestLoopID     : false,
  operatorStatus    : false,
  unreadMessages    : new Array(), //The total number of messages for all open conversations
  requestExpireDate : new Array(),
  
  initWindow: function(){
    $("#toggleOperatorStatus").click(function(){
        VisitorChat.toggleOperatorStatus();
        return false;
    });
    
    this._super();
  },
  
  initWatchers: function() {
    //Remove old elvent handlers
    $('.conversationLink').unbind();
  
    //Watch coversation link clicks.  Loads up the conversation all ajaxy
    $('.conversationLink').click(function(){
      //Empty out the current chat.
      $('#clientChat').empty();
      
      //Load the chat.
      VisitorChat.updateChat(this);
      
      return false;
    });
    
    this._super();
  },
  
  init: function()
  {
    this.loadStyles();
    this.initWindow();
    this.initWatchers();
  },
  
  run: function() {
    this.updateUserInfo();
    this._super();
  },
  
  start: function() {
      //load the conversation list.
      this._super();
  },
  
  updateConversationListWithUnreadMessages: function()
  {
    //Do we need to display a notice?
    for (conversation in this.unreadMessages) {
      var html = "";
      if (this.unreadMessages[conversation]) {
        html = this.unreadMessages[conversation];
      }
      $("#visitorChat_UnreadMessages_" + conversation).html(html);
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
        $("#chatRequest").dialog({
          resizable: false,
          height:140,
          modal: true,
          buttons: {
            "Reject": $.proxy(function() {
              $("#chatRequest").dialog("close");
              this.sendChatRequestResponse(this.currentRequest, 'REJECTED');
              this.clearAlert();
              clearTimeout(VisitorChat.requestLoopID);
            }, this),
            "Accept": $.proxy(function() {
               $("#chatRequest").dialog("close");
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
    $.ajax({
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
    $("#chatRequestCountDown").html(difference);
    
    if (currentDate.getTime() >= VisitorChat.requestExpireDate[id]) {
        $("#chatRequest").dialog("close"); //Remove the dialog box.
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
  
  onConversationStatus_Chatting: function(data)
  {
    if (data['html'] == undefined) {
      return true;
    }
    
    this.updateChatContainerWithHTML("#clientChat", data['html']);
  },
  
  onConversationStatus_Closed: function(data) {
    //Disable the input message input.
    $("visitorChat_messageBox").attr("disabled", "disabled");
    
    //Display a closed message.
    var html = "<div class='chat_notify' id='visitorChat_closed'>The conversation has been closed.</div>";
    html = $(html).append($("#visterChat_conversation").html());
    
    this.updateChatContainerWithHTML("#clientChat", html);
  },
  
  updateConversationList: function() {
    //Update the Client List
    $.ajax({
      url: this.serverURL + "conversations?format=partial",
      xhrFields: {
          withCredentials: true
      },
      success: $.proxy(function(data) {
          $("#clientList").html(data);
          this.initWatchers();
      }, this)
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
    
    $.ajax({
      type: "POST",
      url: this.serverURL + "users/" + this.userID + "/edit?format=json",
      data: "status=" + status,
      success: $.proxy(function(data) {
        this.updateOperatorStatus(status);
      }, this)
    });
  },
  
  updateOperatorStatus: function(newStatus) {
    if (newStatus == 'BUSY') {
      $("#toggleOperatorStatus").addClass("closed");
      $("#toggleOperatorStatus").removeClass("open");
    } else {
    	$("#toggleOperatorStatus").addClass("open");
        $("#toggleOperatorStatus").removeClass("closed");
    }
    
    $("#currentOperatorStatus").html(newStatus);
    
    this.operatorStatus = newStatus;
  }
  
});

$(function(){
  VisitorChat = new VisitorChat_Chat();
  VisitorChat.start();
});
