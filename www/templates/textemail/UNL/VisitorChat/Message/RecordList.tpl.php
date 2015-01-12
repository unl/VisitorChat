<?php

foreach ($context as $message) {
    echo \Epoch\Controller::$templater->render($message, 'UNL/VisitorChat/Message/View.tpl.php') . "\n";
}
