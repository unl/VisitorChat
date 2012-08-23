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

<h2>Settings</h2>
<h3 class='zenform'>User settings</h3>
<form id='visitorchat_maxChats' class='zenform' name='input' method="post" action="<?php  echo \UNL\VisitorChat\Controller::$URLService->generateSiteURL("user/settings", false, false);?>" >
    <fieldset>
        <ul>
            <li>
                <label for="visitorchat_maxChats">Max Chats</label>
                <input type="text" name="max_chats" id="visitorchat_maxChats" value="<?php echo get_var('max_chats', $context);?>"/>
            </li>
        </ul>
    </fieldset>
    <input id='visitorChat_login_sumbit' type="submit" value="Submit" name="visitorChat_login_sumbit" />
</form>

<div>
    <h3>Notification settings</h3>
    <div id="notificationOptions">
        <a href="#" id="requestNotifications">Show Desktop Notifications</a>
    </div>
    
    <a href="#" id="testNotifications">Test Notifications</a>
</div>