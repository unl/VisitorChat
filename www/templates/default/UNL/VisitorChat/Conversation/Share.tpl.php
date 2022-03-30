<h2>Share this conversation</h2>
<form class="dcf-form" id="shareForm" name="share action="<?php UNL\VisitorChat\Controller::$URLService->generateSiteURL('conversation/' . $context->id . '/share', true)?>" method="POST">
    <fieldset class="dcf-m-0 dcf-p-0 dcf-b-0">
        <legend>Select sharing method</legend>
        <ul class="dcf-list-bare">
            <li><input class="dcf-input-control" type="radio" name="method" value="invite" checked="checked">Invite to conversation</li>
        </ul>
    </fieldset>
    <fieldset class="dcf-m-0 dcf-p-0 dcf-b-0">
        <legend>Select who you want to share with</legend>
        <fieldset>
            <?php
            //Display a list of all sites next.
            $list = \UNL\VisitorChat\Controller::$registryService->getAllSites();

            echo $savvy->render($list, 'UNL/VisitorChat/Conversation/ShareList.tpl.php');
            ?>
        </fieldset>
    </fieldset>
    <input class="dcf-btn dcf-btn-primary submit" type="submit" value="Submit">
</form>