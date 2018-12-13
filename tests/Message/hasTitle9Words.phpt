--TEST--
Message\Record: test hasTitle9Words()
--FILE--
<?php

require_once __DIR__ . '/../loadConfig.php';

$original = \UNL\VisitorChat\Controller::$title9Words;

\UNL\VisitorChat\Controller::$title9Words = array('flag1', 'flag2');

$message = new \UNL\VisitorChat\Message\Record();

//has no words
$message->message = 'This is a message flag1no';
echo ($message->hasTitle9Words())?'yes':'no';
echo PHP_EOL;

//has words
$message->message = 'This is a message with a flag1.';
echo ($message->hasTitle9Words())?'yes':'no';
echo PHP_EOL;

//Reset
\UNL\VisitorChat\Controller::$title9Words = $original;

?>
===DONE===
--EXPECT--
no
yes
===DONE===
