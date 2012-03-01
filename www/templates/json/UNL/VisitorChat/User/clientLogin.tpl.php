<?php
$data = array();
$data['status'] = 'LOGIN';

//Save the current template path.
$path = \UNL\VisitorChat\Controller::$templater->getTemplatePath();

//Set the path to the html directory.
\UNL\VisitorChat\Controller::$templater->setTemplatePath(array(\UNL\VisitorChat\Controller::$applicationDir . "/www/templates/default/"));

//Render the conversation as html.
$data['html'] = \UNL\VisitorChat\Controller::$templater->render($context);

//Return to the original template path.
\UNL\VisitorChat\Controller::$templater->setTemplatePath($path);

echo json_encode($data, true);