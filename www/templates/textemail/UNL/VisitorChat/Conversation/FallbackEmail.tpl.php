<?php
$client = $context->conversation->getClient();
?>
<?php if ($context->isMySupportEmail()) :?>
assignees=<?php echo str_replace("'", '"', $context->support_assignments) . "\n" ?>
<?php if (!empty($client->email)): ?>
contact=<?php echo $client->email ?>
<?php endif; ?>
<?php endif; ?>

<?php
$context->messages->rewind();
$message = $context->messages->current();

echo str_replace("&lt;br /&gt;", "\n", $message->message);
?>

--

From: <?php echo $client->name ?>

<?php
if (!empty($client->email)) {
    echo "Email: " . $client->email . " \n";
}
?>

URL that the email was submitted at: <?php echo $context->conversation->initial_url; ?>

IP Address: <?php echo $context->conversation->ip_address ?>

User Agent: <?php echo $context->conversation->user_agent ?>

Why did I get this email? See: <?php echo \UNL\VisitorChat\Controller::$url?>faq#whyemails