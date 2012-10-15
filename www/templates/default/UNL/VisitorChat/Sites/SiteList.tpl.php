<ul>
    <?php
    foreach ($context->sites as $site) {
        $role = "none";
        
        foreach ($site->getMembers() as $member) {
            if ($member->getUID() != \UNL\VisitorChat\User\Service::getCurrentUser()->uid) {
                continue;
            }
            
            $role = $member->getRole();
        }
        
        echo "<li>" . $site->getURL() . "
                 <ul>
                    <li>Your chat role: " . $role . "</li>
                    <li><a href='" . \UNL\VisitorChat\Controller::$URLService->generateSiteURL('site/' . $site->getURL()) . "'>View Details</a></li>
                 </ul>
             </li>";
    }
    ?>
</ul>