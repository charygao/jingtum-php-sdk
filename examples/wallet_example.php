<?php
/*
 * PHP SDK example code
 * Showed how to use FinGate to create a new wallet,
 * active it, then check the balance of the 
 * new wallet.
 * Add the test of sending SWT from the new wallet
 * to the FinGate to make sure the address/secret
 * pair works fine.
 * Following methods in the class are tested:
 *  
 */
use JingtumSDK\AccountClass;
use JingtumSDK\FinGate;
use JingtumSDK\Wallet;
use JingtumSDK\APIServer;
use JingtumSDK\TumServer;
use JingtumSDK\Amount;
use JingtumSDK\PaymentOperation;

require_once 'lib/ConfigUtil.php';
require_once 'AccountClass.php';
require_once 'Wallet.php';
require_once 'FinGate.php';
require_once 'Server.php';
require_once 'Tum.php';
require_once 'PaymentOperation.php';

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

//Read in the test configuration and data
$test_data = readTestData("examples/test_data.json");



//Setup the API server using DEV server configurations.
//Check if the initialization is successful
//Then start the wallet operations.
$api_server = new APIServer();

if (is_object($api_server)){
//echo "\n==Set API Server Successful!==\n";
$api_server->setTest(true);


//Create the wallet and Fingate using the configuration files.
//Set the FinGate using info from the configuration file.
$fg1 = $test_data->DEV->wallet1;

print_r("=============Set FinGate Address==============\n");
$fin_gate = new FinGate($fg1->address, $fg1->secret);

//Set the test environment
$fin_gate->setTest(true);

//Set up the minimum active amount to active the Wallet
//if needed. Otherwise FinGate uses the default amount.
$fin_gate->setActivateAmount(25);

//Create the new wallet using the FinGate function
$wallet1 = $fin_gate->createWallet();
//print_r($wallet1);
echo "Get new wallet\n";
echo "Address:". $wallet1->getAddress()."\n";
echo "Secret:".$wallet1->getSecret()."\n";


//Setup the API server used in the FinGate
$res = $fin_gate->activeWallet($wallet1->getAddress());

//Wait for ledger close
sleep(10);

//display the results
echo "Check Balance\n";
$wallet1->setAPIServer($api_server);
$res = $wallet1->getBalance();
$src_val0 = displayBalances($res, 0);
}

payment_test:
//payback the FinGate for testing
//This need to create a wallet class
$wt3 = new Wallet($fg1->address, $fg1->secret);
$wt3->setTest(true);
$res = $wt3->getBalance();
//Should notice the change in the balances
$des_val0 = displayBalances($res, 0);

$pay_value = 0.1;
//$tong1 = new Tum('SWT');
//$payreq->setDestAmount($tong1->getTumAmount($pay_value));
//2. or use amount object
$amt1 = new Amount('SWT', '', $pay_value);
$payreq = new PaymentOperation($wallet1);
$payreq->setDestAddress($wt3->getAddress());//required
$payreq->setDestAmount($amt1->getAmount());

$payreq->setValidate('false');//optional, setup the syn mode, default is true

//3.2 Submit the payment operation
//submit the request
$res = $payreq->submit();

var_dump($res);
sleep(10);

echo "Make payment with $pay_value SWT\n";
$res = $wt3->getBalance();
echo $wt3->getAddress();
//Should notice the change in the balances
  $des_val1 = displayBalances($res, 0);
if ( ($des_val1 - $des_val0 ) == $pay_value )
  echo "Destination account change from $des_val0 to $des_val1\nSame as $pay_value\n";
else{
  echo "Destination account change from $des_val0 to $des_val1\nDiffernt from $pay_value\n";
}

return;//end of the program
?>
