<?php

/*
 * Tum class and extended calsses
 * 09/25/2016
 * Amount
 * Balance
 * Order
 * Trustline
 */
namespace JingtumSDK;

require_once 'lib/DataCheck.php';

class Tum
{
//Private attributes
    //Tum code,
    //native: SWT
    //currency: CNY, USD, EUR
    //Custom Tum:
    protected $code = '';

    //Only for non-native Tum
    //for SWT, this is empty
    protected $issuer = '';

    //Types of Tum
    //native: SWT
    //currency: CNY, USD, EUR,
    //
    //tum: Custom tum, 40 characters
    private  $tum_type = NULL;


    //Build a Tum obj
    function __construct($in_code, $in_issuer = '')
    {
        $this->issuer = $in_issuer;
        $this->code = trim($in_code);
        $this->tum_type = decideType(strtoupper($this->code));
    }



    //return an amount structure
    //with the input value
    public function getTumAmount($in_value)
    {
        //create a amount JSON
        $amount['currency'] = $this->code;
    //Notice that the Amount obj has
    //String to represent the value, not float
        $amount['value'] = strval($in_value);
        $amount['issuer'] = $this->issuer;
        return $amount;
    }


    public function setCode($in_code)
    {
        $this->code = trim($in_code);
    }

    public function setIssuer($in_issuer)
    {
        $this->issuer = trim($in_issuer);
    }
    /**
     *
     * @return the code of Tum
     * This is the same as getCurrency
     *
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     *
     * @return the issuer of Tum
     */
    public function getIssuer()
    {
        return $this->issuer;
    }

    /*
     *Return the Tum as currency+counterparts->code.'+'.$this->issuer;
    */
    public function getPair()
    {
        return $this->code.'+'.$this->issuer;
    }
    /**
     *
     * @return type of Tum
     */
    public function getTumType()
    {
        return $this->tum_type;
    }

}


/*
value   String  余额
currency        String  货币名称，三个字母或是40个字符的字符串
counterparty    String  货币发行方
*/
class Amount extends Tum
{
  //value in str format
  protected $value = '';

    //reserved for DATA server URL
    function __construct($in_code, $in_issuer, $in_value)
    {
        $this->value = $in_value;
        parent::__construct($in_code, $in_issuer);
    }

    public function setValue($in_value)
    {
        $this->value = strval($in_value);
    }

    //
    public function getAmount()
    {   //create a amount JSON
        $amount['currency'] = $this->code;
        //Notice that the Amount obj has
        //String to represent the value, not float
        $amount['value'] = strval($this->value);
        $amount['issuer'] = $this->issuer;
        return $amount;
    }

    //Return a string
    public function getValue()
    {
        return $this->value;
    }

}

/*
value   String  余额
currency        String  货币名称，三个字母或是40个字符的字符串
counterparty    String  货币发行方
freezed String  冻结的金额
*/
class Balance extends Amount
{
  //value freezed for the account
  protected $freezed = '';

    function __construct($in_code, $in_issuer, $in_value, $in_freezed=0)
    {
        //$this->serverURL = $inURL;
        parent::__construct($in_code, $in_issuer, $in_value);
        $this->freezed = $in_freezed;
    }

    public function setFreezedValue($in_value)
    {
        $this->freezed = strval($in_value);
    }

    /**
    *
    * @return the value of freezed Tum
    */
    public function getFreezedValue()
    {
        return $this->freezed;
    }
}


