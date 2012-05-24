<?php
$data = array();
$data['status']                     = $context->conversation->status;
$data['conversation_id']            = $context->conversation->id;
$data['phpssid']                    = session_id();
$data['operators']                  = $context->operators->getArrayCopy()->getRawObject();

//Save the current template path.
$path = \UNL\VisitorChat\Controller::$templater->getTemplatePath();

//Set the path to the html directory.
\UNL\VisitorChat\Controller::$templater->setTemplatePath(array(\UNL\VisitorChat\Controller::$applicationDir . "/www/templates/php/"));

$data['messages'] = unserialize(\UNL\VisitorChat\Controller::$templater->render($context->messages, 'UNL/VisitorChat/Message/RecordList.tpl.php'));

//Return to the original template path.
\UNL\VisitorChat\Controller::$templater->setTemplatePath($path);

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
