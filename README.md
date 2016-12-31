本文档提供了安装井通SDK的说明，和SDK中文件的说明。
同时可以参考在线文档：developer.jingtum.com
井通SDK PHP的运行需要PHP支持 JSON（PHP 5.2.x 以上版本）和安装 CURL 扩展程序库。
ECDSA文件需要(GNU Multiple Precision (GMP) Arithmetic Library）.
'GMP extension seems not to be installed' in /PHP-SDK/lib/ECDSA.php:11

以下命令在Ubuntu 14下自动安装curl扩展程序库。
$sudo apt-get install php5-curl

在Ubuntu下，使用
$sudo apt-get install php5-gmp

在MAC OS下，使用brew
$brew install homebrew/php-gmp
$brew install homebrew/php/php55-gmp

此外，还需要安装与服务器请求的接口WebSocket和其相应的软件库。
在当前目录下，composer.json含有需要的程序信息，如：
     "require": {
       "textalk/websocket": "1.0.*"
     }
composer.lock包含这些程序信息的具体版本。
可以通过Composer工具来安装，具体可以参考 [Composer](https://getcomposer.org/)
的说明。

需要先安装Composer，在linux下：
$php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
$php -r "if (hash_file('SHA384', 'composer-setup.php') === '61069fe8c6436a4468d0371454cf38a812e451a14ab1691543f25a9627b97ff96d8753d92a00654c21e2212a5ae1ff36') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
$php composer-setup.php
$php -r "unlink('composer-setup.php');"
完成这些步骤后，当前目录下会生成composer.phar文件。运行
php composer.phar install
检查是否在vendor下有以下文件
autoload.php  
和目录
composer  
textalk

-----------------------------------------------------------
源程序文件说明
AccountClass.php   - Base class for Wallet and FinGate. 
FinGate.php        - FinGate class and functions. 
OperationClass.php       - Base class for Payment, Order and other Operations. 
OrderOperation.php       - Submit order operation.
PaymentOperation.php     - Make payment operation.
CancelOrderOperation.php - Cancel the order operation. 

Server.php         - Basic server, API server, Tum server
                     and Websocket server.
Tum.php            - Tum, Amount, Balance classes.
Wallet.php         - Wallet class and functions.
-----------------------------------------------------------
配置文件 
composer.json      - composer config file for package installation. 
composer.lock      - composer lock file for package installation.
config.json        - Jingtum server configurations.
-----------------------------------------------------------
所需库文件
lib/
ConfigUtil.php     - Configurations read in and write out.
Constants.php      - Constants used in the SDK.
DataCheck.php      - Data functions to check if the data type is right.
ECDSA.php          - Encrypted functions.
SignUtil.php       - Build Jingtum signature.
SnsNetwork.php     - Functions handling HTTP requests.
-----------------------------------------------------------
示例文件说明
examples/
test_data.json     - Test data used in the test program, only used for
                     the development server.
cancel_order.php   - Order submit and cancel.
issue_tum.php      - Setup the FinGate in test environment and use it 
                     to issue custom Tum. This requires extral info
                     from the user. The user should register at the 
                     Jingtum company to get the information.
order_example.php       - Order submit and check the status.
path_example.php        - Search for payment path then use the payment 
                          path to make payment.
payment_example.php     - Use two test accounts for sending payments
                          in SWT and currency.
server_example.php      - Showed how to initial the servers.
transaction_example.php - Show how to get transaction information.
wallet_example.php      - Create wallet, active it and use it to make a payment.

-----------------------------------------------------------
To run the examples, at the current directory,
install the necessary packages, and run:
php example/payment_example.php
