<?php

namespace SamedayCourier\Shipping\Model\Data;

use Magento\Framework\Api\AbstractExtensibleObject;
use SamedayCourier\Shipping\Api\Data\OrderInfoInterface;

class OrderInfo extends AbstractExtensibleObject implements OrderInfoInterface
{
    public function getId(): ?int
    {
        return $this->_get(self::ID);
    }

    public function setId(int $id): OrderInfoInterface
    {
        return $this->setData(self::ID, $id);
    }


    public function setOrderId(int $orderId): OrderInfoInterface
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * @return int
     */
    public function getOrderId(): int
    {
        return $this->_get(self::ORDER_ID);
    }

    /**
     * @param string|null $locker
     *
     * @return OrderInfoInterface
     */
    public function setSamedaycourierLocker(?string $locker): OrderInfoInterface
    {
        return $this->setData(self::SAMEDAYCOURIER_LOCKER, $locker);
    }

    /**
     * @return string|null
     */
    public function getSamedaycourierLocker(): ?string
    {
        return $this->_get(self::SAMEDAYCOURIER_LOCKER);
    }

    /**
     * @param string|null $addressHd
     *
     * @return OrderInfoInterface
     */
    public function setSamedaycourierDestinationAddressHd(?string $addressHd): OrderInfoInterface
    {
        return $this->setData(self::SAMEDAYCOURIER_DESTINATION_ADDRESS_HD, $addressHd);
    }

    /**
     * @return string|null
     */
    public function getSamedaycourierDestinationAddressHd(): ?string
    {
        return $this->_get(self::SAMEDAYCOURIER_DESTINATION_ADDRESS_HD);
    }

    /**
     * @param string|null $fee
     *
     * @return OrderInfoInterface
     */
    public function setSamedaycourierFee(?string $fee): OrderInfoInterface
    {
        return $this->setData(self::SAMEDAYCOURIER_FEE, $fee);
    }

    /**
     * @return string|null
     */
    public function getSamedaycourierFee(): ?string
    {
        return $this->_get(self::SAMEDAYCOURIER_FEE);
    }
}
