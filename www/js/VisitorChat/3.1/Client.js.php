<?php
require_once(__DIR__ . "/ChatBase.js.php");
require_once(\UNL\VisitorChat\Controller::$applicationDir . "/www/js/jquery.cookies.min.js");
?>

var VisitorChat_Client = VisitorChat_ChatBase.extend({
    loginHTML: false,
    clientName: false,
    initialMessage: false,
    confirmationHTML: false,
    userType: 'client',
    method: 'chat',

    startEmail:function () {
        this.method = 'email';
        this.launchEmailContainer();
        this.start();
    },

    startChat:function (chatInProgress) {
        this.method = 'chat';
        this.launchChatContainer();

        if (this.blocked) {
            var html = "Your IP address has been blocked.  If you feel that this is an error, please contact operator@unl.edu";

            this.updateChatContainerWithHTML("#visitorChat_container", html, false);
            return;
        }

        if (chatInProgress && this.chatStatus == "LOGIN") {
            this.chatStatus = "CHATTING";

            return this.start();
        }

        //Always show the chat if we are logged in as an operator.  Otherwise only show if someone is available.
        if (this.userType == 'operator') {
            html = "<div class='chat_notify'>You are currently logged in as an operator and can not start a client conversation from this browser.  If you want to start a conversation, please either log out or do so in another web browser.</div>";
            this.updateChatContainerWithHTML("#visitorChat_container", html, false);
        } else {
            this.updateChatContainerWithHTML("#visitorChat_container", this.loginHTML, false);
        }

        WDN.jQuery("#visitorChat_footerHeader").css({'display':'none'});
        WDN.jQuery("#visitorChat_container #visitorChat_email_fallback_text").html('If no operators are available,&nbsp;I would like to receive an email.');

        //Due to IE, make sure that we clear the value of the input if it equals the placeholder value
        if (WDN.jQuery("#visitorChat_messageBox").val() == WDN.jQuery("#visitorChat_messageBox").attr("placeholder")) {
            WDN.jQuery("#visitorChat_messageBox").val('');
        }

        if (typeof visitorchat_config !== 'undefined' && typeof visitorchat_config.chat_welcome_message !== 'undefined') {
            WDN.jQuery('#visitorchat_clientLogin').prepend(WDN.jQuery('<p>').html(visitorchat_config.chat_welcome_message));
        }

        WDN.jQuery("label[for='visitorChat_messageBox']").text("How can we assist you?");
        this.start();
    },

    onOperatorMessage:function (message) {
        //Fire an analytics event on first response.  set cookie for cross domain.
        if (!WDN.jQuery.cookies.get('UNL_Visitorchat_FirstOperatorResponse') && WDN.jQuery.cookies.get('UNL_Visitorchat_Start')) {
            start = WDN.jQuery.cookies.get('UNL_Visitorchat_Start');
            date = new Date();
            date = Math.round(date.getTime() / 1000);
            difference = date - WDN.jQuery.cookies.get('UNL_Visitorchat_Start');

            WDN.analytics.callTrackEvent('WDN Chat', 'Response', 'Received', difference);

            //Set a cookie so that we don't call this if we have to reload the chat (page refresh or move to another page).
            WDN.jQuery.cookies.set('UNL_Visitorchat_FirstOperatorResponse', difference, {domain:'.unl.edu'});
        }
    },

    launchEmailContainer:function() {
        this.chatStatus = "LOGIN";

        //Remove an old one if it is there.
        WDN.jQuery('#visitorChat_container').remove();

        //set up a container.
        var html = "<div id='visitorChat_container'>Please Wait...</div>";

        WDN.jQuery("#visitorchat_clientLogin").replaceWith("<div id='visitorChat_container'></div>");

        WDN.jQuery("#visitorChat_container").show();
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

        //Set header_text to visible
        WDN.jQuery("#visitorChat_header_text").css('display', 'inline');

        WDN.jQuery("#visitorChat_header").show();

        this.chatStatus = "LOGIN";

        WDN.jQuery("#visitorchat_clientLogin").parent().html("Disabled");

        WDN.jQuery("#visitorChat_header").animate({'width':'204px'}, 200);

        //Display and set the name (if found).
        WDN.jQuery("#visitorChat_container").delay(10).slideDown(320, function() {
            if (typeof WDN.idm.displayName == 'function' && WDN.idm.displayName() != undefined) {
                WDN.jQuery("#visitorChat_name").val(WDN.idm.displayName());
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
        var email = WDN.jQuery('#visitorChat_email').val();

        //If the email is empty, don't submit and append a warning to the form, otherwise continue on.
        if (email != '') {
            return true;
        }

        //Check if they are confirming anon...
        if (WDN.jQuery('#visitorChat_footercontainer #visitorChat_login_submit').val() == 'Yes, I do not need a response') {
            //Reset to say 'submit'.
            WDN.jQuery('#visitorChat_footercontainer #visitorChat_login_submit').val("Submit");
            return true;
        }

        //Display error and request confirmation before continuing.
        var html = "<div id='visitorchat_clientLogin_anonwaning'>Since you didn't enter an email, we won't be able to respond. Is this OK?</div>";

        WDN.jQuery('#visitorChat_footercontainer #visitorChat_login_submit').before(html);
        WDN.jQuery('#visitorChat_footercontainer #visitorChat_login_submit').val("Yes, I do not need a response");

        this.initWatchers();

        //remove the warning if they start to enter an email
        WDN.jQuery("#visitorChat_email").keyup(function () {
            WDN.jQuery('#visitorchat_clientLogin_anonwaning').remove();
            WDN.jQuery('#visitorChat_footercontainer #visitorChat_login_submit').val("Submit");
        });

        return false;
    },

    ajaxBeforeSubmit:function (arr, $form, options) {
        //Start an email convo now if need be.
        for (var key = 0; key<arr.length; key++) {
            if (arr[key]['name'] == 'method' && arr[key]['value'] == 'EMAIL') {
                if (this.confirmAnonSubmit()) {
                    this.startEmail();
                } else {
                    return false;
                }
            }
        }

        return this._super(arr, $form, options);
    },

    initValidation: function() {
        WDN.jQuery.validation.addMethod('validate-require-if-question',
            'An email address is required if you ask a question so that we can respond.',
            function(value, object) {
                var message = WDN.jQuery('#visitorChat_messageBox').val();
                if (message.indexOf("?") != -1 && value == "") {
                    return false;
                }

                return true;
            });

        //Remove the vaildation binding so that validation does not stack and is always called before ajax submit.
        WDN.jQuery('#visitorchat_clientLogin').data('validation', false);
        WDN.jQuery('#visitorChat_confirmationEmailForm').data('validation', false);

        //Require email for questions submitted via the footer comment form.
        WDN.jQuery('#visitorChat_footercontainer #visitorChat_email').addClass('validate-require-if-question');

        //Validator
        WDN.jQuery('#visitorchat_clientLogin, #visitorChat_confirmationEmailForm').validation();
    },

    initPlaceHolders: function() {
        //Load placeholders if not supported.
        if (WDN.hasDocumentClass('no-placeholder')) {
            WDN.loadJS(WDN.getTemplateFilePath('scripts/plugins/placeholder/jquery.placeholder.min.js'), function() {
                WDN.jQuery('#visitorChat_footercontainer, #visitorChat').find('[placeholder]').placeholder();
            });
        }
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
            '#visitorChat_login_submit, ' +
            '#visitorChat_header, ' +
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

        this.initPlaceHolders();

        this.initValidation();

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

        WDN.jQuery('#visitorchat_clientLogin').bind('validate-form', function (event, result) {
            if (!result) {
                VisitorChat.initPlaceHolders();
            }
        });

        WDN.jQuery('#visitorChat_footercontainer #visitorchat_clientLogin').bind('validate-form', function (event, result) {
            WDN.jQuery('#visitorchat_clientLogin_anonwaning').remove();

            if (WDN.jQuery('#visitorChat_footercontainer #visitorChat_login_submit').val() == 'Yes, no response needed'
                && WDN.jQuery('#visitorChat_email').val() != '') {
                WDN.jQuery('#visitorChat_footercontainer #visitorChat_login_submit').val("Submit");
            }

            return true;
        });

        WDN.jQuery('#visitorChat_confirmationEmailForm').bind('validate-form', function (event, result) {
            if (result) {
                WDN.jQuery('#visitorChat_confirmationContainer').html("The Email transcript has been sent to " + WDN.jQuery('#visitorChat_confiramtionEmail').val() + " <br /> <a href='#' id='visitorChat_sendAnotherConfirmation'>Send another one</a>.");

                WDN.jQuery().unbind('#visitorChat_sendAnotherConfirmation');

                WDN.jQuery('#visitorChat_sendAnotherConfirmation').click(function(){
                    WDN.jQuery('#visitorChat_confirmationContainer').html(WDN.jQuery(VisitorChat.confirmationHTML).filter('#visitorChat_confirmationContainer').html());
                    VisitorChat.initWatchers();
                    return false;
                });
            }

            return false;
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

            if (this.chatStatus == 'CHATTING') {
                VisitorChat.changeConversationStatus("CLOSED");
                return false;
            }

            //change the method to chat, so that the chat window will close.
            //it MIGHT be open due to captcha.
            this.method = 'chat';

            VisitorChat.stop();

            return false;
        }, this));

        if (VisitorChat.chatStatus == "LOGIN" || VisitorChat.chatStatus == false) {
            //if email_fallback is checked, make sure that the email is required.
            WDN.jQuery("#visitorChat_email_fallback").click(function () {
                if (WDN.jQuery(this).is(":checked")) {
                    WDN.jQuery("label[for='visitorChat_email']").text("Email (Required)");
                    WDN.jQuery('#visitorChat_email').addClass('required-entry');
                } else {
                    WDN.jQuery("label[for='visitorChat_email']").text("Email (Optional)");
                    WDN.jQuery('#visitorChat_email').removeClass('required-entry');
                }
            });
        }

        //This will slide down the Name and Email fields, plus the Ask button
        WDN.jQuery("#visitorChat_messageBox").one("keyup", function () {
            if (typeof WDN.idm.displayName == 'function' && WDN.idm.displayName() != undefined) {
                WDN.jQuery("#visitorChat_name").val(WDN.idm.displayName());
            }
            if (WDN.idm.user.mail) {
                WDN.jQuery("#visitorChat_email").val(WDN.idm.user.mail[0]);
            }

            WDN.jQuery(".visitorChat_info, #visitorChat_login_submit").slideDown("fast", function(){
                if (VisitorChat.initialMessage && !WDN.jQuery("#visitorChat_messageBox").is(":focus")) {
                    WDN.jQuery("#visitorChat_email").focus();
                }
            });
        });

        WDN.jQuery("#visitorChat_failedOptions_yes").click(function() {
            VisitorChat.stop(function(){
                if (VisitorChat.clientName) {
                    WDN.jQuery("#visitorChat_name").val(VisitorChat.clientName);
                }

                if (VisitorChat.initialMessage) {
                    WDN.jQuery("#visitorChat_messageBox").val(VisitorChat.initialMessage);
                }

                WDN.jQuery("#visitorChat_email").focus();
                WDN.jQuery("#visitorChat_messageBox").keyup();
            });

            return true;
        });

        WDN.jQuery('#visitorChat_confirmationEmail').keypress(function (e) {
            if (e.which == 13) {
                e.preventDefault();

                WDN.jQuery('#visitorChat_confirmationEmailForm').submit();
            }
        });

        WDN.jQuery("#visitorChat_failedOptions_no").click(function() {
            VisitorChat.stop();

            return true;
        });

        if (this.chatStatus) {
            WDN.jQuery("#visitorChat_header").hover(function () {
                WDN.jQuery("#visitorChat_logout").css({'display':'inline-block'});
            }, function () {
                WDN.jQuery("#visitorChat_logout").css({'display':'none'});
            });
        }

        //set the for_url
        WDN.jQuery('#initial_url').val(document.URL);
        WDN.jQuery('#initial_pagetitle').val(WDN.jQuery(document).attr('title'));
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
        WDN.jQuery.ajax({
            type:"POST",
            url:this.serverURL + "conversation/" + this.conversationID + "/edit?format=json&" + this.getURLSessionParam(),
            xhrFields:{
                withCredentials:true
            },
            data:"client_is_typing=" + newStatus
        })
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
            var html = '<div class="chat_notify visitorChat_loading">Please wait while we find someone to help you.</div>';
            this.updateChatContainerWithHTML("#visitorChat_container", html);
        } else {
            var html = '<div class="chat_notify visitorChat_loading">Please wait while we process your request.</div>';
            this.updateChatContainerWithHTML("#visitorChat_container", html);
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

        if (this.method == 'email') {
            this.stop();
        }

        //Make sure that the closed button is visible at this point.
        this.chatStatus = 'EMAILED';
        this.initWatchers();
    },

    onLogin:function () {
        this.clientName = WDN.jQuery("#visitorChat_name").val();
        this.initialMessage = WDN.jQuery("#visitorChat_messageBox").val();

        this._super();

        //Record a start event cookie (for analytics)
        VisitorChat.deleteAnalyticsCookies();

        //Set a cookie.
        date = new Date();
        WDN.jQuery.cookies.set('UNL_Visitorchat_Start', (Math.round(date.getTime() / 1000)), {domain:'.unl.edu'});

        // Send analytics data to main account
        _gaq.push(['wdn._setCustomVar',
            1,
            'WDN Chat',
            'Yes',
            2
        ]);

        // Send analytics data to local analytics account if it exists
        if (WDN.analytics.isDefaultTrackerReady()) {
            _gaq.push(['_setCustomVar',
                1,
                'WDN Chat',
                'Yes',
                2
            ]);
        }

        //Mark as started
        WDN.analytics.callTrackEvent('WDN Chat', 'Started');
    },

    onConversationStatus_Closed:function (data) {
        if (WDN.jQuery("#visitorChat_confirmationContainer").length != 0) {
            return false;
        }

        this._super(data);

        this.confirmationHTML = data['confirmationHTML'];

        WDN.jQuery("#visitorChat_chatBox").height("150px");

        WDN.jQuery("#visitorChat_messageForm").remove();

        WDN.jQuery("#visitorChat_closed").append(data['confirmationHTML'])

        this.initWatchers();

        WDN.jQuery().unbind('visitorChat_header');

        this.scroll();
    },

    onConversationStatus_Chatting:function (data) {
        this._super(data);

        //Minimize header function while chatting
        WDN.jQuery('#visitorChat_header').click(function () {
            if (WDN.jQuery('#visitorChat_container').css('display') === 'none') {
                WDN.jQuery("#visitorChat_header").animate({'width':'105px'}, 280);
            } else {
                WDN.jQuery("#visitorChat_header").animate({'width':'204px'}, 280);
            }
        });

        var is_typing = false;
        if (data['operators'] !== undefined) {
            for (operator in data['operators']) {
                if (data['operators'][operator].is_typing == 'YES') {
                    is_typing = true;
                }
            }
        }

        if (is_typing) {
            WDN.jQuery('#visitorChat_is_typing').text('The other party is typing').show(500);
        } else {
            WDN.jQuery('#visitorChat_is_typing').hide(500);
        }

        WDN.jQuery().unbind('visitorChat_header');
    },

    handleUserDataResponse:function (data) {
        this.conversationID = data['conversationID'];

        this.userType = data['userType'];

        //Call the parent logic.
        this._super(data);

        if (data['loginHTML'] !== undefined && data['loginHTML']) {
            this.loginHTML = data['loginHTML'];

            WDN.jQuery("#wdn_feedback_comments").replaceWith("<div id='visitorChat_footercontainer'>" + this.loginHTML + "</div>");

            //media queries are not supported in ie8, so show the footer container by default.
            if (WDN.jQuery("html").hasClass('ie8')) {
                WDN.jQuery("#visitorChat_footercontainer").show();
            }

            this.initWatchers();
        }

        //Handle the rest of the data.
        if (data['conversationID'] && this.chatStatus == false) {
            this.startChat(true);
        }

        this.displaySiteAvailability();
    },

    onConversationStatus_Captcha:function (data) {
        if (this.method == 'email') {
            this.launchChatContainer();
        }

        this.updateChatContainerWithHTML("#visitorChat_container", data['html']);
    },

    onConversationStatus_OperatorLookupFailed:function (data) {
        clearTimeout(VisitorChat.loopID);
        var html = '<div class="chat_notify">Unfortunately all of our operators are currently busy.  Would you like to send an email instead?' +
            '<div id="visitorChat_failedOptions"><a id="visitorChat_failedOptions_yes" href="#">Yes</a> <a id="visitorChat_failedOptions_no" href="#">No</a></div></div>';
        this.updateChatContainerWithHTML("#visitorChat_container", html);
    },

    updatePHPSESSID:function (phpsessid) {
        this.phpsessid = phpsessid;

        //set the cookie (IE ONLY).
        if (navigator.userAgent.indexOf("MSIE") !== -1) {
            WDN.jQuery.cookies.set('UNL_Visitorchat_Session', phpsessid, {domain:'.unl.edu'});
        }
    },

    loadStyles:function () {
        //load styling.
        if (document.createStyleSheet) {
            document.createStyleSheet(this.serverURL + "css/remote.php");
        } else {
            WDN.jQuery("head").append(WDN.jQuery("<link rel='stylesheet' href='" + this.serverURL + "assets/css?for=client&version=" + this.version + "' type='text/css' media='screen' />"));
        }

        WDN.jQuery(window).load(function () {
            VisitorChat.displaySiteAvailability();
        });

        this._super();
    },

    init:function (serverURL, refreshRate) {
        WDN.jQuery("body").append("" +
            "<div id='visitorChat'>" +
                "<div id='visitorChat_header'>" +
                    "<span style='display:none;' id='visitorChat_header_text'>Live Chat</span>" +
                    "<div id='visitorChat_logout'>" +
                        "<a href='#'>close</a>" +
                    "</div>" +
                "</div>" +
                "<div id='visitorChat_sound_container'>" +
                    "<audio id='visitorChat_sound'></audio>" +
                "</div>" +
            "</div>");

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

        WDN.jQuery.xhrPool.abortAll();

        if (this.method != 'email') {
            callbackSet = false;
            if (WDN.jQuery('#visitorChat_container').is(":visible")) {
                callbackSet = true;
                WDN.jQuery("#visitorChat_container").slideUp(400, WDN.jQuery.proxy(function () {
                    if (callback) {
                        callback();
                    }
                }, this));
            }
        }

        this._super();

        if (this.method != 'email') {
            this.closeChatContainer();
        }

        if (WDN.jQuery.cookies.get('UNL_Visitorchat_Start')) {
            date = new Date();
            date = Math.round(date.getTime() / 1000);
            difference = date - WDN.jQuery.cookies.get('UNL_Visitorchat_Start');

            WDN.analytics.callTrackEvent('WDN Chat', 'Ended', undefined, difference);
        }

        //Delete the current cookie.
        VisitorChat.deleteAnalyticsCookies();

        this.initWatchers();

        if (callback && !callbackSet) {
            callback();
        }
    },

    deleteAnalyticsCookies: function()
    {
        //Delete the current cookie.
        WDN.jQuery.cookies.del('UNL_Visitorchat_Start', {domain:'.unl.edu'});
        WDN.jQuery.cookies.del('UNL_Visitorchat_Session', {domain:'.unl.edu'});
        WDN.jQuery.cookies.del('UNL_Visitorchat_FirstOperatorResponse', {domain:'.unl.edu'});
    },

    closeChatContainer: function() {
        WDN.jQuery("#visitorChat_logout").css({'display':'none'});
        WDN.jQuery("#visitorChat_header").animate({'width':'105px'}, 200);

        WDN.jQuery("#visitorChat_footercontainer").html("<div id='visitorChat_footercontainer'>" + this.loginHTML + "</div>");
        WDN.jQuery("#visitorChat_footerHeader").css({'display':'block'});

        if (!this.operatorsAvailable) {
            WDN.jQuery('#visitorChat_header').hide();
        }
    },

    displaySiteAvailability:function () {
        if (this.chatOpened) {
            WDN.jQuery("#visitorChat").show();
            WDN.jQuery("#visitorChat_header").show();
            WDN.jQuery("#visitorChat_header_text").css('display', 'inline');
            return true;
        }

        if (this.operatorsAvailable) {
            WDN.jQuery("#visitorChat_header").show();
            WDN.jQuery("#visitorChat").show();
            WDN.jQuery("#visitorChat_header_text").css('display', 'inline');
        } else {
            WDN.jQuery("#visitorChat_header").hide();
        }

        return true;
    }
});

WDN.jQuery(function(){
    WDN.loadJS('/wdn/templates_3.1/scripts/plugins/validator/jquery.validator.js', function() {
        if (VisitorChat == false) {
            VisitorChat = new VisitorChat_Client("<?php echo \UNL\VisitorChat\Controller::$url;?>", <?php echo \UNL\VisitorChat\Controller::$refreshRate;?>);
        }
    });
});
