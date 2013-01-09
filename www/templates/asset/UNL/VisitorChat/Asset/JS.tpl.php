if (VisitorChat == undefined) {
var VisitorChat = false;
}
<?php

//Include the required things:
require_once(\UNL\VisitorChat\Controller::$applicationDir . "/www/js" . "/SimpleJavaScriptInheritance.js");
require_once(\UNL\VisitorChat\Controller::$applicationDir . "/www/js" . "/form.js");
require_once(\UNL\VisitorChat\Controller::$applicationDir . "/www/js" . "/VisitorChat/ChatBase.js");

switch($context->for) {
    case 'operator':
        require_once(\UNL\VisitorChat\Controller::$applicationDir . "/www/js" . "/chosen.min.js");
        require_once(\UNL\VisitorChat\Controller::$applicationDir . "/www/js" . "/VisitorChat/Operator.js");
        ?>
        //start the chat
        WDN.jQuery(function(){
            VisitorChat = new VisitorChat_Chat("<?php echo \UNL\VisitorChat\Controller::$url;?>", <?php echo \UNL\VisitorChat\Controller::$refreshRate;?>, <?php echo \UNL\VisitorChat\Controller::$chatRequestTimeout; ?>);
            VisitorChat.start();
        });
        <?php
        break;
    case 'client':
    default:
        require_once(\UNL\VisitorChat\Controller::$applicationDir . "/www/js" . "/jquery.cookies.min.js");
        require_once(\UNL\VisitorChat\Controller::$applicationDir . "/www/js" . "/VisitorChat/Remote.js");
        ?>
        WDN.jQuery(function(){
            WDN.loadJS('/wdn/templates_3.1/scripts/plugins/validator/jquery.validator.js', function() {
                if (VisitorChat == false) {
                    VisitorChat = new VisitorChat_Chat("<?php echo \UNL\VisitorChat\Controller::$url;?>", <?php echo \UNL\VisitorChat\Controller::$refreshRate;?>);
                }
            });
        });
        <?php
}