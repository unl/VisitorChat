require(['jquery', 'idm', 'analytics'], function($, idm, analytics) {
    <?php
    require_once(__DIR__ . "/ChatBase.js.php");
    ?>
    
    var VisitorChat_Client = VisitorChat_ChatBase.extend({
        loginHTML: false,
        clientName: false,
        initialMessage: false,
        name:'',
        email:'',
        confirmationHTML: false,
        userType: 'client',
        method: 'chat',
    
        startEmail:function () {
            this.method = 'email';
            this.displaySiteAvailability(true);
            this.launchEmailContainer();
            this.start();
            $("#visitorChat_messageBox").attr('placeholder', 'We will get back to you as soon as possible.');
            //Submit as email
            $("#visitorChat_login_chatmethod").val("EMAIL");
        },
    
        startChat:function (chatInProgress) {
            this.method = 'chat';
            this.displaySiteAvailability(true);
            this.launchChatContainer();

            if (chatInProgress && this.chatStatus == "LOGIN") {
                this.chatStatus = "CHATTING";
                return this.start();
            }

            $("#visitorChat_container #visitorChat_email_fallback_text").html('If no operators are available,&nbsp;I would like to receive an email.');
            
            this.start();

            $("#visitorChat_messageBox").attr("placeholder", "How can we assist you?");
            //Submit as chat
            $("#visitorChat_login_chatmethod").val("CHAT");
        },
        
        start:function () {
            $("#visitorChat_header").animate({'width':'214px'}, 200);
            
            if (this.blocked) {
                var html = "Your IP address has been blocked.  If you feel that this is an error, please contact operator@unl.edu";
                this.updateChatContainerWithHTML("#visitorChat_container", html, false);
                return;
            }
            
            //Always show the chat if we are logged in as an operator.  Otherwise only show if someone is available.
            if (this.userType == 'operator') {
                html = "<div class='chat_notify'>You are currently logged in as an operator and can not start a client conversation from this browser.  If you want to start a conversation, please either log out or do so in another web browser.</div>";
                this.updateChatContainerWithHTML("#visitorChat_container", html, false);
            } else {
                this.updateChatContainerWithHTML("#visitorChat_container", this.loginHTML, false);
            }

            WDN.jQuery("#visitorChat_footerHeader").css({'display':'none'});

            //Due to IE, make sure that we clear the value of the input if it equals the placeholder value
            if ($("#visitorChat_messageBox").val() == $("#visitorChat_messageBox").attr("placeholder")) {
                $("#visitorChat_messageBox").val('');
            }
            
            this._super();
        },
    
        onOperatorMessage:function (message) {
            //Fire an analytics event on first response.  set cookie for cross domain.
            if (!WDN.getCookie('UNL_Visitorchat_FirstOperatorResponse') && WDN.getCookie('UNL_Visitorchat_Start')) {
                start = WDN.getCookie('UNL_Visitorchat_Start');
                date = new Date();
                date = Math.round(date.getTime() / 1000);
                difference = date - start;
    
                analytics.callTrackEvent('WDN Chat', 'Response', 'Received', difference);
    
                //Set a cookie so that we don't call this if we have to reload the chat (page refresh or move to another page).
                WDN.setCookie('UNL_Visitorchat_FirstOperatorResponse', difference, null, '/');
            }
        },
    
        launchEmailContainer:function() {
            this.chatStatus = "LOGIN";
    
            //Remove an old one if it is there.
            $('#visitorChat_container').remove();
    
            //set up a container.
            $("#visitorChat").append(
                "<div id='visitorChat_container'>" +
                "<div class='chat_notify visitorChat_loading'>Initializing, please wait.</div>" +
                "</div>"
            );
    
            //set up a container.
            var html = "<div id='visitorChat_container'>Please Wait...</div>";
    
            $("#visitorchat_clientLogin").replaceWith("<div id='visitorChat_container'></div>");
    
            $("#visitorChat_container").show();
        },
    
        launchChatContainer:function () {
            //Remove an old one if it is there.
            $('#visitorChat_container').remove();
    
            //set up a container.
            $("#visitorChat").append(
                "<div id='visitorChat_container'>" +
                    "<div class='chat_notify visitorChat_loading'>Initializing, please wait.</div>" +
                    "</div>"
            );
    
            //Set header_text to visible
            $("#visitorChat_header_text").css('display', 'inline');
    
            $("#visitorChat_header").show();
    
            this.chatStatus = "LOGIN";
    
            $("#visitorchat_clientLogin").parent().html("Disabled");
    
            //Display and set the name (if found).
            $("#visitorChat_container").delay(10).slideDown(320, function() {
                if (idm.getDisplayName()) {
                    $("#visitorChat_name").val(idm.getDisplayName());
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
            var email = $('#visitorChat_email').val();
    
            //If the email is empty, don't submit and append a warning to the form, otherwise continue on.
            if (email != '') {
                return true;
            }
    
            //Check if they are confirming anon...
            if ($('#visitorChat_login_submit').val() == 'Yes, I do not need a response') {
                //Reset to say 'submit'.
                $('#visitorChat_login_submit').val("Submit");
                return true;
            }
    
            //Display error and request confirmation before continuing.
            var html = "<div id='visitorchat_clientLogin_anonwaning'>Since you didn't enter an email, we won't be able to respond. Is this OK?</div>";
    
            $('#visitorChat_login_submit').before(html);
            $('#visitorChat_login_submit').val("Yes, I do not need a response");
    
            this.initWatchers();
    
            //remove the warning if they start to enter an email
            $("#visitorChat_email").keyup(function () {
                $('#visitorchat_clientLogin_anonwaning').remove();
                $('#visitorChat_login_submit').val("Submit");
            });
    
            return false;
        },
    
        ajaxBeforeSubmit:function (arr, $form, options) {
            //Start an email convo now if need be.
            for (var key = 0; key<arr.length; key++) {
                if (arr[key]['name'] == 'method' && arr[key]['value'] == 'EMAIL') {
                    if (this.confirmAnonSubmit()) {
                        //this.startEmail();
                    } else {
                        return false;
                    }
                }
            }
    
            return this._super(arr, $form, options);
        },
    
        initValidation: function() {
            $.validation.addMethod('validate-require-if-question',
                'An email address is required if you ask a question so that we can respond.',
                function(value, object) {
                    var message = $('#visitorChat_messageBox').val();
                    if (message.indexOf("?") != -1 && value == "") {
                        return false;
                    }
    
                    return true;
                });
    
            //Remove the vaildation binding so that validation does not stack and is always called before ajax submit.
            $('#visitorchat_clientLogin').data('validation', false);
            $('#visitorChat_confirmationEamilForm').data('validation', false);
    
            //Require email for questions submitted via the footer comment form.
            $('#visitorChat_footercontainer #visitorChat_email').addClass('validate-require-if-question');
    
            //Validator
            $('#visitorchat_clientLogin, #visitorChat_confirmationEamilForm').validation();
        },
    
        initPlaceHolders: function() {
            //Load placeholders if not supported.
            if (WDN.hasDocumentClass('no-placeholder')) {
                WDN.loadJS(WDN.getTemplateFilePath('scripts/plugins/placeholder/jquery.placeholder.min.js'), function() {
                    $('#visitorChat_footercontainer, #visitorChat').find('[placeholder]').placeholder();
                });
            }
        },
    
        initWatchers:function () {
            /* This method is called several times thoughout
             * executation.  Thus in order to stop the stacking
             * of watch functions, we should always unbind previous
             * watch functions before applying the new ones.
             */
            $('#visitorChat_container, ' +
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
                '#visitorChat_confirmationEamilForm').unbind();
    
            this.initPlaceHolders();
    
            this.initValidation();
    
            //Reveal timestamp
            $("#visitorChat_chatBox > ul > li").hover(
                function () {
                    $(this).children(".timestamp").animate({'opacity':'1'}, 120);
                    $(this).children(".stamp").animate({'opacity':'1'}, 120);
                }, function () {
                    $(this).children(".timestamp").animate({'opacity':'0'}, 120);
                    $(this).children(".stamp").animate({'opacity':'0.65'}, 120);
                }
            );
    
            $('#visitorchat_clientLogin').bind('validate-form', function (event, result) {
                if (!result) {
                    VisitorChat.initPlaceHolders();
                }
            });
    
            $('#visitorChat_footercontainer #visitorchat_clientLogin').bind('validate-form', function (event, result) {
                $('#visitorchat_clientLogin_anonwaning').remove();
    
                if ($('#visitorChat_footercontainer #visitorChat_login_submit').val() == 'Yes, no response needed'
                    && $('#visitorChat_email').val() != '') {
                    $('#visitorChat_footercontainer #visitorChat_login_submit').val("Submit");
                }
    
                return true;
            });
    
            $('#visitorChat_confirmationEamilForm').bind('validate-form', function (event, result) {
                if (result) {
                    $('#visitorChat_confirmationContainer').html("The Email transcript has been sent to " + $('#visitorChat_confiramtionEmail').val() + " <br /> <a href='#' id='visitorChat_sendAnotherConfirmation'>Send another one</a>.");
    
                    $().unbind('#visitorChat_sendAnotherConfirmation');
    
                    $('#visitorChat_sendAnotherConfirmation').click(function(){
                        $('#visitorChat_confirmationContainer').html($(VisitorChat.confirmationHTML).filter('#visitorChat_confirmationContainer').html());
                        VisitorChat.initWatchers();
                        return false;
                    });
                }
    
                return false;
            });
    
            //Call the parent
            this._super();
    
            //Click header to open up Chat
            $('#visitorChat_header').click(function () {
                if (!$('#visitorChat_container').is(":visible")) {
                    $("#visitorChat_container").slideDown(320);
                } else {
                    $("#visitorChat_container").slideUp(320);
    
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
                    if (VisitorChat.method == 'chat') {
                        VisitorChat.startChat();
                    } else {
                        VisitorChat.startEmail();
                    }
                    
                }
    
                return false;
            });
    
            //Logout function
            $('#visitorChat_logout').click($.proxy(function () {
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
                $("#visitorChat_email_fallback").click(function () {
                    if ($(this).is(":checked")) {
                        $("#visitorChat_email").attr("placeholder", "Email (Required)");
                        $('#visitorChat_email').addClass('required-entry');
                    } else {
                        $("#visitorChat_email").attr("placeholder", "Email (Optional)");
                        $('#visitorChat_email').removeClass('required-entry');
                    }
                });
            }
    
            //This will slide down the Name and Email fields, plus the Ask button
            $("#visitorChat_messageBox").one("keyup", function () {
                if (idm.getDisplayName()) {
                    $("#visitorChat_name").val(idm.getDisplayName());
                }
                if (idm.getEmailAddress()) {
                    $("#visitorChat_email").val(idm.getEmailAddress());
                }
    
                $(".visitorChat_info, #visitorChat_login_submit").slideDown("fast", function(){
                    if (VisitorChat.initialMessage && !$("#visitorChat_messageBox").is(":focus")) {
                        $("#visitorChat_email").focus();
                    }
                });
            });
    
            $("#visitorChat_failedOptions_yes").click(function() {
                VisitorChat.startEmail();
                if (VisitorChat.initialMessage) {
                    $("#visitorChat_messageBox").val(VisitorChat.initialMessage);
                }

                $("#visitorChat_name").val(VisitorChat.name);
                $("#visitorChat_email").val(VisitorChat.email);
                
                $("#visitorChat_email").focus();
                $("#visitorChat_messageBox").keyup();
                
                return true;
            });
    
            $('#visitorChat_confirmationEmail').keypress(function (e) {
                if (e.which == 13) {
                    e.preventDefault();
    
                    $('#visitorChat_confirmationEamilForm').submit();
                }
            });
    
            $("#visitorChat_failedOptions_no").click(function() {
                VisitorChat.stop();
    
                return true;
            });
    
            if (this.chatStatus) {
                $("#visitorChat_header").hover(function () {
                    $("#visitorChat_logout").css({'display':'inline-block'});
                }, function () {
                    $("#visitorChat_logout").css({'display':'none'});
                });
            }
    
            //set the for_url
            $('#initial_url').val(document.URL);
            $('#initial_pagetitle').val($(document).attr('title'));
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
            this.clientName = $("#visitorChat_name").val();
            this.initialMessage = $("#visitorChat_messageBox").val();
            this.name = $("#visitorChat_name").val();
            this.email = $("#visitorChat_email").val();
    
            this._super();
    
            //Record a start event cookie (for analytics)
            VisitorChat.deleteAnalyticsCookies();
            
            //Set a cookie.
            date = new Date();
            WDN.setCookie('UNL_Visitorchat_Start', (Math.round(date.getTime() / 1000)), null, '/');
    
            //Send analytics data
            _gaq.push(['wdn._setCustomVar',
                1,
                'WDN Chat',
                'Yes',
                2
            ]);
    
            //Mark as started
            analytics.callTrackEvent('WDN Chat', 'Started');
        },
    
        onConversationStatus_Closed:function (data) {
            if ($("#visitorChat_confirmationContainer").length != 0) {
                return false;
            }
    
            this._super(data);
    
            this.confirmationHTML = data['confirmationHTML'];
    
            $("#visitorChat_chatBox").height("150px");
            
            $("#visitorChat_messageForm").remove();
    
            $("#visitorChat_closed").append(data['confirmationHTML'])
    
            this.initWatchers();
    
            $().unbind('visitorChat_header');
    
            this.scroll();
        },
    
        onConversationStatus_Chatting:function (data) {
            this._super(data);
    
            //Minimize header function while chatting
            $('#visitorChat_header').click(function () {
                if ($('#visitorChat_container').css('display') === 'none') {
                    $("#visitorChat_header").animate({'width':'176px'}, 280);
                } else {
                    $("#visitorChat_header").animate({'width':'214px'}, 280);
                }
            });
    
            $().unbind('visitorChat_header');
        },
    
        handleUserDataResponse:function (data) {
            this.conversationID = data['conversationID'];
    
            this.userType = data['userType'];
    
            //Call the parent logic.
            this._super(data);
    
            if (data['loginHTML'] !== undefined && data['loginHTML']) {
                this.loginHTML = data['loginHTML'];
    
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
            VisitorChat.operatorsAvailable = false;
            var html = '<div class="chat_notify">Unfortunately all of our operators are currently busy.  Would you like to send an email instead?' +
                '<div id="visitorChat_failedOptions"><a id="visitorChat_failedOptions_yes" href="#">Yes</a> <a id="visitorChat_failedOptions_no" href="#">No</a></div></div>';
            this.updateChatContainerWithHTML("#visitorChat_container", html);
        },
    
        updatePHPSESSID:function (phpsessid) {
            this.phpsessid = phpsessid;
    
            //set the cookie (IE ONLY).
            if (navigator.userAgent.indexOf("MSIE") !== -1) {
                WDN.setCookie('UNL_Visitorchat_Session', phpsessid, null, '/');
            }
        },
    
        loadStyles:function () {
            var stylesheet = this.serverURL + "assets/css?for=client&v=" + this.version;
            //load styling.
            if (document.createStyleSheet) {
                document.createStyleSheet(stylesheet);
            } else {
                $("head").append($("<link rel='stylesheet' href='" + stylesheet + "' type='text/css' media='screen' />"));
            }
    
            $(window).load(function () {
                VisitorChat.displaySiteAvailability();
            });
    
            this._super();
        },
    
        init:function (serverURL, refreshRate) {
            $("body").append("" +
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
            var phpsessid = WDN.getCookie('UNL_Visitorchat_Session');
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
    
            $.xhrPool.abortAll();
            
            if (this.method != 'email') {
                callbackSet = false;
                if ($('#visitorChat_container').is(":visible")) {
                    callbackSet = true;
                    $("#visitorChat_container").slideUp(400, $.proxy(function () {
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
    
            if (WDN.getCookie('UNL_Visitorchat_Start')) {
                date = new Date();
                date = Math.round(date.getTime() / 1000);
                difference = date - WDN.getCookie('UNL_Visitorchat_Start');
    
                analytics.callTrackEvent('WDN Chat', 'Ended', undefined, difference);
            }
    
            //Delete the current cookie.
            VisitorChat.deleteAnalyticsCookies();
            
            VisitorChat.updateUserInfo();
    
            this.initWatchers();
    
            if (callback && !callbackSet) {
                callback();
            }
        },
        
        deleteAnalyticsCookies: function() {
            //Delete the current cookie.
            WDN.setCookie('UNL_Visitorchat_Start', '0', -1, '/');
            WDN.setCookie('UNL_Visitorchat_Session', '0', -1, '/');
            WDN.setCookie('UNL_Visitorchat_FirstOperatorResponse', '0', -1, '/');
        },
    
        closeChatContainer: function() {
            $("#visitorChat_logout").css({'display':'none'});
            $("#visitorChat_header").animate({'width':'176px'}, 200);
        },
    
        displaySiteAvailability:function (force) {
            if (this.chatOpened && !force) {
                $("#visitorChat").show();
                $("#visitorChat_header").show();
                $("#visitorChat_header_text").css('display', 'inline');
                return true;
            }
    
            $("#visitorChat_header").show();
            $("#visitorChat").show();
            $("#visitorChat_header_text").css('display', 'inline');
            
            if (this.operatorsAvailable) {
                $("#visitorChat_header_text").html('Chat with us');
                $("#visitorChat").addClass('online');
                $("#visitorChat").removeClass('offline');
                VisitorChat.method = 'chat';
            } else {
                $("#visitorChat_header_text").html('Send us a message');
                $("#visitorChat").addClass('offline');
                $("#visitorChat").removeClass('online');
                VisitorChat.method = 'email';
            }
    
            return true;
        }
    });

    $(function(){
        WDN.loadJS('/wdn/templates_3.1/scripts/plugins/validator/jquery.validator.js', function() {
            if (VisitorChat == false) {
                VisitorChat = new VisitorChat_Client("<?php echo \UNL\VisitorChat\Controller::$url;?>", <?php echo \UNL\VisitorChat\Controller::$refreshRate;?>);
            }
        });
    });

});


