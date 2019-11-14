<div id="visitorChat_is_typing"></div>
<form class="dcf-form unl_visitorchat_form unl-darker-gray" id="visitorChat_messageForm" name="input" action="<?php echo $context->getEditURL();?>" method="post">
    <fieldset class="dcf-m-0 dcf-p-0 dcf-b-0">
        <ul class="dcf-list-bare">
            <li class="visitorChat_center">
                <label for="visitorChat_messageBox">Your Message</label>
                <textarea id="visitorChat_messageBox" name="message" rows="3" cols="25"></textarea>
            </li>
        </ul>
    </fieldset>
    <input name="conversations_id" type="hidden" value="<?php echo $context->conversations_id ?>">
    <input name="_class" type="hidden" value="<?php echo get_class($context->getRawObject()); ?>">
    <input class="dcf-btn dcf-btn-primary" id="visitorChat_message_submit" name="visitorChat_message_submit" type="submit" value="Submit">
</form>
