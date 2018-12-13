<ul>
    <li><a href="<?php echo \UNL\VisitorChat\Controller::$URLService->generateSiteURL('manage');?>">Dashboard</a></li>
    <li><a href="<?php echo \UNL\VisitorChat\Controller::$URLService->generateSiteURL('history');?>">History</a>
        <ul>
            <li><a href="<?php echo \UNL\VisitorChat\Controller::$URLService->generateSiteURL('history');?>">My History</a></li>
            <li><a href="<?php echo \UNL\VisitorChat\Controller::$URLService->generateSiteURL('history/sites');?>">Managed Site History</a></li>
        </ul>
    </li>
    <?php if ($user = \UNL\VisitorChat\User\Service::getCurrentUser()): ?>
    <li>
        <a href="<?php echo \UNL\VisitorChat\Controller::$URLService->generateSiteURL('sites');?>">My Sites</a>
    </li>
    <li>
        <a href="<?php echo \UNL\VisitorChat\Controller::$URLService->generateSiteURL('blocks');?>">Blocked IPs</a>
        <ul>
            <li>
                <a href="<?php echo \UNL\VisitorChat\Controller::$URLService->generateSiteURL('blocks/edit');?>">Block an IP address</a>
            </li>
        </ul>
    </li>
    <li>
        <a href="<?php echo \UNL\VisitorChat\Controller::$URLService->generateSiteURL('user/settings');?>"><?php echo \UNL\VisitorChat\User\Service::getCurrentUser()->name;?></a>
        <ul>
            <li><a href="<?php echo \UNL\VisitorChat\Controller::$URLService->generateSiteURL('user/settings');?>">Settings</a></li>
            <li><a href="<?php echo \UNL\VisitorChat\Controller::$url?>logout" title="Log Out">Logout</a></li>
        </ul>
    </li>
    <?php endif; ?>
    <li>
        <a href="<?php echo \UNL\VisitorChat\Controller::$URLService->generateSiteURL('faq');?>">FAQ</a>
    </li>
</ul>
