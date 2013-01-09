<?php
switch ($context->for) {
    case 'client':
        require_once(\UNL\VisitorChat\Controller::$applicationDir . "/www/css/remote.css");
        break;
    case 'operator':
        require_once(\UNL\VisitorChat\Controller::$applicationDir . "/www/css/share.css");
        require_once(\UNL\VisitorChat\Controller::$applicationDir . "/www/css/chosen.css");
        require_once(\UNL\VisitorChat\Controller::$applicationDir . "/www/css/operator.css");
        break;
    default: 
        echo "unknown for???";
}