<form class='unl_visitorchat_form' id='visitorChat_messageForm' name="input" action="<?php echo $context->getEditURL();?>" method="post">
    <fieldset>
          <textarea rows="3" cols="25"  id='visitorChat_messageBox' name="message"></textarea>
    </fieldset>
    <input type="hidden" name="conversations_id" value='<?php echo $context->conversations_id ?>'/>
    <input type="hidden" name="_class" value='<?php echo get_class($context->getRawObject()); ?>'/>
    
</form>

