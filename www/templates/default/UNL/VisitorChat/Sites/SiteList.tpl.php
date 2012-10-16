    <?php
    foreach ($context->sites as $site) {
        $role = "none";
        
        foreach ($site->getMembers() as $member) {
            if ($member->getUID() != \UNL\VisitorChat\User\Service::getCurrentUser()->uid) {
                continue;
            }
            
            $role = $member->getRole();
        }
        echo "<div class='zenbox primary grid4'>" .
         "<h3><a href='" . $site->getURL() . "'>" . $site->getTitle() . "</a>".
		 "<a class='zen-header-link' href='" . \UNL\VisitorChat\Controller::$URLService->generateSiteURL('sites/' . $site->getURL()) . "'>View Details</a></h3>
                 <ul>
                    <li>Your chat role: " . $role . "</li>" .
                 "</ul></div>";
    }
    ?>