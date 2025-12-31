<?php

namespace SamedayCourier\Shipping\Helper;

use RuntimeException;

class BgnCurrencyConvertor
{
    /**
     * @var string $currency
     */
    private $currency;

    /**
     * @var float $amount
     */
    private $amount;

    public function __construct(string $currency, float $amount)
    {
        $this->currency = $currency;
        $this->amount = $amount;
    }

    /**
     * @return string
     *
     * @throws RuntimeException
     */
    public function convert(): string
    {
        switch ($this->currency) {
            case StoredDataHelper::EURO_CURRENCY_CODE:
                return $this->convertBGNtoEUR($this->amount);
            case StoredDataHelper::SAMEDAY_ELIGIBLE_CURRENCIES[ApiHelper::BULGARIA_CODE]:
                return $this->convertEURtoBGN($this->amount);
            default:
                throw new RuntimeException('Invalid currency');
        }
    }

    /**
     * @param string $carrierTitle
     * @param string $estimatedPrice
     * @param string $estimatedCurrency
     *
     * @return string
     */
    public function buildCurrencyConversionLabel(
        string $carrierTitle,
        string $estimatedPrice,
        string $estimatedCurrency
    ): string
    {
        return sprintf(
            '%s (≈ %s %s)',
            $carrierTitle,
            $estimatedPrice,
            $estimatedCurrency
        );
    }

    /**
     * @param float $amount
     *
     * @return string
     */
    private function convertBGNtoEUR(float $amount): string
    {
        return number_format(($amount * 0.511292), 2, '.', '');
    }

    /**
     * @param float $amount
     *
     * @return string
     */
    private function convertEURtoBGN(float $amount): string
    {
        return number_format(($amount * 1.95583), 2, '.', '');
    }
}
