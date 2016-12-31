<?php
/*
 * PHP SDK example code for order process
 * submit order with TUM and cancel it. 
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
function displayOrderList($res){

if ( $res['success'] == true ){
//print_r($res);
  if ( is_array($res['orders']) ){
    $num = count($res['orders']);
    echo "Total $num orders:\n";
    for ( $i = 0; $i < $num; $i ++){
      $type = $res['orders'][$i]['type'];
      $seq = $res['orders'][$i]['sequence'];
      $code = $res['orders'][$i]['taker_gets']['currency'];
      $value = $res['orders'][$i]['taker_gets']['value'];
      $code1 = $res['orders'][$i]['taker_pays']['currency'];
      $value1 = $res['orders'][$i]['taker_pays']['value'];
      echo "Order $seq $type : $value $code for $value1 $code1\n";
    }
  }
}
else
  echo "Error in get OrderList\n";
}

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

//Setup the API server, use test environment
$api_server = new APIServer();
$api_server->setTest(true);


//Source account to submit the order
src_account:
$wt0 = new Wallet($test_wallet2->address, $test_wallet2->secret);
if ( $wt0->setAPIServer($api_server)){
$res = $wt0->getOrderList();
displayOrderList($res);

}
else
  echo 'Error in initing Wallet Server';

//Submit an order and then cancel it
echo "============Build an order==============\n";
//1 SWT with 10 CNY
//create the two Amount object
$pay_value = 1.0;
$get_value = 1.0;
$amt1 = new Amount('SWT', '', $pay_value);
//$amt2 = new Amount($test_cny->currency, $test_cny->issuer, $get_value);
$amt2['currency'] = $test_cny->currency;
$amt2['issuer'] = $test_cny->issuer;
$amt2['value'] = $get_value;

echo "=======Submit the order===================\n";
$req3 = new OrderOperation($wt0);
$req3->setOrderType('buy');//required
$req3->setTakePays($amt1);               //required
$req3->setTakeGets($amt2);               //required
//Submit order
$ret = $req3->submit();

if ( $ret['success'] == true ){
  $pass ++;
  print_r(json_encode($ret));
  //$order_id = $ret['hash'];
  $order_id = $ret['sequence'];
  goto cancel_order;

echo "=======Cancel the order===================\n";
cancel_order:
//to cancel a submitted order, need to wait until the ledger closed
//then cancel it
sleep(10);
if ( ! empty($order_id) ){
  echo "\nCancelling order $order_id\n";
  $cancel_req = new CancelOrderOperation($wt0);
  $cancel_req->setOrderNum($order_id);
  $ret = $cancel_req->submit();
  if ( $ret['success'] == true )
    echo "Cancel order $order_id successfully!\n";
  else
    print_r($ret); 
}

}else{
  echo "Failed to submit the order!\n";
  print_r(json_encode($ret));
}  
return;

?>
