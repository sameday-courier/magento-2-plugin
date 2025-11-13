<?php
declare(strict_types=1);

namespace SamedayCourier\Shipping\Plugin;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Api\SearchResultsInterface;

class OrderRepositoryPlugin
{
    private $extensionFactory;

    public function __construct(OrderExtensionFactory $extensionFactory)
    {
        $this->extensionFactory = $extensionFactory;
    }

    public function afterGet(
        OrderRepositoryInterface $subject,
        OrderInterface $order
    ): OrderInterface {
        return $this->addLocker($order);
    }

    public function afterGetList(
        OrderRepositoryInterface $subject,
        SearchResultsInterface $searchResults
    ): SearchResultsInterface {
        foreach ($searchResults->getItems() as $order) {
            $this->addLocker($order);
        }

        return $searchResults;
    }

    private function addLocker(OrderInterface $order): OrderInterface
    {
        $lockerData = $order->getData('samedaycourier_locker');

        if (!$lockerData) {
            return $order;
        }

        $extensionAttributes = $order->getExtensionAttributes() ?: $this->extensionFactory->create();
        $extensionAttributes->setSamedaycourierLocker($lockerData);
        $order->setExtensionAttributes($extensionAttributes);

        return $order;
    }
}
