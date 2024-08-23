<?php

namespace SamedayCourier\Shipping\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class GeneralHelper extends AbstractHelper
{
    public const SAMEDAY_SERVICE_6H_CODE = '6H';
    public const SAMEDAY_SERVICE_24H_CODE = '24';
    public const SAMEDAY_SERVICE_LOCKER_CODE = 'LN';
    public const SAMEDAY_SERVICE_CROSSBORDER_24_CODE = 'XB';
    public const SAMEDAY_SERVICE_CROSSBORDER_LOCKER_CODE = 'XL';
    public const SAMEDAY_SERVICE_PUDO_CODE = 'PP';
    public const OOH_SERVICE_LABEL = 'Out of home delivery';

    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

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
     * OOH - stands for Out of home Services
     */
    public const OOH_SERVICES = [
        self::SAMEDAY_SERVICE_LOCKER_CODE,
        self::SAMEDAY_SERVICE_PUDO_CODE,
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
     * @param string $serviceCode
     *
     * @return bool
     */
    public function isOoHService(string $serviceCode): bool
    {
        return in_array($serviceCode, self::OOH_SERVICES, true);
    }

    /**
     * @return string
     */
    public function getHostCountry(): string
    {
        return $this->scopeConfig->getValue('carriers/samedaycourier/country') ?? ApiHelper::ROMANIA_CODE;
    }
}
