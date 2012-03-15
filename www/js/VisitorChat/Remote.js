var VisitorChat_Chat = VisitorChat_ChatBase.extend({
  loginHTML: false,
  
  startEmail: function() {
    this.launchChatContainer();
    this.start();
  },
  
  startChat: function(chatInProgress) {
    this.launchChatContainer();
    
    WDN.jQuery("#visitorchat_clientLogin").parent().html("Disabled");
    
    if (chatInProgress) {
      this.chatStatus = false;
      return this.start();
    }
    
    this.updateChatContainerWithHTML("#visitorChat_container", this.loginHTML);
    
    WDN.jQuery("#visitorChat_login_chatmethod").val("CHAT");
    
    this.start();
  },
  
  launchChatContainer: function()
  {
    //Remove an old one if it is there.
    WDN.jQuery('#visitorChat_container').remove();

    //set up a container.
    WDN.jQuery("body").append("<div id='visitorChat'>" +
    		//Note: We have to call the server to get the phpssid.
            "<div id='visitorChat_header'>Chat" +
               //"Chat <span id='visitorChat_availability'></span>" +
                  //Turn this div into an unordered list
                  "<ul class='visitorChat_options'>" +
                       "<li><a id='visitorChat_close' href='" + this.serverURL + "logout'>close</a></li>" +
                  "</ul>" +
                  "<div id='visitorChat_sound_container'>" +
                      "<audio id='visitorChat_sound' src='"+ this.serverURL + "audio/message.wav'></audio></div>" +
                "</div>" +
              "<div id='visitorChat_container'><div class='chat_notify visitorChat_loading'>Initializing, please wait.</div></div>" +
          "</div>");       
    
    this.chatStatus = "LOGIN";
    
    this.loginHTML = WDN.jQuery("#visitorchat_clientLogin").parent().html();
    
    this.displaySiteAvailability();
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
    WDN.jQuery('#visitorChat_launcher,' +
            '#visitorChat_close,' +
            '#visitorChat_container,' +
            'visitorChat_email_fallback,' +
            '#visitorChat_collapse' +
            '#visitorChat_login_sumbit').unbind();
    
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
    
    WDN.jQuery('#visitorChat_launcher, #visitorChat_close').click(WDN.jQuery.proxy(function(){
      if (VisitorChat.chatOpened) {
        if ((this.chatStatus == 'CHATTING'
            || this.chatStatus == 'OPERATOR_PENDING_APPROVAL') && !this.confirmClose()) {
          return false;
        }
        VisitorChat.stop();
      } else {
        VisitorChat.startChat();
      }
      
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
        WDN.jQuery(".visitorChat_info, #visitorChat_login_sumbit").slideDown("fast");
    });
    
    //This is where the collapse function happens
    WDN.jQuery("#visitorChat_header").click(function(){
        WDN.jQuery("#visitorChat_container").slideToggle("fast", function() {
            if (WDN.jQuery('#visitorChat_container').css('display') === 'none') {
            	WDN.jQuery("#visitorChat_close").animate({'opacity': '0'}, 240)
                WDN.jQuery("#visitorChat_header").animate({'width': '100px'}, 240)
            } else {
            	WDN.jQuery("#visitorChat_close").animate({'opacity': '1'}, 240)
            	WDN.jQuery("#visitorChat_header").animate({'width': '232px'}, 240)
            }
        });
    });
    
  },
  
  handleUserDataResponse: function(data) {
    this.conversationID  = data['conversationID'];
    var previousPHPSESSID = this.phpsessid;
    
    //Call the parent logic.
    this._super(data);
    
    //set the cookie.
    WDN.jQuery.cookies.set('UNL_Visitorchat_Session', this.phpsessid, {domain: '.unl.edu'});
    
    //Handle the rest of the data.
    if (data['conversationID']) {
      this.startChat(true);
    }
    
    this.displaySiteAvailability();
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
    
    WDN.jQuery("#visitorChat_footercontainer").html(this.loginHTML);
    
    //Delete the current cookie.
    WDN.jQuery.cookies.del('UNL_Visitorchat_Session');
    
    this.initWatchers();
  },
  
  displaySiteAvailability: function() {
    if (this.operatorsAvailable) {
      WDN.jQuery("#visitorChat_launcher").html("Chat");
      WDN.jQuery("#visitorChat_launcher, #visitorChat_header").addClass('visitorChat_online');
      WDN.jQuery("#visitorChat_launcher, #visitorChat_header").removeClass('visitorChat_offline');
    } else {
      WDN.jQuery("#visitorChat_launcher, #visitorChat_header").addClass('visitorChat_offline');
      WDN.jQuery("#visitorChat_launcher, #visitorChat_header").removeClass('visitorChat_online');
      WDN.jQuery("#visitorChat_launcher").html("Email");
    }
  }
});

WDN.jQuery(function(){
  WDN.loadJS('/wdn/templates_3.1/scripts/plugins/validator/jquery.validator.js', function() {
    VisitorChat = new VisitorChat_Chat();
  });
});
