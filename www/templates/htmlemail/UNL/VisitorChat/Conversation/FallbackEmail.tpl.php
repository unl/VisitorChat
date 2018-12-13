<div>
    <?php
    $context->messages->rewind();
    $message = $context->messages->current();

    echo str_replace("&lt;br /&gt;", "<br />", $message->message);
    ?>
    <br />
</div>

<?php
$client = $context->conversation->getClient();
?>

<div>
    <p>
        From: <?php echo $client->name ?><br />
        <?php
        if (!empty($client->email)) {
            echo "Email: " . $client->email . " <br />";
        }
        ?>
        URL that the email was submitted at: <?php echo $context->conversation->initial_url; ?> <br />
        IP Address: <?php echo $context->conversation->ip_address ?><br />
        User Agent: <?php echo $context->conversation->user_agent ?><br />
    </p>
</div>



<div>
    <a href="<?php echo \UNL\VisitorChat\Controller::$url?>faq#whyemails">Why did I get this email?</a>
</div>