<?php
$client = $context->conversation->getClient();

// Include assignee info if nu support and assignments are defined
if ($context->isNUSupportEmail() && !empty($supportGroupString = trim($context->support_assignments))) {

  // Parse assigments by space allowing for single quote enclosure into array
  $supportAssignments = str_getcsv($supportGroupString, ' ', "'");

  // Get first assignee as primary
  echo 'Primary Assignee=' . array_shift($supportAssignments) . "\n";

  // List any other assignees delimited by commas
  if (count($supportAssignments)) {
      echo 'Other Assignees=' . implode(", ", $supportAssignments) . "\n";
  }
  
  if (!empty(trim($client->email))) {
    echo 'Email Address=' . trim($client->email) . "\n";
  }

  $firstName = 'Website';
  $lastName = 'User';
  if (!empty(trim($client->name))) {
    $nameParts = explode(" ", trim($client->name), 2);
    if (!empty($nameParts[0])) {
      $firstName = $nameParts[0];
    }
    if (!empty($nameParts[1])) {
        $lastName = $nameParts[1];
    }
  }
  echo 'First Name=' . $firstName . "\n";
  echo 'Last Name=' . $lastName . "\n";
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
