<div id="visitorChat_confirmationContainer" tabindex="-1">
    <p class="dcf-txt-sm">Enter your email address to receive a transcript of this conversation.</p>
    <form class="dcf-form unl_visitorchat_form unl-darker-gray" id="visitorChat_confirmationEmailForm" action="<?php echo UNL\VisitorChat\Controller::$URLService->generateSiteURL('conversation/' . $context->conversation->id . '/sendConfirmEmail', true, true)?>" method="POST">
        <div class="dcf-form-group">
            <label class="dcf-txt-xs" for="visitorChat_confiramtionEmail">Your Email</label>
            <input class="validate-email required-entry dcf-w-100%" id="visitorChat_confiramtionEmail" name="email" type="text" required>
        </div>
        <input name="conversations_id" type="hidden" value="<?php echo $context->conversation->id ?>">
        <input class="dcf-btn dcf-btn-primary" id="visitorChat_confirmEmail_submit" name="visitorChat_confirmEmail_submit" type="submit" value="Submit">
    </form>
</div>
