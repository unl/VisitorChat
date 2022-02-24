<?php
$ua = $context->parseUserAgent();
?>
<h2 class="dcf-txt-h3 dcf-mb-1 unl-cream"><?php echo $context->getClient()->name; ?></h2>
<p class="dcf-txt-xs dcf-mb-0">
    Using <strong><?php echo $ua->browser; ?></strong> on <strong><?php echo $ua->os; ?></strong>,
    <span class="address">IP Address: <?php echo $context->ip_address; ?> <a href="<?php echo \UNL\VisitorChat\Controller::$URLService->generateSiteURL("blocks/edit?ip_address=" . $context->ip_address) ?>" id="block_ip">(block this IP)</a></span>
</p>
