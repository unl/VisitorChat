<?php
$user = \UNL\VisitorChat\User\Service::getCurrentUser();
?>

<div id="visitorChat_conversation">
    <div class="dcf-relative dcf-pt-6 dcf-pr-5 dcf-pb-5 dcf-pl-5 unl-cream unl-bg-darker-gray" id="visitorChat_conversation_header">
        <div id="clientInfo">
            <?php echo \Epoch\Controller::$templater->render(\UNL\VisitorChat\Conversation\ClientInfo::getFromConversationRecord($context->conversation->getRawObject())); ?>
        </div>
        <?php if ($user->type == 'operator'): ?>
            <div class="dcf-absolute dcf-pin-top dcf-pin-right dcf-mt-4 dcf-mr-4" id="visitorChat_conversation_options">
                <ul class="dcf-list-bare dcf-list-inline">
                    <li id="leaveConversation"><a class="unl-cream" href="#" title="Leave Conversation"><!--Leave Conversation--></a></li>
                    <li id="shareConversation"><a class="unl-cream" href="#" title="Share Conversation"><!--Share--></a></li>
                    <li id="closeConversation"><a class="unl-cream" href="#" title="End Conversation"><!--End Conversation--></a></li>
                </ul>
            </div>
        <?php endif; ?>
        <div class="dcf-mt-2 dcf-txt-xs" id="visitorChat_url">
            <span id="visitorChat_url_title"><span><?php echo $context->conversation->getClient()->name;?></span></span>
            <span class="visitorChat_topicPage dcf-d-block">
            Conversation started at: <a href="<?php echo $context->conversation->initial_url;?>" target="_new"><?php echo $context->conversation->initial_pagetitle;?></a></span>
        </div>
    </div>
    <div class="dcf-relative dcf-mb-3 dcf-pr-4 dcf-overflow-x-hidden dcf-overflow-y-scroll dcf-z-0" id="visitorChat_chatBox" role="log" aria-live="polite">
        <ul class="dcf-list-bare dcf-mb-0">
        </ul>
    </div>

    <?php
    //render a new message box.
    if ($context->conversation->status == "CHATTING") {
        echo \Epoch\Controller::$templater->render(new \UNL\VisitorChat\Message\Edit(array('conversation_id' => $context->conversation->id)));
    }
    ?>
</div>
