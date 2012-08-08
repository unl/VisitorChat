<div id='visitorChat_confirmationContainer'>
    Enter your email address to receive a transcript of this conversation.
    <form id='visitorChat_confirmationEamilForm' action="<?php echo UNL\VisitorChat\Controller::$URLService->generateSiteURL('conversation/' . $context->conversation->id . '/sendConfirmEmail', true)?>" class='unl_visitorchat_form' method="POST">
        <fieldset>
            <legend>Email Address</legend>
            <ul>
                <li class='visitorChat_center'>
                    <input type='text' id='visitorChat_confiramtionEmail' class='validate-email' name='email' />
                </li>
            </ul>
        </fieldset>

        <input type='hidden' name='conversations_id' value='<?php echo $context->conversation->id ?>'/>
    </form>
</div>
