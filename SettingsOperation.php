<?php
/**
 * PHP SDK for Jingtum network； SettingsOperation
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
 *   SettingsOperation
 * 02/18/2017
 * changed
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
//API接口/v1/accounts/{:source_address}/settings?validated=true，POST方法
class SettingsOperation extends OperationClass
{
    //Operations need to submit
    //private $settings = '';//array holding the settings
    private $wallet = null;

    private $regular_key='';
    private $disable_master = '';

    private $disallow_swt = '';
    private $domain = '';
    private $email_hash= '';
    private $global_freeze= '';
    private $message_key= '';
    private $no_freeze= '';
    private $password_spent= '';
    private $require_authorization= '';
    private $require_destination_tag= '';
    private $transfer_rate = '';
    private $wallet_locator= '';
    private $wallet_size= '';    

    private $nickname='';

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
        
        $this->disable_master = (boolean)($in_wallet->getDisableMaster());
        $this->disallow_swt = $in_wallet->getDisallowSwt();
        $this->domain = $in_wallet->getDomain();
        $this->email_hash = $in_wallet->getEmail();
        $this->global_freeze = $in_wallet->getGlobalFreeze();
        $this->message_key = $in_wallet->getMessageKey();
        $this->no_freeze = $in_wallet->getNoFreeze();
        
        //$this->password_spent = $in_wallet->getPasswordSpent();
        $this->require_authorization = (boolean)($in_wallet->getRequireAuthorization());
        $this->require_destination_tag = (boolean)($in_wallet->getRequireDestinationTag());
        $this->transfer_rate = $in_wallet->getTransferRate();
        $this->wallet_locator = $in_wallet->getWalletLocator();
        $this->wallet_size = $in_wallet->getWalletSize();
        }else
          throw new Exception("Input is not a valid wallet object");
    }
  
     

       //May need to check if the cmd is valid or not.
    public function setDisableMaster($in_val){$this->disable_master = $in_val;} 
    public function setDisallowSwt($in_val){ $this->disallow_swt = $in_val;} 
    public function setDomain($in_val){ $this->domain= $in_val;}
    public function setEmail($in_val){ $this->email_hash= $in_val;} 
    //public function setGlobalFreeze($in_val){ $this->global_freeze= $in_val;} 
    public function setMessageKey($in_val){ $this->message_key= $in_val;} 
    //public function setNoFreeze($in_val){ $this->no_freeze= $in_val;} 
    public function setNickname($in_val){ $this->nickname= $in_val;} 
    public function setRegularKey($in_key){ $this->nickname= $in_val;} 

    //public function setPasswordSpent($in_val){ $this->password_spent= $in_val;} 
    public function setRequireAuthorization($in_val){ $this->require_authorization= $in_val;} 
    public function setRequireDestinationTag($in_val){ $this->require_destination_tag= $in_val;} 
    public function setTransferRate($in_val){ $this->transfer_rate= $in_val;} 
    public function setWalletLocator($in_val){ $this->wallet_locator= $in_val;}
    public function setWalletSize($in_val){ $this->wallet_size= $in_val;}  


    //using all the info to create the operation URL
    //to use for Server submission
    //return the operation data
    //method = 'GET', 'POST', 'DEL' for network requests
    //url = URL used for commands
    //params = Parameters needed to post to the server
    public function submit($call_back_func)
    {
        //info to build the server URL
        $sets['disable_master'] =   $this->disable_master;
        $sets['disallow_swt'] = $this->disallow_swt;
        $sets['domain'] = $this->domain;
        $sets['email_hash'] = $this->email_hash;
        //$sets['global_freeze'] = $this->global_freeze;
        $sets['message_key'] = $this->message_key;
        //$sets['no_freeze'] = $this->no_freeze;
        //$sets['password_spent'] = $this->password_spent;
        $sets['require_authorization'] = $this->require_authorization;
        $sets['require_destination_tag'] = $this->require_destination_tag;
        $sets['transfer_rate'] = $this->transfer_rate;
        $sets['wallet_locator'] = $this->wallet_locator;
        $sets['wallet_size'] = $this->wallet_size;
        $sets['nickname'] = $this->nickname;
 
        //info to submit
        $params['secret'] = $this->src_secret;
        $params['settings'] = $sets;
        
        //send out the POST
        $cmd['method'] = 'POST';
        $cmd['url'] = str_replace("{0}", $this->src_address, SETTINGS) . '?validated=' . $this->sync;
        $cmd['params'] = $params;

        //submit the command and return the results 
        if ($call_back_func)
        {
          $call_back_func($this->api_server->submitRequest($cmd, $this->src_address, $this->src_secret));
        }
        else
          return $this->api_server->submitRequest($cmd, $this->src_address, $this->src_secret);
    }
    
}
