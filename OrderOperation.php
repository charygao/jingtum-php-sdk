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
 * 01/18/2017
 * Added the 增加setPrice选项和setPair
 * setType(Order.SELL);
 * setAmount(1000.00);
 * setPrice(0.0005);
 * 08/20/2017 Fixed the sell/buy order seq.
 */

namespace JingtumSDK;

use JingtumSDK\OperationClass;

require_once('OperationClass.php');
require_once './lib/DataCheck.php';

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
    private $tum0 = '';//
    private $tum1 = '';
   
    private $src_amount = '';
    private $src_price = '';

    
    const BUY = 'buy';
    const SELL = 'sell';
  

    //Note: Parent constructors are not called implicitly 
    //if the child class defines a constructor.
    //In order to run a parent constructor, a call to
    //parent::__construct() within the child constructor is required. 
    //If the child does not define a constructor then it may be inherited 
    //from the parent class just like a normal class method 
    //(if it was not declared as private). 
     function __construct($in_wallet) {
       parent::__construct($in_wallet);

       $this->tum0 = '';
       $this->tum1 = '';
       $this->order_type = '';

       $this->src_amount = '';
       $this->src_price = '';
   }
  
    //Setup the order type 
    //Only two order types were allowed
    //sell, buy
    public function setType($in_type)
    {
       $check_type = strtoupper(trim($in_type));
       if ( $check_type  === 'SELL' ||
            $check_type === 'BUY') {
         $this->order_type = $in_type;
       }
       else{
         throw new Exception("Error in the input type");
       }
       //may need to check if the value is boolean or not
    }

    //Return the order type 
    //Only two order types were allowed, all in small cases.
    //sell, buy
    public function getType($in_type)
    {
        return $this->order_type = $in_type;
    }
    
    //input string should have the format as
    //tumCode+TumIssuer

    public function getTumfromPair($in_str)
    {
      $pair = explode(':',$in_str);

      //If the input has two part, 
      //assume one is the issuer
      if ( count($pair) == 2){
        $out_tum['currency'] = $pair[0];
        $out_tum['counterparty'] = $pair[1];
      }
      else
      {
        if ( count($pair) == 1){
          //only for SWT
          $out_tum['currency'] = $pair[0];
          $out_tum['counterparty'] = '';
        }else
          throw new Exception("Input should have a pair");
      }
      return $out_tum;
    }

    /*
    * Return an Array with JSON format data contains the 
    * order info, used for Batch operation
    * The returned JSON has two part
    * secret    -
    * operation -
      "account": "jEQM2moWBW2PmH2oUscw2nBhmeuV35ojKH",
      "type": "sell",
      "order": {
      "type": "sell",
      "taker_gets": {
      "currency": "USD",
      "counterparty": "jBciDE8Q3uJjf111VeiUNM775AMKHEbBLS",
      "value": "1"
      },
      "taker_pays": {
      "currency": "CNY",
      "counterparty": "jBciDE8Q3uJjf111VeiUNM775AMKHEbBLS",
      "value": "1"
      }
      }
      },
    *    
    */
    public function getOperation()
    {
        //info to build the server URL
        if ( $this->order_type == '')
          throw new Exception('Order type is not set');
        
        if ( $this->tum0 == '' || $this->tum1 == '')
          throw new Exception('Missing tum information');

        if ( $this->src_amount == '')
          throw new Exception('Source amount is not set');

        if ( $this->src_price == '')
          throw new Exception('Price is not set');

        $order['type'] = $this->order_type;

        //Set the taker_pays and gets according to
        //the order type
        $des_amount = $this->src_amount * $this->src_price;
        if ( $this->order_type == 'buy'){
          
          //buy the tum owned
          $taker_pays = $this->tum0;
          $taker_gets = $this->tum1;

          $taker_pays['value'] = strval($this->src_amount);
          $taker_gets['value'] = strval($des_amount);          
        }else{
          
          //Sell
          $taker_pays = $this->tum1;
          $taker_gets = $this->tum0;

          $taker_pays['value'] = strval($des_amount);
          $taker_gets['value'] = strval($this->src_amount);   
        }

        // echo $this->src_price."\n";
        // echo $this->src_amount."\n";



        $order['taker_pays'] = $taker_pays;
        $order['taker_gets'] = $taker_gets;
        
        //info to submit
        $params['account'] = $this->src_address;
        $params['type'] = $this->order_type;
        $params['order'] = $order;
 
        //info to submit
        // $out_json['secret'] = $this->src_secret;
        // $out_json['operation'] = $params;

        return $params;
    }

    //Setup the tum pair
    public function setPair($in_str)
    {
       
       if ( is_string($in_str)) {
        $pair = explode('/', $in_str);


        if (count($pair) != 2)
          throw new Exception("Input should have a pair", 1);
          
          echo "set taker pays $pair[0]\n";

          $this->tum0 = $this->getTumfromPair($pair[0]);

          $this->tum1 = $this->getTumfromPair($pair[1]);

          //var_dump($this->tum0);
          //var_dump($this->tum1);

       }
       else{
         printf("Errors in the input tum pair %s\n",$in_str);
         return false;
       }
       //may need to check if the value is boolean or not
    }

    //Setup the source tum amount
    public function setAmount($in_value)
    {
       
       if ( is_numeric($in_value)) {
         $this->src_amount = $in_value;
       }
       else{
         printf("Errors in the input Amount %s\n",$in_value);
         return false;
       }
       //may need to check if the value is boolean or not
    }

    //Setup the source tum amount
    public function setPrice($in_price)
    {
       
       if ( is_numeric($in_price)) {
        $this->src_price = $in_price;
     
       }
       else{
         printf("Errors in the input price %s\n",$in_price);
         return false;
       }
       //may need to check if the value is boolean or not
    }




    //May need to check if the cmd is valid or not.
    //Add the check of the taker_pays 
    //and the taker_gets

    public function submit($call_back_func=null)
    {
        //info to build the server URL
        if ( $this->order_type == '')
          throw new Exception('Order type is not set');
        
        if ( $this->tum0 == '' || $this->tum1 == '')
          throw new Exception('Missing tum information');

        if ( $this->src_amount == '')
          throw new Exception('Source amount is not set');

        if ( $this->src_price == '')
          throw new Exception('Price is not set');

        $order['type'] = $this->order_type;

        //Set the taker_pays and gets according to
        //the order type
       $des_amount = $this->src_amount * $this->src_price;

        if ( $this->order_type == 'buy'){
          
          //buy AAA/BBB 
          //User gets AAA pays BBB,
          //BBB should be freeze
          $taker_pays = $this->tum0;
          $taker_gets = $this->tum1;

          $taker_pays['value'] = strval($this->src_amount);
          $taker_gets['value'] = strval($des_amount);          
        }else{
          
          //sell AAA/BBB
          //User pays AAA gets BBB
          $taker_gets = $this->tum0;          
          $taker_pays = $this->tum1;


          $taker_pays['value'] = strval($des_amount);
          $taker_gets['value'] = strval($this->src_amount);   
        }

        // echo $this->src_price."\n";
         echo $this->order_type.":".$this->src_amount."\n";



        $order['taker_pays'] = $taker_pays;
        $order['taker_gets'] = $taker_gets;
        
        //info to submit
        $params['secret'] = $this->src_secret;
        $params['order'] = $order;
        
        $cmd['method'] = 'POST';
        $cmd['url'] = str_replace("{0}", $this->src_address, ORDERS)
                      . '?validated=' . $this->sync;
        $cmd['params'] = $params;

        //submit the command and return the results 
                //submit the command and return the results 
        if ($call_back_func)
        {
          $call_back_func($this->api_server->submitRequest($cmd, $this->src_address, $this->src_secret));
        }
        else
          return $this->api_server->submitRequest($cmd, $this->src_address, $this->src_secret);
   
    }
    
}//end 
?>
