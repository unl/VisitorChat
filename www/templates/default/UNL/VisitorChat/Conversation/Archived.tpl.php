<?php  $ua = $context->conversation->parseUserAgent();?>
<div class="dcf-grid dcf-col-gap-vw archived-chat">
    <div class="dcf-col-100% dcf-col-67%-start@md" id='clientChatContainer'>
        <div id="clientChat">
            <div id='visterChat_conversation' class="dcf-txt-sm">
                <div id='visitorChat_conversation_header'>
                    <div id='visitorChat_url'>
                        <span id='visitorChat_url_title'>
                            <span><?php echo $context->conversation->getClient()->name;?></span>
                        </span>
                        <span class="visitorChat_topicPage">
                        at <a href='<?php echo $context->conversation->initial_url;?>' target='_new'><?php echo $context->conversation->initial_pagetitle;?></a></span>
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
    </div>
    <div class="dcf-col-100% dcf-col-33%-end@md" id="clientChatInfoContainer">
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
                        <th colspan="2"><?php echo date("F j, Y, g:ia", strtotime($context->conversation->date_created));?></th>
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
                    <tr>
                        <td>Browser:</td>
                        <td><?php echo (isset($ua->browser))?$ua->browser:'unknown';?></td>
                    </tr>
                    <tr>
                        <td>OS:</td>
                        <td><?php echo (isset($ua->os))?$ua->os:'unknown';?></td>
                    </tr>
                    <tr>
                        <td>IP address:</td>
                        <td><?php echo $context->conversation->ip_address;?></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div id="clientChat_Invitations">
            <?php echo \Epoch\Controller::$templater->render($context->conversation->getInvitations())?>
        </div>
        <?php
        if ($context->conversation->canDelete(\UNL\VisitorChat\User\Service::getCurrentUser())) {
            ?>
            <div id="options">
                <form action="<?php echo $context->conversation->getEditURL(); ?>" method="post">
                    <input type="submit" value="Delete this Conversation" name="delete" id="delete_conversation">
                </form>
            </div>
            <?php
        }
        ?>
    </div>
</div>