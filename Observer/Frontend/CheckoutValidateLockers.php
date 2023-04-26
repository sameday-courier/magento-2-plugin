<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Observer\Frontend;

use Exception;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Sales\Model\Order;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Directory\Model\Region;

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

    private const SAMEDAY_EASYBOX_SERVICE = 'samedaycourier_LN';

    /**
     * @param RequestInterface $request
     * @param Json $json
     */
    public function __construct(
        RequestInterface $request,
        Json $json
    )
    {
        $this->request = $request;
        $this->json = $json;
    }

    /**
     * @throws Exception
     */
    public function execute(EventObserver $observer): void
    {
        /** @var Order $order */
        $order = $observer->getOrder();

        $samedaycourier_locker = $this->request->getCookie('samedaycourier_locker');

        if (null !== $samedaycourier_locker && self::SAMEDAY_EASYBOX_SERVICE === $order->getShippingMethod()) {
            $order->setData('samedaycourier_locker', $samedaycourier_locker);

            if (null !== $shippingAddress = $order->getShippingAddress()) {
                // Save HD address
                $order->setData('samedaycourier_destination_address_hd', $this->json->serialize([
                        'city' => $shippingAddress->getCity(),
                        'street' => $shippingAddress->getStreet(),
                        'region' => $shippingAddress->getRegion(),
                    ])
                );

                // Modify Shipping Address with locker address
                $samedaycourier_locker = $this->json->unserialize($samedaycourier_locker);

                $shippingAddress->setCity($samedaycourier_locker['city']);
                $shippingAddress->setStreet(sprintf(
                        '%s (%s)',
                        $samedaycourier_locker['address'],
                        $samedaycourier_locker['name']
                    )
                );

                $objectManager = ObjectManager::getInstance();

                /** @var Region $region */
                $region = $objectManager->create(Region::class);
                $regionId = $region->loadByName(
                        $samedaycourier_locker['county'],
                        $shippingAddress->getCountryId()
                    )->getId()
                ;

                $shippingAddress->setRegionId($regionId);
                $shippingAddress->setRegion($samedaycourier_locker['county']);

                $shippingAddress->save();
            }
        }
    }
}
