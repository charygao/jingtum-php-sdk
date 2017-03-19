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
use JingtumSDK\BatchOperation;
use JingtumSDK\OrderOperation;
use JingtumSDK\CancelOrderOperation;
use JingtumSDK\PaymentOperation;
use JingtumSDK\RelationOperation;
use JingtumSDK\RemoveRelationOperation;

require_once 'lib/ConfigUtil.php';
require_once 'AccountClass.php';
require_once 'Wallet.php';
require_once 'FinGate.php';
require_once 'Server.php';
require_once 'Tum.php';
require_once 'PaymentOperation.php';
require_once 'OrderOperation.php';
require_once 'BatchOperation.php';
require_once 'CancelOrderOperation.php';
require_once 'RemoveRelationOperation.php';
require_once 'RelationOperation.php';


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


//Example call back function
//handle submit orders batch 
function order_call_back_func($res)
{
  echo 'In the call back function'."\n";
  if($res['success'] == true){
    echo "submit successfully\n";
    var_dump($res);
    echo "Cancel orders\n";
    // $order_id = 1;
    // $cancel_req = new CancelOrderOperation($wallet2);
    // $cancel_req->setOrderNum($order_id);

    // $cancel_req->submit('cancel_call_back_func');

  }
  else
    var_dump($res);
}

/***************************************/
// Main test program
//Read in the test configuration and data
/***************************************/
echo "======================================\n";
echo "*\n* Jingtum Test program\n* Batch operation test 1.0\n*\n";
echo "======================================\n";

//Read in the test configuration and data
$test_data = readTestData("examples/test_data.json");

$fg1 = $test_data->DEV->wallet1;

$test_wallet2 = $test_data->DEV->wallet5;
$test_wallet3 = $test_data->DEV->wallet6;


$test_cny = $test_data->DEV->CNYAmount1;


//Set the FinGate using info from the configuration file.
print_r("=============Set FinGate mode==============\n");
$fin_gate = FinGate::getInstance();

//Set up the FinGate account using secret
$fin_gate->setAccount($fg1->secret, $fg1->address);

//Set the test environment
$fin_gate->setMode(FinGate::DEVELOPMENT);


echo "Setup wallet 2 and 3 for testing\n";
$wallet2 = new Wallet($test_wallet2->secret);

//$wallet3 = new Wallet($test_wallet3->secret, $test_wallet3->address);
//$wallet3 = new Wallet($fg1->secret);
$wallet3 = new Wallet($test_wallet3->secret);



// goto relation_test;
goto order_test;

payment_test:
//A payment obj for testing
$payreq1 = new PaymentOperation($wallet2);
$payreq2 = new PaymentOperation($wallet3);
$pay_value = 0.01;

echo "======================================\n";
echo "*\n* Batch operation test program\n";
echo "======================================\n";

$amt1 = new Amount($pay_value, 'SWT', '');
// $amt1 = new Amount($pay_value, 'CNY', 'jMcCACcfG37xHy7FgqHerzovjLM5FCk7tT');

$payreq1->setDestAddress($wallet3->getAddress());//required
$payreq1->setMemo("CNY PAYMENT1".time());
$payreq1->setAmount($amt1);


$payreq2->setDestAddress($wallet2->getAddress());//required
$payreq2->setMemo("CNY PAYMENT2".time());
$payreq2->setAmount($amt1);

//Prepare the batch operations
$batch_ops = new BatchOperation($wallet2);

$batch_ops->setOperation($payreq1);
$batch_ops->setOperation($payreq2);

$res = $batch_ops->submit();//('call_back_func');

//Check the operations

if ($res['success'] == true){
  echo "Operation ".$res['hash']." is success\n";
  var_dump($res['state']);
  //$res = $wallet2->getOperations($res['hash']);
}else
  var_dump($res);

return;

echo "======================================\n";
echo "*\n* Batch operation test on submit Order\n";
echo "======================================\n";

order_test:

$order_req1 = new OrderOperation($wallet2);
$order_req2 = new OrderOperation($wallet3);
$pay_price = 1.1;
$pay_amount = 0.01;
$tum_pair = 'SWT'.'/'.$test_cny->currency.':'.$test_cny->issuer;

$order_req1->setType($order_req1::BUY);//required
$order_req1->setPrice($pay_price);
$order_req1->setAmount($pay_amount);
$order_req1->setPair($tum_pair);

$order_req2->setType($order_req2::BUY);//required
$order_req2->setPrice($pay_price);
$order_req2->setAmount($pay_amount);
$order_req2->setPair($tum_pair);

//Prepare the batch operations
$batch_ops = new BatchOperation($wallet2);

$batch_ops->setOperation($order_req1);
$batch_ops->setOperation($order_req2);

// $batch_ops->submit('order_call_back_func');

//Check the operations
$res = $batch_ops->submit();//('call_back_func');

var_dump($res);
if ($res['success'] == true){
  //Get the transaction 
  $res = $wallet2->getTransaction($res['hash']);
  $order_id = $ret['sequence'];

    $cancel_req = new CancelOrderOperation($wallet2);
    $cancel_req->setOrderNum($order_id);

    var_dump($cancel_req->submit());
}


return;

relation_test:
//Setup two relations with test wallet 2
$amt1 = new Amount(80, 'USD','jMcCACcfG37xHy7FgqHerzovjLM5FCk7tT');
$amt2 = new Amount(80, 'CNY','jBciDE8Q3uJjf111VeiUNM775AMKHEbBLS');

$req1 = new RelationOperation($wallet2);

$req1->setType('authorize');//required
$req1->setAmount($amt1);
$req1->setCounterparty($test_wallet3->address);

$req2 = new RelationOperation($wallet2);

$req2->setType('authorize');//required
$req2->setAmount($amt2);
$req2->setCounterparty($test_wallet3->address);

//Prepare the batch operations
$batch_ops = new BatchOperation($wallet2);

$batch_ops->setOperation($req1);
$batch_ops->setOperation($req2);
$res = $batch_ops->submit();//('call_back_func');

if ($res['success'] == true){
  echo "Operation ".$res['hash']." is success\n";
  echo "\nCancelling Relation\n";
  $cancel_req = new RemoveRelationOperation($wallet2);
  $cancel_req->setType('authorize');
  $cancel_req->setAmount($amt1);
  $cancel_req->setCounterparty($test_wallet3->address);
  $res = $cancel_req->submit();
  var_dump($res);

  //cancel 2nd relation
  $cancel_req->setAmount($amt2);
  $res = $cancel_req->submit();
  var_dump($res);
}else
  var_dump($res);
return;//end of the program
?>
