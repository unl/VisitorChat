<?php
$data = array();
$data['latest_message_id']   = $context->latest_message_id;
$data['status']              = $context->conversation->status;
$data['conversation_id']     = $context->conversation->id;
$data['phpssid']             = session_id();

if ($context->sendHTML) {
    //Save the current template path.
    $path = \UNL\VisitorChat\Controller::$templater->getTemplatePath();
    
    //Set the path to the html directory.
    \UNL\VisitorChat\Controller::$templater->setTemplatePath(array(\UNL\VisitorChat\Controller::$applicationDir . "/www/templates/default/"));
    
    //Render the conversation as html.
    $data['html'] = \UNL\VisitorChat\Controller::$templater->render($context, 'UNL/VisitorChat/Conversation/View.tpl.php');
    
    //Return to the original template path.
    \UNL\VisitorChat\Controller::$templater->setTemplatePath($path);
}

if ($context->invitations) {
    //Save the current template path.
    $path = \UNL\VisitorChat\Controller::$templater->getTemplatePath();
    
    //Set the path to the html directory.
    \UNL\VisitorChat\Controller::$templater->setTemplatePath(array(\UNL\VisitorChat\Controller::$applicationDir . "/www/templates/default/"));
    
    //Render the conversation as html.
    $data['invitations_html'] = \UNL\VisitorChat\Controller::$templater->render($context->invitations, 'UNL/VisitorChat/Invitation/RecordList.tpl.php');
    
    //Return to the original template path.
    \UNL\VisitorChat\Controller::$templater->setTemplatePath($path);
}

echo json_encode($data, true);
