<?php

namespace SamedayCourier\Shipping\Helper;

use Exception;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Sameday\Objects\Service\OptionalTaxObject;
use SamedayCourier\Shipping\Api\Data\LockerInterface;
use SamedayCourier\Shipping\Api\Data\PickupPointInterface;
use SamedayCourier\Shipping\Api\Data\ServiceInterface;
use SamedayCourier\Shipping\Api\PickupPointRepositoryInterface;
use SamedayCourier\Shipping\Api\ServiceRepositoryInterface;
use SamedayCourier\Shipping\Model\ResourceModel\LockerRepository;

class StoredDataHelper extends AbstractHelper
{
    public const DEFAULT_VALUE_LOCKER_MAX_ITEMS = 5;

    public const SAMEDAYCOURIER_LOCKER_MAX_ITEMS = 'carriers/samedaycourier/locker_max_items';
    public const SAMEDAYCOURIER_ENV_MODE = 'carriers/samedaycourier/testing';
    public const SAMEDAYCOURIER_USERNAME = 'carriers/samedaycourier/username';
    public const REPAYMENT_TAX_LABEL = 'carriers/samedaycourier/repayment_tax_label';
    public const REPAYMENT_TAX_VALUE = 'carriers/samedaycourier/repayment_tax';
    public const CASH_ON_DELIVERY_CODE = 'cashondelivery';
    public const CHECKMO_CODE = 'checkmo';
    public const COD_OPTIONS = [
        self::CASH_ON_DELIVERY_CODE, self::CHECKMO_CODE
    ];
    public const SERVICE_OPTIONAL_TAX_PDO = 'PDO';
    public const DISPLAY_HTML_ELEM = [
        'show' => 'block',
        'hide' => 'none',
    ];

    public const EURO_CURRENCY_CODE = 'EUR';

    public const SAMEDAY_ELIGIBLE_CURRENCIES = [
        ApiHelper::ROMANIA_CODE => 'RON',
        ApiHelper::BULGARIA_CODE => self::EURO_CURRENCY_CODE,
        ApiHelper::HUNGARY_CODE => 'HUF',
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

    /**
     * @var PriceCurrencyInterface $currencyInterface
     */
    private $currencyInterface;

    public function __construct(Context $context,
            PickupPointRepositoryInterface $pickupPointRepository,
            ServiceRepositoryInterface $serviceRepository,
            LockerRepository $lockerRepository,
            ApiHelper $apiHelper,
            Json $json,
            PriceCurrencyInterface $currencyInterface
        )
    {
        parent::__construct($context);

        $this->pickupPointRepository = $pickupPointRepository;
        $this->serviceRepository = $serviceRepository;
        $this->lockerRepository = $lockerRepository;
        $this->apiHelper = $apiHelper;
        $this->json = $json;
        $this->currencyInterface = $currencyInterface;
    }

    private function isTesting(): bool
    {
        return (bool) $this->scopeConfig->getValue(self::SAMEDAYCOURIER_ENV_MODE);
    }

    /**
     * @return string
     */
    public function getHostCountry(): string
    {
        return $this->apiHelper->getHostCountry();
    }

    public function getApiUsername(): string
    {
        return (string) $this->scopeConfig->getValue(self::SAMEDAYCOURIER_USERNAME);
    }

    public function getRepaymentFeeValue(): int
    {
        return (int) $this->currencyInterface->convert($this->scopeConfig->getValue(self::REPAYMENT_TAX_VALUE));
    }

    public function getRepaymentFeeLabel(): string
    {
        return (string) $this->scopeConfig->getValue(self::REPAYMENT_TAX_LABEL);
    }

    /**
     * @return PickupPointInterface[]
     */
    public function getPickupPoints(): array
    {
        return $this->pickupPointRepository->getListByTesting($this->isTesting());
    }

    /**
     * @return ServiceInterface[]
     */
    public function getServices(): array
    {
        return $this->serviceRepository->getAllActiveByTesting($this->isTesting());
    }

    /**
     * @param $samedayId
     *
     * @return LockerInterface|null
     */
    public function getLocker($samedayId): ?LockerInterface
    {
        try {
            $locker = $this->lockerRepository->getLockerById($samedayId);
        } catch (Exception $exception) {return null;}

        return $locker;
    }

    /**
     * @param $samedayServiceOptionalTaxes
     *
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


    /**
     * @param string $forCountryCode
     *
     * @return null|string
     */
    public function buildDestCurrency(string $forCountryCode): ?string
    {
        $forCountryCode = strtolower($forCountryCode);

        return self::SAMEDAY_ELIGIBLE_CURRENCIES[$forCountryCode] ?? null;
    }

    /**
     * Warning when COD repayment needs attention for currency mismatch and/or cross-border destination.
     *
     * @param string|null $destCountryId Destination country code (order shipping address)
     * @param string $orderCurrency Order currency code
     * @param float $repayment COD repayment amount
     * @param string|null $hostCountryCode Sameday account country (carriers/samedaycourier/country)
     */
    public function buildCurrencyWarningMessage(
        ?string $destCountryId,
        string $orderCurrency,
        float $repayment,
        ?string $hostCountryCode = null
    ): ?string {
        if ($repayment <= 0 || $destCountryId === null || $destCountryId === '') {
            return null;
        }

        $destCountry = strtolower($destCountryId);
        $hostCountry = $hostCountryCode !== null && $hostCountryCode !== ''
            ? strtolower($hostCountryCode)
            : null;
        $isCrossBorder = $hostCountry !== null && $hostCountry !== $destCountry;

        $destCurrency = $this->buildDestCurrency($destCountry);
        $currencyMismatch = $destCurrency !== $orderCurrency;

        if (!$currencyMismatch && !$isCrossBorder) {
            return null;
        }

        if ($currencyMismatch) {
            if ($destCurrency === null) {
                $destCurrency = sprintf(
                    'not one of the accepted currency %s, %s or %s',
                    self::SAMEDAY_ELIGIBLE_CURRENCIES[ApiHelper::ROMANIA_CODE],
                    self::SAMEDAY_ELIGIBLE_CURRENCIES[ApiHelper::BULGARIA_CODE],
                    self::SAMEDAY_ELIGIBLE_CURRENCIES[ApiHelper::HUNGARY_CODE]
                );
            }

            return sprintf(
                'Be aware that the intended currency is %s but the Repayment value is expressed in %s. Please consider a conversion !!',
                $destCurrency,
                $orderCurrency
            );
        }

        return sprintf(
            'Be aware that this is a cross-border order (destination %s, Sameday account %s). Please consider a conversion !!',
            strtoupper($destCountry),
            strtoupper($hostCountry)
        );
    }

    /**
     * @param string $serviceCode
     *
     * @return bool
     */
    public function isEligibleToLocker(string $serviceCode): bool
    {
        return $this->apiHelper->isEligibleToLocker($serviceCode);
    }
}
