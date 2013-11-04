<div>
    <p>
        A message has been submitted on <?php echo $context->conversation->date_created;?>
    </p>
    <p>
        <?php 
        $client = $context->conversation->getClient();
        $response = "";
        if ($context->conversation->email_fallback && !empty($client->email)) {
            $response = "The user requests a response <br />";
        }
        ?>
        Message from <?php echo $client->name ?><br />
        IP: <?php echo $context->conversation->ip_address ?><br />
        <?php
        if (!empty($client->email)) {
            echo "Email: " . $client->email . " <br />";
        }
        ?>
        User Agent: <?php echo $context->conversation->user_agent ?><br />
        at <?php echo $context->conversation->initial_url; ?> <br /> <?php echo $response;?>
    </p>
</div>

<div>
    <?php echo \UNL\VisitorChat\Controller::$templater->render($context->messages, 'UNL/VisitorChat/Message/RecordList.tpl.php');?>
</div>

<div>
    <a href="<?php echo \UNL\VisitorChat\Controller::$url?>faq#whyemails">Why did I get this email?</a>
</div>