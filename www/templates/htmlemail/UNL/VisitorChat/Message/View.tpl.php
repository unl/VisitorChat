<?php
$message = str_replace("&lt;br /&gt;", "<br />",  $context->message);

echo $message . "<br /><span class=\"stamp\">from " . $context->getPoster()->getAlias() . "</span>";
