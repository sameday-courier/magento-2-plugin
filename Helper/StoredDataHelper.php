<?php

namespace SamedayCourier\Shipping\Helper;

use Exception;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Serialize\Serializer\Json;
use Sameday\Objects\Service\OptionalTaxObject;
use SamedayCourier\Shipping\Api\Data\LockerInterface;
use SamedayCourier\Shipping\Api\PickupPointRepositoryInterface;
use SamedayCourier\Shipping\Api\ServiceRepositoryInterface;
use SamedayCourier\Shipping\Model\ResourceModel\LockerRepository;

class StoredDataHelper extends AbstractHelper
{
    public const SAMEDAYCOURIER_ENV_MODE = 'carriers/samedaycourier/testing';
    public const SAMEDAYCOURIER_USERNAME = 'carriers/samedaycourier/username';
    public const REPAYMENT_TAX_LABEL = 'carriers/samedaycourier/repayment_tax_label';
    public const REPAYMENT_TAX_VALUE = 'carriers/samedaycourier/repayment_tax';
    public const CASH_ON_DELIVERY_CODE = 'cashondelivery';
    public const SERVICE_OPTIONAL_TAX_PDO = 'PDO';
    public const DISPLAY_HTML_ELEM = [
        'show' => 'block',
        'hide' => 'none',
    ];

    private $pickupPointRepository;
    private $serviceRepository;
    private $lockerRepository;

    /**
     * @var ApiHelper
     */
    private $apiHelper;

    /**
     * @var Json
     */
    private $json;

    public function __construct(Context $context,
            PickupPointRepositoryInterface $pickupPointRepository,
            ServiceRepositoryInterface $serviceRepository,
            LockerRepository $lockerRepository,
            ApiHelper $apiHelper,
            Json $json
        )
    {
        parent::__construct($context);

        $this->pickupPointRepository = $pickupPointRepository;
        $this->serviceRepository = $serviceRepository;
        $this->lockerRepository = $lockerRepository;
        $this->apiHelper = $apiHelper;
        $this->json = $json;
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

    /**
     * @param $samedayServiceOptionalTaxes
     * @return string
     */
    public function serializeServiceOptionalTaxes($samedayServiceOptionalTaxes): string
    {
        $data = [];

        /** @var OptionalTaxObject[] $samedayServiceOptionalTaxes */
        foreach ($samedayServiceOptionalTaxes as $tax) {
            $data[] = [
                'id' => $tax->getId(),
                'name' => $tax->getName(),
                'code' => $tax->getCode(),
                'tax' => $tax->getTax(),
                'costType' => $tax->getCostType()->getType(),
                'packageType' => $tax->getPackageType()->getType(),
            ];
        }

        return $this->json->serialize($data);
    }

    public function deserializeServiceOptionalTaxes($samedayServiceOptionalTaxes)
    {
        if (null === $samedayServiceOptionalTaxes) {
            return null;
        }

        return $this->json->unserialize($samedayServiceOptionalTaxes);
    }
}
