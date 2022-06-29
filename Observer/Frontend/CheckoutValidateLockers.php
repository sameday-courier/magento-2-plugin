<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Observer\Frontend;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Serialize\Serializer\Json;

class CheckoutValidateLockers implements ObserverInterface
{
    /**
     * @var RequestInterface $request
     */
    protected $request;

    /**
     * @var Json $json
     */
    protected $json;

    public function __construct(RequestInterface $request, Json $json)
    {
        $this->request = $request;
        $this->json = $json;
    }

    public function execute(EventObserver $observer)
    {
        $order = $observer->getOrder();
        $cookie = $this->request->getCookie('samedaycourier_locker', null);

        if (null !== $cookie) {
            $samedayLocker = $this->json->unserialize($cookie);
            if (isset($samedayLocker['lockerId']) && $order->getData()['shipping_method'] === 'samedaycourier_15') {
                $order->setData('samedaycourier_locker', $samedayLocker['lockerId']);
            }
        }
    }
}
