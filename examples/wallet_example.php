<?php
/*
 * PHP SDK example code
 * Showed how to use FinGate to create a new wallet,
 * active it, then make payment to the new wallet.
 *  
 * 
 */
use JingtumSDK\FinGate;
use JingtumSDK\Wallet;
use JingtumSDK\APIServer;
use JingtumSDK\TumServer;

require_once 'lib/ConfigUtil.php';
require_once 'FinGate.php';
require_once 'Server.php';

//Read in the test configuration and data
$test_data = readTestData("examples/test_data.json");

//Create the wallet and Fingate using the configuration files.
//Set the FinGate using info from the configuration file.
$fg1 = $test_data->DEV->fingate1;
print_r("=============Get FinGate Address==============\n");
$fin_gate = new FinGate($fg1->address, $fg1->secret);
printf("%s\n",$fin_gate->getAddress());
printf("%s\n",$fin_gate->getSecret());

//Create the new wallet using the FinGate function
$wallet1 = $fin_gate->createWallet();
echo "Get new wallet\n";
echo "Address: $wallet1->getAddress()\n";
echo "Secret: $wallet1->getSecret()\n";

//Setup the API server using DEV server configurations.
//Check if the initialization is successful
//Then start the wallet operations.
$api_server = new APIServer();
if (is_object($api_server)){
echo "\n==Set API Server Successful!==\n";
$api_server->setTest(true);

//Active the new wallet, set up the minimum active amount
//if needed. Otherwise FinGate uses the default amount.
$fin_gate->setActivateAmount(30);
//$res = $api_server->submitRequest($fin_gate->activeWallet($wallet1->getAddress()));
$fin_gate->setAPIServer($api_server);
$res = $fin_gate->activeWallet($wallet1->getAddress());

//display the results
var_dump($res);
}


return;//end of the program
?>
