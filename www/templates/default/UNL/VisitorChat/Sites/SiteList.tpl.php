<div class="dcf-grid-full dcf-grid-halves@sm dcf-grid-thirds@md dcf-col-gap-vw dcf-txt-center centered">
    <?php foreach ($context->sites as $site): ?>
        <?php
        $role = "none";
        foreach ($site->getMembers() as $member) {
            if ($member->getUID() != \UNL\VisitorChat\User\Service::getCurrentUser()->uid) {
                continue;
            }
            
            $role = $member->getRole();
        }

        $class = 'busy';
        if ($site->getAvailableCount()) {
            $class='available';
        }
        
        ?>
        <div>
          <div class='visual-island site-box <?php echo $class ?>'>
            <h2 class="force-wrap dcf-txt-md">
              <a href="<?php echo $site->getURL()  ?>"><?php echo $site->getTitle() ?></a>
            </h2>
            <a href="<?php echo \UNL\VisitorChat\Controller::$URLService->generateSiteURL('sites/site?url=' . urlencode($site->getRawObject()->getURL())) ?>">View Details</a>
            <p>
              Your chat role: <strong><?php echo $role ?></strong>
            </p>
          </div>
        </div>
    <?php endforeach; ?>
</div>
