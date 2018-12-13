<?php
$ua = $context->parseUserAgent();
?>
<span class="client-name"><?php echo $context->getClient()->name; ?></span>
<p>
    Using <strong><?php echo $ua->browser; ?></strong> from a <strong><?php echo $ua->os; ?></strong>,
    <span class="address">IP Address: <?php echo $context->ip_address; ?> <a href="<?php echo \UNL\VisitorChat\Controller::$URLService->generateSiteURL("blocks/edit?ip_address=" . $context->ip_address) ?>" id="block_ip">(block this IP)</a></span>
</p>