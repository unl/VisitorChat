<div id='clientChatContainer'>
    <div id="clientChat">
        <div id='visterChat_conversation'>
            <div id='visitorChat_conversation_header'>
                <div id='visitorChat_url'>
                    <span id='visitorChat_url_title'><?php echo $context->conversation->getClient()->name;?></span> <?php echo date("F j, Y, g:ia", strtotime($context->conversation->date_created));?><br />
                    on <a href='<?php echo $context->conversation->initial_url;?>' target='_new'><?php echo $context->conversation->initial_pagetitle;?></a>
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
            <h2>General Information</h2>
            <?php
            $name = "Unknown";
            if (!empty($context->conversation->closer_id)) {
                $name = \UNL\VisitorChat\User\Record::getByID($context->conversation->closer_id)->name;
            }
            
            echo "Closed By: " . $name;
            ?>
        </div>
        <div id="clientChat_Invitations">
            <h2>Invitations</h2>
            <?php echo \Epoch\Controller::$templater->render($context->conversation->getInvitations())?>
        </div>
    </div>
</div>
<div style="clear:both"></div>