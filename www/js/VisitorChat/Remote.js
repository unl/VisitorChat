var VisitorChat_Chat = VisitorChat_ChatBase.extend({
  loginHTML: false,
  
  startEmail: function() {
    this.launchChatContainer();
    this.start();
  },
  
  startChat: function(chatInProgress) {
    this.launchChatContainer();
    
    if (chatInProgress) {
      this.chatStatus = false;
      return this.start();
    }
    
    this.updateChatContainerWithHTML("#visitorChat_container", this.loginHTML);
    WDN.jQuery("#visitorChat_footerHeader").css({'display': 'none'});
    
    WDN.jQuery("#visitorChat_container #visitorChat_email_fallback_text").html('If no operators are available,&nbsp;I would like to receive an email.');
    
    this.start();

  },
  
  launchChatContainer: function()
  {
    //Remove an old one if it is there.
    WDN.jQuery('#visitorChat_container').remove();

    //set up a container.
    WDN.jQuery("#visitorChat").append(
        "<div id='visitorChat_container'>" +
          "<div class='chat_notify visitorChat_loading'>Initializing, please wait.</div>" +
        "</div>"
    );
    
    WDN.jQuery("#visitorChat_header").show();
    
    this.chatStatus = "LOGIN";
    
    this.loginHTML = WDN.jQuery("#visitorchat_clientLogin").parent().html();
    
    WDN.jQuery("#visitorchat_clientLogin").parent().html("Disabled");
    
    WDN.jQuery("#visitorChat_header").animate({'width': '204px'}, 200);
    WDN.jQuery("#visitorChat_container").delay(10).slideDown(320);  
  },
  
  confirmClose: function(id) {
      if (this.chatStatus == 'CLOSED') {
        return true;
      }
      
      if (confirm("End your chat?")) {
        return true;
      }
      
      return false;
  },
  
  initWatchers: function() {
    /* This method is called several times thoughout 
     * executation.  Thus in order to stop the stacking
     * of watch functions, we should always unbind previous 
     * watch functions before applying the new ones.
     */
    WDN.jQuery('#visitorChat_container, ' +
            '#visitorChat_email_fallback, ' +
            '#visitorChat_logout, ' +
            '#visitorChat_login_submit, ' +
            '#visitorChat_header, ' +
            '#visitorChat_chatBox > ul > li').unbind();
    
    //Reveal timestamp
    WDN.jQuery("#visitorChat_chatBox > ul > li").hover(
      function () {
        WDN.jQuery(this).children(".timestamp").animate({'opacity': '1'}, 120);
        WDN.jQuery(this).children(".stamp").animate({'opacity': '1'}, 120);
      }, function () {
        WDN.jQuery(this).children(".timestamp").animate({'opacity': '0'}, 120);
        WDN.jQuery(this).children(".stamp").animate({'opacity': '0.65'}, 120);
      }
    );
    
    //Make sure the footer input is only submitting as email
    WDN.jQuery("#visitorChat_footercontainer #visitorChat_login_chatmethod").val("EMAIL");
    
    //Make sure the chat input is only submitting as chat.
    WDN.jQuery("#visitorChat_container #visitorChat_login_chatmethod").val("CHAT");
    
    //Validator
    WDN.jQuery('#visitorchat_clientLogin').validation();
    
    WDN.jQuery('#visitorChat_footercontainer #visitorchat_clientLogin').bind('validate-form', function(event, result) {
      if (result) {
        VisitorChat.startEmail();
      }
      return true;
    });
    
    //Call the parent
    this._super();
    
    //Click header to open up Chat
    WDN.jQuery('#visitorChat_header').click(function(){
      if (!WDN.jQuery('#visitorChat_container').is(":visible")) {
		WDN.jQuery("#visitorChat_container").slideDown(320);
      } else {
        WDN.jQuery("#visitorChat_container").slideUp(320);
      }
	  
      if (VisitorChat.chatOpened) {
    	if (VisitorChat.chatStatus == 'CHATTING' || VisitorChat.chatStatus == 'OPERATOR_PENDING_APPROVAL') {
    	  return false;
        }
        VisitorChat.stop();
      } else {
        VisitorChat.startChat();
      }
        
      return false;
    });
    
    //Logout function
    WDN.jQuery('#visitorChat_logout').click(WDN.jQuery.proxy(function(){
      if (!VisitorChat.confirmClose()) {
        return false;
      }
      
      VisitorChat.stop();
      
      return false;
    }, this));
    
    //Field watermarks
    WDN.jQuery("#visitorChat_name").watermark("Name (Optional)");
    WDN.jQuery("#visitorChat_email").watermark("Email (Required)");
    WDN.jQuery("#visitorChat_messageBox").watermark("Question or comment?");
    
    //if email_fallback is checked, make sure that the email is required.
    WDN.jQuery("#visitorChat_email_fallback").click(function(){
      if(WDN.jQuery(this).is(":checked")) {
        WDN.jQuery("#visitorChat_email").watermark("Email (Required)");
        WDN.jQuery('#visitorChat_email').addClass('required-entry');
      } else {
        WDN.jQuery("#visitorChat_email").watermark("Email (Optional)");
        WDN.jQuery('#visitorChat_email').removeClass('required-entry');
      }
    });
    
    WDN.jQuery("#visitorChat_container").ready(function(){
      //Are there no operators available?  If not, make email_fallback checked by default.
      if (!this.operatorsAvailable) {
        WDN.jQuery('#visitorChat_email_fallback').prop("checked", true);
        WDN.jQuery('#visitorChat_email').addClass('required-entry');
      }
    });
    
    //This will slide down the Name and Email fields, plus the Ask button
    WDN.jQuery("#visitorChat_messageBox").keyup(function(){
        WDN.jQuery(".visitorChat_info, #visitorChat_login_submit").slideDown("fast");
    });
  },
  
  onConversationStatus_Chatting: function(data)
  {
    this._super(data);
    
    //Minimize header function while chatting
    WDN.jQuery('#visitorChat_header').click(function(){
      if (WDN.jQuery('#visitorChat_container').css('display') === 'none') {
          WDN.jQuery("#visitorChat_header").animate({'width': '60px'}, 280);
      } else {
          WDN.jQuery("#visitorChat_header").animate({'width': '204px'}, 280);
      }
    });
    
    //Logout option now visible
    WDN.jQuery("#visitorChat_header").hover(function () {
        WDN.jQuery("#visitorChat_logout").css({'display': 'inline-block'});
      }, function () {
        WDN.jQuery("#visitorChat_logout").css({'display': 'none'});
    });
  },
  
  handleUserDataResponse: function(data) {
    this.conversationID  = data['conversationID'];
    
    //Call the parent logic.
    this._super(data);
    
    //Handle the rest of the data.
    if (data['conversationID']) {
      this.startChat(true);
      
    }
    
    this.displaySiteAvailability();
  },
  
  updatePHPSESSID: function(phpsessid) {
    this.phpsessid = phpsessid;
  
    //set the cookie.
    WDN.jQuery.cookies.set('UNL_Visitorchat_Session', phpsessid, {domain: '.unl.edu'});
  },

  loadStyles: function() {
    //load styling.
    if (document.createStyleSheet){
      document.createStyleSheet(this.serverURL + "css/remote.php");
    } else {
      WDN.jQuery("head").append(WDN.jQuery("<link rel='stylesheet' href='" + this.serverURL + "css/remote.php' type='text/css' media='screen' />"));
    }
    
    this._super();
  },
  
  init: function() {
    //Handle cookies. (IE session handling);
    var phpsessid = WDN.jQuery.cookies.get('UNL_Visitorchat_Session');
    if (phpsessid != null) {
      this.phpsessid = phpsessid;
    }
    
    //set the for_url
    WDN.jQuery('#initial_url').val(document.URL);
    WDN.jQuery('#initial_pagetitle').val(WDN.jQuery(document).attr('title'));
    
    this._super();
  },
  
  stop: function() {
	if (WDN.jQuery('#visitorChat_container').is(":visible")) {
      WDN.jQuery("#visitorChat_container").slideUp(400, WDN.jQuery.proxy(function() {
	    this.stop();
      }, this));
	}
	
	this._super();
	
    WDN.jQuery("#visitorChat_logout").css({'display': 'none'});
    WDN.jQuery("#visitorChat_header").animate({'width': '60px'}, 200);
    
    WDN.jQuery("#visitorChat_footercontainer").html(this.loginHTML);
	WDN.jQuery("#visitorChat_footerHeader").css({'display': 'block'});
	
    //Delete the current cookie.
    WDN.jQuery.cookies.del('UNL_Visitorchat_Session');
    
    //Reset email-fallback text
    WDN.jQuery("#email-fallback-text").html('I would like a response via email.');
    
    this.initWatchers();
  },
  
  displaySiteAvailability: function() {
    if (this.operatorsAvailable || this.chatOpened) {
      WDN.jQuery("#visitorChat_header").css({'display': 'block'});
    } else {
    	WDN.jQuery("#visitorChat_header").css({'display': 'none'});
    }
  }
});

WDN.jQuery(function(){
  WDN.loadJS('/wdn/templates_3.1/scripts/plugins/validator/jquery.validator.js', function() {
    VisitorChat = new VisitorChat_Chat();
  });
});
