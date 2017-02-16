<div id='visitorChat_confirmationContainer'>
    Enter your email address to receive a transcript of this conversation.
    <form id='visitorChat_confirmationEmailForm' action="<?php echo UNL\VisitorChat\Controller::$URLService->generateSiteURL('conversation/' . $context->conversation->id . '/sendConfirmEmail', true, true)?>" class='unl_visitorchat_form' method="POST">
        <fieldset>
            <legend>Email Address</legend>
            <ul>
                <li class='visitorChat_center'>
                    <label for="visitorChat_confiramtionEmail">Your Email</label>
                    <input type='text' id='visitorChat_confiramtionEmail' class='validate-email required-entry' name='email' />
                </li>
            </ul>
        </fieldset>

        <input type='hidden' name='conversations_id' value='<?php echo $context->conversation->id ?>'/>
    </form>
</div>
