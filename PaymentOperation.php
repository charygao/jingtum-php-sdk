<?php
/**
 * PHP SDK for Jingtum network； PaymentOperation
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
 * PaymentOperation
 * 01/18/2017
 * Added setMemo function
 * Added getJSON function to add the info into batch operations
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
//API接口/v1/accounts/{:source_address}/payments?validated=true，POST方法
class PaymentOperation extends OperationClass
{
    //Operations need to submit
    private $dest_address = '';
    private $amount = '';
    private $path = '';
    private $memo_data = '';
    private $client_resource_id = '';
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
       $this->amount['value'] = '';
       $this->amount['currency'] = '';
       $this->amount['issuer'] = '';
       $this->path = '';
       $this->client_resource_id = '';
        }else
          throw new Exception("Input is not a valid wallet object");
    }
  
    /*
    * Return an Array with JSON format data contains the 
    * payment info, used for Batch operation
    * The returned JSON has two part
    * secret    -
    * operation -
        "type": "Payment",
        "account": "jJ524DekvGBKTKu1gxAhMS8raa3mMfdwta",
        "payment": {
        "source_account": "jJ524DekvGBKTKu1gxAhMS8raa3mMfdwta",
        "destination_amount": {
        "currency": "SWT",
        "issuer": "",
        "value": "0.000002"
        },
        "destination_account": "jHokET15vHKFwg9djpieZryiTzgDHRJLrh",
        "paths": "[]"
        }
        },
    *    
    */
    public function getOperation()
    {
        //info to build the server URL
        $payment['destination_amount'] = $this->amount;
        $payment['source_account'] = $this->src_address;
        $payment['destination_account'] = $this->dest_address;

        $payment['paths'] = $this->path;

        //Added the payment memos
        if ($this->memo_data != ''){
        $memos['memo_type'] = 'string';
        $memos['memo_data'] = $this->memo_data;

        $payment['memos'][0] = $memos;
        }


        $params['payment'] = $payment;
        $params['account'] = $this->src_address;
        $params['type'] = "Payment";
 
        //info to submit
        // $out_json['secret'] = $this->src_secret;
        // $out_json['operation'] = $params;

        return $params;
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
       $this->amount['value'] = strval($in_tum_amount->getValue());
       $this->amount['issuer'] = $in_tum_amount->getIssuer();

       }else if(is_array($in_tum_amount)){
        echo "input Amount is JSON\n";
       $this->amount['currency'] = $in_tum_amount['currency'];
       $this->amount['value'] = strval($in_tum_amount['value']);
       $this->amount['issuer'] = $in_tum_amount['issuer'];
       }
    }
   
    
    public function setDestAddress($in_address)
    {
       $this->dest_address = $in_address;
    }

    public function setMemo($in_str)
    {
      if (is_string($in_str))
        $this->memo_data = $in_str;
      else
        throw new Exception('Input memo needs to be a string.');

       
    }

    public function setPath($in_path)
    {
       $this->path = $in_path;
       
    }

    //using input choice 
    public function setChoice($in_choice)
    {
       //find the path by using the key
       $this->path = $this->wallet->getPathByKey($in_choice);
       
    }
    
    public function setClientId($in_id)
    {
      if (is_string($in_id))
       $this->client_resource_id = $in_id;
      else
        throw new Exception('Input client ID needs to be a string.');
    }
    


    //May need to check if the cmd is valid or not.
    public function submit($call_back_func=null)
    {
        //info to build the server URL
        $payment['destination_amount'] = $this->amount;
        $payment['source_account'] = $this->src_address;
        $payment['destination_account'] = $this->dest_address;
       
        if ( !empty($this->path)){
          $payment['paths'] = $this->path;
        }

        //Added the payment memos
        if (!empty($this->memo_data)){
        $memos['memo_type'] = 'string';
        $memos['memo_data'] = $this->memo_data;

        $payment['memos'][0] = $memos;
        }
 
        //info to submit
        $params['secret'] = $this->src_secret;
        

        if ( empty($this->client_resource_id))
          $this->client_resource_id = $this->api_server->getClientId();
        $params['client_resource_id'] = $this->client_resource_id;
        $params['payment'] = $payment;
        
        $cmd['method'] = 'POST';
        $cmd['url'] = str_replace("{0}", $this->src_address, PAYMENTS) . '?validated=' . $this->sync;
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
