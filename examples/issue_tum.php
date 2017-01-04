<?php
/*
 * PHP SDK example code
 * Showed how to use FinGate to issue custom Tum. 
 * 
 */
use JingtumSDK\FinGate;
use JingtumSDK\TumServer;

require_once 'lib/ConfigUtil.php';
require_once 'FinGate.php';
require_once 'Server.php';

//Read in the test configuration and data
$test_data = readTestData("examples/test_data.json");

//Create the wallet and Fingate using the configuration files.
//Set the FinGate using info from the configuration file.
$fg1 = $test_data->DEV->fingate1;
if (empty($fg1->sign_key) || empty($fg1->custom_id))
{
  echo "No FinGate token/sign key! please get them from Jingtum FinGate!\n";
  return;
}

print_r("=============Set FinGate address and secret==============\n");
$fin_gate = new FinGate($fg1->address, $fg1->secret);
printf("%s\n",$fin_gate->getAddress());
printf("%s\n",$fin_gate->getSecret());

print_r("=============Set FinGate Token&Sign Key===========");
//Set the FinGate parameters obtained from Jingtum Company.
$fin_gate->setToken($fg1->custom_id);
$fin_gate->setSignKey($fg1->sign_key);

printf("\nToken: %s\n", $fin_gate->getToken());
print_r(json_encode($fin_gate->getSignKey()));

//Setup the Tum server using DEV server configurations.
/* All issue Tum commands are POST method*/
echo "\n=============Set FinGate Tum Server===========\n";
$tum_server = new TumServer();
$tum_server->setTest(true);

if (is_object($tum_server)){
echo "\n==Set Tum Server Successful!==\n";
$fin_gate->setTumServer($tum_server);

//Get the current Tum issue info
echo "\n==Get the issued Tum info !==\n";

//$res = $fin_gate->queryCustomTum($fg1->tum1);
$fin_gate->setToken('00000003');
$res = $fin_gate->queryCustomTum('8100000003000020160022201800220020000001');
var_dump($res);
return;
echo "\n==Issue Tum Successful!==\n";
printf("Tum name: %s, Amount issued: %s\n", $res["name"], $res['circulation']);

//Issue new Tum with Tum amount
$uuid = $fin_gate->getClientResourceID();
$res = $fin_gate->issueCustomTum($uuid, $fg1->tum1, 20.17);

var_dump($res);

if ( is_string(json_encode($res))){
//  printf("Tum: %s, issue amount: %s\n", $res["name"],$res['amount']);
  if ( $res['code'] == 0)
    printf("Issuing Tum successfully!\n");
  else
    printf("Error in issuing Tum: %s\n", $res['code']);
}
return;
//Check the order
$res = $fin_gate->queryIssue($uuid);
printf("Order %s is %d\n", $res['order'], $res['status']);
//var_dump($res);
printf("query Tum:......\n");

$res = $fin_gate->queryCustomTum($fg1->tum1);
printf("Tum name: %s , Amount issued: %s\n", $res["name"], $res['circulation']);
}
return;//end of the program
?>
