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
use SamedayCourier\Shipping\Helper\StoredDataHelper;

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

    /**
     * @var StoredDataHelper $storedDataHelper
     */
    private $storedDataHelper;

    /**
     * @param RequestInterface $request
     * @param Json $json
     * @param ShippingService $shippingService
     * @param StoredDataHelper $storedDataHelper
     */
    public function __construct(
        RequestInterface $request,
        Json $json,
        ShippingService $shippingService,
        StoredDataHelper $storedDataHelper
    )
    {
        $this->request = $request;
        $this->json = $json;
        $this->shippingService = $shippingService;
        $this->storedDataHelper = $storedDataHelper;
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
        $shippingCode = explode('_', $order->getShippingMethod())[1] ?? null;

        if (null !== $samedaycourier_locker
            && $this->storedDataHelper->isEligibleToLocker($shippingCode)
        ) {
            $order->setData('samedaycourier_locker', $samedaycourier_locker);

            // Modify Shipping Address with locker address
            $samedaycourier_locker = $this->json->unserialize($samedaycourier_locker);

            $this->shippingService->updateShippingAddress(
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
