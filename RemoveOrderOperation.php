<?php
/**
 * PHP SDK for Jingtum network； RemoveOrderOperation
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
class RemoveOrderOperation extends OperationClass
{
    //The Order number of the account 
    //since this number is unique only for one
    //single account, 
    //Use String to save the order number
    //to prevent the 32 or 64 bit integer problem
    
    private $order_num = '';
   
    public function setOrderNum($in_order_num)
    {
       //check if the value is valid
       $int_order = strint($in_order_num);
       if ($int_order > 0 )
         $this->order_num = $in_order_num;
       else{
         printf("Error in the input order num!\n");
       }
    }
 
//DELETE方法请求时需设置Content-Length消息头
//validateAddress
    public function submit()
    {
        $cmd['method'] = 'DELETE';
        $cmd['url'] = str_replace("{0}", $this->src_address, ORDERS).$this->order_num;
        $cmd['params'] = $this->src_secret;
        
        //submit the command and return the results 
        return $this->api_server->submitRequest($cmd, $this->src_address, $this->src_secret);
    }
    
}//end 
?>
