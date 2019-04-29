<div id="visitorChat_is_typing"></div>
<form class='unl_visitorchat_form unl-darker-gray' id='visitorChat_messageForm' name="input" action="<?php echo $context->getEditURL();?>" method="post">
    <fieldset>
          <ul class="dcf-list-bare">
            <li class='visitorChat_center'>
                <label class="dcf-label" for="visitorChat_messageBox">Your Message</label>
                <textarea class="dcf-input-text" rows="3" cols="25"  id='visitorChat_messageBox' name="message"></textarea>
            </li>
          </ul>
    </fieldset>
    <input type="hidden" name="conversations_id" value='<?php echo $context->conversations_id ?>'/>
    <input type="hidden" name="_class" value='<?php echo get_class($context->getRawObject()); ?>'/>
    <input id='visitorChat_message_submit' class="dcf-btn dcf-btn-primary" type="submit" value="Submit" name="visitorChat_message_submit" />
    <button class="dcf-btn dcf-btn-secondary" id="visitorChat_SearchBtn" style="display: none" onClick="VisitorChat.doSearch();">Search</button>
</form>
