<div>
    <p>
        Thank you for chatting with us.  A record of your conversation can be found bellow.
    </p>
</div>

<div>
    <?php echo \UNL\VisitorChat\Controller::$templater->render($context->messages, 'UNL/VisitorChat/Message/RecordList.tpl.php');?>
</div>
