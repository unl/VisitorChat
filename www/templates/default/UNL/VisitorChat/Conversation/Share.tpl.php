<h2>Share this conversation</h2>
<form id='shareForm' name="share action="<?php UNL\VisitorChat\Controller::$URLService->generateSiteURL('conversation/' . $context->id . '/share', true)?>" method="POST">
    <fieldset>
        <legend>Select sharing method</legend>
        <ul>
            <li><input type='radio' name='method' value='invite' checked='checked' />Invite to conversation</li>
        </ul>
    </fieldset>
    
    <fieldset>
        <legend>Select who you want to share with</legend>
        <fieldset>
            <?php 
            //Display a list of all sites next.
            $list = \UNL\VisitorChat\Controller::$registryService->getAllSites();

            echo $savvy->render($list, 'UNL/VisitorChat/Conversation/ShareList.tpl.php');
            ?>
        </fieldset>
    </fieldset>
    
    <input type="submit" class='submit' value="Submit"/>
</form>