<?php
if (false == headers_sent()
    && $code = $context->getCode()) {
    header('HTTP/1.1 '.$code.' '.$context->getMessage());
    header('Status: '.$code.' '.$context->getMessage());
}

$page->addScriptDeclaration("WDN.initializePlugin('notice');");
?>
<div class="dcf-notice dcf-notice-warning" hidden data-no-close-button>
    <h2>Whoops! Sorry, there was an error:</h2>
    <div><p><?php echo $context->getCode(); ?>: <?php echo $context->getMessage(); ?></p></div>
</div>
