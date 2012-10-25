//TODO:  Simply attach the getUserData function to the end of the loop function.
var VisitorChat_Chat = VisitorChat_ChatBase.extend({
    currentRequest:false, //The current request ID.
    requestTimeout:false, //Life of a in-coming chat request
    requestLoopID:false,
    overlayLoopID:false,
    operatorStatus:false,
    unreadMessages:new Array(), //The total number of messages for all open conversations
    requestExpireDate:new Array(),
    invitationsHTML:false, //Holds a copy of the latest invitations html
    operators:new Array(), //An array of operators currently in the chat
    idleWatchLoopID: false, //the idle watch loop id
    lastActiveTime: new Date(), //The exact date that the operator was last active
    idleWatchLoopTime: 3000, //the frequency of the idle watch loop (defaults to once every 5 secodns)
    idleTimeout: 7200000,  //time of being inactive before going idle (default to 7200000 or 2 hours)
	clientInfo: "",

    initWindow:function () {
        WDN.jQuery("#toggleOperatorStatus").click(function () {
            if (VisitorChat.operatorStatus == 'AVAILABLE') {
                VisitorChat.checkOperatorCountBeforeStatusChange();
            } else {
                VisitorChat.toggleOperatorStatus('USER');
            }
            return false;
        });

        //Flash the overlay to make notifications more visible.
        this.flashOverlay();

        //For status toggle useability
        WDN.jQuery('#toggleOperatorStatus').hover(function () {
            var isOpen = WDN.jQuery(this).hasClass('open');

            if (isOpen) {
                WDN.jQuery(this).children('#currentOperatorStatus').html("Go offline?");
            } else {
                WDN.jQuery(this).children('#currentOperatorStatus').html("Go online?");
            }

        }, function () {
            var isOpen = WDN.jQuery(this).hasClass('open');

            if (isOpen) {
                WDN.jQuery(this).children('#currentOperatorStatus').html("You are available");
            } else {
                WDN.jQuery(this).children('#currentOperatorStatus').html("You are unavailable");
            }
        });

        //Every time the mouse moves, update the last active time
        WDN.jQuery('body').mousemove(function(){
            VisitorChat.lastActiveTime = new Date();
        });

        WDN.jQuery(window).scroll(function(){
            VisitorChat.lastActiveTime = new Date();
        });

        // Initialize Tooltip for stuff!
        WDN.initializePlugin('tooltip');

        VisitorChat.idleWatchLoopID = setTimeout("VisitorChat.idleWatch()", this.idleWatchLoopTime);

        this._super();
    },

    idleWatch: function() {
        //Return early if we are already busy
        if (this.operatorStatus != 'AVAILABLE') {
            VisitorChat.idleWatchLoopID = setTimeout("VisitorChat.idleWatch()", this.idleWatchLoopTime);
            return true;
        }

        currentDate = new Date();

        diff = currentDate.getTime() - this.lastActiveTime.getTime();

        if (diff >= this.idleTimeout && this.operatorStatus == 'AVAILABLE') {
            this.toggleOperatorStatus('CLIENT_IDLE');

            this.alert('idle');

            //Create a new dialog to tell the operator that they missed a chat request.
            WDN.jQuery("#alert").html("Due to inactivity, you have been set to 'busy'.  You are considered inactive if you have not shown any activity for " + (this.idleTimeout/1000)/60 + " minutes.");
            WDN.jQuery("#alert").dialog({
                resizable:false,
                height:180,
                modal:true,
                open: function() {
                    WDN.jQuery('.ui-dialog-buttonpane button:visible:eq(1)').focus();
                },
                buttons:{
                    "Okay":WDN.jQuery.proxy(function () {
                        WDN.jQuery("#alert").dialog("close");
                    }, this),
                    "Go back online":WDN.jQuery.proxy(function () {
                        WDN.jQuery("#alert").dialog("close");
                        this.toggleOperatorStatus('USER');
                    }, this)
                }
            });
        }

        VisitorChat.idleWatchLoopID = setTimeout("VisitorChat.idleWatch()", this.idleWatchLoopTime);
    },

    showBrightBox:function () {
        var mouse_is_inside = false;

        //Navigation needs to be under back-drop
        WDN.jQuery("#wdn_navigation_wrapper").css({'z-index':'1'});

        //Add in the back-drop and show brightBox
        WDN.jQuery("body").append("<div id='visitorChat_backDrop'></div>");
        WDN.jQuery('#visitorChat_brightBox').fadeIn("fast");

        //Track mouse position
        WDN.jQuery('#visitorChat_brightBox').mouseleave(function () {
            mouse_is_inside = false;
        });

        WDN.jQuery('#visitorChat_brightBox').mouseenter(function () {
            mouse_is_inside = true;
        });

        //Click outside container to close
        WDN.jQuery("#visitorChat_backDrop").mouseup(function () {
            if (!mouse_is_inside) {
                WDN.jQuery("#visitorChat_backDrop").remove();
                WDN.jQuery('#visitorChat_brightBox').fadeOut(100);
                WDN.jQuery("#wdn_navigation_wrapper").css({'z-index':'auto'});
            }
        });
    },

    initWatchers:function () {
        //Remove old elvent handlers
        WDN.jQuery('.conversationLink, #closeConversation, #visitorChat_messageBox, #shareConversation, #visitorChat_operatorInvite > li, #clientChat_Invitations, #clientInfo').unbind();
		
		// Hover for Client Info
		WDN.jQuery('#visitorChat_url_title > span').mouseover(function(){
			WDN.jQuery('#clientInfo').fadeIn('fast', function(){
				WDN.jQuery(this).hover(function(){
					WDN.jQuery(this).show();
				}, function(){
					WDN.jQuery(this).fadeOut('fast');
				});
			});
		});

        //Watch coversation link clicks.  Loads up the conversation all ajaxy
        WDN.jQuery('.conversationLink').click(function () {
            //Empty out the current chat.
            VisitorChat.clearChat();

            //reset the chat status.
            VisitorChat.chatStatus = false;

            //Load the chat.
            VisitorChat.updateChat(this);
			
			//Add selected class for active client
			var isSelected = WDN.jQuery(this).parent().hasClass('selected');
		
			if (!isSelected) {
		
				var prevSelected = WDN.jQuery('#clientList').find('.selected');
				var nowSelected = WDN.jQuery(this).parent();
				var clientName = WDN.jQuery(this).children('span').text();
				
				// Add transitions to newly selected, take out from old.
				prevSelected.children('a').removeClass('transition');
				WDN.jQuery(this).addClass('transition');
		
				// Slide <span> back
				prevSelected.children().children('span').animate({
					paddingLeft: "5px"
				}, 250);
		
				// Find selected, remove class and transition
				prevSelected.removeClass('selected');
		
				// Slide out new client
				nowSelected.children().children('span').animate({
					paddingLeft: "20px"
				}, 250);
		
				// Add 'selected' class
				nowSelected.addClass('selected');
			}
			

            return false;
        });
		
	

        WDN.jQuery('#closeConversation').click(function () {
            if (confirm("Are you sure you want to end the conversation?")) {
                VisitorChat.changeConversationStatus("CLOSED");
            }
        });

        WDN.jQuery('#shareConversation').click(function () {
            VisitorChat.openShareWindow();
        });

        WDN.jQuery('#leaveConversation').click(function () {
            if (confirm("Are you sure you want to leave the conversation?")) {
                VisitorChat.leaveConversation();
            }
        });

        if (WDN.jQuery().qtip !== undefined) {
            var elems = WDN.jQuery('#visitorChat_InvitationList .tooltip[title]');

            WDN.tooltip.addTooltip(elems);

            elems.trigger('mouseover');
        }


        this._super();
    },

    clearChat:function () {
        WDN.jQuery('#clientChat').empty();
        WDN.jQuery('#clientChat_Invitations').empty();
        this.invitationsHTML = "";
        this.latestMessageId = 0;
    },

    init:function (serverURL, refreshRate, requestTimeout) {
        //set vars
        this.serverURL = serverURL;
        this.refreshRate = refreshRate;
        this.requestTimeout = requestTimeout;

        this.loadStyles();
        this.initWindow();
        this.initWatchers();

        if (window.webkitNotifications && window.webkitNotifications.checkPermission()) {
            WDN.jQuery('#notificationOptions').show();
        }

        WDN.jQuery('#testNotifications').click(function () {
            VisitorChat.alert('test', true);
        });

        //Request permission for notifications.
        WDN.jQuery('#requestNotifications').click(function () {
            if (!window.webkitNotifications) {
                return false;
            }

            window.webkitNotifications.requestPermission(function () {
                if (!window.webkitNotifications.checkPermission()) {
                    WDN.jQuery('#notificationOptions').hide();
                }
            });
            return false
        });
    },

    run:function () {
        this.updateUserInfo();
        this._super();
    },

    start:function () {
        //load the conversation list.
        this._super();
    },

    openShareWindow:function () {
        //Update the Client List
        WDN.jQuery.ajax({
            url:this.serverURL + "conversation/" + this.conversationID + "/share?format=partial",
            xhrFields:{
                withCredentials:true
            },
            statusCode: {
                //Did our session expire?
                401: function() {
                    window.location.reload(); //reload the page
                }
            },
            success:WDN.jQuery.proxy(function (data) {

                WDN.jQuery("#visitorChat_brightBox").html(data);
                this.showBrightBox();
                this.loadShareWatchers();
                //start a new dialog box.
            }, this),
        });

        WDN.jQuery('#visitorChat_brightBox').height('350px');
    },

    loadShareWatchers:function () {
        WDN.jQuery(".chzn-select").chosen({no_results_text: "No results matched", max_selected_options: 5});

        WDN.jQuery("#shareForm").submit(function() {
            VisitorChat.confirmShare();
            return false;
        });
    },

    confirmShare:function () {
        var to = WDN.jQuery('#share_to').val();
        var toHTML = WDN.jQuery('option[value="'+to+'"]').text();

        if (to == 'default') {
            alert('Please select person or a team');
            return false;
        }

        //Clean to as it may contain lots of whitepsace
        toHTML = WDN.jQuery.trim(toHTML);

        var method = WDN.jQuery('input[name=method]:checked', '#shareForm').val();
        var methodHTML = method;

        if (confirm('Are sure you want to ' + methodHTML + ' ' + toHTML + '?')) {
            this.share(method, to);
        }
    },

    share:function (method, to) {
        WDN.jQuery.ajax({
            type:"POST",
            url:this.serverURL + "conversation/" + this.conversationID + "/share?format=json",
            statusCode: {
                //Did our session expire?
                401: function() {
                    window.location.reload(); //reload the page
                }
            },
            data:"method=" + method + "&to=" + to
        }).error(function (msg) {
                alert('There was an error sharing, please try back later.');
            });
    },

    leaveConversation:function () {
        WDN.jQuery.ajax({
            type:"POST",
            url:this.serverURL + "conversation/" + this.conversationID + "/leave?format=json",
            statusCode: {
                //Did our session expire?
                401: function() {
                    window.location.reload(); //reload the page
                }
            },
            data:"confirm=1"
        }).error(function (msg) {
                alert('There was an error leaving, please try back later.');
            });
    },

    updateConversationListWithUnreadMessages:function () {
        //Do we need to display a notice?
        for (conversation in this.unreadMessages) {
            var html = "";
            if (this.unreadMessages[conversation]) {
                html = this.unreadMessages[conversation];
            }
            
			// Don't display if '0' unread messages
			if (html === '0' || html === '') {
				WDN.jQuery("#visitorChat_UnreadMessages_" + conversation).hide();
			} else {
				WDN.jQuery("#visitorChat_UnreadMessages_" + conversation).html(html);
			}
        }
    },

    updateUnreadMessages:function (newTotals) {
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

    updateUserInfo:function () {
        if (this.operatorStatus == 'BUSY') {
            return false;
        }

        this._super();
    },

    handleUserDataResponse:function (data) {
        //Were we logged out?
        if (!data['userID']) {
            window.location.reload(); //reload the page
        }

        //Alert the user if the server set them to busy.
        if (data['userStatus'] != this.operatorStatus && data['userStatusReason'] == 'SERVER_IDLE') {
            this.alert('idle');
            WDN.jQuery("#alert").html("Due to inactivity with the server, you have been set to 'busy'.  This usually happens when you forget to change your status to 'unavailable' before you close the browser or after your computer has lost connection with the server.");
            WDN.jQuery("#alert").dialog({
                resizable:false,
                height:180,
                modal:true,
                open: function() {
                    WDN.jQuery('.ui-dialog-buttonpane button:visible:eq(1)').focus();
                },
                buttons:{
                    "Okay":WDN.jQuery.proxy(function () {
                        WDN.jQuery("#alert").dialog("close");
                    }, this),
                    "Go back online":WDN.jQuery.proxy(function () {
                        WDN.jQuery("#alert").dialog("close");
                        this.toggleOperatorStatus('USER');
                    }, this)
                }
            });
        }

        this.updateOperatorStatus(data['userStatus']);

        this._super(data);

        //Alert if there are new and unread messages.
        this.updateUnreadMessages(data['unreadMessages']);

        this.totalMessages = data['totalMessages'];

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
                resizable:false,
                height:140,
                modal:true,
                open: function() {
                    WDN.jQuery('.ui-dialog-buttonpane button:visible:eq(1)').focus();
                },
                buttons:{
                    "Reject":WDN.jQuery.proxy(function () {
                        WDN.jQuery("#chatRequest").dialog("close");
                        this.sendChatRequestResponse(this.currentRequest, 'REJECTED');
                        this.clearAlert();
                        clearTimeout(VisitorChat.requestLoopID);
                    }, this),
                    "Accept":WDN.jQuery.proxy(function () {
                        WDN.jQuery("#chatRequest").dialog("close");
                        this.sendChatRequestResponse(this.currentRequest, 'ACCEPTED');
                        clearTimeout(VisitorChat.requestLoopID);
                        this.clearAlert();
                    }, this)
                }
            });

            this.currentRequest = data['pendingAssignment'];
            this.startRequestLoop(data['pendingAssignment'], data['pendingDate'], data['serverTime']);
        }
    },

    flashOverlay: function(color) {
        if (color == undefined) {
            color = '#aaa';
        } else if (color == '#aaa') {
            color = '#C40302';
        } else {
            color = '#aaa';
        }

        //switch to a new color
        WDN.jQuery(".ui-widget-overlay").css('background', color);
        WDN.jQuery(".ui-widget-overlay").css('opacity', .5);

        //Google chrome has issues with clearing the timeout.  Work around it...
        if (VisitorChat.overlayLoopID == -1) {
            return;
        }

        VisitorChat.overlayLoopID = setTimeout("VisitorChat.flashOverlay('" + color +"')", 1000);
    },

    stopFlashingOverlay: function() {
        clearTimeout(VisitorChat.overlayLoopID);
        VisitorChat.overlayLoopID = -1;
    },

    generateChatURL:function () {
        var conversation = ""
        if (this.conversationID) {
            conversation = "&conversation_id=" + this.conversationID;
        }
        return this.serverURL + "conversation?format=json&last=" + this.latestMessageId + conversation + "&PHPSESSID=" + this.phpsessid;
    },

    sendChatRequestResponse:function (id, response) {
        WDN.jQuery.ajax({
            type:"POST",
            url:this.serverURL + "assignment/" + id + "/edit?format=json&PHPSESSID=" + this.phpsessid,
            statusCode: {
                //Did our session expire?
                401: function() {
                    window.location.reload(); //reload the page
                }
            },
            data:"status=" + response
        }).done(function (msg) {
                if (response == "REJECTED") {
                    return true;
                }
            });
    },

    startRequestLoop:function (id, startDate, serverTime) {
        startDate = Date.parse(new Date(Date.parse(startDate)).toUTCString());
        serverTime = Date.parse(new Date(Date.parse(serverTime)).toUTCString());
        currentDate = Date.parse(new Date().toUTCString());

        var offset = currentDate - serverTime;
        var startDate = startDate + offset;

        this.requestExpireDate[id] = new Date(startDate + this.requestTimeout);

        this.requestLoop(id);
    },

    requestLoop:function (id) {
        currentDate = new Date();
        difference = Math.round((VisitorChat.requestExpireDate[id] - currentDate.getTime()) / 1000);
        WDN.jQuery("#chatRequestCountDown").html(difference);

        if (currentDate.getTime() >= VisitorChat.requestExpireDate[id]) {
            this.onRequestExpired();
            return false;
        }

        VisitorChat.requestLoopID = setTimeout("VisitorChat.requestLoop(" + id + ")", 1000);
    },

    /**
     * Called when a pending chat request expires.
     *
     * Tell the user that they missed a chat request and they have been set to 'busy'.
     */
    onRequestExpired:function () {
        //Close the current request dialog.
        WDN.jQuery("#chatRequest").dialog("close"); //Remove the dialog box.
        clearTimeout(VisitorChat.requestLoopID); //Clear the timeout.
        this.clearAlert();

        //Create a new dialog to tell the operator that they missed a chat request.
        WDN.jQuery("#alert").html("You have missed an assignment.  In order to provide the best response times to the clients, you have been set to 'busy'.");
        WDN.jQuery("#alert").dialog({
            resizable:false,
            height:180,
            modal:true,
            open: function() {
                WDN.jQuery('.ui-dialog-buttonpane button:visible:eq(1)').focus();
            },
            buttons:{
                "Okay":WDN.jQuery.proxy(function () {
                    WDN.jQuery("#alert").dialog("close");
                }, this),
                "Go back online":WDN.jQuery.proxy(function () {
                    WDN.jQuery("#alert").dialog("close");
                    this.toggleOperatorStatus('USER');
                }, this)
            }
        });
    },

    updateChat:function (url) {
        if (this.conversationID == false && url == undefined) {
            return false;
        }

        if (this.chatStatus == false) {
            url = url + "&clientInfo=true";
        }

        this._super(url);
    },

    updateChatWithData:function (data) {
        if (data['invitations_html'] !== undefined && data['invitations_html']) {
            this.updateInvitationsListWithHTML(data['invitations_html']);
        }

        if (data['client_html'] !== undefined && data['client_html']) {
			//alert('here');
			this.clientInfo = data['client_html'];
            //WDN.jQuery('#clientInfo').html(data['client_html']);
        }

        if (data['operators'] !== undefined) {
            this.operators = new Array();

            for (operator in data['operators']) {
                this.operators.push(data['operators'][operator]);
            }
            if (this.operators.length > 1) {
                WDN.jQuery('#leaveConversation').show();
            } else {
                WDN.jQuery('#leaveConversation').hide();
            }
        }

        return this._super(data);
    },

    updateInvitationsListWithHTML:function (html) {
        if (this.invitationsHTML != html) {
            this.invitationsHTML = html;
            WDN.jQuery("#clientChat_Invitations").html(html);
        }
    },

    onConversationStatus_Chatting:function (data) {
        if (this.latestMessageId == 0) {
            if (data['html'] == undefined) {
                return false;
            }

            this.updateChatContainerWithHTML("#clientChat", data['html']);
        }

        if (data['messages'] == undefined) {
            return true;
        }

        this.appendMessages(data['messages']);
    },

    onConversationStatus_Closed:function (data) {
        //Disable the input message input.
        WDN.jQuery("#visitorChat_messageBox").attr("disabled", "disabled");

        //Don't let the operator share or close (because it is already closed).
        WDN.jQuery("#shareConversation").remove();
        WDN.jQuery("#closeConversation").remove();

        //Display a closed message.
        var html = "<div class='chat_notify' id='visitorChat_closed'>This conversation has been closed.</div>";
        html = WDN.jQuery("#clientChat").prepend(html);
        //this.updateChatContainerWithHTML("#clientChat", html);

        //set the opacity of all siblings
        WDN.jQuery('#visitorChat_closed').siblings().css({'opacity':'0.1'})
        //set the opacity of current item to full, and add the effect class
        WDN.jQuery('#visitorChat_closed').css({'opacity':'1.0'});

        if (data['messages'] == undefined) {
            return true;
        }

        this.appendMessages(data['messages']);
    },

    updateConversationList:function () {
        //Update the Client List
        WDN.jQuery.ajax({
            url:this.serverURL + "conversations?format=partial",
            xhrFields:{
                withCredentials:true
            },
            statusCode: {
                //Did our session expire?
                401: function() {
                    window.location.reload(); //reload the page
                }
            },
            success:WDN.jQuery.proxy(function (data) {
                WDN.jQuery("#clientList").html(data);
				WDN.jQuery("#conversationId_" + this.conversationID).addClass('selected');
				WDN.jQuery("#conversationId_" + this.conversationID).children().children('span').css({
					paddingLeft: "20px"
				});
                this.initWatchers();
            }, this)
        });
    },

    checkOperatorCountBeforeStatusChange:function () {
        WDN.jQuery.ajax({
            type:"GET",
            url:this.serverURL + "user/sites?format=json",
            statusCode: {
                //Did our session expire?
                401: function() {
                    window.location.reload(); //reload the page
                }
            },
            success:WDN.jQuery.proxy(function (data) {
                var offline = new Array();

                i = 0;
                for (url in data) {
                    if ((data[url]['total_available'] - 1) < 1) {
                        offline[i] = data[url]['title'];
                    }

                    i++;
                }

                if (offline.length > 0) {
                    this.displayStatusChangeAlert(offline);
                } else {
                    this.toggleOperatorStatus('USER');
                }

            }, this),
            error:WDN.jQuery.proxy(function (data) {
                this.toggleOperatorStatus('CLIENT_IDLE');
            }, this)
        });
    },

    displayStatusChangeAlert:function (offline) {
        var html = "You are the last person online for the following sites.  If you go offline now, these sites will have chat functionality turned off. <ul id='visitorChat_sitesWarning'>";

        for (site in offline) {
            html += "<li>" + offline[site] + "</li>";
        }

        html += "</ul>";

        WDN.jQuery("#alert").html(html);

        //start a new dialog box.
        WDN.jQuery("#alert").dialog({
            resizable:false,
            modal:true,
            open: function() {
                WDN.jQuery('.ui-dialog-buttonpane button:visible:eq(1)').focus();
            },
            buttons:{
                "Go Offline Anyway":WDN.jQuery.proxy(function () {
                    WDN.jQuery("#alert").dialog("close");
                    this.toggleOperatorStatus('USER');
                }, this),
                "Nevermind":WDN.jQuery.proxy(function () {
                    WDN.jQuery("#alert").dialog("close");
                }, this),
            }
        });
    },

    toggleOperatorStatus:function (reason) {
        var status = "BUSY";

        if (this.operatorStatus == "BUSY") {
            status = "AVAILABLE";
        }

        if (!this.userID) {
            return false;
        }

        WDN.jQuery.ajax({
            type:"POST",
            url:this.serverURL + "users/" + this.userID + "/edit?format=json",
            data:"status=" + status + "&reason=" + reason,
            statusCode: {
                //Did our session expire?
                401: function() {
                    window.location.reload(); //reload the page
                }
            },
            success:WDN.jQuery.proxy(function (data) {
                this.updateOperatorStatus(status);
            }, this)
        });
    },

    updateOperatorStatus:function (newStatus) {
        var formatStatus = 'Available';

        $flag = WDN.jQuery("#toggleOperatorStatus").hasClass("closed");

        if (newStatus == 'BUSY') {
            formatStatus = 'You are unavailable';
        }

        if (newStatus == 'BUSY') {
            WDN.jQuery("#toggleOperatorStatus").addClass("closed");
            WDN.jQuery("#toggleOperatorStatus").removeClass("open");
        } else {
            WDN.jQuery("#toggleOperatorStatus").addClass("open");
            WDN.jQuery("#toggleOperatorStatus").removeClass("closed");
            formatStatus = 'You are available';
        }

        //Don't call this if its the same status
        if (newStatus !== this.operatorStatus) {
            WDN.jQuery("#currentOperatorStatus").html(formatStatus);
        }

        this.operatorStatus = newStatus;
    }
});
