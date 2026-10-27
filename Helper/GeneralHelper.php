<?php

namespace SamedayCourier\Shipping\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Sameday\Objects\Types\AwbPdfType;

class GeneralHelper extends AbstractHelper
{
    public const SAMEDAY_SERVICE_6H_CODE = '6H';
    public const SAMEDAY_SERVICE_24H_CODE = '24';
    public const SAMEDAY_SERVICE_LOCKER_CODE = 'LN';
    public const SAMEDAY_SERVICE_CROSSBORDER_24_CODE = 'XB';
    public const SAMEDAY_SERVICE_CROSSBORDER_LOCKER_CODE = 'XL';
    public const SAMEDAY_SERVICE_PUDO_CODE = 'PP';
    public const SAMEDAY_SERVICE_CROSSBORDER_PUDO_CODE = 'XP';
    public const OOH_SERVICE_LABEL = 'Out of home delivery';
    public const CACHE_CITIES_DATA_KEY = 'sameday_courier_cities';

    /**
     * Locker IDs at or above this threshold are treated as PUDO when oohType is missing.
     */
    private const OOH_TYPE_PUDO_LOCKER_ID_THRESHOLD = 500000;

    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    public const AVAILABLE_SHIP_COUNTRIES = [
        ApiHelper::ROMANIA_CODE,
        ApiHelper::BULGARIA_CODE,
        ApiHelper::HUNGARY_CODE,
    ];

    public const OOH_LABEL = [
        ApiHelper::ROMANIA_CODE => 'Ridicare Sameday Point/Easybox',
        ApiHelper::BULGARIA_CODE => 'вземете от Sameday Point/Easybox',
        ApiHelper::HUNGARY_CODE => 'Felvenni től Sameday Point/Easybox',
    ];

    public const OOH_LABEL_INFO = [
        ApiHelper::ROMANIA_CODE => 'Optiunea Ridicare Personala include ambele servicii LockerNextDay, respectiv Pudo!',
        ApiHelper::BULGARIA_CODE => 'Тази опция включва LockerNextDay и PUDO!',
        ApiHelper::HUNGARY_CODE => 'Ez az opció magában foglalja a LockerNextDay és a PUDO szolgáltatást is!',
    ];

    /**
     * oohType index → domestic OOH service code (0 = Easybox, 1 = PUDO).
     */
    public const OOH_SERVICES = [
        self::SAMEDAY_SERVICE_LOCKER_CODE,
        self::SAMEDAY_SERVICE_PUDO_CODE,
    ];

    /**
     * Easybox family: domestic LN, cross-border XL.
     */
    public const EASYBOX_TYPE_SERVICE = [
        self::SAMEDAY_SERVICE_LOCKER_CODE,
        self::SAMEDAY_SERVICE_CROSSBORDER_LOCKER_CODE,
    ];

    /**
     * PUDO family: domestic PP, cross-border XP.
     */
    public const PUDO_TYPE_SERVICE = [
        self::SAMEDAY_SERVICE_PUDO_CODE,
        self::SAMEDAY_SERVICE_CROSSBORDER_PUDO_CODE,
    ];

    private const SAMEDAY_IN_USE_SERVICES = [
        self::SAMEDAY_SERVICE_6H_CODE,
        self::SAMEDAY_SERVICE_24H_CODE,
        self::SAMEDAY_SERVICE_LOCKER_CODE,
        self::SAMEDAY_SERVICE_CROSSBORDER_24_CODE,
        self::SAMEDAY_SERVICE_CROSSBORDER_LOCKER_CODE,
    ];

    /**
     * @return string[]
     */
    public function getInUseServices(): array
    {
        return self::SAMEDAY_IN_USE_SERVICES;
    }

    /**
     * @return string
     */
    public function getAwbLabelFormat(): string
    {
        return $this->scopeConfig->getValue('carriers/samedaycourier/awb_label_format') ?? AwbPdfType::A4;
    }

    /**
     * @param string $serviceCode
     *
     * @return bool
     */
    public function isOoHService(string $serviceCode): bool
    {
        return in_array($serviceCode, self::OOH_SERVICES, true);
    }

    public function isPudoServiceType(string $serviceCode): bool
    {
        return in_array($serviceCode, self::PUDO_TYPE_SERVICE, true);
    }

    public function isEasyboxServiceType(string $serviceCode): bool
    {
        return in_array($serviceCode, self::EASYBOX_TYPE_SERVICE, true);
    }

    /**
     * Remap checkout OOH code (LN/XL) to the AWB service for the selected location type.
     * Easybox → LN/XL, PUDO → PP/XP (same domestic/cross-border index).
     *
     * @param string|int|null $oohType
     */
    public function resolveAwbServiceCode(string $orderServiceCode, $oohType): string
    {
        $serviceKeys = array_flip(self::EASYBOX_TYPE_SERVICE) + array_flip(self::PUDO_TYPE_SERVICE);
        if (!isset($serviceKeys[$orderServiceCode])) {
            return $orderServiceCode;
        }

        $oohTypeCode = self::OOH_SERVICES[$oohType] ?? $oohType;
        $targetServices = in_array($oohTypeCode, self::PUDO_TYPE_SERVICE, true)
            ? self::PUDO_TYPE_SERVICE
            : self::EASYBOX_TYPE_SERVICE;

        return $targetServices[$serviceKeys[$orderServiceCode]] ?? $orderServiceCode;
    }

    /**
     * @param array|null $locker
     * @return int|null 0 = Easybox, 1 = PUDO
     */
    public function resolveOohTypeFromLocker(?array $locker): ?int
    {
        if ($locker === null || $locker === []) {
            return null;
        }

        if (isset($locker['oohType']) && $locker['oohType'] !== '' && $locker['oohType'] !== null) {
            return (int) $locker['oohType'];
        }

        $lockerId = isset($locker['lockerId']) ? (int) $locker['lockerId'] : 0;
        if ($lockerId <= 0) {
            return null;
        }

        return $lockerId >= self::OOH_TYPE_PUDO_LOCKER_ID_THRESHOLD ? 1 : 0;
    }

    /**
     * @return string
     */
    public function getHostCountry(): string
    {
        return $this->scopeConfig->getValue('carriers/samedaycourier/country') ?? ApiHelper::ROMANIA_CODE;
    }

    /**
     * @return bool
     */
    public function useSamedayNomenclature(): bool
    {
        return (bool) $this->scopeConfig->getValue('carriers/samedaycourier/use_sameday_cities');
    }
}
