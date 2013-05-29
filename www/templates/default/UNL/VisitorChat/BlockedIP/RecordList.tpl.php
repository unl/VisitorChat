<script type="text/javascript">
    //<![CDATA[
    WDN.jQuery(document).ready(function(){
        WDN.initializePlugin('zenform');
        WDN.jQuery('.datepicker').datepicker();
    });
    //]]>
</script>

<h3 class='zenform'>Filters</h3>
<form class='zenform' method="get" action="<?php echo \UNL\VisitorChat\Controller::$URLService->generateSiteURL("blocks");?>" >
    <fieldset>
        <ul>
            <li>
                <label for="ip_address">IP address:</label>
                <input type="text" name="ip_address" id="ip_address" value="<?php echo (isset($_GET['ip_address']))?$_GET['ip_address']:'' ?>" />

                <label for="enabled">Current State:</label>
                <select name="state" id="state">
                    <option value="active" <?php echo (isset($_GET['state']) && $_GET['state'] == 'active')?'selected="selected"':'';?>>Active</option>
                    <option value="inactive" <?php echo (isset($_GET['state']) && $_GET['state'] == 'inactive')?'selected="selected"':'';?>>Inactive</option>
                    <option value="all" <?php echo (isset($_GET['state']) && $_GET['state'] == 'all')?'selected="selected"':'';?>>All</option>
                </select>
                <input type="submit" value="Submit" />
            </li>
        </ul>
    </fieldset>
</form>

<ul>
    <?php
    foreach ($context as $block) {
        ?>
        <li>
            <a href="<?php echo $block->getEditURL() ?>"><?php echo $block->ip_address ?></a> 
            ends at <?php echo $block->block_end ?>
            by <?php echo $block->getUser()->name ?>
        </li>
        <?php
    }
    ?>
</ul>