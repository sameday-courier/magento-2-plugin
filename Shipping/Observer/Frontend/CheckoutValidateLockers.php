<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Observer\Frontend;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

class CheckoutValidateLockers implements ObserverInterface
{
    protected $request;

    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    public function execute(EventObserver $observer)
    {
        $order = $observer->getOrder();
        $samedayLockerCookie = (int) $this->request->getCookie('samedaycourier_locker_id', null);
        if ($samedayLockerCookie) {
            $order->setData('samedaycourier_locker', $samedayLockerCookie);
        }
    }
}
