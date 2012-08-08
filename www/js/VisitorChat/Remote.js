var VisitorChat_Chat = VisitorChat_ChatBase.extend({
    loginHTML: false,
    clientName: false,
    initialMessage: false,
    confirmationHTML: false,
    userType: 'client',

    startEmail:function () {
        this.launchChatContainer();
        this.start();
    },

    startChat:function (chatInProgress) {
        this.launchChatContainer();

        if (chatInProgress) {
            this.chatStatus = false;
            return this.start();
        }

        //Always show the chat if we are logged in as an operator.  Otherwise only show if someone is available.
        if (this.userType == 'operator') {
            html = "<div class='chat_notify'>You are currently logged in as an operator and can not start a client conversation from this browser.  If you want to start a conversation, please either log out or do so in another web browser.</div>";
            WDN.jQuery("#visitorChat_container").html(html);
        } else {
            this.updateChatContainerWithHTML("#visitorChat_container", this.loginHTML);
        }

        WDN.jQuery("#visitorChat_footerHeader").css({'display':'none'});
        WDN.jQuery("#visitorChat_email").hide();
        WDN.jQuery("#visitorChat_container #visitorChat_email_fallback_text").html('If no operators are available,&nbsp;I would like to receive an email.');

        this.start();
    },

    onOperatorMessage:function (message) {
        //Fire an analytics event on first response.  set cookie for cross domain.
        if (!WDN.jQuery.cookies.get('UNL_Visitorchat_FirstOperatorResponse')) {
            start = WDN.jQuery.cookies.get('UNL_Visitorchat_Start');
            date = new Date();
            date = Math.round(date.getTime() / 1000);
            difference = date - WDN.jQuery.cookies.get('UNL_Visitorchat_Start');

            WDN.analytics.callTrackEvent('WDN Chat', 'Response', 'Received', difference);

            //Set a cookie so that we don't call this if we have to reload the chat (page refresh or move to another page).
            WDN.jQuery.cookies.set('UNL_Visitorchat_FirstOperatorResponse', difference, {domain:'.unl.edu'});
        }
    },

    launchChatContainer:function () {
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

        WDN.jQuery("#visitorChat_header").animate({'width':'204px'}, 200);

        //Display and set the name (if found).
        WDN.jQuery("#visitorChat_container").delay(10).slideDown(320, function(){
            WDN.jQuery("#visitorChat_name").val(WDN.idm.displayName());
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

    initWatchers:function () {
        /* This method is called several times thoughout
         * executation.  Thus in order to stop the stacking
         * of watch functions, we should always unbind previous
         * watch functions before applying the new ones.
         */
        WDN.jQuery('#visitorChat_container, ' +
            '#visitorChat_email_fallback, ' +
            '#visitorChat_logout, ' +
            '#visitorChat_end,' +
            '#visitorChat_login_submit, ' +
            '#visitorChat_header, ' +
            '#visitorChat_chatBox > ul > li,' +
            '#visitorChat_messageBox,' +
            '#visitorChat_email,' +
            '#visitorChat_confiramtionEmail,' +
            '#visitorChat_failedOptions_yes,' +
            '#visitorChat_failedOptions_yes,' +
            '#visitorChat_sendAnotherConfirmation,' +
            '#visitorChat_name,').unbind();

        //Reveal timestamp
        WDN.jQuery("#visitorChat_chatBox > ul > li").hover(
            function () {
                WDN.jQuery(this).children(".timestamp").animate({'opacity':'1'}, 120);
                WDN.jQuery(this).children(".stamp").animate({'opacity':'1'}, 120);
            }, function () {
                WDN.jQuery(this).children(".timestamp").animate({'opacity':'0'}, 120);
                WDN.jQuery(this).children(".stamp").animate({'opacity':'0.65'}, 120);
            }
        );

        //Make sure the footer input is only submitting as email
        WDN.jQuery("#visitorChat_footercontainer #visitorChat_login_chatmethod").val("EMAIL");

        //Make sure the chat input is only submitting as chat.
        WDN.jQuery("#visitorChat_container #visitorChat_login_chatmethod").val("CHAT");

        //Validator
        WDN.jQuery('#visitorchat_clientLogin, #visitorChat_confirmationEamilForm').validation();

        WDN.jQuery('#visitorChat_footercontainer #visitorchat_clientLogin').bind('validate-form', function (event, result) {
            if (result) {
                VisitorChat.startEmail();
            }
            return true;
        });

        WDN.jQuery('#visitorChat_confirmationEamilForm').bind('validate-form', function (event, result) {
            if (result) {
                WDN.jQuery('#visitorChat_confirmationContainer').html("The Email transcript has been sent to " + WDN.jQuery('#visitorChat_confiramtionEmail').val() + " <br /> <a href='#' id='visitorChat_sendAnotherConfirmation'>Send another one</a>.");

                WDN.jQuery().unbind('#visitorChat_sendAnotherConfirmation');

                WDN.jQuery('#visitorChat_sendAnotherConfirmation').click(function(){
                    WDN.jQuery('#visitorChat_confirmationContainer').html(WDN.jQuery(VisitorChat.confirmationHTML).filter('#visitorChat_confirmationContainer').html());
                    return false;
                });
            }

            return true;
        });

        //Call the parent
        this._super();

        //Click header to open up Chat
        WDN.jQuery('#visitorChat_header').click(function () {
            if (!WDN.jQuery('#visitorChat_container').is(":visible")) {
                WDN.jQuery("#visitorChat_container").slideDown(320);
            } else {
                WDN.jQuery("#visitorChat_container").slideUp(320);

                if (VisitorChat.chatStatus == "LOGIN") {
                    VisitorChat.stop();
                    return false;
                }
            }

            if (VisitorChat.chatOpened) {
                if (VisitorChat.chatStatus == 'CHATTING' || VisitorChat.chatStatus == 'OPERATOR_PENDING_APPROVAL') {
                    return false;
                }

            } else {
                VisitorChat.startChat();
            }

            return false;
        });

        //Logout function
        WDN.jQuery('#visitorChat_logout').click(WDN.jQuery.proxy(function () {
            if (this.chatStatus == 'CHATTING' && !VisitorChat.confirmClose()) {
                return false;
            }

            VisitorChat.stop();

            return false;
        }, this));

        //Allow the client to end the conversation
        WDN.jQuery('#visitorChat_end').click(WDN.jQuery.proxy(function () {
            if (!VisitorChat.confirmClose()) {
                return false;
            }

            VisitorChat.changeConversationStatus("CLOSED");

            return false;
        }, this));

        if (VisitorChat.chatStatus == "LOGIN" || VisitorChat.chatStatus == false) {
            //Field watermarks
            WDN.jQuery("#visitorChat_name").watermark("Name (optional)");
            WDN.jQuery("#visitorChat_email").watermark("Email (optional)");
            WDN.jQuery("#visitorChat_messageBox").watermark("Question or comment?");

            //if email_fallback is checked, make sure that the email is required.
            WDN.jQuery("#visitorChat_email_fallback").click(function () {
                if (WDN.jQuery(this).is(":checked")) {
                    WDN.jQuery("#visitorChat_email").watermark("Email (Required)");
                    WDN.jQuery('#visitorChat_email').addClass('required-entry');
                } else {
                    WDN.jQuery("#visitorChat_email").watermark("Email (Optional)");
                    WDN.jQuery('#visitorChat_email').removeClass('required-entry');
                }
            });
        }

        if (VisitorChat.chatStatus == 'CLOSED') {
            WDN.jQuery("#visitorChat_confiramtionEmail").watermark("Email Address");
        }

        //This will slide down the Name and Email fields, plus the Ask button
        WDN.jQuery("#visitorChat_messageBox").keyup(function () {
            WDN.jQuery(".visitorChat_info, #visitorChat_login_submit").slideDown("fast", function(){
                if (VisitorChat.initialMessage && !WDN.jQuery("#visitorChat_messageBox").is(":focus")) {
                    WDN.jQuery("#visitorChat_email").focus();
                }
            });
        });

        WDN.jQuery("#visitorChat_failedOptions_yes").click(function() {
            VisitorChat.stop(function(){
                WDN.jQuery("#visitorChat_name").val(VisitorChat.clientName);
                WDN.jQuery("#visitorChat_messageBox").val(VisitorChat.initialMessage);
                WDN.jQuery("#visitorChat_email").focus();
                WDN.jQuery("#visitorChat_messageBox").keyup();
            });

            return true;
        });

        WDN.jQuery('#visitorChat_confirmationEmail').keypress(function (e) {
                if (e.which == 13) {
                    e.preventDefault();

                    WDN.jQuery('#visitorChat_confirmationEamilForm').submit();
                }
        });

        WDN.jQuery("#visitorChat_failedOptions_no").click(function() {
            VisitorChat.stop();

            return true;
        });

        //set the for_url
        WDN.jQuery('#initial_url').val(document.URL);
        WDN.jQuery('#initial_pagetitle').val(WDN.jQuery(document).attr('title'));
    },

    onLogin:function () {
        this.clientName = WDN.jQuery("#visitorChat_name").val();
        this.initialMessage = WDN.jQuery("#visitorChat_messageBox").val();

        this._super();

        //Record a start event cookie (for analytics)
        if (!WDN.jQuery.cookies.get('UNL_Visitorchat_Start')) {
            //Set a cookie.
            date = new Date();
            WDN.jQuery.cookies.set('UNL_Visitorchat_Start', (Math.round(date.getTime() / 1000)), {domain:'.unl.edu'});

            //Send analytics data
            _gaq.push(['wdn._setCustomVar',
                1,
                'WDN Chat',
                'Yes',
                2
            ]);

            //Mark as started
            WDN.analytics.callTrackEvent('WDN Chat', 'Started');
        }
    },

    onConversationStatus_Closed:function (data) {
        if (WDN.jQuery("#visitorChat_confirmationContainer").length != 0) {
            return false;
        }

        this._super(data);

        this.confirmationHTML = data['confirmationHTML'];

        WDN.jQuery("#visitorChat_chatBox").height("150px");

        WDN.jQuery("#visitorChat_closed").append(data['confirmationHTML'])

        this.initWatchers();

        WDN.jQuery().unbind('visitorChat_header');
        
        //Logout option now visible
        this.displayLogoutButton();

        this.scroll();
    },
    
    displayLogoutButton: function() {
        WDN.jQuery("#visitorChat_logout").show();
        WDN.jQuery("#visitorChat_header").hover(function () {
            WDN.jQuery("#visitorChat_logout").css({'display':'inline-block'});
        }, function () {
            WDN.jQuery("#visitorChat_logout").css({'display':'none'});
        });
    },

    onConversationStatus_Chatting:function (data) {
        this._super(data);

        //Minimize header function while chatting
        WDN.jQuery('#visitorChat_header').click(function () {
            if (WDN.jQuery('#visitorChat_container').css('display') === 'none') {
                WDN.jQuery("#visitorChat_header").animate({'width':'60px'}, 280);
            } else {
                WDN.jQuery("#visitorChat_header").animate({'width':'204px'}, 280);
            }
        });

        WDN.jQuery().unbind('visitorChat_header');
        //Logout option now visible
        WDN.jQuery("#visitorChat_header").hover(function () {
            WDN.jQuery("#visitorChat_end").css({'display':'inline-block'});
        }, function () {
            WDN.jQuery("#visitorChat_end").css({'display':'none'});
        });
    },

    handleUserDataResponse:function (data) {
        this.conversationID = data['conversationID'];

        this.userType = data['userType'];

        //Call the parent logic.
        this._super(data);

        if (data['loginHTML'] !== undefined && data['loginHTML']) {
            this.loginHTML = data['loginHTML'];
            WDN.jQuery("#wdn_feedback_comments").replaceWith(this.loginHTML);
            this.initWatchers();
        }

        //Handle the rest of the data.
        if (data['conversationID']) {
            this.startChat(true);

        }

        this.displaySiteAvailability();
    },

    onConversationStatus_Emailed:function (data) {
        this.displayLogoutButton();
        clearTimeout(VisitorChat.loopID);
        var html = '<div class="chat_notify">Thank you, an email has been sent!</div>';
        this.updateChatContainerWithHTML("#visitorChat_container", html);
    },

    onConversationStatus_Captcha:function (data) {
        this.displayLogoutButton();
        if (WDN.jQuery('#visitorChat_captchaForm').length != 0) {
            return;
        }
        
        script = WDN.jQuery(data['html']).next('script').attr('src');
        WDN.loadJS(script);
        this.updateChatContainerWithHTML("#visitorChat_container", data['html']);
    },

    onConversationStatus_OperatorLookupFailed:function (data) {
        this.displayLogoutButton();
        clearTimeout(VisitorChat.loopID);
        var html = '<div class="chat_notify">Unfortunately all of our operators are currently busy.  Would you like to send an email instead?' +
            '<div id="visitorChat_failedOptions"><a id="visitorChat_failedOptions_yes" href="#">Yes</a> <a id="visitorChat_failedOptions_no" href="#">No</a></div></div>';
        this.updateChatContainerWithHTML("#visitorChat_container", html);
    },

    updatePHPSESSID:function (phpsessid) {
        this.phpsessid = phpsessid;

        //set the cookie.
        WDN.jQuery.cookies.set('UNL_Visitorchat_Session', phpsessid, {domain:'.unl.edu'});
    },

    loadStyles:function () {
        //load styling.
        if (document.createStyleSheet) {
            document.createStyleSheet(this.serverURL + "css/remote.php");
        } else {
            WDN.jQuery("head").append(WDN.jQuery("<link rel='stylesheet' href='" + this.serverURL + "css/remote.php' type='text/css' media='screen' />"));
        }

        this._super();
    },

    init:function (serverURL, refreshRate) {
        WDN.jQuery("body").append("" + 
            "<div id='visitorChat'>" +
                 "<div id='visitorChat_header'>" +
                    "<span id='visitorChat_header_text'>Chat</span>" +
                    "<div id='visitorChat_logout'>" +
                        "<a href='#'>close</a>" +
                    "</div>" +
                    "<div id='visitorChat_end'>" +
                        "<a href='#'>end conversation</a>" +
                    "</div>" +
                "</div>" +
                "<div id='visitorChat_sound_container'>" +
                    "<audio id='visitorChat_sound'></audio>" +
                "</div>" +
            "</div>")

        WDN.jQuery("#visitorChat_logout").hide();
        WDN.jQuery("#visitorChat_end").hide();

        //Handle cookies. (IE session handling);
        var phpsessid = WDN.jQuery.cookies.get('UNL_Visitorchat_Session');
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

        callbackSet = false;
        if (WDN.jQuery('#visitorChat_container').is(":visible")) {
            callbackSet = true;
            WDN.jQuery("#visitorChat_container").slideUp(400, WDN.jQuery.proxy(function () {
                if (callback) {
                    callback();
                }
            }, this));
        }

        this._super();

        this.closeChatContainer();

        if (WDN.jQuery.cookies.get('UNL_Visitorchat_Start')) {
            date = new Date();
            date = Math.round(date.getTime() / 1000);
            difference = date - WDN.jQuery.cookies.get('UNL_Visitorchat_Start');

            WDN.analytics.callTrackEvent('WDN Chat', 'Ended', undefined, difference);
        }

        //Delete the current cookie.
        WDN.jQuery.cookies.del('UNL_Visitorchat_Start', {domain:'.unl.edu'});
        WDN.jQuery.cookies.del('UNL_Visitorchat_Session', {domain:'.unl.edu'});
        WDN.jQuery.cookies.del('UNL_Visitorchat_FirstOperatorResponse', {domain:'.unl.edu'});

        this.initWatchers();

        if (callback && !callbackSet) {
            callback();
        }
    },

    closeChatContainer: function() {
        WDN.jQuery("#visitorChat_logout").css({'display':'none'});
        WDN.jQuery("#visitorChat_header").animate({'width':'60px'}, 200);

        WDN.jQuery("#visitorChat_footercontainer").html(this.loginHTML);
        WDN.jQuery("#visitorChat_footerHeader").css({'display':'block'});
    },

    displaySiteAvailability:function () {
        if (this.chatOpened) {
            return true;
        }
        
        if (this.operatorsAvailable) {
            WDN.jQuery("#visitorChat_header").show();
        } else {
            WDN.jQuery("#visitorChat_header").hide();
        }
        
        return true;
    }
});
