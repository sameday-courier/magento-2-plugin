<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Observer\Frontend;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use SamedayCourier\Shipping\Helper\StoredDataHelper;

class CheckoutRepaymentFee implements ObserverInterface
{
    private $session;
    private $storedDataHelper;
    private $scopeConfig;

    public function __construct(
        Session $session,
        StoredDataHelper $storedDataHelper,
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->session = $session;
        $this->storedDataHelper = $storedDataHelper;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param EventObserver $observer
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
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
