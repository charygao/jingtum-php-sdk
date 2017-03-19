<?php
/*
 * PHP SDK example code for transaction process
 * get transaction record by hash
 * 
 * Require test data set
 * test_data.json 
 */
use JingtumSDK\Wallet;
use JingtumSDK\FinGate;

require_once 'lib/ConfigUtil.php';
require_once 'Wallet.php';
require_once 'FinGate.php';
require_once 'Tum.php';

//Display the transactions from return of the function
function displayTransactionList($res){

if ( $res['success'] == true ){
//print_r($res);
  if (array_key_exists('transactions', $res)){
    //if ( is_array($res['transactions']) ){
    $num = count($res['transactions']);
    echo "Total $num transactions:\n";
    for ( $i = 0; $i < $num; $i ++){
      $type = $res['transactions'][$i]['type'];
      $time = $res['transactions'][$i]['date'];
      $code = $res['transactions'][$i]['result'];
      $fee = $res['transactions'][$i]['fee'];
      echo "Trans $type at $time : $code for $fee\n";
    }
  }
  //return the 1st transaction record hash
  return $res['transactions'][0]['hash'];
}
else{
  echo "Error in get OrderList\n";
  var_dump($res);
}
}

echo "==========================================\n";
echo "*\n* Jingtum Test program\n* Transactions test 1.0\n*\n";
echo "==========================================\n";

//Read in the test configuration and data
$test_data = readTestData("examples/test_data.json");

if ( $test_data == false ){
   echo "Input test data is not valid\n";
   return;
}

$fg1 = $test_data->DEV->fingate1;
$test_wallet2 = $test_data->DEV->wallet2;
$test_wallet3 = $test_data->DEV->wallet3;
$test_cny = $test_data->DEV->CNYAmount1;

//Set the FinGate using info from the configuration file.
print_r("======Set FinGate mode======n");
$fin_gate = FinGate::getInstance();

//Set the test environment
$fin_gate->setMode(FinGate::DEVELOPMENT);

//variable holding the hash value
$last_hash = NULL;

//Test account to check the transactions
src_account:
$wt0 = new Wallet($test_wallet2->secret);
if ( $wt0 ){
echo "======Get Transactions======\n";

//default only return up to 10 transactions in the 1st page.
$res = $wt0->getTransactionList();
displayTransactionList($res);

return;
//Can use options to filter out the transaction list
/*参数类型说明
source_account String 交易方地址
destination_account String 支付交易的接收方地址
exclude_failed Boolean 是否移除失败的交易历史
direction String 支付交易的方向，incoming或outgoing
results_per_page Integer 返回的每页数据量，默认每页10项
page Integer 返回第几页的数据，从第1页开始
*/
echo "======With Options======\n";
//$options['source_account'] = $test_wallet3->address;
//$options['destination_account'] = $test_wallet3->address;
//$options['exclude_failed'] = true;
//$options['direction'] = 'incoming';//'outgoing'
$options['results_per_page'] = 20;
$options['page'] = 1;
$res = $wt0->getTransactionList($options);

//get a hash ID from the list
$last_hash = displayTransactionList($res);

}
else
  echo 'Error in initing Wallet Server';

echo "\n======Check transaction by HASH======\n";
if ( $last_hash ){
  $res = $wt0->getTransaction($last_hash);
  if ( $res['success'] == true){
    echo "Find the transaction info:\n";
    var_dump($res['transaction']);
  }
  else
    echo "Cannot find transaction with HASH ID $last_hash\n";
}
else
  echo "Empty hash ID\n";


?>
