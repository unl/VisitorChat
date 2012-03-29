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
    
    WDN.jQuery("#visitorChat_container #visitorChat_email_fallback_text").html('If no operators are available,<br />I would like to receive an email.');
    WDN.jQuery("#visitorChat_container").slideDown(450);
    WDN.jQuery("#visitorChat_header").animate({'width': '230px',
                                               'opacity': '1'}, 280);
    WDN.jQuery("#visitorChat_header_text").animate({'opacity': '1'}, 240);
    
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
    
    this.chatStatus = "LOGIN";
    
    this.loginHTML = WDN.jQuery("#visitorchat_clientLogin").parent().html();
    
    this.displaySiteAvailability();
    
    WDN.jQuery("#visitorchat_clientLogin").parent().html("Disabled");
  },
  
  confirmClose: function(id) {
      var link = document.getElementById(id);
      
      if (confirm("Logout?")) {
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
    WDN.jQuery('#visitorChat_launcher, ' +
            '#visitorChat_close, ' +
            '#visitorChat_container, ' +
            '#visitorChat_email_fallback, ' +
            '#visitorChat_collapse, ' +
            '#visitorChat_login_sumbit, ' +
            '#visitorChat_header').unbind();
    
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
    WDN.jQuery('#visitorChat_header').click(WDN.jQuery.proxy(function(){
      if (VisitorChat.chatOpened) {
        if ((VisitorChat.chatStatus == 'CHATTING' || VisitorChat.chatStatus == 'OPERATOR_PENDING_APPROVAL') && !VisitorChat.confirmClose()) {
          return false;
        }
        VisitorChat.stop();
      } else {
        VisitorChat.startChat();
      }
        
      return false;
    }, this));
    
    //Hover header function
    WDN.jQuery("#visitorChat_header").hover(
      function(){
    	WDN.jQuery(this).animate({opacity: '1'}, 140)
      }, function(){
    	WDN.jQuery(this).animate({opacity: '0.8'}, 140)
      }
    );
    
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
        WDN.jQuery(".visitorChat_info, #visitorChat_login_sumbit").slideDown("fast");
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
    
    this._super();
  },

  stop: function() {
    this._super();
    WDN.jQuery("#visitorChat_logout").css({'display': 'none'});
    WDN.jQuery("#visitorChat_header").animate({'width': '60px',
    	   							           'opacity': '0.8'}, 280);
    WDN.jQuery("#visitorChat_header_text").animate({'opacity': '0'}, 240);
    WDN.jQuery("#visitorChat_footercontainer").html(this.loginHTML);

    //Delete the current cookie.
    WDN.jQuery.cookies.del('UNL_Visitorchat_Session');
    
    //Reset email-fallback text
    WDN.jQuery("#email-fallback-text").html('I would like a response via email.');
    
    this.initWatchers();
  },
  
  displaySiteAvailability: function() {
    if (this.operatorsAvailable) {
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
