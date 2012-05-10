--TEST--
APPLICATION TEST - Client Login: test POST with no operators available
--FILE--
<?php
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config.inc.php';

$DBHelper->installDB("FreshDB.sql");

//Set up server enviroment.
$_SERVER['REQUEST_URI']  = "/clientLogin";
$_SERVER['QUERY_STRING'] = "";

//Request json output
$_GET = array('format'=>'json');

//Send post data
$_POST = array('initial_url'       =>'http://unl.edu',
               'initial_pagetitle' => 'UNL',
               'message'           =>'hello?');

$app = new \UNL\VisitorChat\Controller($_GET);

$result = $app->render();

$result = json_decode($result, true);

echo "latest_message_id: " . $result['latest_message_id'] . PHP_EOL;

echo "status: " . $result['status'] . PHP_EOL;

echo "conversation_id: " . $result['conversation_id'] . PHP_EOL;

if (isset($result['phpssid'])) {
    echo "phpssid: 1" . PHP_EOL;
}

?>
===DONE===
--EXPECT--
latest_message_id: 0
status: EMAILED
conversation_id: 1
phpssid: 1
===DONE===
