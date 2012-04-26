<?php 
$message = str_replace("&lt;br /&gt;", "<br />", $context->message);

echo $message . "<br /><span class='timestamp'>" . date("g:i:s A", strtotime($context->date_created)) . "</span><span class='stamp'>from " . $context->getPoster()->name . "</span>";
?>