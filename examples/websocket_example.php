<?php
/*
 * PHP SDK WebSocket 示例代码
 * @version 1.0.0
 * @author Zpli 
 * @copyright © 2016, Jingtum Incs. All rights reserved.
 * Test the Websocket communications
 */

//This will be need to use the Websocket client.
require_once 'vendor/autoload.php';

use JingtumSDK\Wallet;
use JingtumSDK\WebSocketServer;
use WebSocket\Client;

require_once 'Wallet.php';
require_once 'Server.php';

/***************************************/
// Main test program
//Read in the test configuration and data
/***************************************/
echo "***************************************\n*\n";
echo "Jingtum Test program\nWebsocket test 1.0\n*\n";
echo "***************************************\n\n";

$test_data = readTestData("examples/test_data.json");

if ( $test_data == false ){
   echo "Input test data is not valid\n";
   return;
}

//use a test wallet
$test_wallet2 = $test_data->DEV->wallet2;

//Setup the Fingate and Wallet for the test
$web_socket_server = new WebSocketServer();
$web_socket_server->setTest(true);

//$wallet = new Wallet('jfCiWtSt4juFbS3NaXvYV9xNYxakm5yP9S', 'snwjtucx9vEP7hCazriMbVz8hFiK9');
$wallet = new Wallet($test_wallet2->address, $test_wallet2->secret);

//Start testing
//connect to the websocket server
//then subscribe a wallet
//wait for the signals to come and display
//them
printf("\nConnect to web socket server\n");
$ret = $web_socket_server->connect();
echo "\n";
print_r($ret);

$ret = $web_socket_server->subscribe($wallet->getAddress(), $wallet->getSecret());
echo "\n";
print_r($ret);
echo "\nListening...\n";

//Listening to the web socket for the incoming messages
//on the wallet
$wait_sec = 3;
for ($i = 1; $i <= 3; $i ++){
  sleep($wait_sec);
  //program stops here if no input message received.
  $ret = $web_socket_server->setTxHandler();
  printf("Message %d: %s\n",$i, $ret);
}

$ret = $web_socket_server->unsubscribe($wallet->getAddress());

print_r("close connection\n");
$ret = $web_socket_server->disconnect();


?>
