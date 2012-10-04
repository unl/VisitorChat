<div id='clientChatContainer'>
    <div id="clientChat">
        <div id='visterChat_conversation'>
            <div id='visitorChat_conversation_header'>
                <div id='visitorChat_url'>
                    <span id='visitorChat_url_title'><?php echo $context->conversation->getClient()->name;?></span> <?php echo date("F j, Y, g:ia", strtotime($context->conversation->date_created));?><br />
                    on <a class='visitorChat_topicPage' href='<?php echo $context->conversation->initial_url;?>' target='_new'><?php echo $context->conversation->initial_pagetitle;?></a>
                </div>
            </div>
            <div id='visitorChat_chatBox'>
                <ul>
                    <?php
                    foreach ($context->messages as $message) {
                        echo "<li class='" . $message->getDisplayclass() . "'>" . \Epoch\Controller::$templater->render($message) . "</li>";
                    }
                    ?>
                </ul>
            </div>
        </div>
    </div>
    <div id="clientChatInfoContainer">
        <div id="clientChat_GeneralInformation">
            <h2>Details</h2>
            <?php
            //Determine who closed the conversation.
            $name = "Unknown";
            if (!empty($context->conversation->closer_id)) {
                $name = \UNL\VisitorChat\User\Record::getByID($context->conversation->closer_id)->name;
            }
            
            $duration = "Unknown";
            if ($duration = $context->conversation->getDuration()) {
                $duration = round($duration/60) . " min";
            }
            
            ?>
            <table class='zentable neutral'>
                <thead>
                    <tr>
                        <th colspan="2">Chat Information</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Chat Status:</td>
                        <td><?php echo $context->conversation->status; ?></td>
                    </tr>
                    <tr>
                        <td>Method:</td>
                        <td><?php echo $context->conversation->method; ?></td>
                    </tr>
                    <tr>
                        <td>Closed By:</td>
                        <td><?php echo $name; ?></td>
                    </tr>
                    <tr>
                        <td>Duration:</td>
                        <td><?php echo $duration; ?></td>
                    </tr>
                </tbody>
            </table>
            <div id='clientInfo'>
                <?php echo \Epoch\Controller::$templater->render(\UNL\VisitorChat\Conversation\ClientInfo::getFromConversationRecord($context->conversation->getRawObject()));?>
            </div>
        </div>
        <div id="clientChat_Invitations">
            <?php echo \Epoch\Controller::$templater->render($context->conversation->getInvitations())?>
        </div>
    </div>
</div>
<div style="clear:both"></div>