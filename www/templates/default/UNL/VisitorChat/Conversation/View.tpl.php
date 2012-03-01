<div id='visterChat_conversation'>
    <div id='visitorChat_url'>
        <span id='visitorChat_url_title'>For Site:</span> <?php echo $context->conversation->initial_url;?>
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
    echo \Epoch\Controller::$templater->render(new \UNL\VisitorChat\Message\Edit(array('conversations_id' => $context->conversation->id)));
    ?>
</div>