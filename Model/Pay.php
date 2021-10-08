<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SoftBuild\HitPay\Model;

use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Framework\DataObject;

class Pay extends \Magento\Payment\Model\Method\AbstractMethod
{
    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = 'hitpay';
    
    /**
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     *
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     *
     * @var Magento\Checkout\Model\Session
     */
    protected $orderSession;
    
    /**
     *
     * @var SoftBuild\HitPay\Helper\Data
     */
    protected $hitpayHelper;
    
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;
    
    protected $directory_list;
    
    public function __construct(
        \SoftBuild\HitPay\Helper\Data $hitpayHelper,
        \Magento\Checkout\Model\Session $orderSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\App\Filesystem\DirectoryList $directory_list, 
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        \Magento\Directory\Helper\Data $directory = null
    ) {
        $this->hitpayHelper = $hitpayHelper;
        $this->orderSession = $orderSession;
        $this->orderFactory = $orderFactory;
        $this->customerSession = $customerSession;
        $this->urlBuilder = $urlBuilder;
        $this->directory_list = $directory_list;

        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data,
            $directory
        );
    }

    /**
     * Get instructions text from config
     *
     * @return string
     */
    public function getInstructions()
    {
        return '';
    }
    
    public function getConfigValue($key)
    {
        $pathConfig = 'payment/' . $this->_code . "/" . $key;
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->_scopeConfig->getValue($pathConfig, $storeScope);
    }
    
    public function getCheckout()
    {
        return $this->orderSession;
    }
    
    public function getQuote()
    {
        return $this->getCheckout()->getQuote();
    }
    
    public function getOrder()
    {
        return $this->getCheckout()->getLastRealOrder();
    }

    public function getCheckoutRedirectUrl($params = []) 
    {
        return $this->urlBuilder->getUrl('hitpay/index/pay', $params);
    }

    public function getCallbackUrl($params = [])
    {
        return $this->urlBuilder->getUrl('hitpay/index/callback', $params);
    }
    
    public function getCancelUrl($params = [])
    {
        return $this->urlBuilder->getUrl('hitpay/index/cancel', $params);
    }
    
    public function getCheckoutSuccessUrl()
    {
        return $this->urlBuilder->getUrl('checkout/onepage/success');
    }
    
    public function getStoreUrl()
    {
        return $this->urlBuilder->getUrl();
    }
    
    public function getStoreName()
    {
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();        
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $storeName = $storeManager->getStore()->getName();
        if (empty($storeName)) {
            $storeName = $storeManager->getStore()->getStoreUrl();
        }
        return $storeName;
    }
    
    public function log($message)
    {
        $debug = true;
        if ($debug) {
            file_put_contents($this->directory_list->getPath('log') . '/hitpay.log', date("Y-m-d H:i:s").": ", FILE_APPEND);
            file_put_contents($this->directory_list->getPath('log') . '/hitpay.log', print_r($message, true), FILE_APPEND);
            file_put_contents($this->directory_list->getPath('log') . '/hitpay.log', "\n", FILE_APPEND);
        }
    }
}
