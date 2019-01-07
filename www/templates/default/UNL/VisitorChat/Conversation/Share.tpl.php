<h2>Share this conversation</h2>
<form id='shareForm' name="share action="<?php UNL\VisitorChat\Controller::$URLService->generateSiteURL('conversation/' . $context->id . '/share', true)?>" method="POST">
    <fieldset>
        <legend class="dcf-legend">Select sharing method</legend>
        <ul class="dcf-list-bare">
            <li><input class="dcf-input-control" type='radio' name='method' value='invite' checked='checked' />Invite to conversation</li>
        </ul>
    </fieldset>
    
    <fieldset>
        <legend class="dcf-legend">Select who you want to share with</legend>
        <fieldset>
            <?php 
            //Display a list of all sites next.
            $list = \UNL\VisitorChat\Controller::$registryService->getAllSites();

            echo $savvy->render($list, 'UNL/VisitorChat/Conversation/ShareList.tpl.php');
            ?>
        </fieldset>
    </fieldset>
    
    <input class="dcf-btn dcf-btn-primary" type="submit" class='submit' value="Submit"/>
</form>