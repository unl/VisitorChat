<div id="visitorChat_confirmationContainer" tabindex="-1">
    Enter your email address to receive a transcript of this conversation.
    <form class="dcf-form unl_visitorchat_form unl-darker-gray" id="visitorChat_confirmationEmailForm" action="<?php echo UNL\VisitorChat\Controller::$URLService->generateSiteURL('conversation/' . $context->conversation->id . '/sendConfirmEmail', true, true)?>" method="POST">
        <fieldset class="dcf-m-0 dcf-p-0 dcf-b-0">
            <legend>Email Address</legend>
            <ul class="dcf-list-bare">
                <li class="visitorChat_center">
                    <label for="visitorChat_confiramtionEmail">Your Email</label>
                    <input class="validate-email required-entry" id="visitorChat_confiramtionEmail" name="email" type="text">
                </li>
            </ul>
        </fieldset>
        <input name="conversations_id" type="hidden" value="<?php echo $context->conversation->id ?>">
        <input class="dcf-btn dcf-btn-primary" id="visitorChat_confirmEmail_submit" name="visitorChat_confirmEmail_submit" type="submit" value="Submit">
    </form>
</div>
