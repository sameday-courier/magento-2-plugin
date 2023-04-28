<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Observer\Frontend;

use Exception;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Sales\Model\Order;
use Magento\Framework\Serialize\Serializer\Json;
use SamedayCourier\Shipping\Helper\ShippingService;

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

    /**
     * @var ShippingService $shippingService
     */
    private $shippingService;

    private const SAMEDAY_EASYBOX_SERVICE = 'samedaycourier_LN';

    /**
     * @param RequestInterface $request
     * @param Json $json
     * @param ShippingService $shippingService
     */
    public function __construct(
        RequestInterface $request,
        Json $json,
        ShippingService $shippingService
    )
    {
        $this->request = $request;
        $this->json = $json;
        $this->shippingService = $shippingService;
    }

    /**
     * @throws Exception
     */
    public function execute(EventObserver $observer): void
    {
        /** @var Order $order */
        $order = $observer->getOrder();

        if (null === $shippingAddress = $order->getShippingAddress()) {
            return;
        }

        // Save HD address
        $order->setData('samedaycourier_destination_address_hd', $this->json->serialize([
                'city' => $shippingAddress->getCity(),
                'street' => $shippingAddress->getStreet(),
                'region' => $shippingAddress->getRegion(),
            ])
        );

        $samedaycourier_locker = $this->request->getCookie('samedaycourier_locker');

        if (null !== $samedaycourier_locker && self::SAMEDAY_EASYBOX_SERVICE === $order->getShippingMethod()) {
            $order->setData('samedaycourier_locker', $samedaycourier_locker);

            // Modify Shipping Address with locker address
            $samedaycourier_locker = $this->json->unserialize($samedaycourier_locker);

            $this->shippingService->persistAddress(
                $shippingAddress,
                $samedaycourier_locker['city'],
                $samedaycourier_locker['county'],
                sprintf(
                    '%s (%s)',
                    $samedaycourier_locker['address'],
                    $samedaycourier_locker['name']
                )
            );
        }
    }
}
