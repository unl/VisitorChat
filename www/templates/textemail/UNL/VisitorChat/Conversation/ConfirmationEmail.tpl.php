
Thank you for chatting with us at <?php echo $context->conversation->initial_url; ?>

A record of your conversation can be found below.

<?php echo \UNL\VisitorChat\Controller::$templater->render($context->messages, 'UNL/VisitorChat/Message/RecordList.tpl.php');?>
