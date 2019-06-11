<div class="site-details">
    <dl>
        <dt>Site URL</dt>
        <dd><a href="<?php echo $context->site->getURL();?>"><?php echo $context->site->getURL();?></a></dd>
        <dt>Support Email</dt>
        <dd><a href='mailto:<?php echo $context->site->getEmail();?>'><?php echo $context->site->getEmail();?></a></dd>
        <?php if ($context->currentUserHasManagerAccess()): ?>
            <dt>Options</dt>
            <dd>
                <ul>
                    <li>
                        <a class='zen-header-link' href='<?php echo \UNL\VisitorChat\Controller::$URLService->generateSiteURL('sites/statistics?url=' . urlencode($context->site->getRawObject()->getURL())) ?>'>Statistics</a>
                    </li>
                    <li>
                        <a class='zen-header-link' href='<?php echo \UNL\VisitorChat\Controller::$URLService->generateSiteURL('sites/history?url=' . urlencode($context->site->getRawObject()->getURL())) ?>'>History</a>
                    </li>
                </ul>
            </dd>
        <?php endif; ?>
        <dt>Members</dt>
        <dd>
            <ul>
                <?php foreach ($context->site->getMembers() as $member): ?>
                    <?php $chatUser = \UNL\VisitorChat\User\Record::getByUID($member->getUID()); ?>
                    <li>
                        <?php if (!$chatUser): ?>
                            <?php echo $member->getUID() ?> (<?php echo $member->getRole() ?>) [this member has not logged into the chat system yet]
                            <?php continue; //we don't know anything else about this user, so move to the next one ?>
                        <?php endif; ?>
                        <span class="user-status <?php echo strtolower($chatUser->getStatus()->status) ?>"></span>
                        <span class="dcf-sr-only">(<?php echo strtolower($chatUser->getStatus()->status) ?>)</span>
                        <?php
                        $alias = "";
                        if (!empty($chatUser->alias)) {
                            $alias = " (" . $chatUser->alias . ")";
                        }
                        ?>

                        <a href='<?php echo \UNL\VisitorChat\Controller::$URLService->generateSiteURL('users/' . $chatUser->id) ?>'><?php echo $chatUser->name ?></a>
                        
                        <?php if (!empty($chatUser->alias)): ?>
                            <?php echo $chatUser->alias ?>
                        <?php endif; ?>
                        
                        <span class="user-role">(<?php echo $member->getRole() ?>)</span>
                        
                        <?php if ($context->currentUserHasManagerAccess()): ?>
                            - <a href='<?php echo \UNL\VisitorChat\Controller::$URLService->generateSiteURL('sites/history?url=' . urlencode($context->site->getRawObject()->getURL())) . '&users_id=' . $chatUser->id ?>'>view history</a>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
            <?php
              $editMembersLink = $context->site->getEditSiteMembersLink();
              if (!empty($editMembersLink)) {
            ?>
            <a class="dcf-btn dcf-btn-primary dcf-mb-2 dcf-txt-decor-none dcf-txt-3xs" href="<?php echo $editMembersLink; ?>">Edit Members</a>
            <?php } ?>
        </dd>
    </dl>
</div>
