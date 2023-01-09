<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Observer\Frontend;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

class CheckoutValidateLockers implements ObserverInterface
{
    /**
     * @var RequestInterface $request
     */
    protected $request;

    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    public function execute(EventObserver $observer)
    {
        $order = $observer->getOrder();
        $cookie = $this->request->getCookie('samedaycourier_locker', null);

        if (null !== $cookie) {
            $order->setData('samedaycourier_locker', $cookie);
        }
    }
}
