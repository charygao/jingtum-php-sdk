<?php
/**
 * PHP SDK for Jingtum network; Build sign string for
 * version 2 API requirement
 * @version 1.0.0
 * @author Jing Zhao
 * @copyright © 2016, Jingtum Labs. All rights reserved.
 */
use JingtumSDK\lib\ECDSA;

require_once 'ECDSA.php';

define('SIGN_HASH_STRING', 'Jingtum2016');

function buildSignString($address, $secret)
{
    $timestamp = sprintf('%.0f', microtime(true) * 1000);
    
    $messageString = SIGN_HASH_STRING . $address . $timestamp;
    
    $ecdsa = new ECDSA($secret);
    
    $signData = substr(hash('sha512', $messageString), 0, 64);
    
    $signature = $ecdsa->signHash($signData);
    
    $pubky = strtoupper($ecdsa->getPubKey());
    
    $res['k'] = $pubky;
    $res['s'] = $signature;
    $res['h'] = $signData;
    $res['t'] = $timestamp;
    return $res;
}
