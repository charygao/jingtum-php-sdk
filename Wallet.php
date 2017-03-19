<?php
/**
 * PHP SDK for Jingtum network； Wallet Class
 * @version 1.1.0
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
 * contains the following API 
 * getOrderBook	获得货币对的挂单列表
 * getOrder	获得尚未成交的单个挂单
 * getOrderList	获得尚未成交的挂单列表
 * getPayment	查询单个支付信息
 * getTransaction	查询单个交易记录信息
 * getWallet	获得当前钱包地址和私钥
 * setActivated	设置钱包激活的状态
 * getPaymentList      
 * getTransactionList
 * 
 */
namespace JingtumSDK;

require_once('vendor/autoload.php');
require_once 'AccountClass.php';
require_once 'Server.php';
require_once './lib/Constants.php';
require_once './lib/SignUtil.php';
require_once './lib/ECDSA.php';
require_once './lib/DataCheck.php';

use JingtumSDK\AccountClass;
use JingtumSDK\APIServer;
use JingtumSDK\TumServer;
use JingtumSDK\lib\SnsNetwork;
use JingtumSDK\lib\ECDSA;

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

/**
 * Wallet class to set the
 * properties, local methods
 * remote method with only "GET" 
 */

class Wallet extends AccountClass
{
    
//secret	String	井通钱包私钥
//address	String	需设置操作的井通地址
//transfer_rate	Integer	手续费
//password_spent	Boolean	帐户是否使用其免费setregularkey交易
//require_destination_tag	Boolean	是否允许目标方标记
//require_authorization	Boolean	是否允许授权
//disallow_swt	Boolean	是否禁用井通币
//disable_master	Boolean	是否禁用该账户的私钥
//no_freeze	Boolean	账号是否全局解冻
//global_freeze	Boolean	账号是否全局冻结
//transaction_sequence	Integer	单子序号
//email_hash	String	电子邮件地址
//wallet_locator String	钱包定位器
//wallet_size	Integer	钱包大小
//message_key	String	公共密钥，用于发送加密的邮件到这个帐户
//domain	String	域名
    
   
//settings
    
    //private $transaction_sequence= '';
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


private $nick_name= '';    

    //API server address
    private $api_server = NULL;//Server
    private $path_key_list = NULL;

    function __construct($secret, $address=NULL)
    {
        parent::__construct($secret, $address);

        $this->api_server = APIServer::getInstance();
    }

    function __destruct() {
       //echo "Destructing " . $this->address . " account!\n";
    }

    /*
     * Set the API server
     * 
    */
    protected function setAPIServer($in_server)
    {
        //Init the Server class object
        if ( is_object($in_server) ){
          $this->api_server = $in_server;
          echo "Set API server in $this->address \n";
          return true;
        }
        else
          return false;
    }


/*
 * settings
*/
    /*
     * get the API server
    */
    public function getAPIServer(){
        return $this->api_server;
    }

    /*
     * set the Wallet settings using input array
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
    */
    private function setSettings($in_set)
    {
     //try{
      $this->disable_master = $in_set['disable_master'];
      $this->disallow_swt = $in_set['disallow_swt'];
      $this->domain = $in_set['domain'];
      $this->email_hash = $in_set['email_hash'];
      $this->global_freeze = $in_set['global_freeze'];
      $this->message_key = $in_set['message_key'];
      $this->no_freeze = $in_set['no_freeze'];
      //$this->password_spent = $in_set['password_spent'];
      $this->require_authorization = $in_set['require_authorization'];
      $this->require_destination_tag = $in_set['require_destination_tag'];
      $this->transfer_rate = $in_set['transfer_rate'];
      $this->wallet_locator = $in_set['wallet_locator'];
      $this->wallet_size = $in_set['wallet_size'];



    }
    /*
     * get the Wallet settings from the API server
    */
    public function getSettings()
    {
        $cmd['method'] = 'GET';
        $cmd['url'] = str_replace("{0}",$this->address, SETTINGS);
        $cmd['params'] = '';

        //Get the settings and update the internal
        //properties
        if ( is_object($this->api_server)){
           $ret = $this->api_server->submitRequest($cmd,
             $this->address, $this->secret);
         if ( $ret['success'] == true ){

            $this->setSettings($ret['settings']);
            //$this->disable_master = $ret['settings']

         }
         return $ret;
        }
        else
           throw new Exception('API Server is not ready!');
    }
    
    //return attributes of settings
    public function getDisableMaster(){return $this->disable_master;} 
    public function getDisallowSwt(){return $this->disallow_swt;} 
    public function getDomain(){return $this->domain;}
    public function getEmail(){return $this->email_hash;} 
    public function getGlobalFreeze(){return $this->global_freeze;} 
    public function getMessageKey(){return $this->message_key;} 
    public function getNoFreeze(){return $this->no_freeze;} 
    //public function getPasswordSpent(){return $this->password_spent;} 
    public function getRequireAuthorization(){return $this->require_authorization;} 
    public function getRequireDestinationTag(){return $this->require_destination_tag;} 
    public function getTransferRate(){return $this->transfer_rate;} 
    public function getWalletLocator(){return $this->wallet_locator;}
    public function getWalletSize(){return $this->wallet_size;}  
 
    /**
     *
     * @param string $order, hash value          
     * @return multitype:
     * 获取单个挂单信息
     */
    public function getOrder($order_hash = '')
    {
        $cmd['method'] = 'GET';
        $cmd['url'] = str_replace("{0}",$this->address, ORDERS).$order_hash;
        $cmd['params'] = '';

        if ( is_object($this->api_server))
           return convertOrder($this->api_server->submitRequest($cmd,
             $this->address, $this->secret));
        else
           throw new Exception('API Server is not ready!');
    }

    /**
     *
     * @param hash value        
     * @return multitype:
     * 获取单个挂单信息
     */
    public function getMessage($msg_hash = '')
    {
        $cmd['method'] = 'GET';
        $cmd['url'] = str_replace("{0}",$this->address, TRANSACTIONS).$msg_hash;
        $cmd['params'] = '';

        if ( is_object($this->api_server))
           return convertOrder($this->api_server->submitRequest($cmd,
             $this->address, $this->secret));
        else
           throw new Exception('API Server is not ready!');
    }

    /**
     *
     * 获得货币对的挂单列表， 通过以下API发送接口
     * /v1/accounts/{:address}/order_book/{:base}/{:counter}
     * 需要检测输入参数的格式。
     * base (code+counterparty) 
     * counter
     * move to FinGate
     */
    private function getOrderBook($base, $counter)
    {
        $cmd['method'] = 'GET';
        $cmd['url'] = str_replace("{0}",$this->address, ORDERS). '/' .$base. '/' .$counter;
        $cmd['params'] = '';
        
        if ( is_object($this->api_server))
           return convertOrderBook($this->api_server->submitRequest($cmd,
             $this->address, $this->secret));
        else
           throw new Exception('API Server is not ready!');
    }
    
    /**
     *
     * 获得尚未成交的挂单列表
     * 无输入参数，返回挂单列表。
     */
    public function getOrderList()
    {
        $cmd['method'] = 'GET';
        $cmd['url'] = str_replace("{0}",$this->address, ORDERS);
        $cmd['params'] = '';
        
        if ( is_object($this->api_server))
           return convertOrderList($this->api_server->submitRequest($cmd,
             $this->address, $this->secret));
        else
           throw new Exception('API Server is not ready!');
    }
    
    /**
     *
     * 查询单个支付信息
     * /v1/accounts/{:address}/transactions/{:id}，GET方法
     */
    public function getPayment($id = '')
    {
        $cmd['method'] = 'GET';
        $cmd['url'] = str_replace("{0}",$this->address, PAYMENTS). $id;
        $cmd['params'] = '';
        
        if ( is_object($this->api_server))
           return $this->api_server->submitRequest($cmd,
             $this->address, $this->secret);
        else
           throw new Exception('API Server is not ready!');
    }
    
    /**
     *
     * 查询单个交易记录信息
     * /v1/accounts/{:address}/transactions/{:id}，GET方法
     * $id是 HASH 或资源号
     */
    public function getTransaction($id = '')
    {
        $cmd['method'] = 'GET';
        $cmd['url'] = str_replace("{0}",$this->address, TRANSACTIONS).$id;
        $cmd['params'] = '';
        
        if ( is_object($this->api_server))
           return convertTransaction($this->api_server->submitRequest($cmd,
             $this->address, $this->secret));
        else
           throw new Exception('API Server is not ready!');
    }
    
    /**
     *
     * 获得当前钱包地址和私钥
     * 返回钱包对象
     */
    public function getWallet()
    {
        $ret['address'] = $this->address;
        $ret['secret'] = $this->secret;

        return $ret;
    }
    
    
    
    /**
     * Add the options
     * Changed the counter_party to issuer
     * in the return.
     * 
     * @param string $currency            
     * @param string $counter_party            
     * @return multitype:
     */
    public function getBalance($currency = null, $issuer = null)
    {
      $cmd['method'] = 'GET';
      //$cmd['url'] = str_replace("{0}",$this->address, BALANCES);
      if ( $currency != null )
        $in_options['currency'] = $currency;

      if ($issuer != null)
        $in_options['counter_party'] = $issuer;
      
      //build the url options
      if ( ! empty($in_options) )
      {
          //parse the options into string
          $parm_str = SnsNetwork::makeQueryString($in_options);
          //Attach to the end of the URL
          //
          $cmd['url'] = str_replace("{0}",$this->address, BALANCES)
            .'?'.$parm_str;
      }
      else
        $cmd['url'] = str_replace("{0}",$this->address, BALANCES);


      $cmd['params'] = '';

      if ( is_object($this->api_server)){
        //Send out the request 
        //and change the parameters.

        $ret = $this->api_server->submitRequest($cmd, 
          $this->address, $this->secret); 

        if ( $ret['success'] == true){
          echo "Get balnces done\nConverting...\n";
          return convertBalances($ret);

        }else
          return $ret;
      }
      else
        throw new Exception('API Server is not ready!');
    }

    /**
     * Using the input path list
     * to compute the key 
     * and saved in the interal array
     * using sha1
     * @param path $in_path
     * @return key as the hash of path
     */
    private function setPathKeyList($in_path_list)
    {
      $ecdsa = new ECDSA();

      $path_num = count($in_path_list);

      //reset the internal path list to set
      //the new paths
      $this->path_key_list = array();
      //set the return array for the response
      //this array won't contain the actual value
      //but only keeps the amount
      $key_list = array();
      for ( $i = 0; $i < $path_num; $i++)
      {
        $path_pair['value'] = $in_path_list[$i]['paths'];
        $path_pair['choice'] = $in_path_list[$i]['source_amount'];

        //build a key/pair to save inside the Wallet class
        $new_ret['choice'] = $in_path_list[$i]['source_amount'] ;
        $new_ret['key'] = $ecdsa->hash160($path_pair['value']);

        $path_pair['key'] = $new_ret['key']; 

        
        $key_list[] = $new_ret; 
        $this->path_key_list[] = $path_pair;
      }

      return $key_list;
    }

    /**
     * Generate the key from input path
     * using sha1
     * @param path $in_path
     * @return key as the hash of path
     */
    public function getPathByKey($in_key)
    {
      $find_path = NULL;
      $i = 0;
      while ($i < count($this->path_key_list))
      {
        if ( strcmp($this->path_key_list[$i]['key'], $in_key ) == 0){
          $find_path = $this->path_key_list[$i]['value'];
          break;
        }
        $i++;
      }
      return $find_path;
    }
    /**
     *
     * @param unknown $dest_address            
     * @param unknown $amount            
     * @return the path from :
     */
    //public function getPathList($dest_address, $amount)
    public function getChoices($dest_address, $amount)
    {
        if ( is_object($amount)){
          $payment = $amount->getValue().'+'.$amount->getCurrency().'+'.$amount->getIssuer();
        }else{
          if ( is_array($amount)){
          $payment = $amount['value'].'+' . $amount['currency'] . '+' . $amount['issuer'];
          }
          else
            throw new Exception('Input amount should be an object or array!');
        }
        $cmd['method'] = 'GET';
        $cmd['url'] = str_replace("{0}",$this->address, PAYMENT_PATHS). $dest_address. '/' .$payment;
        $cmd['params'] = '';
        
        if ( is_object($this->api_server)){
          $ret = $this->api_server->submitRequest($cmd,
             $this->address, $this->secret);
          //Added the sha function
          if ( $ret['success'] == 'true' ){
            //Loop throu the pathList
            //and hash them
            $new_ret['success'] = 'true';
            $new_ret['payments'] =  $this->setPathKeyList($ret['payments']);
            return $new_ret;
          }
          else
            return $ret; 
        }
        else
           throw new Exception('API Server is not ready!');
    }

    
    /**
     *
     * @param string $id            
     * @return multitype:
     * 返回当前账号的支付列表
     */
    public function getPaymentList($in_options = '')
    {

      //build the url options
      if ( ! empty($in_options) )
      {
          //parse the options into string
          $parm_str = SnsNetwork::makeQueryString($in_options);
          //Attach to the end of the URL
          //
          $cmd['url'] = str_replace("{0}",$this->address, PAYMENTS)
            .'?'.$parm_str;
      }
      else
        $cmd['url'] = str_replace("{0}",$this->address, PAYMENTS);

        $cmd['method'] = 'GET';
        $cmd['params'] = '';
      
        if ( is_object($this->api_server))
           return $this->api_server->submitRequest($cmd,
             $this->address, $this->secret);
        else
           throw new Exception('API Server is not ready!');
    }

    /**
     * Return the signers of the account
     * 
     * @return multitype:
     */
    public function getSigners()
    {

        $cmd['method'] = 'GET';
        $cmd['url'] = str_replace("{0}",$this->address, SIGNERS);
        $cmd['params'] = '';
        
        if ( is_object($this->api_server))
           return $this->api_server->submitRequest($cmd,
             $this->address, $this->secret);
        else
           throw new Exception('API Server is not ready!');
    }

    /**
     *
     * @param string $currency            
     * @param string $counterparty            
     * @param string $limit            
     * @return multitype:
     */
    public function getTrustLineList($currency = '', $counterparty = '', $limit = '')
    {
        $params['currency'] = $currency;
        $params['counterparty'] = $counterparty;
        $params['limit'] = $limit;
        
        $cmd['method'] = 'GET';
        $cmd['url'] = str_replace("{0}",$this->address, TRUST_LINES);
        $cmd['params'] = $params;
        
        if ( is_object($this->api_server))
           return $this->api_server->submitRequest($cmd,
             $this->address, $this->secret);
        else
           throw new Exception('API Server is not ready!');
    }

    /**
     *
     * @param string $type            
     * @param string $counterparty            
     * @param string $currency                      
     * @return multitype:
     */
    public function getRelation($type = '', $counterparty = '', $currency = '')
    {

        $cmd['method'] = 'GET';
  
      if ( $type != null )
        $in_options['type'] = $type;
          
      if ( $currency != null )
        $in_options['currency'] = $currency;

      if ($counterparty != null)
        $in_options['counterparty'] = $counterparty;
      
      //build the url options
      if ( ! empty($in_options) )
      {
          //parse the options into string
          $parm_str = SnsNetwork::makeQueryString($in_options);
          //Attach to the end of the URL
          echo "PArm: ".$parm_str."\n";
          
          $cmd['url'] = str_replace("{0}",$this->address, RELATIONS)
            .'?'.$parm_str;
      }
      else
        $cmd['url'] = str_replace("{0}",$this->address, RELATIONS).'?type=authorize';

        //$cmd['url'] = str_replace("{0}",$this->address, RELATIONS);
        $cmd['params'] = '';
        
        echo "\n=============\n";

        if ( is_object($this->api_server))
           return $this->api_server->submitRequest($cmd,
             $this->address, $this->secret);
        else
           throw new Exception('API Server is not ready!');
    }


    /**
     * Using input options to 
     * filter out the transactions of the account.
     * @param string $id
     * 查询交易记录信息
     * @return multitype:
     */
    public function getTransactionList($in_options = '')
    {
      //build the url options
      if ( ! empty($in_options) )
      {
          //parse the options into string
          $parm_str = SnsNetwork::makeQueryString($in_options);
          //Attach to the end of the URL
          //
          $cmd['url'] = str_replace("{0}",$this->address, TRANSACTIONS)
            .'?'.$parm_str;
      }
      else
        $cmd['url'] = str_replace("{0}",$this->address, TRANSACTIONS);

        $cmd['method'] = 'GET';
        $cmd['params'] = '';
       
        if ( is_object($this->api_server))
           return $this->api_server->submitRequest($cmd,
             $this->address, $this->secret);
        else
           throw new Exception('API Server is not ready!');
    }

    /**
     *
     * @return boolean
     * Determine if an account is activated by 
     * comparing the SWT with min limit.
     */
    public function isActived()
    {
        $balance = self::getBalance('SWT')['balances'][0];
        print_r($balance); 
        if ($balance['value'] >= 25) {
            return true;
        } else {
            return false;
        }
    }
  
}
