<?php
/*
 * PHP SDK example code
 * Test all three types of server init
 * and set test mode.
 *  
 * 
 */
use JingtumSDK\TumServer;
use JingtumSDK\WebSocketServer;
use JingtumSDK\APIServer;

require_once 'lib/ConfigUtil.php';
require_once 'Server.php';

//Read in the test configuration and data
//$test_data = readTestData("examples/test_data.json");

$api_server = new APIServer();
$api_server->setTest(true);
$api_server->setTest(false);

$ws = new WebSocketServer();
$ws->setTest(true);
$ws->setTest(false);

$ts = new TumServer();
$ts->setTest(false);
$ts->setTest(true);

return;

?>
