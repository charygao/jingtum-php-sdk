<?php
/**
 * PHP SDK for Jingtum network； Operation
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
 * OperationCalss 
 *   GetBalanceOperation - reserved
 *   PaymentOperation
 *   OrderOperation
 *   RelationOperation
 *     AddRelationOperation
 *     RemoveRelationOperation
 *   TrustlineOperation 
 *     AddTrustlineOperation 
 *     RemoveTrustlineOperation 
 *   SetSettingsOperation 
 *   SetRegularkeyOperation 
 *   SetSignersOperation 
 *   MessageOperation 
 *   MultipleOperations 
 */
namespace JingtumSDK;


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
 * 命令定义
 */
define('BALANCES', '/accounts/{0}/balances');
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
    protected $sync = true;
    // Force Extending class to define this method
    // All the operation methods should contain this
    // but can define it differently.
    //this function uses all the info to create the operation URL
    //to use for Server submission
    //return the operation data
    //method = 'GET', 'POST', 'DEL' for network requests
    //url = URL used for commands
    //params = Parameters needed to post to the server.
    abstract protected function build();
    
    function __construct($in_address)
    {
        $this->src_address = $in_address;
        print "OperationClass constructor\n";
        $this->src_secret = '';
    }

    //Init the public address
    public function setSrcAddress($in_address)
    {
       $this->src_address = $in_address;
    }

    //Added to keep the same as other SDKs
    // setSrcSecret
    //
    public function setSrcSecret($in_secret)
    {
       $this->src_secret = $in_secret;
    }

    //Sign the operation
    //
    public function sign($in_secret)
    {
       $this->src_secret = $in_secret;
    }
   
  


     /*
     * Set the operation mode to 
     * true - Synchronous mode
     * false - Asynchronous Mode
     */
    public function setValidate($in_sync)
    {
       $this->sync = $in_sync;
       //may need to check if the value is boolean or not
    }

    /**
     *
     * @return the $address
     */
    public function getAddress()
    {
        //$ret['success'] = true;
        return $this->src_address;
    }
}

/********** Class get balance **********/
class GetBalanceOperation extends OperationClass
{
    
    //using all the info to create the operation URL
    //to use for Server submission
    //return the operation data
    //method = 'GET', 'POST', 'DEL' for network requests
    //url = URL used for commands
    //params = Parameters needed to post to the server.
     function __construct($address) {
     print "In SubClass constructor\n";
        parent::__construct($address);
        
    }
    
    
    //This may be a problem if the address is not in correct format
    //validateAddress
    public function build()
    {
        $cmd['method'] = 'GET';
        $cmd['url'] = str_replace("{0}", parent::getAddress(), BALANCES);
        $cmd['params'] = '';
        
        return $cmd;
    }
    
}
/********** End class GetBalanceOperation **********/

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
       $this->src_secret = '';
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
    public function build()
    {
        //info to build the server URL
        $payment['destination_amount'] = $this->amount;
        $payment['source_account'] = $this->src_address;
        $payment['destination_account'] = $this->dest_address;
        $payment['payment_paths'] = $this->path;
        
        //info to submit
        $params['secret'] = $this->src_secret;
        $params['client_resource_id'] = $this->client_resource_id;
        $params['payment'] = $payment;
        
        $cmd['method'] = 'POST';
        $cmd['url'] = str_replace("{0}", $this->src_address, PAYMENTS) . '?validated=' . $this->sync;
        $cmd['params'] = $params;

        return $cmd;
    }
    
}
/********** Class order **********/
//API接口/v1/accounts/{:address}/orders?validated=true，POST方法
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
     function __construct($in_address) {
       parent::__construct($in_address);
       //print "In SubClass constructor\n";
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
       if ( $check_type  == 'SELL' or
            $check_type == 'BUY') 
       $this->order_type = $in_type;
       else{
         printf("Errors in the input type %s\n",$in_type);
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
       var_dump(json_encode($in_tum_amount));
       $this->taker_pays['currency'] = $in_tum_amount['currency'];
       $this->taker_pays['counterparty'] = $in_tum_amount['issuer'];
       $this->taker_pays['value'] = $in_tum_amount['value'];
    }
    
    public function setTakeGets($in_tum_amount)
    {
       $this->taker_gets['currency'] = $in_tum_amount['currency'];
       $this->taker_gets['counterparty'] = $in_tum_amount['issuer'];
       $this->taker_gets['value'] = $in_tum_amount['value'];
    }



    //May need to check if the cmd is valid or not.
    public function build()
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

        return $cmd;
    }
    
}//end 


/*
API接口/v1/accounts/{:address}/orders/{:order}?validated=true，DELETE方法
*/
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
    public function build()
    {
        $cmd['method'] = 'DELETE';
        $cmd['url'] = str_replace("{0}", $this->src_address, ORDERS).$this->order_num;
        $cmd['params'] = $this->src_secret;
        
        return $cmd;
    }
    
}

/* 
 *Different relations
 * 
*/
class RelationOperation extends OperationClass
{
    //Operations need to submit
    //关系被动方的井通地址
    protected $counterparty = '';
    //关系的额度
    protected $amount = '';
    //关系种类
    protected $relation_type = '';

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
       $this->src_secret = '';
       $this->amount['limit'] = '';
       $this->amount['currency'] = '';
       $this->amount['issuer'] = '';
       $this->counterparty = '';
       $this->relation_type = '';
   }
   
    //using all the info to create the operation URL
    //to use for Server submission
    //return the operation data
    //method = 'GET', 'POST', 'DEL' for network requests
    //url = URL used for commands
    //params = Parameters needed to post to the server.

    //Note, this used the limit instead of value
    public function setAmount($in_tong_amount)
    {
       $this->amount['currency'] = $in_tong_amount['currency'];
       $this->amount['limit'] = $in_tong_amount['value'];
       $this->amount['issuer'] = $in_tong_amount['issuer'];
    }
   
    //Set up the counter party of the relation
    public function setCounterparty($in_address)
    {
       $this->counterparty= $in_address;
    }

    //The types include: friend, authorize
    public function setRelationType($in_type)
    {
       $this->relation_type = $in_type;
       
    }
    
    //May need to check if the cmd is valid or not.
    //This function should not be called in this Class
    public function build()
    {
        //submit method 
        $cmd['method'] = 'POST';
        //info to submit
        $params['secret'] = $this->src_secret;
        $params['type'] = $this->relation_type;
        $params['counterparty'] = $this->counterparty;
        $params['amount'] = $this->amount;
        

        //info to build the server URL
        $cmd['url'] = str_replace("{0}", $this->src_address, RELATIONS) . '?validated=' . $this->sync;
        $cmd['params'] = $params;

        return $cmd;
    }
    
}

/*
*/
class AddRelationOperation extends RelationOperation
{

    //May need to check if the cmd is valid or not.
    public function build()
    {
        //submit method 
        $cmd['method'] = 'POST';
        //info to submit
        $params['secret'] = $this->src_secret;
        $params['type'] = $this->relation_type;
        $params['counterparty'] = $this->counterparty;
        $params['amount'] = $this->amount;
        

        //info to build the server URL
        $cmd['url'] = str_replace("{0}", $this->src_address, RELATIONS) . '?validated=' . $this->sync;
        $cmd['params'] = $params;

        return $cmd;
    }
    
}
/*关系移除
API接口/v1/accounts/{:address}/relations，DELETE方法
DELETE方法请求时需设置Content-Length消息头
 * 
 */
class RemoveRelationOperation extends RelationOperation  
{

    //May need to check if the cmd is valid or not.
    public function build()
    {
        //submit method 
        $cmd['method'] = 'DELETE';
        //info to submit
        $params['secret'] = $this->src_secret;
        $params['type'] = $this->relation_type;
        $params['counterparty'] = $this->counterparty;
        $params['amount'] = $this->amount;
        

        //info to build the server URL
        $cmd['url'] = str_replace("{0}", $this->src_address, RELATIONS) . '?validated=' . $this->sync;
        $cmd['params'] = $params;

        return $cmd;
    
    } 
    
}

/*
Setup the trustline object
 
*/
class TrustlineOperation extends OperationClass
{
    //Trustline parameters need to submit
    protected $currency = '';
    protected $limit = '';
    protected $counterparty = '';
    protected $frozen = '';

    function __construct($address) {
        parent::__construct($address);
    }

    //Set up the counter party of the relation
    public function setCounterparty($in_address)
    {
       $this->counterparty= $in_address;
    }

    public function setCurrency($in_code)
    {
       $this->currency = $in_code;
       
    }

    //Add the limit of the trustline
    public function setLimit($in_limit)
    {
       $this->limit = $in_limit;
    }

    public function setTrustlineFrozen($in_flag)
    {
       //Input should be a boolean
       $this->frozen = $in_flag;
       
    }

    public function build()
    {
      //suppose to be empty 
    }
}

/*
API接口/v1/accounts/{:address}/trustlines?validated=true，POST方法
*/
class AddTrustlineOperation extends TrustlineOperation
{

    //May need to check if the cmd is valid or not.
    public function build()
    {
        //submit method 
        $cmd['method'] = 'POST';
        //info to submit
        $trust['limit'] = $this->limit;
        $trust['currency'] = $this->currency;
        $trust['counterparty'] = $this->counterparty;
        $trust['account_trustline_frozen'] = $this->frozen;

        $params['secret'] = $this->src_secret;
        $params['trustline'] = $trust;
        

        //info to build the server URL
        $cmd['url'] = str_replace("{0}", $this->src_address, TRUST_LINES) . '?validated=' . $this->sync;
        $cmd['params'] = $params;

        return $cmd;
    }
    
}
/*
API接口/v1/accounts/{:address}/trustline，DELETE方法
DELETE方法请求时需设置Content-Length消息头
 * 
 */
class RemoveTrustlineOperation extends TrustlineOperation
{

    //May need to check if the cmd is valid or not.
    public function build()
    {
        //submit method 
        $cmd['method'] = 'DELETE';
        //info to submit
        $trust['limit'] = $this->limit;
        $trust['currency'] = $this->currency;
        $trust['counterparty'] = $this->counterparty;
        $trust['account_trustline_frozen'] = $this->frozen;

        $params['secret'] = $this->src_secret;
        $params['trustline'] = $trust;
        

        //info to build the server URL
        $cmd['url'] = str_replace("{0}", $this->src_address, TRUST_LINES) . '?validated=' . $this->sync;
        $cmd['params'] = $params;

        return $cmd;
    
    } 
    
}


/*
API接口/v1/accounts/{:address}/settings，POST方法
SetSettingsOperation
*/
class SetSettingsOperation extends OperationClass
{
    //Parameters need to submit
    private $settings = '';

    //Note: Parent constructors are not called implicitly 
    //if the child class defines a constructor.
    //In order to run a parent constructor, a call to
    //parent::__construct() within the child constructor is required. 
    //If the child does not define a constructor then it may be inherited 
    //from the parent class just like a normal class method 
    //(if it was not declared as private). 
     function __construct($address) {
       parent::__construct($address);
/*
       $this->settings["domain"] = "";
       $this->settings["wallet_locator"] = "";
       $this->settings["email_hash"] = "";
       $this->settings["message_key"] = "";
       $this->settings["transfer_rate"] = "";
       $this->settings["wallet_size"] = "";
       $this->settings["no_freeze"] = TRUE;
       $this->settings["disable_master"] = TRUE;
       $this->settings["require_destination_tag"] = TRUE;
       $this->settings["require_authorization"] = FALSE;
       $this->settings["disallow_swt"] = FALSE;*/
       $settings = new SettingClass();
    }

    //The types include: friend, authorize
    public function setSettings($in_settings)
    {
       $this->settings= $in_settings;
       
    }
    
    //May need to check if the cmd is valid or not.
    public function build()
    {
        //submit method 
        $cmd['method'] = 'POST';
        //info to submit
        $params['secret'] = $this->src_secret;
        $params['settings'] = $this->settings->getJSON();
        

        //info to build the server URL
        $cmd['url'] = str_replace("{0}", $this->src_address, SETTINGS) . '?validated=' . $this->sync;
        $cmd['params'] = $params;

        return $cmd;
    }
}

/*
 * API接口/v1/accounts/{:address}/settings，POST方法
 *   SetRegularkeyOperation
*/
class SetRegularkeyOperation extends OperationClass
{
    //Regular key of the account need to be set
    private $regular_key = '';
   
    
    //Note: Parent constructors are not called implicitly 
    //if the child class defines a constructor.
    //In order to run a parent constructor, a call to
    //parent::__construct() within the child constructor is required. 
    //If the child does not define a constructor then it may be inherited 
    //from the parent class just like a normal class method 
    //(if it was not declared as private). 
     function __construct($in_address) {
       parent::__construct($in_address);
       //print "In SubClass constructor\n";
       $this->regular_key = '';
   }
  
    //Setup the regular_key 
    public function setRegularkey($in_key)
    {
//       $check_type = strtoupper(trim($in_type));
         $this->regular_key = $in_key;
    }

    //using all the info to create the operation URL
    //to use for Server submission
    //return the operation data
    //method = 'POST', 'DEL' for network requests
    //url = URL used for commands
    //params = Parameters needed to post to the server.

    public function build()
    {
        //info to build the server URL
        $settings['regular_key'] = $this->regular_key;
        
        //info to submit
        $params['secret'] = $this->src_secret;
        $params['settings'] = $settings;
        
        $cmd['method'] = 'POST';
        $cmd['url'] = str_replace("{0}", $this->src_address, SETTINGS)
                      . '?validated=' . $this->sync;
        $cmd['params'] = $params;

        return $cmd;
    }
    
}//end 
/*
 * API接口/v1/accounts/{:address}/settings，POST方法
 *   SetSignersOperation
*/
class SetSignersOperation extends OperationClass
{
    //Parameters need to set
    private $master_weight = '';
    private $quorum = '';
    private $signer_entries = '';
   
    
    //Note: Parent constructors are not called implicitly 
    //if the child class defines a constructor.
    //In order to run a parent constructor, a call to
    //parent::__construct() within the child constructor is required. 
    //If the child does not define a constructor then it may be inherited 
    //from the parent class just like a normal class method 
    //(if it was not declared as private). 
    function __construct($in_address) {
        parent::__construct($in_address);
    }

    public function setMasterWeight($in_weight)
    {
       $this->master_weight = $in_weight;
       
    }

    public function setQuorum($in_weight)
    {
       $this->quorum = $in_weight;
       
    }


    //Note, the input should be an array
    public function setSigners($in_signers)
    {
       $this->signer_entries = $in_signers;
       
    }

    public function build()
    {
        //info to build the server URL
        $settings['master_weight'] = $this->master_weight;
        $settings['quorum'] = $this->quorum;
        $settings['signer_entries'] = $this->signer_entries;
        
        //info to submit
        $params['secret'] = $this->src_secret;
        $params['signers'] = $settings;
        
        $cmd['method'] = 'POST';
        $cmd['url'] = str_replace("{0}", $this->src_address, SETTINGS)
                      . '?validated=' . $this->sync;
        $cmd['params'] = $params;

        return $cmd;
    }
}//end SetSignersOperation 

/*
 * API接口/v1/accounts/{:address}/messages，POST方法
 *   MessageOperation
*/
class MessageOperation extends OperationClass
{
    //Parameters need to submit
    private $dest_address = '';
    private $message_hash = '';
   
    
    //Note: Parent constructors are not called implicitly 
    //if the child class defines a constructor.
    //In order to run a parent constructor, a call to
    //parent::__construct() within the child constructor is required. 
    //If the child does not define a constructor then it may be inherited 
    //from the parent class just like a normal class method 
    //(if it was not declared as private). 
    function __construct($in_address) {
        parent::__construct($in_address);
    }
  
    //Setup the destination account of the message 
    public function setDestAddress($in_address)
    {
        $this->dest_address = $in_address;
    }

    //Setup the message hash to send to dest account
    public function setMessage($in_msg)
    {
        $this->message_hash = $in_msg;
    }

    public function build()
    {
        
        //info to submit
        $params['secret'] = $this->src_secret;
        $params['destination_account'] = $this->dest_address;
        $params['message_hash'] = $this->message_hash;
        
        $cmd['method'] = 'POST';
        $cmd['url'] = str_replace("{0}", $this->src_address, MESSAGES)
                      . '?validated=' . $this->sync;
        $cmd['params'] = $params;

        return $cmd;
    } 
}

/*
 * API接口/v1/accounts/{:address}/operations?validated=true，POST方法
 *   MultipleOperations
 * 
*/
class MultipleOperations extends OperationClass
{
    private $signers = '';
    private $operations = '';

    //Constructor
    function __construct($in_address) {
       parent::__construct($in_address);
    }

    public function build()
    {
        //info to submit
        $params['secret'] = $this->src_secret;
        $params['signers'] = $this->signers;
        $params['operations'] = $this->operations;
        
        $cmd['method'] = 'POST';
        $cmd['url'] = str_replace("{0}", $this->src_address, OPERATIONS) . '?validated=' . $this->sync;
        $cmd['params'] = $params;

        return $cmd;
    }
}

