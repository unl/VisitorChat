<div>
    <p>
        Thank you for chatting with us at <?php echo $context->conversation->initial_url; ?>
        <br />A record of your conversation can be found below.
    </p>
</div>

<div>
    <?php echo \UNL\VisitorChat\Controller::$templater->render($context->messages, 'UNL/VisitorChat/Message/RecordList.tpl.php');?>
</div>
