<?php 
echo htmlspecialchars_decode($context->message) . "<br /><span class=\"stamp\">from " . $context->getPoster()->name . "</span>";
?>