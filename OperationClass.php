<?php
/**
 * PHP SDK for Jingtum network； OperationClass
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
 * 09/25/2016
 * 12/28/2016
 * Added server class for each operation
 * to simplify the steps.
 * For each operation, need to be initial with
 * a Wallet object, then using . 
 * OperationCalss 
 */
namespace JingtumSDK;

/**
 * 命令常量定义
 */
define('BALANCES', '/accounts/{0}/balances/');
define('PAYMENT_PATHS', '/accounts/{0}/payments/paths/');
define('PAYMENTS', '/accounts/{0}/payments/');
define('ORDERS', '/accounts/{0}/orders/');
define('ORDERBOOK', '/accounts/{0}/order_book/');
define('TRUST_LINES', '/accounts/{0}/trustlines/');
define('RELATIONS', '/accounts/{0}/relations/');
define('CORELATIONS', '/accounts/{0}/co-relations/');
define('TRANSACTIONS', '/accounts/{0}/transactions/');
define('OPERATIONS', '/accounts/{0}/operations/');
define('MESSAGES', '/accounts/{0}/messages/');
define('SETTINGS', '/accounts/{0}/settings/');


//base class
abstract class OperationClass
{
    //All the operations need to have a source account
    //to start with.
    protected $src_address = '';
    protected $src_secret = '';
    protected $sync = 'true';
    protected $serverURL= NULL;
    // Force Extending class to define this method
    // All the operation methods should contain this
    // but can define it differently.
    //this function uses all the info to create the operation URL
    //to use for Server submission
    //return the operation data
    //method = 'GET', 'POST', 'DEL' for network requests
    //url = URL used for commands
    //params = Parameters needed to post to the server.
    abstract protected function submit();
    
    function __construct($in_wallet)
    {
      //echo "OperationClass constructor\n";
      if ( is_object($in_wallet)){
        $this->src_address = $in_wallet->getAddress();
        $this->src_secret = $in_wallet->getSecret();
      }
    }

    //Set the operation address
    public function setSrcAddress($in_address)
    {
      $this->src_address = $in_address;
    }

    //Set the operation address' secret
    public function setSrcSecret($in_secret)
    {
       $this->src_secret = $in_secret;
    }
 
    //Changed from setSrcSecret
    //to sign
    //
    public function sign($in_secret)
    {
       $this->src_secret = $in_secret;
    }
   
    /*
     * Set the operation mode to 
     * true - Synchronous mode
     * false - Asynchronous Mode
     * Note the input need to be a string instead
     * of boolean. 
     */
    public function setValidate($in_sync)
    {
       //may need to check if the value is boolean or not
       //The flag need to be a string instead of bool
       if ( is_bool($in_sync) ){
         if ( $in_sync === true )
           $this->sync = 'true';
         else{
           if ( $in_sync === false )
             $this->sync = 'false';
           else
             throw new Exception('Error in input value!');
         }
       }else
       {
         if ( is_string($in_sync) ){
           if ( trim($in_sync) == 'true' )
             $this->sync = 'true';
           else{
             if ( trim($in_sync) == 'false' )
               $this->sync = 'false';
             else
               throw new Exception('Error in input value!');
           }
         }
      }  
    }//end function

    public function setSyn($in_var)
    {
      $this->setValidate($in_var);
    }


    {
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
    protected function submitRequest($in_cmd)
    {
        //Generate a full url with server address and API version
        //info
        $url = $this->serverURL .'/'. $this->version . $in_cmd['url'];

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
        print_r($in_cmd['params']);
        //Submit the parameters to the SERVER
        $ret = SnsNetwork::api($url,
          json_encode($in_cmd['params']),
          $in_cmd['method']);
        return $ret;

    }

}

