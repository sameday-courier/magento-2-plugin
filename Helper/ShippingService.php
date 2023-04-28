<?php

namespace SamedayCourier\Shipping\Helper;

use Magento\Framework\App\ObjectManager;
use Magento\Directory\Model\Region;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\OrderAddressRepositoryInterface;

class ShippingService
{
    /**
     * @var OrderAddressRepositoryInterface $orderAddressRepository
     */
    private $orderAddressRepository;

    public function __construct(
        OrderAddressRepositoryInterface $orderAddressRepository
    )
    {
        $this->orderAddressRepository = $orderAddressRepository;
    }

    public function persistAddress(
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
}
