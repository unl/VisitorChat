<ul>
<?php 
    foreach($context as $conversation)
    {
        $client = $conversation->getClient();
        echo "<li><a href='" . \UNL\VisitorChat\Controller::$url . "conversation?conversation_id=" . $conversation->id . "&format=json' class='conversationLink'><span>" . $client->name . "</span></a>";
        
        $unread = "";
        if ($count = $conversation->getUnreadMessageCount()) {
            $unread = $count;
        }
        
        echo " <span id='visitorChat_UnreadMessages_" . $conversation->id . "' class='unread_message'>" . $unread . "</span>";
        echo "</li>";
    }
    ?>
</ul>