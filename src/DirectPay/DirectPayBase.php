<?php

namespace briarbear\alipay\DirectPay;

abstract class DirectPayBase
{
    protected $options = [];
    public    $gateway = 'https://mapi.alipay.com/gateway.do?';

    /**
     * @param string $gateway
     */
    public function setGateway($gateway)
    {
        $this->gateway = $gateway;
    }

    public function __construct(array $options)
    {
        $this->setOption($this->getDefaultOptions())->setOption($options);
    }

    protected function getDefaultOptions()
    {
        return [
            'charset'      => 'UTF-8',
            'sign_type'    => 'RSA',
            'payment_type' => 1,
            'cacert'       => __DIR__ . '/../../data/cacert.pem',
            'transport'    => 'HTTPS',
        ];
    }

    public function getOption($key, $default = null)
    {
        return isset($this->options[$key]) ? $this->options[$key] : $default;
    }

    public function setOption($key, $value = null)
    {
        if (is_array($key)) {
            $this->options = array_merge($this->options, $key);
        } else {
            $this->options[$key] = $value;
        }

        return $this;
    }

    protected function getDefaultPaymentData()
    {
        return [
            'service'        => $this->getOption('service'),
            'partner'        => $this->getOption('partner'),
            'payment_type'   => $this->getOption('payment_type'),
            'notify_url'     => $this->getOption('notify_url'),
            'seller_id'      => $this->getOption('seller_id'),
            'show_url'       => $this->getOption('show_url'),
            '_input_charset' => strtolower($this->getOption('charset')),
        ];
    }

    public function getVerifier()
    {
        return new Verifier([
            'transport'       => $this->getOption('transport'),
            'sign_type'       => $this->getOption('sign_type'),
            'cacert'          => $this->getOption('cacert'),
            'key'             => $this->getOption('key'),
            'public_key_path' => $this->getOption('public_key_path'),
            'partner'         => $this->getOption('partner'),
            'gateway'         => $this->gateway,
        ]);
    }

    public function verify($data)
    {
        return $this->getVerifier()->verify($data);
    }

    public function verifyRequest()
    {
        return $this->getVerifier()->verifyRequest();
    }

    public function verifyPost()
    {
        return $this->getVerifier()->verifyPost();
    }

    public function verifyGet()
    {
        return $this->getVerifier()->verifyGet();
    }

    /**
     * 生成要请求给支付宝的参数数组
     * @param array $paramsTemp 请求前的参数数组
     * @return array
     */
    protected function buildRequestPara($paramsTemp)
    {
        //除去待签名参数数组中的空值和签名参数
        $paramsFilter = PayHelper::paraFilter($paramsTemp);

        //对待签名参数数组排序
        $paramsSort = PayHelper::argSort($paramsFilter);

        //生成签名结果
        $strSign = $this->buildRequestMysign($paramsSort);

        //签名结果与签名方式加入请求提交参数组中
        $paramsSort['sign'] = $strSign;
        $paramsSort['sign_type'] = strtoupper($this->getOption('sign_type'));

        return $paramsSort;
    }

    /**
     * 生成签名结果
     * @param array $paramsSort 已排序要签名的数组
     * @return string 签名结果字符串
     */
    private function buildRequestMysign($paramsSort)
    {
        //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
        $prestr = PayHelper::createLinkstring($paramsSort);
        $mysign = '';
        switch (strtoupper($this->getOption('sign_type'))) {
            case 'MD5':
                $mysign = PayHelper::md5Sign($prestr, $this->getOption('key'));
                break;
            case 'RSA':
                $mysign = PayHelper::rsaSign($prestr, $this->getOption('private_key_path'));
                break;
            default:
                $mysign = '';
        }
        return $mysign;
    }
}