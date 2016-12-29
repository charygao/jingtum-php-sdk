<?php
/**
 * PHP SDK for Jingtum network； OrderOperation
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
 *   OrderOperation
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

/********** Class order **********/
//API接口/v1/accounts/{:source_address}/orders?validated=true，POST方法
class OrderOperation extends OperationClass
{
    //Operations need to submit
    private $order_type = '';//sell or buy
    private $taker_pays = '';
    private $taker_gets = '';
   
    
    //Note: Parent constructors are not called implicitly 
    //if the child class defines a constructor.
    //In order to run a parent constructor, a call to
    //parent::__construct() within the child constructor is required. 
    //If the child does not define a constructor then it may be inherited 
    //from the parent class just like a normal class method 
    //(if it was not declared as private). 
     function __construct($in_wallet) {
       parent::__construct($in_wallet);

       $this->taker_pays = '';
       $this->taker_gets= '';
       $this->order_type = '';
   }
  
    //Setup the order type 
    //Only two order types were allowed
    //sell, buy
    public function setOrderType($in_type)
    {
       $check_type = strtoupper(trim($in_type));
       if ( $check_type  === 'SELL' or
            $check_type === 'BUY') 
       $this->order_type = $in_type;
       else{
         printf("Errors in the input type %s\n",$in_type);
         return false;
       }
       //may need to check if the value is boolean or not
    }

    //using all the info to create the operation URL
    //to use for Server submission
    //return the operation data
    //method = 'GET', 'POST', 'DEL' for network requests
    //url = URL used for commands
    //params = Parameters needed to post to the server.
    public function setTakePays($in_tum_amount)
    {
       if ( is_array($in_tum_amount)){
       $this->taker_pays['currency'] = $in_tum_amount['currency'];
       $this->taker_pays['counterparty'] = $in_tum_amount['issuer'];
       $this->taker_pays['value'] = strval($in_tum_amount['value']);
       }else
       {
         if ( is_object($in_tum_amount)){
           $this->taker_pays['currency'] = $in_tum_amount->getCurrency();
           $this->taker_pays['counterparty'] = $in_tum_amount->getIssuer();
           $this->taker_pays['value'] = strval($in_tum_amount->getValue());

         }
         else
           throw new Exception('Input should be an array or Amount object!');
       }
    }
    
    public function setTakeGets($in_tum_amount)
    {
      if ( is_array($in_tum_amount)){
        $this->taker_gets['currency'] = $in_tum_amount['currency'];
        $this->taker_gets['counterparty'] = $in_tum_amount['issuer'];
        if ( !is_string($in_tum_amount['value']))
          $this->taker_gets['value'] = strval($in_tum_amount['value']);
        else
          $this->taker_gets['value'] = $in_tum_amount['value'];
      }else
      {
        if ( is_object($in_tum_amount)){
          $this->taker_gets['currency'] = $in_tum_amount->getCurrency();
          $this->taker_gets['counterparty'] = $in_tum_amount->getIssuer();
          $this->taker_gets['value'] = strval($in_tum_amount->getValue());

        }
        else
           throw new Exception('Input should be an array or Amount object!');
      } 
    }



    //May need to check if the cmd is valid or not.
    public function submit()
    {
        //info to build the server URL
        $order['type'] = $this->order_type;
        $order['taker_pays'] = $this->taker_pays;
        $order['taker_gets'] = $this->taker_gets;
        
        //info to submit
        $params['secret'] = $this->src_secret;
        $params['order'] = $order;
        
        $cmd['method'] = 'POST';
        $cmd['url'] = str_replace("{0}", $this->src_address, ORDERS)
                      . '?validated=' . $this->sync;
        $cmd['params'] = $params;

        //submit the command and return the results 
        return $this->api_server->submitRequest($cmd, $this->src_address, $this->src_secret);
        return $cmd;
    }
    
}//end 
?>
