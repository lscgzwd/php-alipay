<?php
namespace briarbear\alipay\DirectPay;

class PayHelper {
    /**
     * 除去数组中的空值和签名参数
     * @param array $para 签名参数组
     * @return array 去掉空值与签名参数后的新签名参数组
     */
    public static function paraFilter($para)
    {
        $para_filter = array();
        foreach ($para as $key => $val) {
            if ($key == 'sign' || $key == 'sign_type' || $val == '') {
                continue;
            } else {
                $para_filter[$key] = $para[$key];
            }
        }
        return $para_filter;
    }

    /**
     * 对数组排序
     * @param array $para 排序前的数组
     * @return array 排序后的数组
     */
    public static function argSort($para)
    {
        ksort($para);
        reset($para);
        return $para;
    }

    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
     * @param array $para 需要拼接的数组
     * @return string 拼接完成以后的字符串
     */
    public static function createLinkstring($para)
    {
        $arg = '';
        foreach ($para as $key => $val) {
            $arg .= $key . '=' . $val . '&';
        }
        //去掉最后一个&字符
        $arg = substr($arg, 0, strlen($arg) - 1);

        //如果存在转义字符，那么去掉转义
        if (get_magic_quotes_gpc()) {
            $arg = stripslashes($arg);
        }

        return $arg;
    }

    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串，并对字符串做urlencode编码
     * @param array $para 需要拼接的数组
     * @return  string 拼接完成以后的字符串
     */
    public static function createLinkstringUrlencode($para)
    {
        $arg = '';
        foreach ($para as $key => $val) {
            $arg .= $key . '=' . urlencode($val) . '&';
        }
        //去掉最后一个&字符
        $arg = substr($arg, 0, strlen($arg) - 1);

        //如果存在转义字符，那么去掉转义
        if (get_magic_quotes_gpc()) {
            $arg = stripslashes($arg);
        }

        return $arg;
    }

    /**
     * 签名字符串
     * @param string $prestr 需要签名的字符串
     * @param string $key 私钥
     * @return string 签名结果
     */
    public static function md5Sign($prestr, $key)
    {
        $prestr = $prestr . $key;
        return md5($prestr);
    }

    /**
     * 验证签名
     * @param string $prestr 需要签名的字符串
     * @param string $sign 签名结果
     * @param string $key 私钥
     * @return boolean 签名结果
     */
    public static function md5Verify($prestr, $sign, $key)
    {
        $prestr = $prestr . $key;
        $mysgin = md5($prestr);

        if ($mysgin == $sign) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * RSA验签
     * @param array $data 待签名数据
     * @param string $public_key_path 支付宝的公钥文件路径
     * @param stirng $sign 要校对的的签名结果
     * @return boolean 验证结果
     */
    public static function rsaVerify($data, $public_key_path, $sign)
    {
        $pubKey = file_get_contents($public_key_path);
        $res = openssl_get_publickey($pubKey);
        $result = (bool) openssl_verify($data, base64_decode($sign), $res);
        openssl_free_key($res);
        return $result;
    }
    /**
     * RSA签名
     * @param array $data 待签名数据
     * @param string $private_key_path 商户私钥文件路径
     * @return string 签名结果
     */
    public static function rsaSign($data, $private_key_path)
    {
        $priKey = file_get_contents($private_key_path);
        $res = openssl_get_privatekey($priKey);
        openssl_sign($data, $sign, $res);
        openssl_free_key($res);
        //base64编码
        $sign = base64_encode($sign);
        return $sign;
    }
}