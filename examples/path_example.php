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
function displayPayments($ret, $j = 0){
if ( $ret['success'] == true ){
  if ( is_array($ret['payments']) ){
    $num = count($ret['payments']); 
    echo "Total $num payments\n";
  }
}else
{
  echo "\nError in payment list\n";
  print_r($ret);

}
}

//A simple function to display the
//number of payment path found
function displayPaymentPaths($ret, $j = 0){
if ( $ret['success'] == true ){
  if ( is_array($ret['payments']) ){
    
    $num = count($ret['payments']);
    echo "Total $num payment choices\n";
    for ($i = 0; $i < $num; $i ++ ){
      echo 'Choices: '.$i.' '.$ret['payments'][$i]['choice']['currency'].' ';
      echo $ret['payments'][$i]['choice']['value'].' '.$ret['payments'][$i]['key']."\n";
    }
  }
}else
{
  echo "\nError in getting payment paths\n";
  print_r($ret);

}
}

//A simple function to display the
//display the balance in the account
function displayBalances($ret, $j = 0){
  $return_value = -999;
  if ( $ret['success'] == true ){
  if ( is_array($ret['balances']) ){
    for ( $i = 0; $i < count($ret['balances']); $i ++){
      $code = $ret['balances'][$i]['currency'];
      $value = $ret['balances'][$i]['value'];
      if ( $i == $j )
        $return_value = $value;
      echo "\n$code : $value";
    }
  }
}
else
{
  echo "\nError in balances \n";
  print_r($ret);
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
  echo $wt2->getAddress();
  //  $ret = $wt2->getBalance();
  //Get the src value before payment for validation
  //$src_val0 = displayBalances($ret, 0);

}
else
  echo 'Error in initing Wallet Server';

dest_account:
$wt3 = new Wallet($test_wallet3->address, $test_wallet3->secret);
//Need to setup the api server
if ( $wt3->setAPIServer($api_server)){
//  $ret = $wt3->getBalance();
  echo "Dest wallet".$wt3->getAddress()."\n";
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


/***************************************/
//Step 4.
//Submit payment with payment path
//4.1 Search for the PATH
PATH_payment:
$amt1 = new Amount($test_cny->currency, $test_cny->issuer, $pay_value);

$payreq->setDestAmount($amt1->getAmount());
$payreq->setDestAddress($wt3->getAddress());

echo "************Check payment path  $pay_value CNY***************\n";
$ret = $wt2->getPathList($wt3->getAddress(), $amt1);

//var_dump($ret);
displayPaymentPaths($ret);

//Get the new path list
if ( count($ret['payments']) > 0 ){
echo "Find ".count($ret['payments'])." paths\n";
//choose the 1st path
$key = $ret['payments'][0]['key'];

echo "\nKey: ".$key."\n";
$path = $wt2->getPathByKey($key);

echo "Path: ".$path."\n";

//4.2 Set a path to the payment operation
$payreq->setPath($path);
$payreq->setValidate('false');//optional, setup the syn mode, default is true

//get the balance before change
$ret = $wt3->getBalance();
$des_val0 = displayBalances($ret, 4);

//4.3 Make the payment
$ret = $payreq->submit();

print_r($ret);

//Step 5. Check for the change in the balance.
//wait for the close of ledger
echo 'Wait for ledger closing.';
for ($i = 0; $i<10; $i++){
    echo '.';
sleep(1);
}
echo "\n";
//Should notice the change in the balances
$ret = $wt3->getBalance();
$des_val1 = displayBalances($ret, 4);

if ( ($des_val1 - $des_val0 ) == $pay_value )
  echo "Destination account change from $des_val0 to $des_val1\nSame as $pay_value\n";
else{
  echo "Destination account change from $des_val0 to $des_val1\nDiffernt from $pay_value\n";
}
}else
  echo "No payment path is available! \n";
?>
