<?php
/*
 * PHP SDK example code
 * Test the memo size
 * with SWT payment
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

$fg1 = $test_data->DEV->fingate1;
$test_wallet2 = $test_data->DEV->wallet2;
$test_wallet5 = $test_data->DEV->wallet5;
$test_wallet6 = $test_data->DEV->wallet6;

$test_cny = $test_data->DEV->CNYAmount1;


//Set the FinGate using info from the configuration file.
print_r("=============Set FinGate mode==============\n");
$fin_gate = FinGate::getInstance();

//Set the test environment
$fin_gate->setMode(FinGate::DEVELOPMENT);


echo "Setup wallet 2 and 3 for testing\n";
//$wallet2 = new Wallet($test_wallet5->secret);//, $test_wallet2->address);
$wallet2 = new Wallet($fg1->secret);//, $test_wallet5->address);

$wallet3 = new Wallet($test_wallet5->secret);//, $test_wallet3->address);

//A payment obj for testing
$payreq = new PaymentOperation($wallet2);
$pay_value = 0.01;
$amt1 = new Amount($pay_value, 'SWT', '');
//$amt1 = new Amount(100, 'CNY', 'jBciDE8Q3uJjf111VeiUNM775AMKHEbBLS');

//display the results
echo "Check Balance of the new wallet\n";
//Make sure the src wallet have enough balance
$res = $wallet2->getBalance();
$src_val0 = displayBalances($res, 0);
$newpayreq = new PaymentOperation($wallet2);
$newpayreq->setDestAddress($wallet3->getAddress());//required
echo "======================================\n";
echo "*\n* test memo program\n";
echo "======================================\n";
$in_memo = "0123456789";
$client_id = "MemoTest".time();
//$newpayreq->setMemo($in_memo);
$newpayreq->setAmount($amt1);
$newpayreq->setClientID($client_id);//optional, if not provided, SDK will generate an internal one
//Submit the payment operation
$newpayreq->submit('call_back_func');

return;
?>
