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
'GMP extension seems not to be installed' in /PHP-SDK/lib/ECDSA.php:11

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

##安装PHP SDK

首先保证系统安装了composer。如果没有，则需要安装Composer工具，具体可以参考 
[Composer](https://getcomposer.org/)
的说明。
curl -sS https://getcomposer.org/installer | php

在当前目录下的composer.json中加入PHP SDK的程序信息，如：
```
     "require": {
       "jingtum/jingtum-php-sdk": "1.0.2"
     }
```

运行以下命令安装：
```
php composer.phar install
```



## 文件说明

### 源程序文件说明

AccountClass.php   - Base class for Wallet and FinGate. 

FinGate.php        - FinGate class and functions. 

OperationClass.php       - Base class for Payment, Order and other Operations. 

OrderOperation.php       - Submit order operation.

PaymentOperation.php     - Make payment operation.

CancelOrderOperation.php - Cancel the order operation. 

Server.php         - Basic server, API server, Tum server and Websocket server.

Tum.php            - Tum, Amount, Balance classes.

Wallet.php         - Wallet class and functions.

###配置文件 

composer.json      - composer config file for package installation. 

composer.lock      - composer lock file for package installation.

config.json        - Jingtum server configurations.

### 所需库文件

lib/

ConfigUtil.php     - Configurations read in and write out.

Constants.php      - Constants used in the SDK.

DataCheck.php      - Data functions to check if the data type is right.

ECDSA.php          - Encrypted functions.

SignUtil.php       - Build Jingtum signature.

SnsNetwork.php     - Functions handling HTTP requests.

### 示例文件说明

examples/

test_data.json           - Test data used in the test program, only used in the development server.

order_examples.php       - Order submit and check the status.

payment_examples.php     - Create wallet, active it and use it to make a payment. Also use two test accounts for sending payments in SWT and currency.

transaction_examples.php - Show how to get transaction information.

tum_examples.php         -  Issue custom Tum and query the Tum status.

## 程序示例

To run the examples, install the necessary packages, copy the example files and run:

```php
php examples/payment_examples.php
```
