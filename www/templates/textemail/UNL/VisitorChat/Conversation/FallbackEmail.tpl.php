
A message has been submitted on <?php echo $context->conversation->date_created;?>

<?php 
$client = $context->conversation->getClient();
$response = "";
if ($context->conversation->email_fallback && !empty($client->email)) {
    $response = "The user requests a response \n";
}
?>
Message from <?php echo $client->name ?>
IP: <?php echo $context->conversation->ip_address ?>
<?php
if (!empty($client->email)) {
    echo "Email: " . $client->email . " \n";
}
?>
User Agent: <?php echo $context->conversation->user_agent ?>
at <?php echo $context->conversation->initial_url; ?>
<?php echo $response;?>

<?php echo \UNL\VisitorChat\Controller::$templater->render($context->messages, 'UNL/VisitorChat/Message/RecordList.tpl.php');?>

this is text.

Why did I get this email? See: <?php echo \UNL\VisitorChat\Controller::$url?>faq#whyemails