<form method="get" action="<?php echo \UNL\VisitorChat\Controller::$URLService->generateSiteURL("blocks");?>" >
    <fieldset>
        <legend class="dcf-legend">Filters</legend>
        <ul class="dcf-list-bare">
            <li>
                <label for="ip_address">IP address:</label>
                <input class="dcf-input-text" type="text" name="ip_address" id="ip_address" value="<?php echo (isset($_GET['ip_address']))?$_GET['ip_address']:'' ?>" />

                <label class="dcf-label" for="enabled">Current State:</label>
                <select class="dcf-input-select" name="state" id="state">
                    <option value="active" <?php echo (isset($_GET['state']) && $_GET['state'] == 'active')?'selected="selected"':'';?>>Active</option>
                    <option value="inactive" <?php echo (isset($_GET['state']) && $_GET['state'] == 'inactive')?'selected="selected"':'';?>>Inactive</option>
                    <option value="all" <?php echo (isset($_GET['state']) && $_GET['state'] == 'all')?'selected="selected"':'';?>>All</option>
                </select>
                <input class="dcf-btn dcf-btn-primary" type="submit" value="Submit" />
            </li>
        </ul>
    </fieldset>
</form>

<?php if (count($context)): ?>
    <h2>Results</h2>
    <ul>
        <?php foreach ($context as $block): ?>
            <li>
                <a href="<?php echo $block->getEditURL() ?>"><?php echo $block->ip_address ?></a> 
                ends at <?php echo $block->block_end ?>
                by <?php echo $block->getUser()->name ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
