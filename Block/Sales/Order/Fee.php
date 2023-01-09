<?php

namespace SamedayCourier\Shipping\Block\Sales\Order;

use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Template;
use SamedayCourier\Shipping\Helper\StoredDataHelper;

class Fee extends Template
{
    private $storedDataHelper;

    public function __construct(
        StoredDataHelper $storedDataHelper,
        Template\Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);

        $this->storedDataHelper = $storedDataHelper;
    }

    public function initTotals(): self
    {
        $parent = $this->getParentBlock();
        $order = $parent->getOrder();

        if (0 < $samedayCourierFee = $order->getSamedaycourierFee()) {
            $fee = new DataObject(
                [
                    'code'=> 'fee',
                    'strong'=> false,
                    'value'=> $samedayCourierFee,
                    'label'=> __($this->storedDataHelper->getRepaymentFeeLabel()),
                ]
            );

            $parent->addTotal($fee, 'fee');
        }

        return $this;
    }
}
