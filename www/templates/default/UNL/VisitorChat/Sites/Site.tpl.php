<div class="grid3 first">&nbsp;</div>
<div class="zenbox primary grid6">
<h3><a href="<?php echo $context->site->getURL();?>"><?php echo $context->site->getTitle();?></a>
<?php
    if ($context->userManagesSite(\UNL\VisitorChat\User\Service::getCurrentUser()) || \UNL\VisitorChat\User\Service::getCurrentUser()->isAdmin()) {
        echo "<a class='zen-header-link' href='" . \UNL\VisitorChat\Controller::$URLService->generateSiteURL('sites/statistics?url=' . urlencode($context->site->getRawObject()->getURL())) . "'>Statistics</a>";
    }
    if ($context->userManagesSite(\UNL\VisitorChat\User\Service::getCurrentUser()) || \UNL\VisitorChat\User\Service::getCurrentUser()->isAdmin()) {
        echo "<a class='zen-header-link' href='" . \UNL\VisitorChat\Controller::$URLService->generateSiteURL('sites/history?url=' . urlencode($context->site->getRawObject()->getURL())) . "'>History</a>";
    }
    ?></h3>

<ul>
    <li>Support Email: <a href='mailto:<?php echo $context->site->getEmail();?>'><?php echo $context->site->getEmail();?></a></li>
</ul>

<h4>Members</h4>
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
            
            $history_link = '';
            if (\UNL\VisitorChat\User\Service::getCurrentUser()->managesSite($context->site->getURL()) || \UNL\VisitorChat\User\Service::getCurrentUser()->isAdmin()) {
                $history_link = "<a href='" . \UNL\VisitorChat\Controller::$URLService->generateSiteURL('sites/history?url=' . urlencode($context->site->getURL())) . "&users_id=" . $chatUser->id ."'>History</a>";
            }
            
            echo "<li class='" . strtolower($chatUser->getStatus()->status) . "'><a href='" . \UNL\VisitorChat\Controller::$URLService->generateSiteURL('users/' . $chatUser->id) . "'>" . $chatUser->name . "</a> $alias (" . $member->getRole() . ") $history_link </li>";
        }
    ?>
</ul>
</div>