<div id='visitorChat_confirmationContainer' tabindex="-1">
    Enter your email address to receive a transcript of this conversation.
    <form id='visitorChat_confirmationEmailForm' action="<?php echo UNL\VisitorChat\Controller::$URLService->generateSiteURL('conversation/' . $context->conversation->id . '/sendConfirmEmail', true, true)?>" class='unl_visitorchat_form unl-darker-gray' method="POST">
        <fieldset>
            <legend class="dcf-legend">Email Address</legend>
            <ul class="dcf-list-bare">
                <li class='visitorChat_center'>
                    <label class="dcf-label" for="visitorChat_confiramtionEmail">Your Email</label>
                    <input class="dcf-input-text" type='text' id='visitorChat_confiramtionEmail' class='validate-email required-entry' name='email' />
                </li>
            </ul>
        </fieldset>

        <input type='hidden' name='conversations_id' value='<?php echo $context->conversation->id ?>'/>
        <input id='visitorChat_confirmEmail_submit' class="dcf-btn dcf-btn-primary" type="submit" value="Submit" name="visitorChat_confirmEmail_submit" />
    </form>
</div>
