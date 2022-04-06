<?php

namespace SamedayCourier\Shipping\Plugin\Quote\Address;

use Magento\Quote\Model\Quote\Address\RateResult\Method;

class Rate
{
    /**
     * @param $subject
     * @param $result
     * @param $rate
     *
     * @return mixed
     */
    public function afterImportShippingRate($subject, $result, $rate)
    {
        if ($rate instanceof Method) {
            $result->setShowLockersMap(
                $rate->getShowLockersMap()
            );

            $result->setCountryCode(
                $rate->getCountryCode()
            );
        }

        return $result;
    }
}
