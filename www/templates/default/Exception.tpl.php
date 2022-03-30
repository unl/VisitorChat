<?php
if (false == headers_sent()
    && $code = $context->getCode()) {
    header('HTTP/1.1 '.$code.' '.$context->getMessage());
    header('Status: '.$code.' '.$context->getMessage());
}

$page->addScriptDeclaration("WDN.initializePlugin('notice');");
?>
<div class="wdn_notice alert">
    <div class="message">
        <h4>Whoops! Sorry, there was an error:</h4>
        <p><?php echo $context->getCode(); ?>: <?php echo $context->getMessage(); ?></p>
    </div>
</div>