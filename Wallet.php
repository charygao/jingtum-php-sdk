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
require_once 'Operation.php';
require_once 'Server.php';
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
    private $transfer_rate = '';
    private $password_spent= '';
    private $require_destination_tag= '';
    private $require_authorization= '';
    private $disallow_swt = '';
    private $disable_master = '';
    private $no_freeze= '';
    private $global_freeze= '';
    private $transaction_sequence= '';
    private $email_hash= '';
    private $wallet_locator= '';
    private $wallet_size= '';
    private $message_key= '';
    private $domain = '';

    //API server address
    private $APIServer = NULL;//Server

    function __construct($address, $secret)
    {
        parent::__construct($address, $secret);
    }

    function __destruct() {
       print "Destructing " . $this->address . " account!\n";
    }

    /*
     * Set the API server
    */
    public function setAPIServer($in_server)
    {
        //Init the Server class object
        if ( is_object($in_server) ){
          $this->APIServer = $in_server;
          return true;
        }
        else
          return false;
    }

    
    /**
     *
     * @param string $order, hash value          
     * @return multitype:
     * 获取单个挂单信息
     */
    public function getOrder($order = '')
    {
        $cmd['method'] = 'GET';
        $cmd['url'] = str_replace("{0}",$this->address, ORDERS).$order;
        $cmd['params'] = '';

        if ( is_object($this->APIServer))
           return $this->APIServer->submitRequest($cmd,
             $this->address, $this->secret);
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
     */
    public function getOrderBook($base, $counter)
    {
        $cmd['method'] = 'GET';
        $cmd['url'] = str_replace("{0}",$this->address, ORDERS). '/' .$base. '/' .$counter;
        $cmd['params'] = '';
        
        if ( is_object($this->APIServer))
           return $this->APIServer->submitRequest($cmd,
             $this->address, $this->secret);
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
        
        if ( is_object($this->APIServer))
           return $this->APIServer->submitRequest($cmd,
             $this->address, $this->secret);
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
        
        if ( is_object($this->APIServer))
           return $this->APIServer->submitRequest($cmd,
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
        
        if ( is_object($this->APIServer))
           return $this->APIServer->submitRequest($cmd,
             $this->address, $this->secret);
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
     *
     * @param string $currency            
     * @param string $counter_party            
     * @return multitype:
     */
    public function getBalance($currency = '', $counter_party = '')
    {
      $cmd['method'] = 'GET';
      $cmd['url'] = str_replace("{0}",$this->address, BALANCES);

      $ecdsa =  new ECDSA();

      $cmd['params'] = '';

      if ( is_object($this->APIServer))
        return $this->APIServer->submitRequest($cmd, 
          $this->address, $this->secret); 
      else
        throw new Exception('API Server is not ready!');
    }

    /**
     *
     * @param unknown $dest_address            
     * @param unknown $amount            
     * @return the path from :
     */
    public function getPathList($dest_address, $amount)
    {
        $payment = $amount['value'].'+' . $amount['currency'] . '+' . $amount['counterparty'];
        $cmd['method'] = 'GET';
        $cmd['url'] = str_replace("{0}",$this->address, PAYMENT_PATHS). '/' .$dest_address. '/' .$payment;
        $cmd['params'] = '';
        
        if ( is_object($this->APIServer))
           return $this->APIServer->submitRequest($cmd,
             $this->address, $this->secret);
        else
           throw new Exception('API Server is not ready!');
    }

    
    /**
     *
     * @param string $id            
     * @return multitype:
     * 返回当前账号的支付列表
     */
    public function getPaymentList()
    {
        $cmd['method'] = 'GET';
        $cmd['url'] = str_replace("{0}",$this->address, PAYMENTS);
        $cmd['params'] = '';
        
        if ( is_object($this->APIServer))
           return $this->APIServer->submitRequest($cmd,
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
        $cmd['params'] = '';
        
        if ( is_object($this->APIServer))
           return $this->APIServer->submitRequest($cmd,
             $this->address, $this->secret);
        else
           throw new Exception('API Server is not ready!');
    }

    /**
     *
     * @param string $type            
     * @param string $counterparty            
     * @param string $currency            
     * @param string $marker            
     * @return multitype:
     */
    public function getRelationList($type = '', $counterparty = '', $currency = '', $marker = '')
    {
        $params['currency'] = $currency;
        $params['counterparty'] = $counterparty;
        $params['type'] = $type;
        $params['marker'] = $marker;

        $cmd['method'] = 'GET';
        $cmd['url'] = str_replace("{0}",$this->address, RELATIONS);
        $cmd['params'] = $params;
        
        if ( is_object($this->APIServer))
           return $this->APIServer->submitRequest($cmd,
             $this->address, $this->secret);
        else
           throw new Exception('API Server is not ready!');
    }

     /**
     *
     * 获得对家关系列表
     */
    public function getCoRelationList($type = '', $counterparty = '', $currency = '', $marker = '')
    {
        $params['currency'] = $currency;
        $params['counterparty'] = $counterparty;
        $params['type'] = $type;
        $params['marker'] = $marker;

        //Create the parameters to be submitted to the Server
        $cmd['method'] = 'GET';
        $cmd['url'] = str_replace("{0}", $this->address, CORELATIONS);
        $cmd['params'] = $params;

        if ( is_object($this->APIServer))
           return $this->APIServer->submitRequest($cmd,
             $this->address, $this->secret);
        else
           throw new Exception('API Server is not ready!');
    }

    /**
     *
     * @param string $id
     * 查询单个交易记录信息
     * @return multitype:
     */
    public function getTransactionList($id = '')
    {
        $cmd['method'] = 'GET';
        $cmd['url'] = str_replace("{0}",$this->address, TRANSACTIONS). $id;
        $cmd['params'] = '';
        
        if ( is_object($this->APIServer))
           return $this->APIServer->submitRequest($cmd,
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
