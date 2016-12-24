<?php
/*
 * PHP SDK example code
 * Showed how make payments between two accounts
 * with SWT, CURRENCY, and Tum
 * Require test data set
 * test_data.json 
 */
use JingtumSDK\FinGate;
use JingtumSDK\Wallet;
//use JingtumSDK\TumServer;
use JingtumSDK\Tum;
use JingtumSDK\Amount;
use JingtumSDK\APIServer;
use JingtumSDK\PaymentOperation;

require_once 'lib/ConfigUtil.php';
require_once 'FinGate.php';
require_once 'Server.php';
require_once 'Wallet.php';
require_once 'Operation.php';
require_once 'Tum.php';

//Display the return of the balances
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

if ( $test_data == false ){
   echo "Input test data is not valid\n";
   return;
}

/***************************************/
//Step 1.
//Setup the API server, use test environment
$api_server = new APIServer();
$api_server->setTest(true);

/***************************************/
//Step 2.
//Set up two wallets
$test_wallet2 = $test_data->DEV->wallet2;

src_account:
$wt0 = new Wallet($test_wallet2->address, $test_wallet2->secret);
if ( $wt0->setAPIServer($api_server)){
  $res = $wt0->getBalance();
  echo $wt0->getAddress();
  //Get the src value before payment for validation
  $src_val0 = displayBalances($res, 0);

}
else
  echo 'Error in initing Wallet Server';
/*$paylist = $wt0->getPaymentList();
print_r($paylist);
return;*/
dest_account:
$my_wallet2 = $test_data->DEV->wallet3;
$wt2 = new Wallet($my_wallet2->address, $my_wallet2->secret);
//Need to setup the api server
//display the balances
if ( $wt2->setAPIServer($api_server)){

  $res = $wt2->getBalance();
  echo $wt2->getAddress();
  $des_val0 = displayBalances($res, 0);
}

/***************************************/
//Step 3.
//Make the SWT payment
//3.1 Create the payment operation 
//Make payment from wallet0 to wallet2 using SWT
//Building a payment operation
$payreq = new PaymentOperation($wt0->getAddress());
$payreq->setSrcSecret($wt0->getSecret());

$payreq->setDestAddress($wt2->getAddress());//required

goto CNY_payment;

SWT_payment:
//Create the amount
//1. use tum object to create one
$pay_value = 1.0;
//$tong1 = new Tum('SWT');
//$payreq->setDestAmount($tong1->getTumAmount($pay_value));
//2. or use amount object 
$amt1 = new Amount('SWT', '', $pay_value);
$payreq->setDestAmount($amt1->getAmount());

$payreq->setValidate('false');//optional, setup the syn mode, default is true
$payreq->setResourceID($api_server->getClientResourceID());//required

//3.2 Submit the payment operation 
//submit the request
$res = $api_server->submitRequest($payreq->build(), $wt2->getAddress(), $wt2->getSecret());
echo "************Make payment with $pay_value SWT***************\n";
print_r($res);
echo "***************************\n";

//3.3.
//need to wait until the blockchain ledger close, usually 5-10 seconds
sleep(10);

//3.4
//check the balance after the payment
$res = $wt2->getBalance();
echo $wt2->getAddress();
//Should notice the change in the balances
  $des_val1 = displayBalances($res, 0);
if ( ($des_val1 - $des_val0 ) == $pay_value ) 
  echo "Destination account change from $des_val0 to $des_val1\nSame as $pay_value\n";
else{
  echo "Destination account change from $des_val0 to $des_val1\nDiffernt from $pay_value\n";
}


/***************************************/
//Step 4.
//Make the CNY payment
//4.1 Create the payment amount and add to the operation
CNY_payment:
$test_cny = $test_data->DEV->CNYAmount1;
$pay_value = 1.0;
//
echo "************Make payment with $pay_value CNY***************\n";
//create the new tum amount object
$amt1 = new Amount($test_cny->currency, $test_cny->issuer, $pay_value);
$payreq->setDestAmount($amt1->getAmount());

//4.2 Submit the payment operation
//submit the request

//get the blances of the destination wallet
$res = $wt2->getBalance();
//notice the CNY is 4th currency in the test dest wallet
$des_val0 = displayBalances($res, 4);


$payreq->setValidate('false');//optional, setup the syn mode, default is true
$payreq->setResourceID($api_server->getClientResourceID());//required

$res = $api_server->submitRequest($payreq->build(), $wt2->getAddress(), $wt2->getSecret());

//wait for the close of ledger
echo 'Wait for ledger closing.';
for ($i = 0; $i<10; $i++){
    echo '.';
sleep(1);
}
echo "\n";
//Should notice the change in the balances
$res = $wt2->getBalance();
$des_val1 = displayBalances($res, 4);

if ( ($des_val1 - $des_val0 ) == $pay_value )
  echo "Destination account change from $des_val0 to $des_val1\nSame as $pay_value\n";
else{
  echo "Destination account change from $des_val0 to $des_val1\nDiffernt from $pay_value\n";
}

?>
