<?php 
$user = \UNL\VisitorChat\User\Service::getCurrentUser();
?>

<div id='visterChat_conversation' class="dcf-txt-sm">
    <div id='visitorChat_conversation_header'>
        <div id='clientInfo'>
            <?php echo \Epoch\Controller::$templater->render(\UNL\VisitorChat\Conversation\ClientInfo::getFromConversationRecord($context->conversation->getRawObject())); ?>
        </div>
        <?php if ($user->type == 'operator'): ?>
            <div id='visitorChat_conversation_options'>
                <ul class="dcf-list-bare">
                    <li id='leaveConversation'><a href='#' title='Leave Conversation'><!--Leave Conversation--></a></li>
                    <li id='shareConversation'><a href='#' title='Share Conversation'><!--Share--></a></li>
                    <li id='closeConversation'><a href='#' title='End Conversation'><!--End Conversation--></a></li>
                </ul>
            </div>
        <?php endif; ?>
        <div id='visitorChat_url'>
            <span id='visitorChat_url_title'><span><?php echo $context->conversation->getClient()->name;?></span></span>
            <span class="visitorChat_topicPage">
            Conversation started at: <a href='<?php echo $context->conversation->initial_url;?>' target='_new'><?php echo $context->conversation->initial_pagetitle;?></a></span>
        </div>
    </div>
    <div id='visitorChat_chatBox' aria-live="polite" role="log">
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
