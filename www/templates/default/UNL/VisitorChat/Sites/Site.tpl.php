<ul>
    <li>Title: <?php echo $context->site->getTitle();?></li>
    <li>URL: <?php echo $context->site->getURL();?></li>
    <li>Support Email: <?php echo $context->site->getEmail();?></li>
    <li><a href='<?php echo \UNL\VisitorChat\Controller::$URLService->generateSiteURL('history/sites/' . $context->site->getURL()); ?>'>History</a></li>
</ul>

<h3>Members</h3>
<ul>
    <?php 
        foreach ($context->site->getMembers() as $member) {
            if (!$chatUser = \UNL\VisitorChat\User\Record::getByUID($member->getUID())) {
                echo "<li>" . $member->getUID() . " (" . $member->getRole() . ") [this member has not logged into the chat system yet]</li>";
                continue;
            }
            
            $alias = "";
            if (!empty($chatUser->alias)) {
                $alias = " (" . $chatUser->alias . ")";
            }
            
            echo "<li><a href='" . \UNL\VisitorChat\Controller::$URLService->generateSiteURL('users/' . $chatUser->id) . "'>" . $chatUser->name . "</a> $alias (" . $member->getRole() . ")</li>";
        }
    ?>
</ul>