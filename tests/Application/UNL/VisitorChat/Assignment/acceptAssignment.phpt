--TEST--
APPLICATION TEST - Accept Assignment: test POST
--FILE--
<?php
require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/loadConfig.php';

$DBHelper->installDB("pendingAssignment.sql");

//Set up server enviroment.
$_SERVER['REQUEST_URI']  = "/assignment/1/edit";
$_SERVER['QUERY_STRING'] = "";

//Request json output
$_GET = array('format'=>'json');

//Send post data
$_POST = array('status'       =>'ACCEPTED',);

session_start();

$_SESSION['id'] = 2;

$app = new \UNL\VisitorChat\Controller($_GET);
$app->run();
?>
===DONE===
--EXPECT--
Location: http://visitorchattest.com/success?format=json
===DONE===
