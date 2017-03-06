<?php
/*
 * PHP SDK example code
 * Showed how to use FinGate to create a new wallet,
 * active it, then check the balance of the 
 * new wallet.
 * Add the test of sending SWT from the new wallet
 * to the FinGate to make sure the address/secret
 * pair works fine.
 * 
 */
use JingtumSDK\AccountClass;
use JingtumSDK\FinGate;
use JingtumSDK\Wallet;
use JingtumSDK\APIServer;
use JingtumSDK\TumServer;
use JingtumSDK\Amount;
use JingtumSDK\SettingsOperation;

require_once 'lib/ConfigUtil.php';
require_once 'AccountClass.php';
require_once 'Wallet.php';
require_once 'FinGate.php';
require_once 'Server.php';
require_once 'Tum.php';
require_once 'SettingsOperation.php';


//A simple function to display the 
//number of payments returned
function displaySettings($res){
if ( $res['success'] == true ){
  if ( is_array($res['settings']) ){
    $num = count($res['settings']); 
    echo "At least $num settings were found in this address\n";
    // echo $res['settings']['account']."\n";
    // echo $res['settings']['domain']."\n";
    var_dump($res['settings']);
  }
}else
{
  echo "\nError in payment list\n";
  print_r($res);

}
}


//Example call back function
function call_back_func($res)
{
  echo 'In the call back function'."\n";
  if($res['success'] == true){
    echo "submit successfully\n";
    var_dump($res);
  }
  else
    var_dump($res);
}

/*************************************************/
// Main test program
//Read in the test configuration and data
/*************************************************/
echo "======================================\n";
echo "*\n* Jingtum Test program\n* Wallet settings test 1.0\n*\n";
echo "======================================\n";

//Read in the test configuration and data
$test_data = readTestData("examples/test_data.json");

$fg1 = $test_data->DEV->wallet1;
$test_wallet2 = $test_data->DEV->wallet2;
$test_wallet3 = $test_data->DEV->wallet3;

$test_cny = $test_data->DEV->CNYAmount1;


//Set the FinGate using info from the configuration file.
print_r("=============Set FinGate mode==============\n");
$fin_gate = FinGate::getInstance();

//Set up the FinGate account using secret
$fin_gate->setAccount($fg1->secret, $fg1->address);

//Set the test environment
$fin_gate->setMode(FinGate::DEVELOPMENT);



echo "Setup wallet for testing\n";
$wallet2 = new Wallet($test_wallet2->secret, $test_wallet2->address);

//Get the settings from the API server
displaySettings($wallet2->getSettings());

//check the wallet settings


echo "======================================\n";
echo "*\n* Set new attributes test program\n";
echo "======================================\n";

$setreq = new SettingsOperation($wallet2);

//11 set attributes
//string
/*$setreq->setDomain("A test domain".time());
$setreq->setEmail("1123abc@jingtum.com".time());
$setreq->setWalletLocator("Test locator".time());

$setreq->setNickName("MyWallet".time());
//numberic
//boolean
$setreq->setRequireDestinationTag(true);
$setreq->setRequireAuthorization(true);
$setreq->setDisallowSwt(true);
//$setreq->setDisableMaster(true);
*/
$setreq->setMessageKey("");//Keylocator".time());
#$setreq->setTransferRate(1.10);
//$setreq->setWalletSize(10);

//Submit the payment operation
$setreq->submit('call_back_func');


//Get the settings from the API server
displaySettings($wallet2->getSettings());


return;//end of the program
?>
