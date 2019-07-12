<?php
$client = $context->conversation->getClient();

// Include assignee info if mysupport and assignments are defined
if ($context->isMySupportEmail() && !empty($supportGroupString = trim($context->support_assignments))) {

  // Parse assigments by space allowing for single quote enclosure into array
  $supportAssignments = str_getcsv($supportGroupString, ' ', "'");

  // Get first assignee as primary
  echo 'Primary Assignee=' . array_shift($supportAssignments) . "\n";

  // List any other assignees delimited by commas
  if (count($supportAssignments)) {
      echo 'Other Assignees=' . implode(", ", $supportAssignments) . "\n";
  }
}

?>


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