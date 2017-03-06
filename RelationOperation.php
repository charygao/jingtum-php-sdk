<?php
/**
 * PHP SDK for Jingtum network； RelationOperation
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
 *   RelationOperation
 * 01/18/2017
 * Added setMemo function
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

/********** Class Relation **********/
//API接口/v1/accounts/{:source_address}/Relations?validated=true，POST方法
class RelationOperation extends OperationClass
{
    //Operations need to submit
    private $dest_address = '';
    private $amount = '';
    private $type = '';

    private $wallet = null;

    
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
       $this->dest_address = '';       
       $this->amount['limit'] = '';
       $this->amount['currency'] = '';
       $this->amount['issuer'] = '';
       $this->type = '';

        }else
          throw new Exception("Input is not a valid wallet object");
    }
  
     
    //using all the info to create the operation URL
    //to use for Server submission
    //return the operation data
    //method = 'GET', 'POST', 'DEL' for network requests
    //url = URL used for commands
    //params = Parameters needed to post to the server.
    public function setAmount($in_tum_amount)
    {
       if ( is_object($in_tum_amount)){
        //Tum Amount class
        echo "Amount class\n";
        $this->amount['currency'] = $in_tum_amount->getCurrency();
       $this->amount['limit'] = strval($in_tum_amount->getValue());
       $this->amount['issuer'] = $in_tum_amount->getIssuer();

       }else if(is_array($in_tum_amount)){
        echo "input Amount is JSON\n";
       $this->amount['currency'] = $in_tum_amount['currency'];
       $this->amount['limit'] = strval($in_tum_amount['value']);
       $this->amount['issuer'] = $in_tum_amount['issuer'];
       }
    }
   
    
    public function setCounterparty($in_address)
    {
       $this->dest_address = $in_address;
    }

    /*
     * Set the type of relation
     * 
    */
    public function setType($in_str)
    {
      if (is_string($in_str))
        $this->type = $in_str;
      else
        throw new Exception('Input type needs to be a string.');

       
    }


    //May need to check if the cmd is valid or not.
    public function submit($call_back_func=null)
    {

 
        //info to submit
        $params['secret'] = $this->src_secret;

        $params['counterparty'] = $this->dest_address;
        $params['type'] = $this->type;
        $params['amount'] = $this->amount;
        
        $cmd['method'] = 'POST';
        //info to build the server URL
        $cmd['url'] = str_replace("{0}", $this->src_address, RELATIONS) . '?validated=' . $this->sync;
        $cmd['params'] = $params;

        var_dump($params);

        //submit the command and return the results 
        if ($call_back_func)
        {
          $call_back_func($this->api_server->submitRequest($cmd, $this->src_address, $this->src_secret));
        }
        else
          return $this->api_server->submitRequest($cmd, $this->src_address, $this->src_secret);
    }
    
}
