<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Observer\Frontend;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Checkout\Model\Session;
use Magento\Framework\Serialize\Serializer\Json;
use SamedayCourier\Shipping\Helper\StoredDataHelper;

class CheckoutRepaymentFee implements ObserverInterface
{
    private Session $session;
    private Json $json;
    private StoredDataHelper $storedDataHelper;
    private ScopeConfigInterface $scopeConfig;

    public function __construct(
        Session $session,
        Json $json,
        StoredDataHelper $storedDataHelper,
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->session = $session;
        $this->json = $json;
        $this->storedDataHelper = $storedDataHelper;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param EventObserver $observer
     *
     * @return void
     */
    public function execute(EventObserver $observer): void
    {
        $order = $observer->getOrder();
        $quote = $this->session->getQuote();

        if ($quote->getPayment()->getMethod() === $this->storedDataHelper::CASH_ON_DELIVERY_CODE) {
            $order->setData('samedaycourier_fee', $this->scopeConfig->getValue($this->storedDataHelper::REPAYMENT_TAX_VALUE));
        }
    }
}
