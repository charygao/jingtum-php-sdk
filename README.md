本文档提供了安装井通SDK的说明，和SDK中文件的说明。
同时可以参考在线文档：developer.jingtum.com
井通SDK PHP的运行需要PHP支持 JSON（PHP 5.2.x 以上版本）和安装 CURL 扩展程序库。
此外，需要确认接口所使用的井通系统的服务器符合最新的配置，具体内容可参考附录中的系统配置。
例如，在Ubuntu 14下，使用
$apt-cache search php5
可以搜索安装的php5程序库。
以下命令在Ubuntu 14下自动安装curl扩展程序库。
$sudo apt-get install php5-curl


ECDSA文件需要(GNU Multiple Precision (GMP) Arithmetic Library）.
$sudo apt-get install php5-gmp
如果没有安装，会出现类似如下错误：
$PHP Fatal error:  Uncaught exception 'Exception' with message 'GMP extension seems not to be installed' in /PHP-SDK/lib/ECDSA.php:11

还需要与服务器请求的接口是WebSocket软件库。
Installing Preferred way to install is with [Composer](https://getcomposer.org/).
 Just add
     "require": {
       "textalk/websocket": "1.0.*"
     }
in your projects composer.json.
Client usage:
-------------
 ```php
require('vendor/autoload.php');
-----------------------------------------------------------
源程序文件说明
AccountClass.php   - Basic Jingtum account. 
FinGate.php        - FinGate class and functions. 
Operation.php      - Payment, Order and other Operations. 
Server.php         - Basic server, API server, Tum server
                     and Websocket server.
Wallet.php         - Wallet class and functions. 
-----------------------------------------------------------
所需库文件
lib/
ConfigUtil.php     - Configurations read in and write out.
DataCheck.php      - Data functions to check if the data type is right.
ECDSA.php          - Encrypted functions.
SignUtil.php       - Build Jingtum signature.
SnsNetwork.php     - Functions handling HTTP requests.
-----------------------------------------------------------
示例文件说明
examples/
test_data.json     - Test data used in the test program, only used for
                     the development server.
issue_tum.php      - Setup the FinGate in test environment and use it 
                     to issue custom Tum.

To run the examples, copy the PHP file and test_data.json to the directory,
install the necessary packages, and run:
php5 issue_tum.php
