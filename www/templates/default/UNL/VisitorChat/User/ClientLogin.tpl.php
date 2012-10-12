<form id='visitorchat_clientLogin' class='unl_visitorchat_form' name='input' method="post" action="<?php echo \UNL\VisitorChat\Controller::$url;?>clientLogin" style="display: block">
    <fieldset><legend id="visitorChat_footerHeader">Comments for this page</legend>
        <ul>
            <li class="visitorChat_info">
                <label for="visitorChat_name">Your Name</label>
                <input type="text" name="name" id="visitorChat_name" placeholder="Name (optional)"/>
            </li>
            <li class="visitorChat_info">
                <label for="visitorChat_email">Your Email</label>
                <input type="text" name="email" class="validate-email" id="visitorChat_email" placeholder="Email (optional)"/>
            </li>
            <li class="visitorChat_info">
                <input type="checkbox" id="visitorChat_email_fallback" name="email_fallback" value="0" />
                <span id="visitorChat_email_fallback_text">I would like a response via email.</span>
            </li>
            <li class="visitorChat_center">
                <label for="visitorChat_messageBox">Your Message</label>
                <textarea rows="3" cols="25" class="required-entry" id='visitorChat_messageBox' name="message" placeholder="Question or comment?"></textarea>
            </li>
            
        </ul>
    </fieldset>
    <input type="hidden" name="initial_url" id="initial_url" value=""/>
    <input type="hidden" name="initial_pagetitle" id="initial_pagetitle" value=""/>
    <input id="visitorChat_login_chatmethod" type="hidden" name="method" value="EMAIL" />
    <input id='visitorChat_login_submit' type="submit" value="Submit" name="visitorChat_login_submit" />
</form>
