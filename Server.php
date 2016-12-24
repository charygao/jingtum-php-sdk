<?php
/**
 * PHP SDK for Jingtum network；Server Class  
 * @version 1.1.0
 * Copyright (C) 2016 by Jingtum Inc.
 * or its affiliates. All rights reserved.
 * Licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with
 * the License. A copy of the License is located at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * Class for Server communications 
 * APIserver
 * WebSocketServer
 * TumServer
 *  
 */
namespace JingtumSDK;

use JingtumSDK\lib\SnsNetwork;
use JingtumSDK\lib\ECDSA;
use WebSocket\Client;

require_once('vendor/autoload.php');
require_once './lib/SignUtil.php';
require_once './lib/ECDSA.php';
require_once './lib/ConfigUtil.php';
require_once './lib/SnsNetwork.php';

/**
 * Required the CURL extension
 * need to install it first.
 */
if (! function_exists('curl_init')) {
    throw new Exception('JingtumSDK needs the cURL PHP extension.');
}

/**
 * The PHP doesn't support JSON,
 * Pleae upgrade to PHP 5.2.x and above. 
 */
if (! function_exists('json_decode')) {
    throw new Exception('JingtumSDK needs the JSON PHP extension.');
}

//Abstract class
//for types of servers
abstract class ServerClass
{
    //Protected attributes 
    protected $serverURL = '';

    function __construct($in_url = NULL)
    {

      if ( empty($in_url) ){
          throw new Exception ('Error of empty url!');
      }
      else{
        $this->serverURL = $in_url;
      }
    }

    /**
     *
     * @return the URL
     */
    public function getServerURL()
    {
      if ( empty($in_url) ){
        throw new Exception ('Error of empty url!');
      }
      else
        return $this->serverURL;
    }
 
    public function setServer($in_url)
    {
      if ( $in_url == NULL ){
        printf("No server address found!\nPlease enter a valid server address!\n");
        return true;
      }
      else{
        printf("Setup server %s\n", $in_url);;
        $this->serverURL = $in_url;
        return false;
      }
    } 
}//end Abstract class
 

//Jingtum API server
//Handles the request and responses
//with the API server.

class APIServer extends ServerClass
{ 
    //API server
    //API version
    private $version = '';
    
    private $config = NULL;

    //internal prefix to create transaction ID
    private $prefix = 'prefix';

    //internal counter to generate transaction ID
    private $uuid = 0;
    
    //Declare the instance 
    private static $instance = NULL;

    //reserved for DATA server URL
    //function __construct($in_url, $in_version = 'v1')
    //Default set the Server to production server PRO
    //if the input is false, set to develop server DE
    function __construct()
    {
      //Load the default config file
      //return should be an object holding JSON info.
      $this->config = readConfigJSON("config.json");
      
      if ( is_object($this->config))
      {
        //Use production server 
        try {

          parent::__construct($this->config->PRO->api);

        } catch (Exception $e) {
            echo "Error in setup API from the config\n";
        }        

        try {

          $this->version = $this->config->PRO->api_version;
          if ( $this->version != 'v1' && $this->version != 'v2')
            throw new Exception('API version error!');
        }catch (Exception $e) {
          echo "Error in get the API version\n";
        }
      }else
        throw new Exception ('Error of read config info!');
    }
   
    //Destructor
    function __destruct(){
      printf("Release API server!\n");
    }
 
    //Return an instance object
    public static function getInstance()
    {
        if (! self::$instance instanceof self) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    /*
     * Submit the operations to the Jingtum server
     * The input contains following info
     * method = 'GET', 'POST', 'DEL' for network requests
     * url = URL used for commands
     * params = Parameters needed to post to the server.
     * Example:
        $cmd['method'] = 'GET';
        $cmd['url'] = str_replace("{0}", $this->address, BALANCES);
        $cmd['params'] = '';
    */
    public function submitRequest($in_cmd, $in_address='', $in_secret='')
    {
        //Generate a full url with server address and API version
        //info
        $url = $this->serverURL . '/' . $this->version . $in_cmd['url'];
        
        /*Handles different API version
         * This part doesn't work by 09/25/2016
         */
        if ($this->version == 'v2') {
            $res = buildSignString($in_address, $in_secret);
            
            $params['k'] = $res['k'];
            $params['s'] = $res['s'];
            $params['h'] = $res['h'];
            $params['t'] = $res['t'];
        }

        echo "\nSubmitting......\n$url\n";
        //Submit the parameters to the SERVER
        $ret = SnsNetwork::api($url, 
          json_encode($in_cmd['params']), 
          $in_cmd['method']);
        return $ret;
        
    }

    //Check if the server is connected
    public function getConnected()
    {
      //API /v1/，GET method
      $url = $this->serverURL . '/' . $this->version . '/connected';
      $ret = SnsNetwork::api($url, '', 'GET');
      return $ret;
    }

    //Return a pair of wallet from the SERVER
    //Note the return is in JSON format
    //and should be handle differently than
    //the local function.
    public function getNewWalletFromServer()
    {
      //API /v1/uuid，GET method
      $url = $this->serverURL . '/' . $this->version . '/wallet/new';
      $ret = SnsNetwork::api($url, '', 'GET');
      return $ret;
    }
  
    //Set the test environment according tot he input flag
    //
    public function setTest($test_flag = 'true')
    {
      //use the input boolean flag to set the 
      //server url
      if ( is_bool($test_flag)){
        if ( is_object($this->config) ){
          //Conver the 
          if ( $test_flag == true){ 
            $this->serverURL = $this->config->DEV->api;
            $this->version = $this->config->DEV->api_version;
          }
          else
          { 
            $this->serverURL = $this->config->PRO->api; 
            $this->version = $this->config->PRO->api_version;
          }
        }else
          echo "No configuration is set!";
          //reload the config file 
      }else{
        echo "Input need to be a boolean";
      }
      echo "Server set to $this->serverURL\n";
    }

    //Return a uuid from the API SERVER
    //Change it to use prefix and UNIX time
    //Format as the follows:
    //prefix.yyyymmddHHMMss.000000
    //
    public function getClientResourceID()
    {
      //API /v1/uuid，GET method
      //Increase the internal counter by 1
      $id = sprintf("%06d",++$this->uuid);
      //keep it between 1 and 999999
      if ( $this->uuid > 999999 )
        $this->uuid = 0;

      return $this->prefix.time().$id;
    }
 
}

/*
Jingtum WebSocket Server handles the processing
messages from the Jingtum BlockChain to the client.
It receive the responses from the background server
and
this Class requires The Websocket Client for PHP package 
*/
class WebSocketServer extends ServerClass
{
    private $ws_server = ''; 

    function __construct()
    {
      //Load the default config file
      //return should be an object holding JSON info.
      $this->config = readConfigJSON("config.json");

      if ( is_object($this->config))
      {
        //Use production server
        try {

          parent::__construct($this->config->PRO->ws);

        } catch (Exception $e) {
            echo "Error in setup WebSocket server from the config\n";
        }

      }else
        throw new Exception ('Error of read config info!');
    }

    /**
     * Connect to the socket server to receive
     * messages.
     * @return NULL
    */
    public function connect()
    {
        $this->ws_server = new Client($this->serverURL);
        if ($this->ws_server->isConnected() == true )
          printf("Web socket connected successfully!\n");
        return $this->ws_server->receive();
    }

    /**
     *
     * @subscribe 
     * To get the info of the input wallet
     * from the connect Socket server.     
     * 客户端在连接上服务之后，通过发送订阅请求进行订阅，订阅请求如下：
     * 订阅请求中，必须将订阅用户的地址和私钥一起提交上来，
     * 每个用户只能订阅用户自己的交易信息。
     */
    public function subscribe($in_address, $in_secret)
    {
        
        $command['command'] = 'subscribe';
        $command['account'] = $in_address;
        $command['secret'] = $in_secret;
        
        $this->ws_server->send(json_encode($command));
        
        return $this->ws_server->receive();
    }

    /**
     *
     * @unsubscribe 
     * Stop receiving the info
     * from the connect Socket server.
    */
    public function unsubscribe($in_address)
    {
        $command['command'] = 'unsubscribe';
        $command['account'] = $in_address;
        
        $this->ws_server->send(json_encode($command));
        
        return $this->ws_server->receive();
    }

    /**
    * Send the close request to close the
    * connection.
    * @return NULL
    */
    public function disconnect()
    {
        $command['command'] = 'close';
        $this->ws_server->send(json_encode($command));
        if ($this->ws_server->isConnected() == false )
          printf("Web socket disconnected!\n");
        return $this->ws_server->receive();
    }
   
    /**
     *
     * @return NULL
     * getSocketMessage() -> setTxHandler
     * Return the Transaction Handler
     * The socket will return when it receives
     * a message.
    */
    public function setTxHandler()
    {
      return $this->ws_server->receive();
    }

    //Set the test server
    public function setTest($test_flag = 'true')
    {
      //use the input boolean flag to set the
      //server url
      if ( is_bool($test_flag)){
        if ( is_object($this->config) ){
          //Conver the
          if ( $test_flag == true){
            $this->serverURL = $this->config->DEV->ws;
          }
          else
          {
            $this->serverURL = $this->config->PRO->ws;
          }
        }else
          echo "No configuration is set!";
          //reload the config file
      }else{
        echo "Input need to be a boolean";
      }
    }
 
}

/*
 * Tum Server
 * handles the Tum issued information 
 * for each FinGate
*/
class TumServer extends ServerClass
{ 
    //Tum server
    //Tum version
    
    //Declare the instance 
    private static $instance = NULL;

    //reserved for DATA server URL
    function __construct($in_url = NULL)
    {
      //$this->serverURL = $inURL;
      if ( empty($in_url)){

        //Load the default config file
        //return should be an object holding JSON info.
        $this->config = readConfigJSON("config.json");

        if ( is_object($this->config))
        {
          //Use production server
          try {

          parent::__construct($this->config->PRO->fingate);

          } catch (Exception $e) {
            echo "Error in setup WebSocket server from the config\n";
          }

        }else
          throw new Exception ('Error of read config info!');

      }
      else{
        throw new  Exception('Input url not a string!'); 
      }
    }

    //Set the test server
    public function setTest($test_flag = 'true')
    {
      //use the input boolean flag to set the
      //server url
      if ( is_bool($test_flag)){
        if ( is_object($this->config) ){
          //Conver the
          if ( $test_flag == true){
            $this->serverURL = $this->config->DEV->fingate;
          }
          else
          {
            $this->serverURL = $this->config->PRO->fingate;
          }
        }else
          echo "No configuration is set!";
          //reload the config file
      }else{
        echo "Input need to be a boolean";
      }
    }
    
    /*
     * Submit the operations to the Tum server
     * for issuing Custom Tum 
     * The input contains following info
     * method = 'GET', 'POST', 'DEL' for network requests
     * only POST will be used in Tum. 
     * url = URL used for commands
     * params = Parameters needed to post to the server.
     * Example:
        $cmd['method'] = 'POST';
        $cmd['url'] = https://fingate.jingtum.com/v1/business/node;
        $cmd['params'] = '';
    */
    public function submitRequest($in_cmd)
    {
        if ( empty($in_cmd) )
          throw new Exception('Empty command in submitRequest.');
 
        //Conver the input command to upper case for check
        $temp_cmd = strtoupper(trim($in_cmd['method']));
        if ( $temp_cmd == 'POST' || $temp_cmd == 'GET' 
            || $temp_cmd == 'DELETE' ){
        //Generate a full url with server address and API version
        //info
          $url = $this->serverURL . $in_cmd['url'];
        
          $ret = SnsNetwork::api($url, 
          json_encode($in_cmd['params']), 
          $temp_cmd);
          return $ret;
        }
        else
          throw new Exception('Unknow command in submitRequest.');
    }
}
