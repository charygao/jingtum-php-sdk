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
 *   PaymentOperation
 */

namespace JingtumSDK;

use JingtumSDK\OperationClass;

require_once('OperationClass.php');

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
    private $client_resource_id = '';
    
    //Note: Parent constructors are not called implicitly 
    //if the child class defines a constructor.
    //In order to run a parent constructor, a call to
    //parent::__construct() within the child constructor is required. 
    //If the child does not define a constructor then it may be inherited 
    //from the parent class just like a normal class method 
    //(if it was not declared as private). 
     function __construct($address) {
       parent::__construct($address);
       //print "In SubClass constructor\n";
       $this->dest_address = '';       
       $this->amount['value'] = '';
       $this->amount['currency'] = '';
       $this->amount['issuer'] = '';
       $this->path = '';
       $this->client_resource_id = '';
    }
   
    //using all the info to create the operation URL
    //to use for Server submission
    //return the operation data
    //method = 'GET', 'POST', 'DEL' for network requests
    //url = URL used for commands
    //params = Parameters needed to post to the server.
    public function setDestAmount($in_tum_amount)
    {
       $this->amount['currency'] = $in_tum_amount['currency'];
       $this->amount['value'] = $in_tum_amount['value'];
       $this->amount['issuer'] = $in_tum_amount['issuer'];
       print_r($this->amount);
    }
   
    
    public function setDestAddress($in_address)
    {
       $this->dest_address = $in_address;
    }

    public function setPath($in_path)
    {
       $this->path = $in_path;
       
    }
    
    public function setResourceID($in_id)
    {
       $this->client_resource_id = $in_id;
    }
    


    //May need to check if the cmd is valid or not.
    public function submit()
    {
        //info to build the server URL
        $payment['destination_amount'] = $this->amount;
        $payment['source_account'] = $this->src_address;
        $payment['destination_account'] = $this->dest_address;
        $payment['payment_paths'] = $this->path;
        
        //info to submit
        $params['secret'] = $this->src_secret;
        
        $this->client_resource_id = $this->api_server->getClientResourceID();
        $params['client_resource_id'] = $this->client_resource_id;
        $params['payment'] = $payment;
        
        $cmd['method'] = 'POST';
        $cmd['url'] = str_replace("{0}", $this->src_address, PAYMENTS) . '?validated=' . $this->sync;
        $cmd['params'] = $params;

        //submit the command and return the results 
return $this->api_server->submitRequest($cmd, $this->src_address, $this->src_secret);
    }
    
}
