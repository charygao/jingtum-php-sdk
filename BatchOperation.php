<?php
/**
 * PHP SDK for Jingtum network； operationsOperation
 * @version 1.0.0
 * 
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
 *   BatchOperation
 * 03/18/2017
 * support the following actions in batch operations
 * payment
 * submit order
 * settings
 * 
 * 
 */

namespace JingtumSDK;

use JingtumSDK\OperationClass;
use JingtumSDK\Wallet;

require_once('OperationClass.php');
require_once('Wallet.php');

/**
 * 如果您的 PHP 没有安装 cURL 扩展，请先安装
 */
if (! function_exists('curl_init')) {
    throw new Exception('JingtumSDK needs the cURL PHP extension.');
}

/**
 * 如果您的 PHP 不支持JSON，请升级到 PHP 5.2.x 以上版本
 */
if (! function_exists('json_decode')) {
    throw new Exception('JingtumSDK needs the JSON PHP extension.');
}

/********** Class payment **********/
//API接口/v1/accounts/{:source_address}/operations?validated=true，POST方法
//Need to reset the operation arrays after submit.


class BatchOperation extends OperationClass
{
    //Operations need to submit
    //private $operations = '';//array holding the operations
    private $wallet = null;

    private $signers ;
    private $operations;
    
    //Note: Parent constructors are not called implicitly 
    //if the child class defines a constructor.
    //In order to run a parent constructor, a call to
    //parent::__construct() within the child constructor is required. 
    //If the child does not define a constructor then it may be inherited 
    //from the parent class just like a normal class method 
    //(if it was not declared as private). 
     function __construct($in_wallet) {

      if ( is_object($in_wallet)){
        $this->wallet = $in_wallet;
     
        parent::__construct($in_wallet);
        //print "In SubClass constructor\n";
        //f($in_wallet->getDomain())

        $this->signers = array();
        $this->operations = array();
        
        }else
          throw new Exception("Input is not a valid wallet object");
    }
  
     

   //May need to check if the cmd is valid or not.
    //Get the input operation info
    public function setOperation($in_ops){

        array_push($this->signers, $in_ops->getSrcSecret());
        array_push($this->operations, $in_ops->getOperation());

    } 
 

    //Clean the internal array
    public function reset(){
        $this->signers = array();
        $this->operations = array();

    } 

    //using all the info to create the operation URL
    //to use for Server submission
    //return the operation data
    //method = 'GET', 'POST', 'DEL' for network requests
    //url = URL used for commands
    //params = Parameters needed to post to the server
    public function submit($call_back_func=null)
    {

        $num = count($this->operations);
        if ($num < 1 )
            throw new Exception("No operations set!");
        else
            echo "Total $num operations!\n";

        //info to build the data to submit
        $params['signers'] =   $this->signers;

        $params['operations'] = $this->operations;
 
        $params['secret'] = $this->src_secret;

        
        //send out the POST
        $cmd['method'] = 'POST';
        $cmd['url'] = str_replace("{0}", $this->src_address, OPERATIONS) . '?validated=' . $this->sync;
        $cmd['params'] = $params;

var_dump($params);

        //submit the command and return the results 
        if ($call_back_func != null)
        {
          $call_back_func($this->api_server->submitRequest($cmd, $this->src_address, $this->src_secret));
        }
        else
          return $this->api_server->submitRequest($cmd, $this->src_address, $this->src_secret);
    }
    
}
