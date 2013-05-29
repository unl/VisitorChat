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
        WDN.jQuery('.datepicker').datepicker();
    });
    //]]>
</script>

<?php
if ($context->id) {
    ?>
    <ul>
        <li>Originally Created by <?php echo $context->getUser()->name;?></li>
    </ul>
    <?php
}
?>

<h3 class='zenform'>Create/Edit IP address block</h3>
<form class='zenform' method="post" action="<?php echo \UNL\VisitorChat\Controller::$URLService->generateSiteURL("blocks/" . (($context->id)?$context->id . '/':'')  . "edit");?>" >
    <fieldset>
        <ul>
            <li>
                <label for="ip_address">Block IP address</label><br />
                <input type="text" name="ip_address" id="ip_address" value="<?php echo get_var('ip_address', $context);?>" required />
            </li>
            <li>
                <label for="time">For</label>
                <input type="text" name="time" id="time" value="<?php echo $context->getTimeLength();?>" required />
                <label for="time_units">Time Units</label>
                <select name="time_units" id="time_units">
                    <option value="hours" <?php echo ($context->getTimeUnit()=='hours')?'selected="selected"':'';?>>Hours</option>
                    <option value="days" <?php echo ($context->getTimeUnit()=='days')?'selected="selected"':'';?>>Days</option>
                </select>
            </li>
            <li>
                <label for="enabled">Status</label><br />
                <select name="status" id="status">
                    <option value="ENABLED" <?php echo (get_var('enabled', $context)=='ENABLED')?'selected="selected"':'';?>>Enabled</option>
                    <option value="DISABLED" <?php echo (get_var('enabled', $context)=='DISABLED')?'selected="selected"':'';?>>Disabled</option>
                </select>
            </li>
        </ul>
    </fieldset>
    <input type="submit" value="Submit" />
</form>