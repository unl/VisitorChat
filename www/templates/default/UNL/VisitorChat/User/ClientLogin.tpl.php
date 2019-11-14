<form class="dcf-form unl_visitorchat_form unl-darker-gray" id="visitorchat_clientLogin" name="input" method="post" action="<?php echo \UNL\VisitorChat\Controller::$url;?>clientLogin" style="display: block">
    <fieldset class="dcf-m-0 dcf-p-0 dcf-b-0">
        <legend id="visitorChat_footerHeader">Comments for this page</legend>
        <ul class="dcf-list-bare">
            <li id="visitorChatbot_messageBoxContainer" class="visitorChat_center">
                <label for="visitorChat_messageBox">Your Message</label>
                <textarea class="required-entry" id="visitorChat_messageBox" name="message" rows="3" cols="25" ></textarea>
            </li>
            <li class="dcf-mt-4 dcf-mb-4 dcf-txt-md dcf-bold" id="visitorChatbot_intent_message" style="display:none">
            </li>
            <li class="visitorChat_info">
                <label for="visitorChat_name">Your Name (optional)</label>
                <input id="visitorChat_name" name="name" type="text">
            </li>
            <li class="visitorChat_info">
                <label for="visitorChat_email">Your Email (optional)</label>
                <input class="validate-email" id="visitorChat_email" name="email" type="text">
            </li>
            <li class="visitorChat_info">
                <input id="visitorChat_email_fallback" name="email_fallback" type="checkbox" value="0">
                <label id="visitorChat_email_fallback_text" for="visitorChat_email_fallback" >I would like a response via email.</label>
            </li>
        </ul>
    </fieldset>
    <input id="initial_url" name="initial_url" type="hidden" value="">
    <input id="initial_pagetitle" name="initial_pagetitle" type="hidden" value="">
    <input id="visitorChat_login_chatmethod" name="method" type="hidden" value="EMAIL">
    <input id="visitorChatbot_intent" name="chatbot_intent" type="hidden" value="">
    <input id="visitorChatbot_intent_defaults" name="chatbot_intent_defaults" type="hidden" value="">
    <input class="dcf-btn dcf-btn-primary" id="visitorChat_login_submit" name="visitorChat_login_submit" type="submit" value="Submit">
</form>
