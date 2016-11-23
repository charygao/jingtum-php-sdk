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
//use WebSocket\Exception;
use WebSocket\Client;

require_once('vendor/autoload.php');
require_once './lib/SignUtil.php';
require_once './lib/ECDSA.php';
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

    function __construct($in_url)
    {
      if ( empty($in_url) ){
        printf("Empty url!\nPlease enter a valid server address!\n");
      }
      else{
        printf("Setup server %s\n", $in_url);;
        $this->serverURL = $in_url;
      }
    }

    /**
     *
     * @return the URL
     */
    public function getServerURL()
    {
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
    
    //Declare the instance 
    private static $instance = NULL;

    //reserved for DATA server URL
    function __construct($in_url, $in_version = 'v1')
    {
        //$this->serverURL = $inURL;
        parent::__construct($in_url);
        $this->version = $in_version;
    }
   
    //Destructor
    function __destruct(){
      printf("Release API server!\n");
    }
 
    //Return an instance object
    public static function getInstance()
    {
        if (! self::$instance instanceof self) {
            // echo 'lgh-big';
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
    public function submitRequest($in_cmd, $in_address, $in_secret)
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
        
        //Note
        //print_r($url);
        //print_r($in_cmd['params']);
        //print_r("\n===========================\n");
//        $ret = $in_cmd['method'];
/*debug
        print_r("\nDebugging\n======URL==========\n");
        echo $url;
        print_r("\n=======PARAMETER==============\n");
        echo json_encode($in_cmd['params']);
        print_r("\n========RESPONSE==============\n");
*/
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
    function __construct($in_url)
    {
      //$this->serverURL = $inURL;
      if ( is_string($in_url))
        parent::__construct($in_url);
      else
        throw new  Exception('Input url not a string!'); 
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
        
    /*    print_r("\nDebugging\n======URL==========\n");
        echo $url;
        print_r("\n=======PARAMETER==============\n");
        echo json_encode($in_cmd['params']);
    */
          $ret = SnsNetwork::api($url, 
          json_encode($in_cmd['params']), 
          $temp_cmd);
          return $ret;
        }
        else
          throw new Exception('Unknow command in submitRequest.');
    }
}
