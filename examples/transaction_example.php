<?php
/*
 * PHP SDK example code for transaction process
 * get transaction record by hash
 * 
 * Require test data set
 * test_data.json 
 */
use JingtumSDK\Wallet;
use JingtumSDK\APIServer;

require_once 'lib/ConfigUtil.php';
require_once 'Server.php';
require_once 'Wallet.php';
require_once 'Tum.php';

//Display the transactions from return of the function
function displayTransactionList($res){

if ( $res['success'] == true ){
//print_r($res);
  if ( is_array($res['transactions']) ){
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
}
else{
  echo "Error in get OrderList\n";
  var_dump($res);
}
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


//Test account to check the transactions
src_account:
$wt0 = new Wallet($test_wallet2->address, $test_wallet2->secret);
if ( $wt0->setAPIServer($api_server)){


$res = $wt0->getTransactionList();
displayTransactionList($res);

//Can use options to filter out the transaction list
/*参数类型说明
source_account String 交易方地址
destination_account String 支付交易的接收方地址
exclude_failed Boolean 是否移除失败的交易历史
direction String 支付交易的方向，incoming或outgoing
results_per_page Integer 返回的每页数据量，默认每页10项
page Integer 返回第几页的数据，从第1页开始
*/
echo "=========With Options================\n";
//$options['source_account'] = $test_wallet3->address;
//$options['destination_account'] = $test_wallet3->address;
//$options['exclude_failed'] = true;
//$options['direction'] = 'incoming';//'outgoing'
$options['results_per_page'] = 20;
$options['page'] = 1;
$res = $wt0->getTransactionList($options);

displayTransactionList($res);

}
else
  echo 'Error in initing Wallet Server';

echo "============Check transaction by HASH==============\n";
return;

?>
