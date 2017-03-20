<?php
$client = $context->conversation->getClient();
?>

<div>
    <p>
        This is a record of a conversation with Message from <?php echo $client->name ?> at <?php echo $context->conversation->initial_url; ?>

        <?php
        if (!empty($client->email)) {
            echo "<br /> Email: " . $client->email . " <br />";
        }
        ?>
    </p>
</div>

<div>
    <?php echo \UNL\VisitorChat\Controller::$templater->render($context->messages, 'UNL/VisitorChat/Message/RecordList.tpl.php');?>
</div>
