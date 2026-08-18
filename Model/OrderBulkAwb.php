<?php

namespace SamedayCourier\Shipping\Model;

use Magento\Framework\Model\AbstractModel;
use SamedayCourier\Shipping\Api\Data\OrderBulkAwbInterface;
use SamedayCourier\Shipping\Model\ResourceModel\OrderBulkAwb as OrderBulkAwbResource;

class OrderBulkAwb extends AbstractModel implements OrderBulkAwbInterface
{
    protected function _construct()
    {
        $this->_init(OrderBulkAwbResource::class);
    }

    public function getOrderId()
    {
        return (int) $this->getData(self::ORDER_ID);
    }

    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, (int) $orderId);
    }

    public function getStatus()
    {
        return (int) $this->getData(self::STATUS);
    }

    public function setStatus($status)
    {
        return $this->setData(self::STATUS, (int) $status);
    }

    public function getFeedback()
    {
        return $this->getData(self::FEEDBACK);
    }

    public function setFeedback($feedback)
    {
        return $this->setData(self::FEEDBACK, $feedback);
    }
}
