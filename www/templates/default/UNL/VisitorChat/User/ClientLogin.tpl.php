<?php 
function get_var($var, $context) {
    if (isset($context->$var)) {
        return $context->$var;
    }
    
    return null;
}
?>
<form id='visitorchat_clientLogin' class='unl_visitorchat_form' name='input' method="post" action="<?php echo $context->getEditURL(); ?>" >
    <fieldset><legend>Client Login</legend>
        <ul>
            <li class="visitorChat_info">
                <label for="visitorChat_name"></label>
                <input type="text" name="name" id="visitorChat_name" value="<?php echo get_var('name', $context);?>"/>
            </li>
            <li class="visitorChat_info">
                <label for="visitorChat_email"></label>
                <input type="text" name="email" class="validate-email" id="visitorChat_email" value="<?php echo get_var('email', $context);?>"/>
            </li>
            <li class="visitorChat_info">
                <input type="checkbox" id="visitorChat_email_fallback" name="email_fallback" value="1" />
                <span id="email-fallback-text">I would like a response via email.</span>
            </li>
            <li class="visitorChat_center">
                <textarea rows="3" cols="25" class="required-entry" id='visitorChat_messageBox' name="message"></textarea>
            </li>
            
        </ul>
    </fieldset>
    <input type="hidden" name="initial_url" id="initial_url" value=""/>
    <input type="hidden" name="id" value='<?php echo $context->id;?>'/>
    <input type="hidden" name="_class" value='<?php echo get_class($context->getRawObject()); ?>'/>
    <input id="visitorChat_login_sumbit" type="submit" value="Submit" name="visitorChat_login_sumbit" />
</form>
