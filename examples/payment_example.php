<?php
/*
 * PHP SDK example code
 * Showed how make payments between two accounts
 * with SWT and CURRENCY
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
    echo "At least $num payments were found in this address\n";
  }
}else
{
  echo "\nError in payment list\n";
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
echo "======================================\n";
echo "*\n* Jingtum Test program\n* Payment test 1.0\n*\n";
echo "======================================\n";

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
//Set up the source wallet object

src_account:
echo "======================================\n";
echo "Setup wallet1\n";
$wt2 = new Wallet($test_wallet2->address, $test_wallet2->secret);

if ( is_object($wt2))
{
  $wt2->setTest(true);
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

/***************************************/
//Step 2.
//Set up the destination wallet object

dest_account:
echo "======================================\n";
echo "Setup wallet2\n";
$wt3 = new Wallet($test_wallet3->address, $test_wallet3->secret);
//display the balances
if ( $wt3)
{
//Need to set to the test api server
  $wt3->setTest(true);
  $res = $wt3->getBalance();
  echo $wt3->getAddress();
  $des_val0 = displayBalances($res, 0);
}

/***************************************/
//Step 3.
//Test the SWT payment between two accounts
//3.1 Create the payment operation 
//Make payment from wallet2 to wallet3 using SWT
//Building a payment operation
echo "======================================\n";
echo "SWT payment test\n\n";
echo "Prepare the payment...\n";
$payreq = new PaymentOperation($wt2);


SWT_payment:
//Create the amount
//1. use tum object to create one
$pay_value = 1.0;

//2. or use amount object 
$swt_amt = new Amount('SWT', '', $pay_value);
$payreq->setDestAmount($swt_amt->getAmount());

$payreq->setDestAddress($wt3->getAddress());//required

$payreq->setValidate('false');//optional, setup the syn mode, default is true

//3.2 Submit the payment operation 
//submit the request using the default server within the source wallet
echo "Submit the payment with $pay_value SWT...\n";
$res = $payreq->submit();

//3.3.
//need to wait until the blockchain ledger close, usually 5-10 seconds
echo "Check for the balance change...\n";
sleep(10);

//3.4
//check the balance after the payment
$res = $wt3->getBalance();

//Should notice the change in the balances
  $des_val1 = displayBalances($res, 0);
if ( ($des_val1 - $des_val0 ) == $pay_value ){
  echo "Destination balance changed from $des_val0 to $des_val1\n";
  echo "Same as $pay_value\n";
}
else{
  echo "Destination balance changed from $des_val0 to $des_val1\nDiffernt from $pay_value\n";
}

displayPayments($wt3->getPaymentList());

SWT_reserver_payment:
//Doing reverse payment from wt3 to wt2
$rev_payreq = new PaymentOperation($wt3);

//2.use the same amount object
$rev_payreq->setDestAmount($swt_amt->getAmount());

$rev_payreq->setDestAddress($wt2->getAddress());//required
echo "Make reverse payment with $pay_value SWT\n";
$res = $rev_payreq->submit();

/***************************************/
//Step 4.
//Test the CNY payment
//4.1 Create the payment amount and add to the operation
CNY_payment:
echo "======================================\n";
echo "CNY payment test\n\n";
echo "Prepare the payment...\n";

//create the new tum amount object
$pay_value = 1.0;
$cny_amt = new Amount($test_cny->currency, $test_cny->issuer, $pay_value);

$payreq->setDestAmount($cny_amt->getAmount());


//get the blances of the destination wallet
$res = $wt3->getBalance();
//notice the CNY is 2nd currency in the test dest wallet
$des_val0 = displayBalances($res, 1);


//4.2 Submit the payment operation
$payreq->setValidate('false');//optional, setup the syn mode, default is true

echo "Submit payment with $pay_value $test_cny->currency\n";
$res = $payreq->submit();

if ($res['success']){
  //Check the balance changes if the return is true
  //wait for the close of ledger
  echo 'Wait for ledger closing.';
  for ($i = 0; $i<10; $i++){
    echo '.';
  sleep(1);
  }
  echo "\n";
  //Should notice the change in the balances
  $res = $wt3->getBalance();
  $des_val1 = displayBalances($res, 1);

  if ( ($des_val1 - $des_val0 ) == $pay_value )
    echo "Destination account change from $des_val0 to $des_val1\nSame as $pay_value\n";
  else{
    echo "Destination account change from $des_val0 to $des_val1\nDiffernt from $pay_value\n";
  }

}else{
  echo "Error of submit payment!\n";
  var_dump($res);
}

//2.use the same amount object
$rev_payreq->setDestAmount($cny_amt->getAmount());
echo "Make reverse payment with $pay_value $test_cny->currency\n";
$res = $rev_payreq->submit();

?>
