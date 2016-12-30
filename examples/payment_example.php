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
use JingtumSDK\Tum;
use JingtumSDK\Amount;
use JingtumSDK\APIServer;
use JingtumSDK\PaymentOperation;

require_once 'lib/ConfigUtil.php';
require_once 'FinGate.php';
require_once 'Server.php';
require_once 'Wallet.php';
require_once 'PaymentOperation.php';
require_once 'Tum.php';

//A simple function to display the 
//number of payments returned
function displayPayments($res, $j = 0){
if ( $res['success'] == true ){
  if ( is_array($res['payments']) ){
    $num = count($res['payments']); 
    echo "Total $num payments\n";
  }
}else
{
  echo "\nError in payment list\n";
  print_r($res);

}
}

//A simple function to display the
//number of payment path found
function displayPaymentPaths($res, $j = 0){
if ( $res['success'] == true ){
  if ( is_array($res['payments']) ){
    
    $num = count($res['payments']);
    echo "Total $num payment paths\n";
    for ($i = 0; $i < $num; $i ++ )
      echo 'PATH: '.$res['payments'][$i]['paths']."\n";
  }
}else
{
  echo "\nError in payment paths\n";
  print_r($res);

}
}

//A simple function to display the
//display the balance in the account
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

/***************************************/
// Main test program
//Read in the test configuration and data
/***************************************/
$test_data = readTestData("examples/test_data.json");

if ( $test_data == false ){
   echo "Input test data is not valid\n";
   return;
}

$test_wallet2 = $test_data->DEV->wallet2;
$test_wallet3 = $test_data->DEV->wallet3;
$test_cny = $test_data->DEV->CNYAmount1;

/***************************************/
//Step 1.
//Setup the API server with test environment
$api_server = new APIServer();
$api_server->setTest(true);

/***************************************/
//Step 2.
//Set up wallet object

src_account:
$wt2 = new Wallet($test_wallet2->address, $test_wallet2->secret);

if ( $wt2->setAPIServer($api_server)){
  $res = $wt2->getBalance();
  echo $wt2->getAddress();
  //Get the src value before payment for validation
  $src_val0 = displayBalances($res, 0);

}
else
  echo 'Error in initing Wallet Server';
//List the number of payment
$paylist = $wt2->getPaymentList();
displayPayments($paylist);

dest_account:
$wt3 = new Wallet($test_wallet3->address, $test_wallet3->secret);
//Need to setup the api server
//display the balances
if ( $wt3->setAPIServer($api_server)){

  $res = $wt3->getBalance();
  echo $wt3->getAddress();
  $des_val0 = displayBalances($res, 0);
}

/***************************************/
//Step 3.
//Make the SWT payment between two accounts
//3.1 Create the payment operation 
//Make payment from wallet0 to wallet2 using SWT
//Building a payment operation
$payreq = new PaymentOperation($wt2);

$pay_value = 1.0;
//goto CNY_payment;
//goto PATH_payment;

SWT_payment:
//Create the amount
//1. use tum object to create one
$pay_value = 1.0;
//2. or use amount object 
$amt1 = new Amount('SWT', '', $pay_value);
$payreq->setDestAmount($amt1->getAmount());

$payreq->setDestAddress($wt3->getAddress());//required

$payreq->setValidate('false');//optional, setup the syn mode, default is true

//3.2 Submit the payment operation 
//submit the request using the default server within the source wallet
$res = $payreq->submit();

echo "************Make payment with $pay_value SWT***************\n";
print_r($res);

//3.3.
//need to wait until the blockchain ledger close, usually 5-10 seconds
echo "************Check for the balance change***************\n";
sleep(10);

//3.4
//check the balance after the payment
$res = $wt3->getBalance();
echo $wt3->getAddress();
//Should notice the change in the balances
  $des_val1 = displayBalances($res, 0);
if ( ($des_val1 - $des_val0 ) == $pay_value ) 
  echo "Destination account change from $des_val0 to $des_val1\nSame as $pay_value\n";
else{
  echo "Destination account change from $des_val0 to $des_val1\nDiffernt from $pay_value\n";
}

displayPayments($wt3->getPaymentList());

//Doing reverse payment from wt3 to wt2
$rev_payreq = new PaymentOperation($wt3);

//2.use the same amount object
$rev_payreq->setDestAmount($amt1->getAmount());

$rev_payreq->setDestAddress($wt2->getAddress());//required
$res = $rev_payreq->submit();
echo "************Make reverse payment with $pay_value SWT***************\n";
print_r($res);

/***************************************/
//Step 4.
//Make the CNY payment
//4.1 Create the payment amount and add to the operation
CNY_payment:
//
echo "************Make payment with $pay_value CNY***************\n";
//create the new tum amount object
$amt1 = new Amount($test_cny->currency, $test_cny->issuer, $pay_value);

$payreq->setDestAmount($amt1->getAmount());

//4.2 Submit the payment operation
//submit the request

//get the blances of the destination wallet
$res = $wt3->getBalance();
//notice the CNY is 4th currency in the test dest wallet
$des_val0 = displayBalances($res, 4);


$payreq->setValidate('false');//optional, setup the syn mode, default is true
$payreq->setResourceID($api_server->getClientResourceID());//required

$res = $api_server->submitRequest($payreq->build(), $wt3->getAddress(), $wt3->getSecret());

print_r($res['success']);
//wait for the close of ledger
echo 'Wait for ledger closing.';
for ($i = 0; $i<10; $i++){
    echo '.';
sleep(1);
}
echo "\n";
//Should notice the change in the balances
$res = $wt3->getBalance();
$des_val1 = displayBalances($res, 4);

if ( ($des_val1 - $des_val0 ) == $pay_value )
  echo "Destination account change from $des_val0 to $des_val1\nSame as $pay_value\n";
else{
  echo "Destination account change from $des_val0 to $des_val1\nDiffernt from $pay_value\n";
}

/***************************************/
//Step 5.
//Submit payment with payment path
//5.1 Search for the PATH
PATH_payment:
$amt1 = new Amount($test_cny->currency, $test_cny->issuer, $pay_value);

$payreq->setDestAmount($amt1->getAmount());

echo "************Check payment path  $pay_value CNY***************\n";
$res = $wt2->getPathList($wt3->getAddress(), $amt1);

if ( count($res['payments']) > 0 ){
//choose the 1st path
$path = $res['payments'][0]['paths'];

//Set it to the payment operation
$payreq->setPath($path);
$payreq->setValidate('false');//optional, setup the syn mode, default is true
$payreq->setResourceID($api_server->getClientResourceID());//required

//get the balance before change
$res = $wt3->getBalance();
$des_val0 = displayBalances($res, 4);

$res = $api_server->submitRequest($payreq->build(), $wt3->getAddress(), $wt3->getSecret());

print_r($res['success']);

//wait for the close of ledger
echo 'Wait for ledger closing.';
for ($i = 0; $i<10; $i++){
    echo '.';
sleep(1);
}
echo "\n";
//Should notice the change in the balances
$res = $wt3->getBalance();
$des_val1 = displayBalances($res, 4);

if ( ($des_val1 - $des_val0 ) == $pay_value )
  echo "Destination account change from $des_val0 to $des_val1\nSame as $pay_value\n";
else{
  echo "Destination account change from $des_val0 to $des_val1\nDiffernt from $pay_value\n";
}
}else
  echo "No payment path is available! \n";
?>
