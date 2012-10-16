<p class="sub-head">Alias: <?php echo $context->getAlias(); ?></p>

<div class="zenbox primary grid4">
<h3>Sites</h3>
<ul>
    <?php
        foreach (\UNL\VisitorChat\Controller::$registryService->getSitesForUser($context->uid) as $site) {
            $role = "none";
            
            foreach ($site->getMembers() as $member) {
                if ($member->getUID() != $context->uid) {
                    continue;
                }
                
                $role = $member->getRole();
            }
            
            echo "<li><a href='" . \UNL\VisitorChat\Controller::$URLService->generateSiteURL('sites/' . $site->getURL()) . "'>" . $site->getTitle() . "</a> ($role)</li>";
        }
    ?>
</ul>
</div>