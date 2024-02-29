<?php

namespace SamedayCourier\Shipping\Helper;

use Magento\Framework\App\ObjectManager;
use Magento\Directory\Model\Region;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderAddressRepositoryInterface;

class ShippingService
{
    public const SHIPPING_METHOD_PREFIX = 'Sameday Courier';

    public const SHIPPING_METHOD_CODE = 'samedaycourier';

    /**
     * @var OrderRepositoryInterface $orderRepository
     */
    private $orderRepository;

    /**
     * @var OrderAddressRepositoryInterface $orderAddressRepository
     */
    private $orderAddressRepository;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrderAddressRepositoryInterface $orderAddressRepository
    )
    {
        $this->orderRepository = $orderRepository;
        $this->orderAddressRepository = $orderAddressRepository;
    }

    public function updateShippingAddress(
        OrderAddressInterface $shippingAddress,
        string $city,
        string $county,
        string $street
    ): void
    {
        $shippingAddress->setCity($city);
        $shippingAddress->setRegion($county);
        $shippingAddress->setStreet($street);

        $objectManager = ObjectManager::getInstance();

        /** @var Region $region */
        $region = $objectManager->create(Region::class);
        $regionId = $region->loadByName(
                $county,
                $shippingAddress->getCountryId()
            )->getId()
        ;

        $shippingAddress->setRegionId($regionId);

        $this->orderAddressRepository->save($shippingAddress);
    }

    public function updateShippingMethod(
        OrderInterface $order,
        string $shippingMethodDescription
    ): void
    {
        $order->setShippingDescription(
            sprintf(
                '%s - %s',
                self::SHIPPING_METHOD_PREFIX,
                $shippingMethodDescription
            )
        );

        $this->orderRepository->save($order);
    }
}
