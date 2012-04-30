<?php 
$user = \UNL\VisitorChat\User\Record::getCurrentUser();
?>

<div id='visterChat_conversation'>
    <div id='visitorChat_conversation_header'>
        <div id='visitorChat_url'>
            <span id='visitorChat_url_title'><?php echo $context->conversation->getClient()->name;?></span><br />
            with <ul id='visitorChat_url_operatorList'>
                <?php 
                foreach ($context->conversation->getAcceptedAndCompletedAssignments() as $assignment) {
                    $operator = $assignment->getUser();
                    
                    echo "<li>" . $assignment->getUser()->name . "</li>";
                }
                ?>
            </ul> <br />
            on <a href='<?php echo $context->conversation->initial_url;?>' target='_new'><?php echo $context->conversation->initial_pagetitle;?></a>
        </div>
        <?php 
        if ($user->type == 'operator') {
        ?>
        <div id='visitorChat_conversation_options'>
            <ul>
                <li><a href='#' id='shareConversation'>Share</a></li>
                <li><a href='#' id='closeConversation'>End Conversation</a></li>
            </ul>
        </div>
        <?php 
        }
        ?>
    </div>
    <div id='visitorChat_chatBox'>
        <ul>
            <?php 
            foreach ($context->messages as $message) {
            	$class = 'visitorChat_them';
            	
            	if ($message->users_id == $context->conversation->users_id) {
            		$class = 'visitorChat_client';
            	}
            	
            	if ($message->users_id == $user->id) {
            		$class = 'visitorChat_me';
            	}
            	
                echo "<li class='". $class . "'>" . \Epoch\Controller::$templater->render($message) . "</li>";
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