<?php
$messages = array();
foreach ($context as $message) {
    $conversation = false;
    
    if (!$conversation) {
        $conversation = $message->getConversation();
    }
    
    $poster = $message->getPoster();
    
    $class = 'visitorChat_them';
    
    if ($message->users_id == $conversation->users_id) {
        $class = 'visitorChat_client';
    }
    
    if ($message->users_id == \UNL\VisitorChat\User\Service::getCurrentUser()->id) {
        $class = 'visitorChat_me';
    }

    
    $messages[$message->id]['message'] = str_replace("&lt;br /&gt;", "<br />", $message->message);
    $messages[$message->id]['date'] =  date("g:i:s A", strtotime($message->date_created));
    $messages[$message->id]['class'] = $class;
    $messages[$message->id]['poster']['name'] =  $poster->name;
    $messages[$message->id]['poster']['type'] =  $poster->type;
}
echo serialize($messages);
?>