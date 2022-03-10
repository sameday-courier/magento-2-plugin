<?php

namespace SamedayCourier\Shipping\Helper;

use SamedayCourier\Shipping\Api\PickupPointRepositoryInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use SamedayCourier\Shipping\Api\ServiceRepositoryInterface;

class StoredDataHelper extends AbstractHelper
{
    public const CASH_ON_DELIVERY_CODE = 'cashondelivery';

    private $pickupPointRepository;
    private $serviceRepository;

    /**
     * @var ApiHelper
     */
    private $apiHelper;

    public function __construct(Context $context,
            PickupPointRepositoryInterface $pickupPointRepository,
            ServiceRepositoryInterface $serviceRepository,
            ApiHelper $apiHelper
        )
    {
        parent::__construct($context);

        $this->pickupPointRepository = $pickupPointRepository;
        $this->serviceRepository = $serviceRepository;
        $this->apiHelper = $apiHelper;
    }

    private function isTesting()
    {
        return (bool) $this->scopeConfig->getValue('carriers/samedaycourier/testing');
    }

    public function getHostCountry()
    {
        return $this->apiHelper->getHostCountry();
    }

    public function getPickupPoints()
    {
        return $this->pickupPointRepository->getListByTesting($this->isTesting());
    }

    public function getServices()
    {
        return $this->serviceRepository->getAllActiveByTesting($this->isTesting());
    }
}
