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


require_once 'lib/ConfigUtil.php';
require_once 'AccountClass.php';
require_once 'Wallet.php';
require_once 'FinGate.php';
require_once 'Server.php';
require_once 'Tum.php';

//Display the balance in the account
function displayBalances($res, $j = 0){
  $return_value = -999;
  if ( $res['success'] == true ){
  if ( is_array($res['balances']) ){
    for ( $i = 0; $i < count($res['balances']); $i ++){
      $code = $res['balances'][$i]['currency'];
      $value = $res['balances'][$i]['value'];
      if ( $i == $j )
        $return_value = $value;
      echo "\n$code : $value";
    }
  }
}
else
{
  echo "\nError in balances \n";
  print_r($res);
}
  echo "\n";
  return $return_value;
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

/***************************************/
// Main test program
//Read in the test configuration and data
/***************************************/
echo "======================================\n";
echo "*\n* Jingtum Test program\n* Tum issuing test 1.0\n*\n";
echo "======================================\n";

//Read in the test configuration and data
$test_data = readTestData("examples/test_data.json");

$fg1 = $test_data->DEV->fingate1;
$test_wallet2 = $test_data->DEV->wallet2;



//Set the FinGate using info from the configuration file.
print_r("=============Set FinGate mode==============\n");
$fin_gate = FinGate::getInstance();

//Set up the FinGate account using secret
$fin_gate->setAccount($fg1->secret, $fg1->address);

//Set the test environment
$fin_gate->setMode(FinGate::DEVELOPMENT);


print_r("======Set FinGate Token&Sign Key======\n");
//Set the FinGate parameters obtained from Jingtum Company.
$fin_gate->setToken($fg1->custom);
$fin_gate->setKey($fg1->sign_key);

echo "Setup a wallet for testing\n";
$wallet1 = new Wallet($test_wallet2->secret, $test_wallet2->address);

$res = $wallet1->getBalance();
$src_val0 = displayBalances($res, 2);


issue_tum_test:
echo "======================================\n";
echo "*\n* Issue tum test program\n";
echo "======================================\n";

//Issue the tum to the wallet1 
$uuid = "JTtestTum".time();
$res = $fin_gate->issueCustomTum($fg1->tum2, 20.17, $uuid, $wallet1->getAddress());
var_dump($res);

$res = $wallet1->getBalance();
$src_val1 = displayBalances($res, 2);

$diff_val = $src_val1 - $src_val0;

echo "Balance changed: $diff_val\n";

$res = $fin_gate->queryIssue($uuid);

var_dump($res);
printf("query Tum:......\n");

$res = $fin_gate->queryCustomTum($fg1->tum2);

var_dump($res);




return;//end of the program
?>
