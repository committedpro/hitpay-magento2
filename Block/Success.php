<?php

namespace SoftBuild\HitPay\Block;

class Success extends \Magento\Framework\View\Element\Template
{
    protected $helper;
    protected $payment;

    public function __construct(
        \SoftBuild\HitPay\Helper\Data $helper,
        \SoftBuild\HitPay\Model\Pay $payment,
        \Magento\Catalog\Block\Product\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
        $this->payment = $payment;
    }
    
    public function getStatusContent()
    {
        $order = $this->payment->getOrder();
        $payment = $order->getPayment();
        $method = $payment->getMethodInstance();
        if ($method && $payment->getMethod() == 'hitpay') {
            $status_content = '';

            if ($order->getState() == \Magento\Sales\Model\Order::STATE_NEW) {
                $status_content = __('Your payment status is pending, we will update the status as soon as we receive notification from hitpay Mobile Payment System.');
            } else if ($order->getState() == \Magento\Sales\Model\Order::STATE_PROCESSING) {
                $status_content = __('Your payment is successful with hitpay Mobile Payment System.');
            } else if ($order->getState() == \Magento\Sales\Model\Order::STATE_CANCELED) {
                $status_content = __('Your payment is failed with hitpay Mobile Payment System.');
            }

            return $status_content;
        }
    }
}
