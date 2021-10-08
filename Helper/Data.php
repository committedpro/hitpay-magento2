<?php
namespace SoftBuild\HitPay\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public $resource = '';
    private const HITPAY_ORDER_TABLE = 'hitpay_order';
    private const HITPAY_WEBHOOK_TRIGGER_TABLE = 'hitpay_webhook_trigger';
    
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        parent::__construct($context);
        $this->resource = $resource;
    }
    
    public function getPaymentResponse($order_id)
    {
        $connection= $this->resource->getConnection();
        $table = $this->resource->getTableName(self::HITPAY_ORDER_TABLE);
        $sql = $connection->select()
                  ->from($table, ['response'])
                  ->where('order_id = ?', (int)($order_id));
        return $connection->fetchOne($sql);
    }
    
    public function addPaymentResponse($order_id, $response)
    {
        $metaData = $this->getPaymentResponse($order_id);
        if (!empty($metaData)) {
            $metaData = json_decode($response, true);
            foreach ($metaData as $key => $val) {
                $this->updatePaymentData($order_id, $key, $val);
            }
        } else {
            $connection = $this->resource->getConnection();
            $table = $this->resource->getTableName(self::HITPAY_ORDER_TABLE);
            $data = ['order_id' => $order_id, 'response' => $response];
            $connection->insert($table, $data);
        }
    }
    
    public function updatePaymentData($order_id, $param, $value)
    {
        $metaData = $this->getPaymentResponse($order_id);
        if (!empty($metaData)) {
            $metaData = json_decode($metaData, true);
            $metaData[$param] = $value;
            $paymentData = json_encode($metaData);
            
            $connection = $this->resource->getConnection();
            $table = $this->resource->getTableName(self::HITPAY_ORDER_TABLE);
            $where = ['order_id = (?)' => (int)($order_id)];
            $connection->update($table, ['response' => $paymentData], $where);
        }
    }
    
    public function deletePaymentData($order_id, $param)
    {
        $metaData = $this->getPaymentResponse($order_id);
        if (!empty($metaData)) {
            $metaData = json_decode($metaData, true);
            if (isset($metaData[$param])) {
                unset($metaData[$param]);
            }
            $paymentData = json_encode($metaData);
            
            $connection = $this->resource->getConnection();
            $table = $this->resource->getTableName(self::HITPAY_ORDER_TABLE);
            $where = ['order_id = (?)' => (int)($order_id)];
            $connection->update($table, ['response' => $paymentData], $where);
        }
    }
    
    public function isWebhookTriggered($order_id)
    {
        $connection= $this->resource->getConnection();
        $table = $this->resource->getTableName(self::HITPAY_WEBHOOK_TRIGGER_TABLE);
        $sql = $connection->select()
                  ->from($table, ['id'])
                  ->where('order_id = ?', (int)($order_id));
        return $connection->fetchOne($sql);
    }
    
    public function addWebhookTriggered($order_id)
    {
        $status = (int)$this->isWebhookTriggered($order_id);
        if (!$status) {
            $connection = $this->resource->getConnection();
            $table = $this->resource->getTableName(self::HITPAY_WEBHOOK_TRIGGER_TABLE);
            $data = ['order_id' => $order_id,];
            $connection->insert($table, $data);
        }
    }
}
