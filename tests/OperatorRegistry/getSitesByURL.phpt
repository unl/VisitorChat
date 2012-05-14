--TEST--
OperatorRegistry: test getMembers()
--FILE--
<?php

require_once __DIR__ . '/../loadConfig.php';
require_once __DIR__ . '/MockRegistryDriver.php';

$registry = new MockRegistryDriver();

$sites = $registry->getSitesByURL('http://www.unl.edu/');

foreach ($sites as $site) {
    foreach ($site->getMembers() as $site_member) {
        /* @var $site_member UNL\VisitorChat\OperatorRegistry\SiteMemberInterface  */
        echo $site_member->getUID() . PHP_EOL;
        echo $site_member->getRole() . PHP_EOL;
        echo $site_member->getSite() . PHP_EOL;
    }
}

?>
===DONE===
--EXPECT--
bbieber2
operator
http://www.unl.edu/
s-mfairch4
manager
http://www.unl.edu/
===DONE===
