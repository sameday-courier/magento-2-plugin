<?php

namespace SamedayCourier\Shipping\Helper;

class GeneralHelper
{
    public const SAMEDAY_SERVICE_6H_CODE = '6H';
    public const SAMEDAY_SERVICE_24H_CODE = '24';
    public const SAMEDAY_SERVICE_LOCKER_CODE = 'LN';
    public const SAMEDAY_SERVICE_CROSSBORDER_24_CODE = 'XB';
    public const SAMEDAY_SERVICE_CROSSBORDER_LOCKER_CODE = 'XL';
    public const SAMEDAY_SERVICE_PUDO_CODE = 'PD';
    public const OOH_SERVICE_LABEL = 'Out of home delivery';

    public const OOH_LABEL = [
        ApiHelper::ROMANIA_CODE => 'Ridicare personala',
        ApiHelper::BULGARIA_CODE => 'Лично вземане',
        ApiHelper::HUNGARY_CODE => 'Személyes átvétel',
    ];

    public const OOH_LABEL_INFO = 'Optiunea Ridicare Personala include ambele servicii LockerNextDay, respectiv Pudo !';

    private const SAMEDAY_IN_USE_SERVICES = [
        self::SAMEDAY_SERVICE_6H_CODE,
        self::SAMEDAY_SERVICE_24H_CODE,
        self::SAMEDAY_SERVICE_LOCKER_CODE,
        self::SAMEDAY_SERVICE_CROSSBORDER_24_CODE,
        self::SAMEDAY_SERVICE_CROSSBORDER_LOCKER_CODE,
    ];

    /**
     * OOH - stands for Out of home Services
     */
    private const OOH_SERVICES = [
        self::SAMEDAY_SERVICE_LOCKER_CODE,
        self::SAMEDAY_SERVICE_PUDO_CODE,
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

    public function getHostCountry(): string
    {
        return ApiHelper::ROMANIA_CODE;
    }
}
