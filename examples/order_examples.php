<?php
/*
 * PHP SDK example code
 * For order process
 * submit order with TUM and cancel it. 
 * Require test data set
 * test_data.json 
 * 
 */
use JingtumSDK\AccountClass;
use JingtumSDK\FinGate;
use JingtumSDK\Wallet;
use JingtumSDK\APIServer;
use JingtumSDK\TumServer;
use JingtumSDK\Amount;
use JingtumSDK\OrderOperation;
use JingtumSDK\CancelOrderOperation;

require_once 'lib/ConfigUtil.php';
require_once 'AccountClass.php';
require_once 'Wallet.php';
require_once 'FinGate.php';
require_once 'Server.php';
require_once 'Tum.php';
require_once 'OrderOperation.php';
require_once 'CancelOrderOperation.php';

//Display the balance in the account
function displayFreezedBalances($res, $j = 0){
  $return_value = -999;
  if ( $res['success'] == true ){
  if ( is_array($res['balances']) ){
    for ( $i = 0; $i < count($res['balances']); $i ++){
      $code = $res['balances'][$i]['currency'];
      $value = $res['balances'][$i]['freezed'];
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

//Check the order status from the 
//transaction list of the wallet
function checkOrderStatus($ret, $in_seq_id=null){

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
        //if ( $ret['transactions'][$i]['seq'] == $in_seq_id )
        //{
          //Get the effects array
          $effects = $ret['transactions'][$i]['effects'];
          $in_seq_id = $ret['transactions'][$i]['seq'];
          //echo "Order $in_seq_id: $effects\n";
          if ( is_array($effects))
            var_dump($effects);
          
          for ( $j = 0; $j < count($effects); $j ++ ){
            echo 'Offer '.$in_seq_id.' status: '.$effects[$j]['effect']."\n";
          }
          break;
        //}
      }
      $i++;
    }
  }
}
else
  echo "Error in get OrderList\n";
}

//A simple function to display the order submitted 
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


//Example call back function for submit order
function call_back_func($res)
{
  echo 'In the call back function'."\n";
  if($res['success'] == true){
    echo "Order submit successfully\n";
    echo "Order ".$res['sequence']." is ".$res['state']."\n";

  }
  else
    var_dump($res);
}

//Example call back function for cancel the order
function cancel_call_back_func($res)
{
  echo 'Call back function for canceling'."\n";
  if($res['success'] == true){
    echo "Cancel Order submited successfully\n";
    var_dump($res);
    //echo "seq".$res['sequence']." is ".$res['state']."\n";
  }
  else
    var_dump($res);
}

//Example call back function for getOrderBook
function order_book_call_back_func($res)
{
  echo 'In the call back function'."\n";
  if($res['success'] == true){
    $num = count($res['bids']);
    echo "Order book bids:".$num."\n";
        echo "Order book asks:".count($res['asks'])."\n";
      for ( $i = 0; $i < $num; $i ++){
     echo "Bids ".$res['bids'][$i]['order_maker'].":".$res['bids'][$i]['price']." Amount ".$res['bids'][$i]['total']."\n";
     //   var_dump($res['bids'][$i]);
     }
      for ( $i = 0; $i < count($res['asks']); $i ++){
     echo "Asks ".$res['bids'][$i]['order_maker'].":".$res['asks'][$i]['price']." Amount ".$res['asks'][$i]['total']."\n";
     //   var_dump($res['bids'][$i]);
     }
  }
  else
    var_dump($res);
}
/************************************************/
// Main test program
//Read in the test configuration and data
/************************************************/

echo "======================================\n";
echo "*\n* Jingtum Test program\n* Order test 1.0\n*\n";
echo "======================================\n";

//Read in the test configuration and data
$test_data = readTestData("examples/test_data.json");

$fg1 = $test_data->DEV->wallet1;
$test_wallet2 = $test_data->DEV->wallet2;
$test_wallet3 = $test_data->DEV->wallet3;

$test_cny = $test_data->DEV->CNYAmount1;


//Set the FinGate using info from the configuration file.
echo "=============Set FinGate mode==============\n";
$fin_gate = FinGate::getInstance();

//Set up the FinGate account using secret
$fin_gate->setAccount($fg1->secret, $fg1->address);

//Set the test environment
$fin_gate->setMode(FinGate::DEVELOPMENT);

echo "=============display the order book ==============\n";
$tum_pair = 'SWT'.'/'.$test_cny->currency.':'.$test_cny->issuer;
//$fin_gate->getOrderBook($tum_pair, 'order_book_call_back_func');


echo "Setup wallet 2 and 3 for testing\n";
$wallet2 = new Wallet($test_wallet2->secret, $test_wallet2->address);
$ret = $wallet2->getOrderList();
var_dump($ret);

// $ret = $wallet2->getTransactionList();
// checkOrderStatus($ret);

$wallet3 = new Wallet($test_wallet3->secret, $test_wallet3->address);



//1 SWT with 10 CNY
//create the two Amount object
$pay_price = 1.1;
$pay_amount = 1.23;
$tum_pair = 'SWT'.'/'.$test_cny->currency.':'.$test_cny->issuer;

echo "======================================\n";
echo "*\n* Create order program\n";
echo "======================================\n";

$req2 = new OrderOperation($wallet2);

//$req2->setType($req2::BUY);//required
$req2->setType($req2::BUY);//required
$req2->setPrice($pay_price);
$req2->setAmount($pay_amount);
$req2->setPair($tum_pair);


//goto order_test;

cancel_order_test:
echo $req2::BUY."\n";

echo "=======Submit one order===================\n";
$res = $wallet2->getBalance();
$src_val0 = displayFreezedBalances($res, 0);

//Submit order
$ret = $req2->submit();

if ( $ret['success'] == true ){
  
  //  print_r(json_encode($ret));
  echo "Order submitted successfully\n";
  echo "HASH ID:".$ret['hash']."\n";

  $res = $wallet2->getBalance();
  $src_val1 = displayFreezedBalances($res, 0);
  // $res = $wallet2->getOrderList();
  // displayOrderList($res);
  echo "=======Check the order===================\n";
$res = $wallet2->getOrder($ret['hash']);
//var_dump($res);



echo "=======Check the order TX===================\n";
$res = $wallet2->getTransaction($ret['hash']);
//var_dump($res);

$order_id = $ret['sequence'];

echo "=======Cancel the order===================\n";
cancel_order:
//to cancel a submitted order, need to wait until the ledger closed
//then cancel it
sleep(5);
if ( ! empty($order_id) ){
  echo "\nCancelling order $order_id\n";
  $cancel_req = new CancelOrderOperation($wallet2);
  $cancel_req->setOrderNum($order_id);

  $cancel_req->submit('cancel_call_back_func');

}

}else{
  echo "Failed to submit the order!\n";
  print_r(json_encode($ret));
}  

//Transaction list
checkOrderStatus($wallet2->getTransactionList());


return;

order_test:
echo "=======Submit order 1===================\n";
$req2->submit('call_back_func');
  $ret = $wallet2->getOrderList();

  displayOrderList($ret);

echo "=======Submit order 2===================\n";
order2:
$req3 = new OrderOperation($wallet3);
$req3->setType($req3::SELL);//required
$req3->setPrice($pay_price);
$req3->setAmount($pay_amount);
$req3->setPair($tum_pair);

//Submit order
$req3->submit('call_back_func');

//to check a submitted order, need to wait until the ledger closed
sleep(10);
  $ret = $wallet3->getOrderList();
  echo "Address".$wallet3->getAddress()."\n";
  displayOrderList($ret);
//Check the order list 
//and transactions to make sure the order is 
//finished.
$ret = $wallet3->getTransactionList();
checkOrderStatus($ret);



return;//end of the program
?>
