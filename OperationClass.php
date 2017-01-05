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

require_once './lib/Constants.php';

//base class
abstract class OperationClass
{
    //All the operations need to have a source account
    //to start with.
    protected $src_address = '';
    protected $src_secret = '';
    protected $sync = 'true';
    protected $api_server = NULL;
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
        $this->api_server = $in_wallet->getAPIServer();
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
     * Note the input can be a string
     * or a boolean. 
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

    /*
     * Set the operation mode to syn mode i
     * true - Synchronous mode
     * false - Asynchronous Mode
     * Note the input need to be
    */
    public function setSyn($in_var)
    {
      $this->setValidate($in_var);
    }

}
?>
