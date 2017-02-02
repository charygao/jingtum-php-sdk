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

//A simple function to display the 
//number of payments returned
function displayPayments($res){
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
echo "*\n* Jingtum Test program\n* Wallet and payment test 1.0\n*\n";
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


//Set up the minimum active amount to active the Wallet
//if needed. Otherwise FinGate uses the default amount.
$fin_gate->setActivateAmount(25);


echo "Setup wallet 2 and 3 for testing\n";
$wallet2 = new Wallet($test_wallet2->secret, $test_wallet2->address);


$wallet3 = new Wallet($test_wallet3->secret, $test_wallet3->address);

//A payment obj for testing
$payreq = new PaymentOperation($wallet2);
$pay_value = 0.01;

//debug lines
goto payment_list;
goto simple_payment_test;
//goto path_payment_test;

echo "======================================\n";
echo "*\n* Create new wallet test program\n";
echo "======================================\n";

new_wallet_test:
//Create the new wallet using the FinGate function
$wallet1 = $fin_gate->createWallet();
//print_r($wallet1);
echo "Get new wallet\n";
echo "Address:". $wallet1->getAddress()."\n";
echo "Secret:".$wallet1->getSecret()."\n";


//Setup the API server used in the FinGate
echo "*\n* Active the new wallet!\n";
$fin_gate->activeWallet($wallet1->getAddress(), 'call_back_func');



//Wait for ledger close
sleep(10);

//display the results
echo "Check Balance of the new wallet\n";

$res = $wallet1->getBalance();
$src_val0 = displayBalances($res, 0);


echo "======================================\n";
echo "*\n* Simple payment test program\n";
echo "======================================\n";

simple_payment_test:
//payback the FinGate for testing
//This need to create a wallet class

$res = $wallet2->getBalance();
//Should notice the change in the balances
$des_val0 = displayBalances($res, 0);

$client_id = "JTtest".time();

$amt1 = new Amount($pay_value, 'SWT', '');

$payreq->setDestAddress($wallet3->getAddress());//required
$payreq->setMemo("SWT PAYMENT".$client_id);
$payreq->setAmount($amt1);
$payreq->setClientID($client_id);//optional, if not provided, SDK will generate an internal one
$payreq->setValidate(false);//optional, setup the syn mode, default is true

//Submit the payment operation
$payreq->submit('call_back_func');

sleep(10);

//Check the payment
$res = $wallet2->getPayment($client_id);
if ($res['success'] == true){
  echo "Payment ".$res['client_resource_id']." is success\n";
  var_dump($res['memos']);
}

payment_list:
//Set the options to display the payment history
//default only display 10 records
$res = $wallet2->getPaymentList();
displayPayments($res);
$options['results_per_page'] = 20;
$options['page'] = 1;

$res = $wallet2->getPaymentList($options);
displayPayments($res);

return;

echo "Make payment with $pay_value SWT\n";
$res = $wallet2->getBalance();


//Should notice the change in the balances
  $des_val1 = displayBalances($res, 0);
if ( ($des_val1 - $des_val0 - $pay_value) < 0.001 )
  echo "Destination account change from $des_val0 to $des_val1\nSame as $pay_value\n";
else{
  echo "Destination account change from $des_val0 to $des_val1\nDiffernt from $pay_value\n";
}

return;

echo "======================================\n";
echo "*\n* Path payment test program\n";
echo "======================================\n";
path_payment_test:

echo "************Check payment path  $pay_value CNY***************\n";

$amt1 = new Amount($pay_value, $test_cny->currency, $test_cny->issuer);

$ret = $wallet2->getChoices($wallet3->getAddress(), $amt1);

displayPaymentPaths($ret);


//Get the new path list
if ( count($ret['payments']) > 0 ){
echo "Find ".count($ret['payments'])." paths\n";
//choose the 1st path
$key = $ret['payments'][0]['key'];


//Set a path to the payment operation
$payreq->setAmount($amt1->getAmount());
$payreq->setDestAddress($wallet3->getAddress());

$payreq->setChoice($key);
$payreq->setValidate('true');//optional, setup the syn mode, default is true


//submit the payment
$payreq->submit('call_back_func');


}
else
  echo "No path found \n";

return;//end of the program
?>
