<?php 
$user = \UNL\VisitorChat\User\Service::getCurrentUser();
?>

<div id='visterChat_conversation'>
    <div id='visitorChat_conversation_header'>
    <div id='clientInfo'>
            <?php
	echo \Epoch\Controller::$templater->render(\UNL\VisitorChat\Conversation\ClientInfo::getFromConversationRecord($context->conversation->getRawObject()));
	?>
    </div>
        <div id='visitorChat_url'>
            <span id='visitorChat_url_title'><span><?php echo $context->conversation->getClient()->name;?></span></span>
            <span class="visitorChat_topicPage">
            at <a href='<?php echo $context->conversation->initial_url;?>' target='_new'><?php echo $context->conversation->initial_pagetitle;?></a></span>
        </div>
        <?php 
        if ($user->type == 'operator') {
        ?>
        <div id='visitorChat_conversation_options'>
            <ul>
                <li><a href='#' id='leaveConversation'><!--Leave Conversation--></a></li>
                <li><a href='#' id='shareConversation'><!--Share--></a></li>
                <li><a href='#' id='closeConversation'><!--End Conversation--></a></li>
            </ul>
        </div>
        <?php 
        }
        ?>
    </div>
    <div id='visitorChat_chatBox'>
        <ul>
        </ul>
    </div>
    
    <?php
    //render a new message box.
    if ($context->conversation->status == "CHATTING") {
        echo \Epoch\Controller::$templater->render(new \UNL\VisitorChat\Message\Edit(array('conversation_id' => $context->conversation->id)));
    }
    ?>
</div>