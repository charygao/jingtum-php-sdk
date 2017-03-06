<?php
/**
 * PHP SDK for Jingtum network； data check functions
 * @version 1.0.0
 * @author Zhengpeng Li
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
 * Data functions used to check the valid data types.
 * contains the following functions
 */
namespace JingtumSDK;

require('vendor/autoload.php');
//require_once 'AccountClass.php';
require_once './lib/SignUtil.php';


/*CONSTANTS used in data checking*/
define('CURRENCY_NAME_LEN', 3);
define('TUM_NAME_LEN', 40);

/*
//Types of Tum
    //native: SWT
    //currency: CNY, USD, EUR,
  /
  tum: Custom tum, 40 characters
*/

function decideType($in_str)
{
    $type = 'unknown';

    if ($in_str == 'SWT' )
      $type = 'native';
    else{
      if ( isCurrency($in_str) )
        $type = 'currency';
      else{
        if ( isTum($in_str))
          $type = 'tum';
      }
        
    }

    return $type;
}

/*
 * Return true if the input string is a valid currency code.
*/
function isCurrency($in_str)
{
    if (is_string($in_str) && strlen($in_str) == CURRENCY_NAME_LEN)
        return true;
    else
        return false;
}

/*
 * Return true if the input string is a valid Tum code.
*/
function isTum($in_str)
{
    if (is_string($in_str) && strlen($in_str) == TUM_NAME_LEN)
        return true;
    else
        return false;
}

/*
 * Return true if the input string is a valid Amount object
*/
function isAmount($in_obj)
{
    if (is_object($in_obj)){
      if ( is_string($in_obj->currency ) )
//        strlen($in_str) == TUM_NAME_LEN)
        return true;
    }
    else
        return false;
}

/*
 * Return a Tum amount object
*/
function getAmount($in_code, $in_value, $in_issuer = '')
{
  $out_amount['currency'] = $in_code;
  $out_amount['value'] = $in_value;
  $out_amount['issuer'] = $in_issuer;

  return $out_amount;
}

/*
 * Return a Tum object using input str
 * currency:issuer
*/
function getTumfromPair($in_str)
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
 * Return a Tum str using input array
 * [currency]
 * [counterparty]
 * Output:
 * currency:issuer
*/
function getPairFromArray($in_array)
{

  //If the input has two part, 
  //assume one is the issuer
  try {
  if ( $in_array['currency'] == 'SWT'){
    $out_pair = $in_array['currency'];
  }
  else
  {
    $out_pair = $in_array['currency'].":".$in_array['counterparty'];
  }
  }catch (Exception $e){
    echo "Error ".$e->getMessage()."\n";
  }
  
  return $out_pair;
}
/*
 * Return an Array of in_balance_array
 *
*/
function convertBalances($in_ret)
{
          echo "processing outputs\n";

$new_ret = array();
          foreach ($in_ret as $key => $value) {
            # code...
            //Find the balances
            if ($key == 'balances'){
              // echo "Balance size\n";
              // echo count($key);
              // echo "------------\n";
              $new_balances = array();
              $new_bal=array();

              foreach ($value as $balkey => $balvalue) {
                              $new_bal['currency'] = $balvalue['currency'];
              $new_bal['value'] = $balvalue['value'];
              $new_bal['freezed'] = $balvalue['freezed'];
              $new_bal['issuer'] = $balvalue['counterparty'];
array_push($new_balances, $new_bal);
            }
              

              $new_ret[$key] = $new_balances;
            }else{
             $new_ret[$key] = $value;
            }
          }
          return $new_ret;

}

/*
 * Convert an order in the transaction
 * from type/taker_gets/taker_pays
 * to type/pair/amount/price
 * Input:
 * "order": {
  "type": "sell",
  "taker_pays": {
  "currency": "CNY",
  "counterparty": "janxMdrWE2SUzTqRUtfycH4UGewMMeHa9f",
  "value": "1"
  },
  "taker_gets": {
  "currency": "SWT",
  "value": "10"
  }
  }
 * Output:
 * "order": {
  "type": "sell",
  "pair": "SWT/CNY:j...s",
  "amount":"1",
 * 
*/
function convertTransaction($in_ret)
{
  return $in_ret;
  //may need to check if the input is an order
          echo "processing Order\n";

$new_ret = array();
//Copy the items in the info and converted the
//bids and asks array
          foreach ($in_ret as $key => $value) {
            # code...
            //Find the balances
            if ($key == 'transaction'){
              echo "tx size\n";
              echo count($key);
              echo "------------\n";
              $new_tx=array();
              foreach ($value as $balkey => $balvalue) {
                              $new_bal['currency'] = $balvalue['currency'];
              $new_bal['value'] = $balvalue['value'];
              $new_bal['freezed'] = $balvalue['freezed'];
              $new_bal['issuer'] = $balvalue['counterparty'];
              }
              

              $new_ret[$key] = $new_tx;
            }elseif ($key == 'asks'){

              $new_ret[$key] = $new_asks;
            }else{
             $new_ret[$key] = $value;
            }
          }
          return $new_ret;

}

/*
 * Return a new OrderBook
 * Input:
 * "price": {
"currency": "USD",
"counterparty": "jBciDE8Q3uJjf111VeiUNM775AMKHEbBLS",
"value": "0.88"
},
"taker_gets_funded": {
"currency": "CNY",
"counterparty": "jBciDE8Q3uJjf111VeiUNM775AMKHEbBLS",
"value": "93"
},
"taker_gets_total": {
"currency": "CNY",
"counterparty": "jBciDE8Q3uJjf111VeiUNM775AMKHEbBLS",
"value": "93"
},
"taker_pays_funded": {
"currency": "USD",
"counterparty": "jBciDE8Q3uJjf111VeiUNM775AMKHEbBLS",
"value": "81.84"
},
"taker_pays_total": {
"currency": "USD",
"counterparty": "jBciDE8Q3uJjf111VeiUNM775AMKHEbBLS",
"value": "81.84"
},
"order_maker": "js46SK8GtxSeGRR6hszxozFxftEnwEK8my",
"sequence": 12,
"passive": false,
"sell": true
}
 * Output:
 * { price: '6',
       order_maker: 'jE4G6wkGswACmjWtRWBri5DwZSh3HUWvQJ',
       sequence: 5,
       funded: '3',
       total: '3' }
 * New Order only has 5 keys
 * price: counter_currency_value/base_currency_value
 * sequence: sequence
 * order_maker: 
*/
function convertOrderBook($in_ret)
{
          echo "processing OrderBook\n";

$new_ret = array();
//Copy the items in the info and converted the
//bids and asks array
          foreach ($in_ret as $key => $value) {
            # code...
            //Find the balances
            if ($key == 'bids'){
              echo "bids size\n";
              echo count($key);
              echo "------------\n";
              $new_bids = array();
              $new_bid=array();

              foreach ($value as $inkey => $invalue) {
                              $new_bid['price'] = $invalue['price']['value'];
              $new_bid['funded'] = $invalue['taker_pays_funded']['value'];
              $new_bid['total'] = $invalue['taker_pays_total']['value'];
              $new_bid['sequence'] = $invalue['sequence'];
              $new_bid['order_maker'] = $invalue['order_maker'];

              //added the converted item to the new array
              array_push($new_bids, $new_bid);
              }//end foreach
              $new_ret[$key] = $new_bids;

            }elseif ($key == 'asks'){
              echo "ask size\n";
              echo count($key);
              echo "------------\n";
              $new_asks = array();
              $new_ask=array();
              foreach ($value as $inkey => $invalue) {
                              $new_ask['price'] = $invalue['price']['value'];
              $new_ask['funded'] = $invalue['taker_gets_funded']['value'];
              $new_ask['total'] = $invalue['taker_gets_total']['value'];
              $new_ask['sequence'] = $invalue['sequence'];
              $new_ask['order_maker'] = $invalue['order_maker'];
array_push($new_asks, $new_ask);
                # code...
/*if($balkey == 'counterparty')
                  $new_balances['issuer']=$balvalue;
                else
                  $new_balances[$balkey]=$balvalue;
  */            }
              

              $new_ret[$key] = $new_asks;
            }else{
             $new_ret[$key] = $value;
            }
          }
          return $new_ret;

}

/*
 * Convert single order to the new format
 * "order": {
"account": "jf96oSdxU7kwfCHF2sjm9GmcvhFBcfN8Py",
"taker_pays": {
"currency": "CNY",
"counterparty": "janxMdrWE2SUzTqRUtfycH4UGewMMeHa9f",
"value": "1"
},
"taker_gets": {
"currency": "SWT",
"counterparty": "",
"value": "10"
},
"passive": false,
"type": "buy",
"sequence": 761
}
* To
*    { account: 'jf96oSdxU7kwfCHF2sjm9GmcvhFBcfN8Py',
     type: 'buy',
     sequence: 761,
     pair: 'SWT/CNY:janxMdrWE2SUzTqRUtfycH4UGewMMeHa9f',
     amount: '0.01',
     price: 1 } 

*/

function convertOrder($in_ret)
{
          echo "processing Order\n";
          $new_ret = array();
//Copy the items in the info and converted the
//bids and asks array
          foreach ($in_ret as $key => $value) {
            # code...
            //Find the orders array
            if ($key == 'order'){
              echo "convert order\n";
              echo count($key);
              echo "------------\n";
              $new_order=array();
                              
              $new_order['sequence'] = $value['sequence'];
              $new_order['type'] = $value['type'];
              //compute the base currency amount
              //and price
              if ($new_order['type'] == 'sell'){
                //base currency is taker_gets
                $new_order['price'] = $value['taker_pays']['value']/$value['taker_gets']['value'];
                $new_order['amount'] = $value['taker_gets']['value'];
                // $new_order['pair'] = getPairFromArray($value['taker_gets']['currency'].":".$value['taker_gets']['counterparty']
                // ."/".$value['taker_pays']['currency'].":".$value['taker_pays']['counterparty'];
                $new_order['pair'] = getPairFromArray($value['taker_gets'])."/".getPairFromArray($value['taker_pays']);
              }elseif ($new_order['type'] == 'buy'){
                //base currency is taker_pays
                $new_order['price'] = $value['taker_gets']['value']/$value['taker_pays']['value'];
                $new_order['amount'] = $value['taker_pays']['value'];
                // $new_order['pair'] = $value['taker_pays']['currency'].":".$value['taker_pays']['counterparty']
                // ."/".$invalue['taker_gets']['currency'].":".$value['taker_gets']['counterparty'];
                $new_order['pair'] = getPairFromArray($value['taker_gets'])."/".getPairFromArray($value['taker_pays']);
              }else{
                echo "Order type error".$value['type'];
              }
              $new_ret[$key] = $new_order;

            }else{
             $new_ret[$key] = $value;
            }
          }
          return $new_ret;

}

/*
 * Convert the order array to the new format

*/

function convertOrderList($in_ret)
{
          echo "processing OrderList\n";

$new_ret = array();
//Copy the items in the info and converted the
//bids and asks array
          foreach ($in_ret as $key => $value) {
            # code...
            //Find the orders array
            if ($key == 'orders'){
              echo "order size\n";
              echo count($key);
              echo "------------\n";
              $new_orders = array();
              $new_order=array();

              foreach ($value as $inkey => $invalue) {
                              
              //$new_order['account'] = $invalue['account'];
              $new_order['type'] = $invalue['type'];
              $new_order['sequence'] = $invalue['sequence'];
              //compute the base currency amount
              //and price
              // if ($new_order['type'] == 'sell'){
              //   $new_order['price'] = $invalue['taker_pays']['value']/$invalue['taker_gets']['value'];
              //   $new_order['amount'] = $invalue['taker_gets']['value'];
              //   $new_order['pair'] = $invalue['taker_gets']['currency'];
              // }
                            if ($new_order['type'] == 'sell'){
                //base currency is taker_gets
                $new_order['price'] = $invalue['taker_pays']['value']/$invalue['taker_gets']['value'];
                $new_order['amount'] = $invalue['taker_gets']['value'];
                // $new_order['pair'] = $invalue['taker_gets']['currency'].":".$invalue['taker_gets']['counterparty']
                // ."/".$invalue['taker_pays']['currency'].":".$invalue['taker_pays']['counterparty'];
                $new_order['pair'] = getPairFromArray($invalue['taker_gets'])."/".getPairFromArray($invalue['taker_pays']);

              }elseif ($new_order['type'] == 'buy'){
                //base currency is taker_pays
                $new_order['price'] = $invalue['taker_gets']['value']/$invalue['taker_pays']['value'];
                $new_order['amount'] = $invalue['taker_pays']['value'];
                // $new_order['pair'] = $invalue['taker_pays']['currency'].":".$invalue['taker_pays']['counterparty']
                // ."/".$invalue['taker_gets']['currency'].":".$invalue['taker_gets']['counterparty'];
                  $new_order['pair'] = getPairFromArray($invalue['taker_pays'])."/".getPairFromArray($invalue['taker_gets']);
              }else{
                echo "Order type error".$invalue['type'];
              }

              //added the converted item to the new array
              array_push($new_orders, $new_order);
              }//end foreach//end foreach
              $new_ret[$key] = $new_orders;

            }else{
             $new_ret[$key] = $value;
            }
          }
          return $new_ret;

}
