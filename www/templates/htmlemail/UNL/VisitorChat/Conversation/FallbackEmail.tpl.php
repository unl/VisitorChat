<div>
    <p>
        <?php
            // Include assignee info if nu support and assignments are defined
            if ($context->isNUSupportEmail() && !empty($supportGroupString = trim($context->support_assignments))) {

            // Parse assignments by space allowing for single quote enclosure into array
            $supportAssignments = str_getcsv($supportGroupString, ' ', "'");

            // Get first assignee as primary
            echo 'Primary Assignee=' . array_shift($supportAssignments) . "<br />";

            // List any other assignees delimited by commas
            if (count($supportAssignments)) {
                echo 'Other Assignees=' . implode(", ", $supportAssignments) . "<br />";
            }
            
            if (!empty(trim($client->email))) {
                echo 'Email Address=' . trim($client->email) . "<br />";
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
            echo 'First Name=' . $firstName . "<br />";
            echo 'Last Name=' . $lastName . "<br />";
            }
        ?>
    </p>
</div>


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