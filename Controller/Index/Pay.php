<?php
namespace SoftBuild\HitPay\Controller\Index;

class Pay extends \Magento\Framework\App\Action\Action
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

    public function execute()
    {
        $model = $this->_objectManager->get('SoftBuild\HitPay\Model\Pay');
        $order = $model->getOrder();
        if ($order && $order->getId() > 0) {
            $amount = $order->getGrandTotal();
            $amount = round($amount, 2);
            $params = array (
                "username" => $model->getConfigValue('username'),
                "description" => $model->getConfigValue('description'),
                "price" => $amount * 100,
                "currency" => $order->getOrderCurrencyCode(),
                "country" => $order->getBillingAddress()->getCountryId(),
                "return_url" => $model->getCheckoutSuccessUrl(),
                "notify_url" => $model->getCallbackUrl(),
                "remote_txid" => $order->getIncrementId(),
                "customer_email" => $order->getBillingAddress()->getEmail(),
                "customer_account_id" => $order->getBillingAddress()->getEmail(),
                "customer_firstname" => $order->getBillingAddress()->getFirstname(),
                "customer_lastname" => $order->getBillingAddress()->getLastname(),
                "customer_cell" => $order->getBillingAddress()->getTelephone(),
                "customer_country" => $order->getBillingAddress()->getCountryId(),
                "product_url" => $model->getStoreUrl(),
                "product_id" => "magento_v1"
            );
            
            $product_name = '';
            foreach ($order->getAllItems() as $item) {
                $product_name .= $item->getName();; 
            }
            $params['product_name'] = $product_name;
            
            $url = "https://pay.onebip.com/purchases?";
            $querystring = http_build_query($params);
            $secret = $model->getConfigValue('api_key');
            $signature = hash_hmac("sha256", $url . $querystring, $secret);
            $urlWithSignature = $url . $querystring . "&signature=". $signature;
            $model->log('Pay:');
            $model->log($urlWithSignature);
            
            echo '<script>window.top.location.href = "'.$urlWithSignature.'";</script>';
            exit;
        } else {
            $this->_redirect('checkout/cart');
        }
    }
}
