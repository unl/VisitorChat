<form id='visitorchat_clientLogin' class='unl_visitorchat_form' name='input' method="post" action="<?php echo \UNL\VisitorChat\Controller::$url;?>clientLogin" style="display: block">
    <fieldset><legend id="visitorChat_footerHeader">Comments for this page</legend>
        <ul>
            <li class="visitorChat_center">
                <label for="visitorChat_messageBox">Your Message</label>
                <textarea rows="3" cols="25" class="required-entry" id='visitorChat_messageBox' name="message"></textarea>
            </li>
            <li class="visitorChat_info">
                <label for="visitorChat_name">Your Name (optional)</label>
                <input type="text" name="name" id="visitorChat_name"/>
            </li>
            <li class="visitorChat_info">
                <label for="visitorChat_email">Your Email (optional)</label>
                <input type="text" name="email" class="validate-email" id="visitorChat_email"/>
            </li>
            <li class="visitorChat_info">
                <input type="checkbox" id="visitorChat_email_fallback" name="email_fallback" value="0" />
                <label for="visitorChat_email_fallback" id="visitorChat_email_fallback_text">I would like a response via email.</label>
            </li>
        </ul>
    </fieldset>
    <input type="hidden" name="initial_url" id="initial_url" value=""/>
    <input type="hidden" name="initial_pagetitle" id="initial_pagetitle" value=""/>
    <input id="visitorChat_login_chatmethod" type="hidden" name="method" value="EMAIL" />
    <input id='visitorChat_login_submit' class="wdn-button wdn-button-triad" type="submit" value="Submit" name="visitorChat_login_submit" />
</form>
