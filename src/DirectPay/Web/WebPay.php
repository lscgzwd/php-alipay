<?php

namespace briarbear\alipay\DirectPay\Web;

use briarbear\alipay\DirectPay\DirectPayBase;
use briarbear\alipay\DirectPay\PayHelper;

class WebPay extends DirectPayBase
{
    const SERVICE = 'create_direct_pay_by_user';

    /**
     * @return array
     */
    protected function getDefaultOptions()
    {
        return array_merge(parent::getDefaultOptions(), [
            'service' => self::SERVICE,
        ]);
    }

    /**
     * 生成支付url
     * @param array $data
     * @return string
     */
    public function generatePaymentUrl(array $data)
    {
        $data = array_merge($this->getDefaultPaymentData(), $data);

        $para = $this->buildRequestPara($data);

        return $this->gateway . PayHelper::createLinkstringUrlencode($para);
    }
}