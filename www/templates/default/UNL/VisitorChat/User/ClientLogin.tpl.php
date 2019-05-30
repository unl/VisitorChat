<form id='visitorchat_clientLogin' class='unl_visitorchat_form unl-darker-gray' name='input' method="post" action="<?php echo \UNL\VisitorChat\Controller::$url;?>clientLogin" style="display: block">
    <fieldset><legend class="dcf-legend" id="visitorChat_footerHeader">Comments for this page</legend>
        <ul class="dcf-list-bare">
            <li id="visitorChatbot_messageBoxContainer" class="visitorChat_center">
                <label class="dcf-label" for="visitorChat_messageBox">Your Message</label>
                <textarea class="dcf-input-text" rows="3" cols="25" class="required-entry" id='visitorChat_messageBox' name="message"></textarea>
            </li>
            <li id="visitorChatbot_intent_message" class="dcf-mt-4 dcf-mb-4 dcf-txt-md dcf-bold" style="display:none">
            </li>
            <li class="visitorChat_info">
                <label class="dcf-label" for="visitorChat_name">Your Name (optional)</label>
                <input class="dcf-input-text" type="text" name="name" id="visitorChat_name"/>
            </li>
            <li class="visitorChat_info">
                <label class="dcf-label" for="visitorChat_email">Your Email (optional)</label>
                <input class="dcf-input-text" type="text" name="email" class="validate-email" id="visitorChat_email"/>
            </li>
            <li class="visitorChat_info">
                <input class="dcf-input-control" type="checkbox" id="visitorChat_email_fallback" name="email_fallback" value="0" />
                <label class="dcf-label" for="visitorChat_email_fallback" id="visitorChat_email_fallback_text">I would like a response via email.</label>
            </li>
        </ul>
    </fieldset>
    <input type="hidden" name="initial_url" id="initial_url" value=""/>
    <input type="hidden" name="initial_pagetitle" id="initial_pagetitle" value=""/>
    <input id="visitorChat_login_chatmethod" type="hidden" name="method" value="EMAIL" />
    <input id="visitorChatbot_intent" type="hidden" name="chatbot_intent" value="" />
    <input id="visitorChatbot_intent_defaults" type="hidden" name="chatbot_intent_defaults" value="" />
    <input id='visitorChat_login_submit' class="dcf-btn dcf-btn-primary" type="submit" value="Submit" name="visitorChat_login_submit" />
</form>
