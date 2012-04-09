<div id='visterChat_conversation'>
    <div id='visitorChat_conversation_header'>
        <div id='visitorChat_url'>
            <span id='visitorChat_url_title'><?php echo $context->conversation->getClient()->name;?></span><br /> on 
            <a href='<?php echo $context->conversation->initial_url;?>' target='_new'><?php echo $context->conversation->initial_pagetitle;?></a>
        </div>
        <?php 
        if (\UNL\VisitorChat\User\Record::getCurrentUser()->type == 'operator') {
        ?>
        <div id='visitorChat_conversation_options'>
            <a href='#' id='closeConversation'>End Conversation</a>
        </div>
        <?php 
        }
        ?>
    </div>
    <div id='visitorChat_chatBox'>
        <ul>
            <?php 
            foreach ($context->messages as $message) {
                echo "<li>" . \Epoch\Controller::$templater->render($message) . "</li>";
            }
            ?>
        </ul>
    </div>
    
    <?php
    //render a new message box.
    if ($context->conversation->status == "CHATTING") {
        echo \Epoch\Controller::$templater->render(new \UNL\VisitorChat\Message\Edit(array('conversations_id' => $context->conversation->id)));
    }
    ?>
</div>