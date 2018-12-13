<?php
$rules = array();

$rules['text'][] = function($spam) {
    if (strpos($spam, 'spam') !== false) {
        return true;
    }
};

return $rules;