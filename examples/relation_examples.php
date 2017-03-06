<?php
/*
 * PHP SDK example code
 * For Relation process
 * submit Relation with TUM and cancel it. 
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
use JingtumSDK\RelationOperation;
use JingtumSDK\RemoveRelationOperation;

require_once 'lib/ConfigUtil.php';
require_once 'AccountClass.php';
require_once 'Wallet.php';
require_once 'FinGate.php';
require_once 'Server.php';
require_once 'Tum.php';
require_once 'RelationOperation.php';
require_once 'RemoveRelationOperation.php';

//Display the balance in the account
function displayRelation($res){

  if ( $res['success'] == true ){
  if ( is_array($res['relations']) ){
    var_dump($res['relations']);  

  }
}
else
{
  echo "\nError in Relation \n";
  print_r($res);
}
  echo "\n";

}




//Example call back function for submit Relation
function call_back_func($res)
{
  echo 'In the call back function'."\n";
  if($res['success'] == true){
    echo "Relation submit successfully\n";
    echo "Relation ".$res['sequence']." is ".$res['state']."\n";

  }
  else
    var_dump($res);
}

//Example call back function for cancel the Relation
function cancel_call_back_func($res)
{
  echo 'Call back function for canceling'."\n";
  if($res['success'] == true){
    echo "Cancel Relation submited successfully\n";
    var_dump($res);
    //echo "seq".$res['sequence']." is ".$res['state']."\n";
  }
  else
    var_dump($res);
}

/************************************************/
// Main test program
//Read in the test configuration and data
/************************************************/

echo "======================================\n";
echo "*\n* Jingtum Test program\n* Relation test 1.0\n*\n";
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

echo "Setup wallet 2 and 3 for testing\n";
$wallet2 = new Wallet($test_wallet2->secret, $test_wallet2->address);

$res = $wallet2->getRelation('authorize',null,"USD:jMcCACcfG37xHy7FgqHerzovjLM5FCk7tT");
displayRelation($res);

// $ret = $wallet2->getTransactionList();
// checkRelationStatus($ret);

$wallet3 = new Wallet($test_wallet3->secret, $test_wallet3->address);
 $ret = $wallet3->getTransactionList();
 checkRelationStatus($ret);



//1 SWT with 10 CNY
//create the two Amount object
// $amt1 = new Amount(1000, 'SWT', '');
// $amt1 = new Amount(500, $test_cny->currency, $test_cny->issuer);
 $amt1 = new Amount(80, 'USD','jMcCACcfG37xHy7FgqHerzovjLM5FCk7tT');
//$amt1 = new Amount(100, 'USD','jMcC37xHy7FgqHerzovjLM5FCk7tT');
//$amt1 = new Amount(100, 'USD','jMT');//test with wrong issuer

 
echo "======================================\n";
echo "*\n* Create Relation program\n";
echo "======================================\n";

$req2 = new RelationOperation($wallet2);


$req2->setType('authorize');//required
$req2->setAmount($amt1);
$req2->setCounterparty($test_wallet3->address);


//goto Relation_test;

cancel_Relation_test:
echo "=======Submit one Relation===================\n";
//Submit Relation
$ret = $req2->submit();
sleep(5);

if ( $ret['success'] == true ){
  
  //  print_r(json_encode($ret));
  echo "Relation submitted successfully\n";
  echo "HASH ID:".$ret['hash']."\n";
  $relation_id = $ret['hash'];
  // $res = $wallet2->getRelationList();
  // displayRelationList($res);
  echo "=======Check the Relation===================\n";
$res = $wallet2->getRelation("authorize");
displayRelation($res);



echo "=======Cancel the Relation===================\n";
cancel_Relation:
//to cancel a submitted Relation, need to wait until the ledger closed
//then cancel it
sleep(10);
if ( ! empty($relation_id) ){
  echo "\nCancelling Relation $relation_id\n";
  $cancel_req = new RemoveRelationOperation($wallet2);
  $cancel_req->setType('authorize');
  $cancel_req->setAmount($amt1);
  $cancel_req->setCounterparty($test_wallet3->address);
  $cancel_req->submit('cancel_call_back_func');

}

}else{
  echo "Failed to submit the Relation!\n";
  print_r(json_encode($ret));
}  


?>
