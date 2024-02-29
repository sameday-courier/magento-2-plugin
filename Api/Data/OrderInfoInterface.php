<?php

namespace SamedayCourier\Shipping\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * @api
 */
interface OrderInfoInterface extends ExtensibleDataInterface
{
    public const ID = 'id';
    public const ORDER_ID = 'order_id';
    public const SAMEDAYCOURIER_LOCKER = 'samedaycourier_locker';
    public const SAMEDAYCOURIER_DESTINATION_ADDRESS_HD = 'samedaycourier_destination_address_hd';
    public const SAMEDAYCOURIER_FEE = 'samedaycourier_fee';

    /**
     * @return int|null
     */
    public function getId(): ?int;

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId(int $id): self;

    /**
     * @return int
     */
    public function getOrderId(): int;

    /**
     * @param int $orderId
     *
     * @return $this
     */
    public function setOrderId(int $orderId): self;

    /**
     * @return string|null
     */
    public function getSamedaycourierLocker(): ?string;

    /**
     * @param string|null $locker
     *
     * @return $this
     */
    public function setSamedaycourierLocker(?string $locker): self;

    /**
     * @return string|null
     */
    public function getSamedaycourierDestinationAddressHd(): ?string;

    /**
     * @param string|null $addressHd
     *
     * @return $this
     */
    public function setSamedaycourierDestinationAddressHd(?string $addressHd): self;

    /**
     * @return string|null
     */
    public function getSamedaycourierFee(): ?string;

    /**
     * @param string|null $fee
     *
     * @return $this
     */
    public function setSamedaycourierFee(?string $fee): self;
}
