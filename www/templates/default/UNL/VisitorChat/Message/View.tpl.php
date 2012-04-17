<?php 
echo htmlspecialchars_decode($context->message) . "<br /><span class='timestamp'>" . date("g:i:s A", strtotime($context->date_created)) . "</span><span class='stamp'>from " . $context->getPoster()->name . "</span>";
?>