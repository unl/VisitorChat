--TEST--
OperatorRegistry: test getMembers()
--FILE--
<?php

require_once __DIR__ . '/../../config.inc.php';
require_once __DIR__ . '/MockDriver.php';

$registry = new MockDriver();

$members = $registry->getMembers('http://www.unl.edu/');

foreach ($members as $site_member) {
    /* @var $site_member UNL\VisitorChat\OperatorRegistry\SiteMemberInterface  */
    echo $site_member->getMember() . PHP_EOL;
    echo $site_member->getRole() . PHP_EOL;
    echo $site_member->getSite() . PHP_EOL;
}

?>
===DONE===
--EXPECT--
bbieber2
manager
http://www.unl.edu/
manager2
manager
http://www.unl.edu/
===DONE===
