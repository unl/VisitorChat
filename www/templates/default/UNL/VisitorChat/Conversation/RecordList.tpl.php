<ul class="dcf-list-bare">
<?php
    foreach($context as $conversation)
    {
        $client = $conversation->getClient();
        echo "<li class='dcf-d-flex dcf-ai-center dcf-jc-between dcf-mb-0' id='conversationId_" . $conversation->id . "'><a class='conversationLink' href='" . \UNL\VisitorChat\Controller::$url . "conversation?conversation_id=" . $conversation->id . "&format=json'><span>" . $client->name . "</span></a>";

        $unread = $conversation->getUnreadMessageCount();

        echo " <span id='visitorChat_UnreadMessages_" . $conversation->id . "' class='unread_count dcf-badge dcf-badge-roundrect dcf-mr-4 dcf-ml-3 unl-bg-scarlet unl-cream'>" . $unread . "</span>";
        echo "</li>";
    }
    ?>
</ul>