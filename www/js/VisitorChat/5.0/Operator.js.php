require(['jquery', 'jqueryui'], function($) {
    <?php
    require_once(__DIR__ . "/ChatBase.js.php");
    require_once(\UNL\VisitorChat\Controller::$applicationDir . "/www/js" . "/chosen.min.js");
    ?>

    //TODO:  Simply attach the getUserData function to the end of the loop function.
    var VisitorChat_Operator = VisitorChat_ChatBase.extend({
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

        // Native fadeOut
        fadeOut: function(el, ms) {
            if (ms) {
              el.style.transition = `opacity ${ms} ms`;
              el.addEventListener(
                'transitionend',
                function(event) {
                  el.style.display = 'none';
                },
                false
              );
            }
            el.style.opacity = '0';
        },
          
        // Native fadeIn
        fadeIn: function(elem, ms) {
            elem.style.opacity = 0;
          
            if (ms) {
              let opacity = 0;
              const timer = setInterval(function() {
                opacity += 50 / ms;
                if (opacity >= 1) {
                  clearInterval(timer);
                  opacity = 1;
                }
                elem.style.opacity = opacity;
              }, 50);
            } else {
              elem.style.opacity = 1;
            }
        },

        initWindow:function () {
            document.querySelector("#toggleOperatorStatus").addEventListener('click' , function () {
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
            document.querySelector("#toggleOperatorStatus").addEventListener('mouseover' ,function () {
                var isOpen = this.classList.contains('open');
                if (isOpen) {
                    $(this).children('#currentOperatorStatus').html("Go offline?");
                } else {
                    $(this).children('#currentOperatorStatus').html("Go online?");
                }

            }, function () {
                var isOpen = this.classList.contains('open');

                if (isOpen) {
                    this.children(document.querySelector("#currentOperatorStatus").innerHTML = "You are available?");
                } else {
                    this.children(document.querySelector("#currentOperatorStatus").innerHTML = "You are unavailable?");
                }
            });

            //Every time the mouse moves, update the last active time
            document.querySelector('body').addEventListener('mousemove' , function(){
                VisitorChat.lastActiveTime = new Date();
            });

            window.addEventListener('scroll', function(){
                VisitorChat.lastActiveTime = new Date();
            });

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

                document.querySelector('#operator-alert-modal-content').innerHTML = '<progress></progress>';
                var helpText = "<p>Due to inactivity, you have been set to 'busy'.  You are considered inactive if you have not shown any activity for " + (this.idleTimeout/1000)/60 + " minutes.</p>";

                helpText += '<ul class="dcf-list-bare dcf-list-inline dcf-p-1 dcf-mt-3 dcf-mb-3">';
                helpText += '<li><button class="dcf-btn dcf-btn-primary" id="operator-alert-okay">Okay</button>';
                helpText += '<li><button class="dcf-btn dcf-btn-secondary" id="operator-alert-go-back-online">Go back online</button>';
                helpText += '</ul>';

                // Trigger click on hidden button to open DCF Modal
                document.querySelector(".operator-alert-modal-toggle-btn").click();
                document.querySelector('#operator-alert-modal-content').innerHTML = helpText;
                document.querySelector('#operator-alert-okay').focus();
                document.querySelector('#operator-alert-go-back-online').addEventListener('click', function() {
                    document.querySelector('#operator-alert-modal-close-btn').click();
                  this.toggleOperatorStatus('USER');
                }.bind(this));
                document.querySelector("#operator-alert-okay").addEventListener('click', function() {
                    document.querySelector('#operator-alert-modal-close-btn').click();
                });
            }

            VisitorChat.idleWatchLoopID = setTimeout("VisitorChat.idleWatch()", this.idleWatchLoopTime);
        },

        showBrightBox:function () {
            var mouse_is_inside = false;

            //Navigation needs to be under back-drop
            document.querySelector("#dcf-navigation").style.zIndex = "1";

            //Add in the back-drop and show brightBox
            document.querySelector("body").insertAdjacentHTML('beforeend', "<div id='visitorChat_backDrop'></div>");
            this.fadeIn(document.querySelector('#visitorChat_brightBox') , 3000);

            //Track mouse position
            document.querySelector('#visitorChat_brightBox').addEventListener('mouseleave' ,function () {
                mouse_is_inside = false;
            });

            document.querySelector('#visitorChat_brightBox').addEventListener('mouseenter' ,function () {
                mouse_is_inside = true;
            });

            //Click outside container to close
            document.querySelector("#visitorChat_backDrop").addEventListener('mouseup' ,function () {
                if (!mouse_is_inside) {
                    document.querySelector("#visitorChat_backDrop").parentNode.removeChild(document.querySelector("#visitorChat_backDrop"));
                    this.fadeOut(document.querySelector('#visitorChat_brightBox') , 100);
                    document.querySelector("#dcf-navigation").style.zIndex = "auto";
                }
            });
        },

        initWatchers:function () {
            //Remove old elvent handlers
            $('.conversationLink, #closeConversation, #block_ip, #visitorChat_messageBox, #shareConversation, #visitorChat_operatorInvite > li, #clientChat_Invitations, #clientInfo, #leaveConversation').unbind();

            //Watch coversation link clicks.  Loads up the conversation all ajaxy
            // The querySelector will crash the operator once clicking onanswering a guest, back to jquery for now ()
            //document.querySelector('.conversationLink').addEventListener('click' , function () {
            $('.conversationLink').click(function () {
                //Empty out the current chat.
                VisitorChat.clearChat();

                //reset the chat status.
                VisitorChat.chatStatus = false;

                //Load the chat.
                VisitorChat.updateChat(this);

                //Add selected class for active client
                var isSelected = this.parentElement.classList.contains('selected');

                if (!isSelected) {
                    //var prevSelected = document.querySelector('#clientList').querySelectorAll('.selected');
                    var prevSelected = $('#clientList').find('.selected');
                    var nowSelected =  $(this).parent();
                    //var nowSelected =  document.querySelector(this).parentNode;
                    // Find selected, remove class and transition
                    prevSelected.removeClass('selected unl-bg-lightest-gray');

                    // Add 'selected' class
                    nowSelected.addClass('selected unl-bg-lightest-gray');
                }

                return false;
            });

            // Doing this will js cause the end conversation to appear multiple time , so reverting back to jquery for now
            //if(this.existInDom('#closeConversation')){
                //document.querySelector('#closeConversation').addEventListener('click' , function () {
                 $('#closeConversation').click(function () {
                         if (confirm("Are you sure you want to end the conversation?")) {
                             VisitorChat.changeConversationStatus("CLOSED");
                         }
                 });
           // }
            

            // I haven't test this
            if(this.existInDom('#shareConversation')){
                document.querySelector('#block_ip').addEventListener('click' ,function () {
                    if (confirm("Are you sure you want to end the conversation and block this IP address?")) {
                        var href= this.href;
                        VisitorChat.changeConversationStatus("CLOSED", function(){
                            window.location = href;
                        });
                        return false;
                    }

                    return false;
                });
            }

            if(this.existInDom('#shareConversation')){
                document.querySelector('#shareConversation').addEventListener('click' ,function () {
                    VisitorChat.openShareWindow();
                });
            }

            if(this.existInDom('#leaveConversation')){
                document.querySelector('#leaveConversation').addEventListener('click' , function() {
                        if (confirm("Are you sure you want to leave the conversation?")) {
                            VisitorChat.leaveConversation();
                        }
                    });
            }

            this._super();
        },

        handleIsTyping:function () {
            if (VisitorChat.isTypingTimeout == false) {
                VisitorChat.sendIsTypingStatus(VisitorChat.assignmentID, 'YES');

                VisitorChat.isTypingTimeout = setTimeout(function(){
                    VisitorChat.isTypingTimeout = false;
                    VisitorChat.sendIsTypingStatus(VisitorChat.assignmentID, 'NO');

                }, 5000);
            }
        },

        sendIsTypingStatus:function(assignmentID, newStatus) {
            var request = new XMLHttpRequest();
            request.open('POST', this.serverURL + "assignment/" + assignmentID + "/edit?format=json", true);
            request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
            request.send("is_typing=" + newStatus);
            },

        clearChat:function () {
            document.querySelector('#clientChat').innerHTML = null;
            document.querySelector('#clientChat_Invitations').innerHTML = null;
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

            if ("Notification" in window) {
                if (Notification && Notification.permission != 'granted') {
                    //WDN.jQuery('#notificationOptions').show();
                    document.querySelector('#notificationOptions').style.display = '';
                }

                WDN.jQuery('#testNotifications').click(function () {
                    VisitorChat.alert('test', true);
                });

                //Request permission for notifications.
                WDN.jQuery('#requestNotifications').click(function () {
                    if (!Notification) {
                        return false;
                    }

                    Notification.requestPermission(function () {
                        if (Notification.permission == 'granted') {
                            WDN.jQuery('#notificationOptions').hide();
                        }
                    });
                    return false
                });
            }
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
            // Add loading to DCF Modal
            document.querySelector("#share-conversation-modal-content").innerHTML = '<progress></progress>';

            // Trigger click on hidden button to open DCF Modal
            document.querySelector(".share-conversation-modal-toggle-btn").click();

            //Update the Client List
            $.ajax({
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
                success: function(data) {
                    // Populate content in DCF modal
                    document.querySelector("#share-conversation-modal-content").innerHTML = data;
                },
                error: function(xhr, status, error) {
                  $("#share-conversation-modal-content").html('<p class="dcf-txt-lg">Error: Loading of share options failed.</p>');
                }
            });
        },

        loadShareWatchers:function () {
            $(".chzn-select").chosen({no_results_text: "No results matched", max_selected_options: 5});

            $("#shareForm").submit(function() {
                VisitorChat.confirmShare();
                return false;
            });
        },

        confirmShare:function () {
            var to = document.querySelector('#share_to').value;
            var toHTML = document.querySelector('option[value="'+to+'"]').textContent;

            if (to == 'default') {
                alert('Please select person or a team');
                return false;
            }

            //Clean to as it may contain lots of whitepsace
            toHTML.trim();

            var method = document.querySelector('input[name=method]:checked', '#shareForm').value;
            var methodHTML = method;

            if (confirm('Are sure you want to ' + methodHTML + ' ' + toHTML + '?')) {
                this.share(method, to);
            }
        },

        share:function (method, to) {
            // var request = new XMLHttpRequest();
            // request.open('POST', this.serverURL + "conversation/" + this.conversationID + "/share?format=json", true);
            // request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
            // request.onload = function() {
            //     if(request.status != 401) window.location.reload();
            // }
           
            // request.send("method=" + method + "&to=" + to);
            // request.onerror = function(msg) {
            //     // There was a connection error of some sort
            //     alert('There was an error sharing, please try back later.');
            // };
            $.ajax({
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
            $.ajax({
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
                    if(this.existInDom("#visitorChat_UnreadMessages_" + conversation)){
                        document.querySelector("#visitorChat_UnreadMessages_" + conversation).style.display = 'none';
                    }
                } else {
                    // doing js will not display nonfi for new message for client, revert back to jquery ( may 18 2022)
                    $("#visitorChat_UnreadMessages_" + conversation).html(html);
                    $("#visitorChat_UnreadMessages_" + conversation).show();
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

            if (data['userStatus'] != this.operatorStatus
                && (data['userStatusReason'] == 'SERVER_IDLE' || data['userStatusReason'] == 'MAINTENANCE' || data['userStatusReason'] == 'EXPIRED_REQUEST')) {
                //Alert the user if the server set them to busy.
                this.alert('idle');

                // Add loading to DCF Modal
                document.querySelector("#operator-alert-content").innerHTML = '<progress></progress>';

                var helpText = "You have been set to BUSY";

                if (data['userStatusReason'] == 'SERVER_IDLE') {
                    helpText = "Due to inactivity with the server, you have been set to 'busy'.  This usually happens when you forget to change your status to 'unavailable' before you close the browser or after your computer has lost connection with the server.";
                }

                if (data['userStatusReason'] == 'MAINTENANCE') {
                    helpText = "Due to server maintenance, you have been set to 'busy'.  Maintenance has been completed and you can now set yourself as AVAILABLE.";
                }

                if (data['userStatusReason'] == 'EXPIRED_REQUEST') {
                    helpText = "You have missed an assignment.  In order to provide the best response times to the clients, you have been set to 'busy'.";
                    clearTimeout(VisitorChat.requestLoopID); //Clear the timeout.
                }

                helpText += '<ul class="dcf-list-bare dcf-list-inline dcf-p-1 dcf-mt-3 dcf-mb-3">';
                helpText += '<li><button class="dcf-btn dcf-btn-primary" id="operator-alert-okay">Okay</button>';
                helpText += '<li><button class="dcf-btn dcf-btn-secondary" id="operator-alert-go-back-online">Go back online</button>';
                helpText += '</ul>';

                // Trigger click on hidden button to open DCF Modal
                document.querySelector(".operator-alert-modal-toggle-btn").click();
                document.querySelector('#operator-alert-modal-content').innerHtml = helpText;
                document.querySelector('#operator-alert-okay').focus();
                document.querySelector('#operator-alert-go-back-online').addEventListener('click', function() {
                    document.querySelector('#operator-alert-modal-close-btn').click();
                  this.toggleOperatorStatus('USER');
                }.bind(this));
                document.querySelector("#operator-alert-okay").addEventListener('click', function() {
                    document.querySelector('#operator-alert-modal-close-btn').click();
                });
            }

            this.updateOperatorStatus(data['userStatus']);

            this._super(data);

            //Alert if there are new and unread messages.
            this.updateUnreadMessages(data['unreadMessages']);

            this.totalMessages = data['totalMessages'];

            //1. Check for any pending conversations.
            if (data['pendingAssignment'] == false || data['pendingDate'] == false) {
                this.currentRequest = false;
                clearTimeout(VisitorChat.requestLoopID);
                // close chat request modal if open
                document.querySelector('#operator-chat-request-modal-close-btn').click();
                return true;
            }

            //Start the alert.
            this.alert('assignment');

            var date = new Date(data['pendingDate']);

            //3. Alert the user.
            if (this.currentRequest != data['pendingAssignment']) {
                // Trigger click on hidden button to open DCF Modal
                document.querySelector(".operator-chat-request-modal-toggle-btn").click();
                document.querySelector('#operator-assignment-accept').focus();
                document.querySelector('#operator-assignment-reject').addEventListener('click', function() {
                  document.querySelector('#operator-chat-request-modal-close-btn').click();
                  this.sendChatRequestResponse(this.currentRequest, 'REJECTED');
                  this.clearAlert();
                  clearTimeout(VisitorChat.requestLoopID);
                }.bind(this));
                document.querySelector('#operator-assignment-accept').addEventListener('click', function() {
                    document.querySelector('#operator-chat-request-modal-close-btn').click();
                  this.sendChatRequestResponse(this.currentRequest, 'ACCEPTED');
                  clearTimeout(VisitorChat.requestLoopID);
                  this.clearAlert();
                }.bind(this));

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
            if(this.existInDom(".ui-widget-overlay")){
                document.querySelector(".ui-widget-overlay").style.background = color;
                document.querySelector(".ui-widget-overlay").style.opacity = "0.5";
            }

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
            return this.serverURL + "conversation?format=json&last=" + this.latestMessageId + conversation;
        },

        sendChatRequestResponse:function (id, response) {
            $.ajax({
                type:"POST",
                url:this.serverURL + "assignment/" + id + "/edit?format=json",
                statusCode: {
                    //Did our session expire?
                    401: function() {
                        window.location.reload(); //reload the page
                    },
                    400: function() {
                        alert("Someone has already accepted this conversation, or the visitor ended the conversation.");
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
            document.querySelector("#chatRequestCountDown").innerHTML = difference;

            if (currentDate.getTime() >= VisitorChat.requestExpireDate[id]) {
                return false;
            }

            VisitorChat.requestLoopID = setTimeout("VisitorChat.requestLoop(" + id + ")", 1000);
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
                this.clientInfo = data['client_html'];
            }

            if (data['operators'] !== undefined) {
                this.operators = new Array();

                for (operator in data['operators']) {
                    this.operators.push(data['operators'][operator]);

                    if (data['operators'][operator].id == VisitorChat.userID) {
                        //This is the current assignment
                        VisitorChat.assignmentID = data['operators'][operator].assignment;
                    }
                }
                if(this.existInDom('#leaveConversation')){
                    if (this.operators.length > 1) {
                        document.querySelector('#leaveConversation').style.display = '';
                    } else {
                        document.querySelector('#leaveConversation').style.display = 'none';
                    }
                }
            }

            return this._super(data);
        },

        updateInvitationsListWithHTML:function (html) {
            if (this.invitationsHTML != html) {
                this.invitationsHTML = html;
                document.querySelector("#clientChat_Invitations").innerHTML = html;
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

            if (data['client_is_typing']) {
                WDN.jQuery('#visitorChat_is_typing').text('The other party is typing').show(500);
            } else {
                WDN.jQuery('#visitorChat_is_typing').hide(500);
            }

            this.appendMessages(data['messages']);
        },

        onConversationStatus_Closed:function (data) {
            //Disable the input message input.
            document.querySelector("#visitorChat_messageBox").setAttribute("disabled", "disabled");

            //Don't let the operator share or close (because it is already closed).
            document.querySelector("#shareConversation").parentNode.removeChild(document.querySelector("#shareConversation"));
            document.querySelector("#closeConversation").parentNode.removeChild(document.querySelector("#closeConversation"));

            //Display a closed message.
            var html = "<div class='chat_notify' id='visitorChat_closed'>This conversation has been closed.</div>";

            html = document.querySelector("#clientChat").insertAdjacentHTML('afterbegin', html);

            //set the opacity of all siblings
            $('#visitorChat_closed').siblings().css({'opacity':'0.1'})
            //set the opacity of current item to full, and add the effect class
            document.querySelector('#visitorChat_closed').style.opacity = '1.0';

            if (data['messages'] == undefined) {
                return true;
            }

            this.appendMessages(data['messages']);
        },

        updateConversationList:function () {
            //Update the Client List
            $.ajax({
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
                success:function (data) { 
                    $("#clientList").html(data);
                    $("#conversationId_" + this.conversationID).addClass('selected');
                    this.updateConversationListWithUnreadMessages();
                    this.initWatchers();
                }.bind(this)
            });
        },

        checkOperatorCountBeforeStatusChange:function () {
            $.ajax({
                type:"GET",
                url:this.serverURL + "user/sites?format=json",
                statusCode: {
                    //Did our session expire?
                    401: function() {
                        window.location.reload(); //reload the page
                    }
                },
                success:function (data) {
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

                }.bind(this),
                error:function (data) {
                    this.toggleOperatorStatus('CLIENT_IDLE');
                }.bind(this)
            });
        },

        displayStatusChangeAlert:function (offline) {
          // Add loading to DCF Modal
          document.querySelector("#operator-alert-modal-content").innerHTML = '<progress></progress>';

          var html = '<p>You are the last person online for the following sites.  If you go offline now, these sites will have chat functionality turned off.</p>';
          html += '<ul class="dcf-h-10 dcf-overflow-x-auto dcf-b-1 dcf-b-solid" id="visitorChat_sitesWarning">';
          for (site in offline) {
            html += '<li>' + offline[site] + '</li>';
          }
          html += '</ul>';

          html += '<ul class="dcf-list-bare dcf-list-inline dcf-p-1 dcf-mt-3 dcf-mb-3">';
          html += '<li><button class="dcf-btn dcf-btn-primary" id="operator-go-offline">Go Offline Anyway</button>';
          html += '<li><button class="dcf-btn dcf-btn-secondary" id="operator-stay-online">Stay Online</button>';
          html += '</ul>';

          // Trigger click on hidden button to open DCF Modal
          document.querySelector(".operator-alert-modal-toggle-btn").click();
          document.querySelector('#operator-alert-modal-content').innerHTML = html;
          document.querySelector('#operator-go-offline').focus();
          document.querySelector('#operator-go-offline').addEventListener('click', function() {
            this.toggleOperatorStatus('USER');
            document.querySelector('#operator-alert-modal-close-btn').click();
          }.bind(this));
          document.querySelector("#operator-stay-online").addEventListener('click', function() {
            document.querySelector('#operator-alert-modal-close-btn').click();
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

            $.ajax({
                type:"POST",
                url:this.serverURL + "users/" + this.userID + "/edit?format=json",
                data:"status=" + status + "&reason=" + reason,
                statusCode: {
                    //Did our session expire?
                    401: function() {
                        window.location.reload(); //reload the page
                    }
                },
                success:function (data) {
                    this.updateOperatorStatus(status);
                }.bind(this)
            });
        },

        updateOperatorStatus:function (newStatus) {
            var formatStatus = 'Available';

            var flag = document.querySelector("#toggleOperatorStatus").classList.contains("closed");

            if (newStatus == 'BUSY') {
                formatStatus = 'You are unavailable';
            }

            if(this.existInDom("#toggleOperatorStatus")){
                if (newStatus == 'BUSY') {
                        document.querySelector("#toggleOperatorStatus").classList.add("dcf-btn-secondary" , "closed");
                        document.querySelector("#toggleOperatorStatus").classList.remove("dcf-btn-primary" , "open");
                } else {
                    document.querySelector("#toggleOperatorStatus").classList.add("dcf-btn-primary" , "open");
                    document.querySelector("#toggleOperatorStatus").classList.add("dcf-btn-secondary" , "closed");
                    formatStatus = 'You are available';
                }
            }

            //Don't call this if its the same status
            if (newStatus !== this.operatorStatus) {;
                document.querySelector("#currentOperatorStatus").innerHTML = formatStatus;
            }

            this.operatorStatus = newStatus;
        },

        // This function check whenever the followin querySelector is loaded or not
        // This is how I check for domReady in Chatbase but should be fix in the future , since this is not the best way to check it
        elementReady :function(e){
            return document.querySelector(e) !== null;
        },

        allSibling : function(el){
            if (el.parentNode === null) return [];

            return Array.prototype.filter.call(el.parentNode.children, function (child) {
                return child !== el;
            });
        },

        //This might be a better elementReady , although sometimes you only want to check it if it's null or not 
        //, then we use elementReady
        existInDom : function(e){
            var element = document.querySelector(e);
            if (typeof(element) != 'undefined' && element != null)
            {
                return true;
            }
            return false;
        }
    });

    //start the chat
    $(function(){
        VisitorChat = new VisitorChat_Operator("<?php echo \UNL\VisitorChat\Controller::$url;?>", <?php echo \UNL\VisitorChat\Controller::$refreshRate;?>, <?php echo \UNL\VisitorChat\Controller::$chatRequestTimeout; ?>);
        VisitorChat.start();
    });
});
