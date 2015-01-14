<?php
$message = str_replace("&lt;br /&gt;", "\n",  $context->message);

echo $message . "\n - from " . $context->getPoster()->getAlias();
