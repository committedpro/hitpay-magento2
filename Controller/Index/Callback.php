<?php
namespace SoftBuild\HitPay\Controller\Index;

use Magento\Framework\Controller\ResultFactory;

class Callback extends \Magento\Framework\App\Action\Action
{
    protected $helper;
    protected $payment;
    protected $orderFactory;

    public function __construct(
        \SoftBuild\HitPay\Helper\Data $helper,
        \SoftBuild\HitPay\Model\Pay $payment,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\App\Action\Context $context
    ) {
        $this->helper = $helper;
        $this->payment = $payment;
        $this->orderFactory = $orderFactory;
        parent::__construct($context);
    }
    
    public function getHttpHeaderSignature()
    {
        $key = 'X-Onebip-Signature';
        if (isset($_SERVER['HTTP_X_ONEBIP_SIGNATURE'])) {
            return $_SERVER['HTTP_X_ONEBIP_SIGNATURE'];
        } else {
            foreach (getallheaders() as $name => $value) {
                if ($name == $key) {
                    return $value;
                }
            }
        }
    }

    public function execute()
    {
        $model = $this->_objectManager->get('SoftBuild\HitPay\Model\Pay');
        $session = $this->_objectManager->get('Magento\Checkout\Model\Session');

        $model->log('Callback:');

        $json = file_get_contents('php://input');

        $body = json_decode($json, true);
        
        $model->log($body);
        $headerSignatureValue = $this->getHttpHeaderSignature();
        $headerSignature = base64_encode(hash_hmac('sha256', $json, $model->getConfigValue('api_key'), $rawOutput = true));

        $model->log($headerSignatureValue);
        $model->log($headerSignature);

        $order_id = (int)($body['remote_txid']);

        if ($order_id > 0) {
            $order = $this->orderFactory->create()->loadByIncrementId($order_id);

            if ($headerSignature == $headerSignatureValue) {
                $model->log('Signature matched');
                if ($body['what'] == 'BILLING_COMPLETED') {
                    $state = \Magento\Sales\Model\Order::STATE_PROCESSING;
                    $status = \Magento\Sales\Model\Order::STATE_PROCESSING;
                    $order->setState($state);
                    $order->setStatus($status);
                    $order->setTotalPaid($order->getGrandTotal());
                    $comment = __('Onepip mobile payment completed. '). __('Transaction ID: '). $body['transaction_id'];
                    $order->addStatusHistoryComment($comment, $status);
                    $order->save();
                } else if ($body['what'] == 'BILLING_ABORTED') {
                    $comment =  __('Onepip mobile payment failed. Reason: ').$body['why'].'. Transaction Id: '.$body['transaction_id'];
                    $order->addStatusHistoryComment($comment, \Magento\Sales\Model\Order::STATE_CANCELED);
                    $order->save();
                } else {
                    $comment =  __('Order Cancelled. Not valid response received.');
                    $order->addStatusHistoryComment($comment, \Magento\Sales\Model\Order::STATE_CANCELED);
                    $order->save();
                }
            }
            else {
                $comment =  __('Order Cancelled. Signature missmatch potential Fraud attempt.');
                $order->addStatusHistoryComment($comment, \Magento\Sales\Model\Order::STATE_CANCELED);
                $order->save();
            }
        }
        exit;
    }
}
