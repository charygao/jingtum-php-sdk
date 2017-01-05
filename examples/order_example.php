<?php
/*
 * PHP SDK example code for order process
 * submit order with SWT:CNY, and another one
 * with CNY:SWT to match it. 
 * Require test data set
 * test_data.json 
 */
use JingtumSDK\FinGate;
use JingtumSDK\Wallet;
use JingtumSDK\Tum;
use JingtumSDK\Amount;
use JingtumSDK\APIServer;
use JingtumSDK\OrderOperation;
use JingtumSDK\CancelOrderOperation;

require_once 'lib/ConfigUtil.php';
require_once 'Server.php';
require_once 'Wallet.php';
require_once 'OrderOperation.php';
require_once 'CancelOrderOperation.php';
require_once 'Tum.php';

//Display the return of the orders 
function displayOrderList($ret){

if ( $ret['success'] == true ){
//print_r($ret);
  if ( is_array($ret['orders']) ){
    $num = count($ret['orders']);
    echo "Total $num orders:\n";
    for ( $i = 0; $i < $num; $i ++){
      $type = $ret['orders'][$i]['type'];
      $seq = $ret['orders'][$i]['sequence'];
      $code = $ret['orders'][$i]['taker_gets']['currency'];
      $value = $ret['orders'][$i]['taker_gets']['value'];
      $code1 = $ret['orders'][$i]['taker_pays']['currency'];
      $value1 = $ret['orders'][$i]['taker_pays']['value'];
      echo "Order $seq $type : $value $code for $value1 $code1\n";
    }
  }
}
else
  echo "Error in get OrderList\n";
}


//Display the transactions from return of the function
function displayTransactionList($ret){

if ( $ret['success'] == true ){
//print_r($ret);
  if ( is_array($ret['transactions']) ){
    $num = count($ret['transactions']);
    echo "Total $num transactions:\n";
    for ( $i = 0; $i < $num; $i ++){
      $type = $ret['transactions'][$i]['type'];
      $time = $ret['transactions'][$i]['date'];
      $code = $ret['transactions'][$i]['result'];
      $fee = $ret['transactions'][$i]['fee'];
      echo "Trans $type at $time : $code for $fee\n";
    }
  }
}
else
  echo "Error in get OrderList\n";
}

//Check the order status from the 
//transaction list of the wallet
function checkOrderStatus($ret, $in_seq_id){

if ( $ret['success'] == true ){
//print_r($ret);
  if ( is_array($ret['transactions']) ){
    $num = count($ret['transactions']);
    echo "Total $num transactions:\n";
    //Go through the transaction list to
    //search for the order seq id
    $i = 0; 
    while ($i < $num){
      //For all the offer transactions
      if ( $ret['transactions'][$i]['type'] == 'offernew'){
        //Check if the seq id match the submitted
        if ( $ret['transactions'][$i]['seq'] == $in_seq_id )
        {
          //Get the effects array
          $effects = $ret['transactions'][$i]['effects'];
          for ( $j = 0; $j < count($effects); $j ++ ){
            echo 'Offer '.$in_seq_id.' status: '.$effects[$j]['effect']."\n";
          }
          break;
        }
      }
      $i++;
    }
  }
}
else
  echo "Error in get OrderList\n";
}

/***************************************/
// Main test program
//Read in the test configuration and data
/***************************************/
echo "======================================\n";
echo "*\n* Jingtum Test program\n* Order test 1.0\n*\n";
echo "======================================\n";

//Read in the test configuration and data
$test_data = readTestData("examples/test_data.json");

if ( $test_data == false ){
   echo "Input test data is not valid\n";
   return;
}

//Used the input test wallet address/secret to check the balance
$test_wallet2 = $test_data->DEV->wallet2;
$test_wallet3 = $test_data->DEV->wallet3;
$test_cny = $test_data->DEV->CNYAmount1;

//reset the counters
$pass = 0;
$fail = 0;


//Source account to submit the order
src_account:
$wt2 = new Wallet($test_wallet2->address, $test_wallet2->secret);

if ( $wt2 ){
$wt2->setTest(true);//
$ret = $wt2->getOrderList();
echo "Address".$wt2->getAddress()."\n";
displayOrderList($ret);

}
else
  echo 'Error in initing Wallet Server';

$wt3 = new Wallet($test_wallet3->address, $test_wallet3->secret);
if ( $wt3 ){
$wt3->setTest(true);
$ret = $wt3->getOrderList();
echo "Address".$wt3->getAddress()."\n";
displayOrderList($ret);

}
else
  echo 'Error in initing Wallet Server';
//Submit an order and then cancel it
echo "============Build two orders==============\n";

//1 SWT with 10 CNY
//create the two Amount object
$pay_value = 1.0;
$get_value = 1.0;
$amt1 = new Amount('SWT', '', $pay_value);
//$amt2 = new Amount($test_cny->currency, $test_cny->issuer, $get_value);
$amt2['currency'] = $test_cny->currency;
$amt2['issuer'] = $test_cny->issuer;
$amt2['value'] = $get_value;

echo "=======Submit order 1===================\n";
order1:
$req3 = new OrderOperation($wt2);
$req3->setOrderType('buy');//required
$req3->setTakePays($amt1);               //required
$req3->setTakeGets($amt2);               //required
//Submit order
$ret = $req3->submit();

if ( $ret['success'] == true ){
  $ret = $wt2->getOrderList();
  echo "Address".$wt2->getAddress()."\n";
  displayOrderList($ret);
}
echo "=======Submit order 2===================\n";
order2:
$req3 = new OrderOperation($wt3);
$req3->setOrderType('buy');//required
$req3->setTakePays($amt2);               //required
$req3->setTakeGets($amt1);               //required
//Submit order
$ret = $req3->submit();

if ( $ret['success'] == true ){
   echo "Order 2 is submitted\n";
   $order_id = $ret['sequence'];
//to check a submitted order, need to wait until the ledger closed
sleep(10);
  $ret = $wt3->getOrderList();
  echo "Address".$wt3->getAddress()."\n";
  displayOrderList($ret);
//Check the order list 
//and transactions to make sure the order is 
//finished.
$ret = $wt3->getTransactionList();
//displayTransactionList($ret);
checkOrderStatus($ret, $order_id);

}else{
  echo "Failed to submit the order!\n";
  print_r(json_encode($ret));
}


?>
