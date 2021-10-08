<?php

namespace SoftBuild\HitPay\Model\System\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Payment\Model\Method\AbstractMethod;

/**
 * Class Mode
 */
class Mode implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 1,
                'label' => __('Live')
            ],
            [
                'value' => 0,
                'label' => __('Sandbox')
            ]
        ];
    }
}
