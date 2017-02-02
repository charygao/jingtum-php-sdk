# jingtum-php-sdk

本文档提供了安装井通SDK PHP语言版本的说明。
同时可以参考在线文档：

developer.jingtum.com

## 运行环境

井通SDK PHP的运行需要PHP支持 JSON（PHP 5.2.x 以上版本）和安装 CURL 扩展程序库。
以下命令在Ubuntu 14下自动安装curl扩展程序库。
```
$sudo apt-get install php5-curl
```

ECDSA文件需要(GNU Multiple Precision (GMP) Arithmetic Library）.
否则会出现以下错误
```
'GMP extension seems not to be installed' in /PHP-SDK/lib/ECDSA.php:11
```

在Ubuntu下，使用
```
$sudo apt-get install php5-gmp
```

在MAC OS下，使用brew
```
$brew install homebrew/php-gmp

$brew install homebrew/php/php55-gmp
```

还需要安装与服务器请求的接口WebSocket和其相应的软件库。

PHP SDK提供了回调函数（callback）。

## 安装PHP SDK

首先保证系统安装了composer。如果没有，则需要安装Composer工具，具体可以参考 
[Composer](https://getcomposer.org/)
的说明。

```
curl -sS https://getcomposer.org/installer | php
```

在当前目录下的composer.json中加入PHP SDK的程序信息，如：

```
     "require": {
       "jingtum/jingtum-php-sdk": "dev-master"
     }
```

运行以下命令安装：
```
php composer.phar install
```


## 程序示例

### 产生一组新的井通帐号

```php
	//Set the FinGate instance
	$fin_gate = FinGate::getInstance();

	//Set the work mode
	$fin_gate->setMode(FinGate::DEVELOPMENT);

	//Set up the FinGate account 
	$fin_gate->setAccount('sha4eGoQu......V3YQ4');

	//Create the wallet from FinGate
	$wallet1 = $fin_gate->createWallet();

	//active the wallet
	$fin_gate->activeWallet($wallet1->getAddress(), 'call_back_func');

```

### 查询帐号的余额信息
```php
	//Set the FinGate instance
	$fin_gate = FinGate::getInstance();

	//Set the work mode
	$fin_gate->setMode(FinGate::DEVELOPMENT);
```

### 使用帐号进行支付

```php
	//Set the FinGate instance
	$fin_gate = FinGate::getInstance();

	//Set the work mode
	$fin_gate->setMode(FinGate::DEVELOPMENT);

	//Setup two wallets for testing
	$wallet2 = new Wallet($test_wallet2->secret, $test_wallet2->address);

	$wallet3 = new Wallet($test_wallet3->secret, $test_wallet3->address);

	//A payment obj for testing
	$op = new PaymentOperation($wallet2);

	//Create the payment amount
	$pay_value = 0.01;
	$amt1 = new Amount($pay_value, 'SWT', '');

	//Set the destination address
	$op->setDestAddress($wallet3->getAddress());

	//Set the amount to pay
	$op->setAmount($amt1);

	//Optional settings
	$client_id = "JTtest".time();
	$op->setClientID($client_id);//optional, if not provided, SDK will generate an internal one
	$op->setMemo("SWT PAYMENT for testing".$client_id);//memo used in the payment
	$op->setValidate(false);//setup the syn mode, default is true

	$op->submit('call_back_func');

```

### 生成挂单

```php
	//Set the FinGate instance
	$fin_gate = FinGate::getInstance();

	//Set the work mode
	$fin_gate->setMode(FinGate::DEVELOPMENT);

    $wallet1 = new Wallet('snwjtucx9......MbVz8hFiK9');

    $op = new OrderOperation($wallet1);
    $op->setType($op->SELL);
    $op->setPair("SWT/CNY:janxMdr...GewMMeHa9f");
    $op->setAmount(1000.00 );
    $op->setValidate(true);

    $op->submit('call_back_func');
```


### 取消挂单

```php
	//Set the FinGate instance
	$fin_gate = FinGate::getInstance();

	//Set the work mode
	$fin_gate->setMode(FinGate::DEVELOPMENT);

    $wallet1 = new Wallet('snwjtucx9......MbVz8hFiK9');
    $op = new CancelOrderOperation($wallet);

    $op->setOrderNum(54);
    $op->setValidate('true');

    $op->submit('call_back_func');
```
