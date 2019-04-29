<?php
$messages = array();
foreach ($context as $message) {
    $poster = $message->getPoster();
    
    $name = $poster->name;
    if ($poster->type == 'operator') {
        $name = $poster->getAlias();
    }
    
    $messages[$message->id]['message']        = str_replace("&lt;br /&gt;", "<br />", \UNL\VisitorChat\Controller::makeClickableLinks($message->message));
    $messages[$message->id]['date']           = date("g:i:s A", strtotime($message->date_created));
    $messages[$message->id]['class']          = $message->getDisplayClass() . " force-wrap";
    $messages[$message->id]['poster']['name'] = $name;
    $messages[$message->id]['poster']['type'] = $poster->type;
}
echo serialize($messages);
?>