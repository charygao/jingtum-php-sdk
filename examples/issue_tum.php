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

print_r("=============Get FinGate Address==============\n");
$fin_gate = new FinGate($fg1->address, $fg1->secret);
printf("%s\n",$fin_gate->getAddress());
printf("%s\n",$fin_gate->getSecret());

//Set the FinGate parameters obtained from Jingtum Company.
$fin_gate->setToken($fg1->custom_id);
$fin_gate->setSignKey($fg1->sign_key);

print_r("=============Get FinGate Token&Sign Key===========<br/>");
printf("\nToken: %s\n", $fin_gate->getToken());
print_r(json_encode($fin_gate->getSignKey()));

//Setup the Tum server using DEV server configurations.
/* All issue Tum commands are POST method*/
//$tum_server = new TumServer($test_data->DEV->fingate_server);
$tum_server = new TumServer();
$tum_server->setTest(true);
if (is_object($tum_server)){
echo "\n==Set Tum Server Successful!==\n";

//Get the current Tum issue info
$res = $tum_server->submitRequest($fin_gate->queryCustomTum($fg1->tum1));
//var_dump($res);
printf("Tum name: %s, Amount issued: %s\n", $res["name"], $res['circulation']);

//Issue new Tum amount
$uuid = $fin_gate->getClientResourceID();
$res = $tum_server->submitRequest($fin_gate->issueCustomTum($uuid, $fg1->tum1, 1118.16));
if ( is_string(json_encode($res))){
//  printf("Tum: %s, issue amount: %s\n", $res["name"],$res['amount']);
  if ( $res['code'] == 0)
    printf("Issuing Tum successfully!\n");
  else
    printf("Error in issuing Tum: %s\n", $res['code']);
}
//Check the order
$res = $tum_server->submitRequest($fin_gate->queryIssue($uuid));
printf("Order %s is %d\n", $res['order'], $res['status']);
//var_dump($res);
printf("query Tum:......\n");

$res = $tum_server->submitRequest($fin_gate->queryCustomTum($fg1->tum1));
printf("Tum name: %s , Amount issued: %s\n", $res["name"], $res['circulation']);
}
return;//end of the program
?>
