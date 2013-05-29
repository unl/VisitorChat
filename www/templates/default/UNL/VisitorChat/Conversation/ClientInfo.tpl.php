<?php
$ua = $context->parseUserAgent();
?>
<span><?php echo $context->getClient()->name; ?></span>
<p>is using <strong><?php echo $ua->browser; ?></strong> from a <strong><?php echo $ua->os; ?></strong></p>
<p class="address">IP Address: <?php echo $context->ip_address; ?> <a href="<?php echo \UNL\VisitorChat\Controller::$URLService->generateSiteURL("blocks/edit?ip_address=" . $context->ip_address) ?>">(block this IP)</a></p>
