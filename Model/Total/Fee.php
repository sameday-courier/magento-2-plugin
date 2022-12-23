<?php

namespace SamedayCourier\Shipping\Model\Total;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Phrase;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Magento\Quote\Model\QuoteValidator;

class Fee extends AbstractTotal
{
    private const REPAYMENT_TAX_LABEL = 'carriers/samedaycourier/repayment_tax_label';
    private const REPAYMENT_TAX_VALUE = 'carriers/samedaycourier/repayment_tax';
    private const COD_PAYMENT_METHOD_CODE = 'cashondelivery';

    /**
     * Collect grand total address amount
     *
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     * @return $this
     */
    protected ?QuoteValidator $quoteValidator = null;

    private int $fee;

    private string $feeLabel;

    public function __construct(
        QuoteValidator $quoteValidator,
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->quoteValidator = $quoteValidator;

        $this->fee = (int) $scopeConfig->getValue(self::REPAYMENT_TAX_VALUE);
        $this->feeLabel = (string) $scopeConfig->getValue(self::REPAYMENT_TAX_LABEL);
    }

    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ): self
    {
        parent::collect($quote, $shippingAssignment, $total);

        $exist_amount = 0;

        $fee = 0;

        if (null === $quote->getPayment()->getMethod() ||
            self::COD_PAYMENT_METHOD_CODE === $quote->getPayment()->getMethod()
        ) {
            $fee = $this->fee;
        }

        $balance = $fee - $exist_amount;
        $total->setTotalAmount('fee', $balance);
        $total->setBaseTotalAmount('fee', $balance);
        $total->setFee($balance);
        $total->setBaseFee($balance);
        $total->setGrandTotal($total->getGrandTotal());
        $total->setBaseGrandTotal($total->getBaseGrandTotal());

        return $this;
    }

    protected function clearValues(Total $total): void
    {
        $total->setTotalAmount('subtotal', 0);
        $total->setBaseTotalAmount('subtotal', 0);
        $total->setTotalAmount('tax', 0);
        $total->setBaseTotalAmount('tax', 0);
        $total->setTotalAmount('discount_tax_compensation', 0);
        $total->setBaseTotalAmount('discount_tax_compensation', 0);
        $total->setTotalAmount('shipping_discount_tax_compensation', 0);
        $total->setBaseTotalAmount('shipping_discount_tax_compensation', 0);
        $total->setSubtotalInclTax(0);
        $total->setBaseSubtotalInclTax(0);
    }

    /**
     * @param Quote $quote
     * @param Total $total
     *
     * @return array
     */
    public function fetch(Quote $quote, Total $total): array
    {
        return [
            'code'=> 'fee',
            'title'=> $this->getLabel(),
            'value'=> $this->fee
        ];
    }

    /**
     * Get Subtotal label
     *
     * @return Phrase
     */
    public function getLabel(): Phrase
    {
        return __($this->feeLabel);
    }
}
