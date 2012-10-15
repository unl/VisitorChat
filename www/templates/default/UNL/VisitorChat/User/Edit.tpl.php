<?php 
function get_var($var, $context) {
    if (isset($context->$var)) {
        return $context->$var;
    }
    
    return null;
}
?>

<script type="text/javascript">
    //<![CDATA[
    WDN.jQuery(document).ready(function(){
        WDN.initializePlugin('zenform');
    });
    //]]>
</script>

<h3 class='zenform'>User settings</h3>
<form id='visitorchat_maxChats' class='zenform' name='input' method="post" action="<?php  echo \UNL\VisitorChat\Controller::$URLService->generateSiteURL("user/settings", false, false);?>" >
    <fieldset>
        <ul>
            <li>
                <label for="alias">Alias (the name that will be displayed to clients.  If empty, your first name will be displayed.</label><br />
                <input type="text" name="alias" id="alias" value="<?php echo get_var('alias', $context);?>"/>
            </li>
            <li>
                <label for="max_chats">Max Chats</label><br />
                <input type="text" name="max_chats" id="max_chats" value="<?php echo get_var('max_chats', $context);?>"/>
            </li>
            <li>
                <label for="popup_notifications">Popup Notifications</label><br />
                <select name="popup_notifications" id="popup_notifications">
                    <option value="1" <?php echo (get_var('popup_notifications', $context)==1)?'selected="selected"':'';?>>On</option>
                    <option value="0" <?php echo (get_var('popup_notifications', $context)==0)?'selected="selected"':'';?>>Off</option>
                </select> 
            </li>
        </ul>
    </fieldset>
    <input id='visitorChat_login_sumbit' type="submit" value="Submit" name="visitorChat_login_sumbit" />
</form>

<div>
    <div id="notificationOptions">
        <a href="#" id="requestNotifications">Show Desktop Notifications</a>
    </div>
    
    <a href="#" id="testNotifications">Test Notifications</a>
</div>