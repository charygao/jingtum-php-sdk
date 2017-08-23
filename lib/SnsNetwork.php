<?php
/**
 * 发送HTTP网络请求类
 *
 * @version 1.0.0
 * @author Jing Zhao
 * @copyright © 2016, Jingtum Labs. All rights reserved.				 
 */
namespace JingtumSDK\lib;

define('ERROR_RESPONSE_DATA_INVALID', 1803); // 返回包格式错误
define('ERROR_CURL', 1900);
// 网络错误, 偏移量1900

class SnsNetwork
{

    /**
     * 执行API调用，返回结果数组
     *
     * @param string $url
     *            调用的URL
     * @param array $params
     *            调用API时带的参数
     * @param string $method
     *            请求方法 post / get
     * @param string $protocol
     *            协议类型 http / https
     * @return array 结果数组
     */
    static public function api($url, $params, $method = 'post', $json = 'true')
    {
        // 发起请求
        $ret = SnsNetwork::makeRequest($url, $params, $method, $json);
        
        if (false === $ret['result']) {
            $result_array = array(
                'ret' => ERROR_CURL + $ret['errno'],
                'msg' => $ret['msg']
            );
        }
        
        $result_array = json_decode($ret['msg'], true);
        
        // 远程返回的不是 json 格式, 说明返回包有问题
        
        if (is_null($result_array)) {
            $result_array = array(
                'ret' => ERROR_RESPONSE_DATA_INVALID,
                'msg' => $ret['msg']
            );
        }
        
        return $result_array;
    }

    /**
     * 执行一个 HTTP 请求
     *
     * @param string $url
     *            执行请求的URL
     * @param mixed $params
     *            可以是array, 也可以是经过url编码之后的string
     * @param string $method
     *            post / get
     * @param string $protocol
     *            http / https
     * @return array 结果数组
     */
    static public function makeRequest($url, $params, $method = 'post', $json = 'true')
    {
        $query_string = self::makeQueryString($params);
        


        $ch = curl_init();

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        if ('GET' == strtoupper($method)) {
           //curl_setopt($ch, CURLOPT_URL, "$url?$query_string");
            curl_setopt($ch, CURLOPT_URL, "$url");
            curl_setopt($ch, CURLOPT_HEADER, false);
        } elseif ('POST' == strtoupper($method)) {
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            if ($json) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length: ' . strlen($query_string)
                ));
            }
        } else {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json; charset=utf-8',
                'Content-Length: ' . strlen($query_string)
            ));
        }
        
        // curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        
        if ($json) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        
        // ob_start();
        $ret = curl_exec($ch);
        // $ret = ob_get_contents();
        
        // ob_end_clean();
        $err = curl_error($ch);
        
        if (false === $ret || ! empty($err)) {
            $errno = curl_errno($ch);
            $info = curl_getinfo($ch);
            curl_close($ch);
            
            return array(
                'result' => false,
                'errno' => $errno,
                'msg' => $err,
                'info' => $info
            );
        }
        
        curl_close($ch);
        
        return array(
            'result' => true,
            'msg' => $ret
        );
    }

    /*
    */
    static public function makeQueryString($params)
    {
        if (is_string($params))
            return $params;
        
        $query_string = array();
        foreach ($params as $key => $value) {
            array_push($query_string, rawurlencode($key) . '=' . rawurlencode($value));
        }
        $query_string = join('&', $query_string);
        
        return $query_string;
    }
}
