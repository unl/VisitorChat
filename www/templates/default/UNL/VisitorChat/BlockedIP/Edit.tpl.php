<?php
function get_var($var, $context) {
    if (isset($context->$var)) {
        return $context->$var;
    }

    return null;
}
?>

<?php if ($context->id): ?>
    <ul>
        <li>Originally Created by <?php echo $context->getUser()->name;?></li>
    </ul>
<?php endif; ?>

<form class="dcf-form" method="post" action="<?php echo \UNL\VisitorChat\Controller::$URLService->generateSiteURL("blocks/" . (($context->id)?$context->id . '/':'')  . "edit");?>" >
    <ul class="dcf-list-bare">
        <li>
            <label class="dcf-legend" for="ip_address">Block IP address</label><br />
            <input class="dcf-input-text" type="text" name="ip_address" id="ip_address" value="<?php echo get_var('ip_address', $context);?>" required />
        </li>
        <li>
            <label class="dcf-label" for="time">For</label>
            <input type="text" name="time" id="time" value="<?php echo $context->getTimeLength();?>" required />
            <label class="dcf-label" for="time_units">Time Units</label>
            <select class="dcf-input-select" name="time_units" id="time_units">
                <option value="hours" <?php echo ($context->getTimeUnit()=='hours')?'selected="selected"':'';?>>Hours</option>
                <option value="days" <?php echo ($context->getTimeUnit()=='days')?'selected="selected"':'';?>>Days</option>
            </select>
        </li>
        <li>
            <label class="dcf-label" for="enabled">Status</label><br />
            <select class="dcf-input-select" name="status" id="status">
                <option value="ENABLED" <?php echo (get_var('enabled', $context)=='ENABLED')?'selected="selected"':'';?>>Enabled</option>
                <option value="DISABLED" <?php echo (get_var('enabled', $context)=='DISABLED')?'selected="selected"':'';?>>Disabled</option>
            </select>
        </li>
    </ul>
    <input class="dcf-btn dcf-btn-primary dcf-mt-6" type="submit" value="Submit" />
</form>