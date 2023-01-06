<?php

namespace SamedayCourier\Shipping\Helper;

use Exception;
use SamedayCourier\Shipping\Api\Data\LockerInterface;
use SamedayCourier\Shipping\Api\PickupPointRepositoryInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use SamedayCourier\Shipping\Api\ServiceRepositoryInterface;
use SamedayCourier\Shipping\Model\ResourceModel\LockerRepository;

class StoredDataHelper extends AbstractHelper
{
    public const SAMEDAYCOURIER_ENV_MODE = 'carriers/samedaycourier/testing';
    public const SAMEDAYCOURIER_USERNAME = 'carriers/samedaycourier/username';
    public const REPAYMENT_TAX_LABEL = 'carriers/samedaycourier/repayment_tax_label';
    public const REPAYMENT_TAX_VALUE = 'carriers/samedaycourier/repayment_tax';
    public const CASH_ON_DELIVERY_CODE = 'cashondelivery';

    private PickupPointRepositoryInterface $pickupPointRepository;
    private ServiceRepositoryInterface $serviceRepository;
    private LockerRepository $lockerRepository;

    /**
     * @var ApiHelper
     */
    private $apiHelper;

    public function __construct(Context $context,
            PickupPointRepositoryInterface $pickupPointRepository,
            ServiceRepositoryInterface $serviceRepository,
            LockerRepository $lockerRepository,
            ApiHelper $apiHelper
        )
    {
        parent::__construct($context);

        $this->pickupPointRepository = $pickupPointRepository;
        $this->serviceRepository = $serviceRepository;
        $this->lockerRepository = $lockerRepository;
        $this->apiHelper = $apiHelper;
    }

    private function isTesting(): bool
    {
        return (bool) $this->scopeConfig->getValue(self::SAMEDAYCOURIER_ENV_MODE);
    }

    public function getHostCountry()
    {
        return $this->apiHelper->getHostCountry();
    }

    public function getApiUsername(): string
    {
        return (string) $this->scopeConfig->getValue(self::SAMEDAYCOURIER_USERNAME);
    }

    public function getRepaymentFeeValue(): int
    {
        return (int) $this->scopeConfig->getValue(self::REPAYMENT_TAX_VALUE);
    }

    public function getRepaymentFeeLabel(): string
    {
        return (string) $this->scopeConfig->getValue(self::REPAYMENT_TAX_LABEL);
    }

    public function getPickupPoints()
    {
        return $this->pickupPointRepository->getListByTesting($this->isTesting());
    }

    public function getServices()
    {
        return $this->serviceRepository->getAllActiveByTesting($this->isTesting());
    }

    public function getLocker($samedayId): ?LockerInterface
    {
        try {
            $locker = $this->lockerRepository->getLockerById($samedayId);
        } catch (Exception $exception) {return null;}

        return $locker;
    }
}
