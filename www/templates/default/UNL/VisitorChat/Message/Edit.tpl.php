<div class="dcf-italic" id="visitorChat_is_typing"></div>
<form class="dcf-form unl_visitorchat_form unl-darker-gray" id="visitorChat_messageForm" name="input" action="<?php echo $context->getEditURL();?>" method="post">
    <div class="dcf-mb-3">
        <label class="dcf-txt-xs" for="visitorChat_messageBox">Your Message</label>
        <textarea id="visitorChat_messageBox" name="message" rows="3" cols="25"></textarea>
    </div>
    <input name="conversations_id" type="hidden" value="<?php echo $context->conversations_id ?>">
    <input name="_class" type="hidden" value="<?php echo get_class($context->getRawObject()); ?>">
    <input class="dcf-btn dcf-btn-primary" id="visitorChat_message_submit" name="visitorChat_message_submit" type="submit" value="Submit">
</form>
