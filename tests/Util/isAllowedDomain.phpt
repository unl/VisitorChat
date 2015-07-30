--TEST--
Util: test isAllowedDomain()
--FILE--
<?php

require_once __DIR__ . '/../loadConfig.php';

$allowed_domains = array(
    'test.com',
    'test.edu'
);

$boolarray = Array(false => 'false', true => 'true');

echo $boolarray[\UNL\VisitorChat\Util::isAllowedDomain('http://test.com', $allowed_domains)] . PHP_EOL;
echo $boolarray[\UNL\VisitorChat\Util::isAllowedDomain('https://test.com', $allowed_domains)] . PHP_EOL;
echo $boolarray[\UNL\VisitorChat\Util::isAllowedDomain('http://test.com/stuff.php?test', $allowed_domains)] . PHP_EOL;
echo $boolarray[\UNL\VisitorChat\Util::isAllowedDomain('http://bad.com', $allowed_domains)] . PHP_EOL;
echo $boolarray[\UNL\VisitorChat\Util::isAllowedDomain('http://bad.com/stuff.php?test', $allowed_domains)] . PHP_EOL;
echo $boolarray[\UNL\VisitorChat\Util::isAllowedDomain('http://bad.com/stuff.php?test=http://test.com', $allowed_domains)] . PHP_EOL;
echo $boolarray[\UNL\VisitorChat\Util::isAllowedDomain('http://subdomain.test.com', $allowed_domains)] . PHP_EOL;
echo $boolarray[\UNL\VisitorChat\Util::isAllowedDomain('http://test.com.bad', $allowed_domains)] . PHP_EOL;
?>
===DONE===
--EXPECT--
true
true
true
false
false
false
true
false
===DONE===
