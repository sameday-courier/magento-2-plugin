<?php

namespace SamedayCourier\Shipping\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Convert\Order as ConvertOrder;
use Magento\Sales\Model\Order\ShipmentRepository;
use Magento\Shipping\Model\Order\TrackFactory;
use Magento\Shipping\Model\Tracking\ResultFactory;

class OrderShipmentHelper extends AbstractHelper
{
    /**
     * @var ConvertOrder $convertOrder
     */
    private $convertOrder;

    /**
     * @var ShipmentRepository $shipmentRepository
     */
    private $shipmentRepository;

    /**
     * @var ResultFactory $trackFactory
     */
    private $trackFactory;

    public function __construct(
        Context $context,
        ConvertOrder $convertOrder,
        ShipmentRepository $shipmentRepository,
        TrackFactory $trackFactory
    )
    {
        parent::__construct($context);

        $this->convertOrder = $convertOrder;
        $this->shipmentRepository = $shipmentRepository;
        $this->trackFactory = $trackFactory;
    }

    /**
     * @param Order $order
     *
     * @return ShipmentInterface|null
     */
    public function saveOrderShipment(Order $order): ?ShipmentInterface
    {
        if ($order->hasShipments()) {
            return null;
        }

        if ($order->canShip()) {
            $orderShipment = $this->convertOrder->toShipment($order);

            foreach ($order->getAllItems() as $orderItem) {
                if (!$orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                    continue;
                }

                $shipmentItem = null;
                try {
                    $shipmentItem = $this->convertOrder->itemToShipmentItem($orderItem);
                } catch (\Exception $exception) {}

                if (null !== $shipmentItem) {
                    try {
                        $shipmentItem->setQty($orderItem->getQtyToShip());
                        $orderShipment->addItem($shipmentItem);
                    } catch (\Exception $exception) {}
                }
            }

            try {
                $orderShipment->register();
                $orderShipment->getOrder()->setIsInProcess(true);

                // Save created Order Shipment
                return $this->shipmentRepository->save($orderShipment);
            } catch (\Exception $exception) {}
        }

        return null;
    }

    /**
     * @param ShipmentInterface $shipment
     * @param array $tracking
     *
     * @return void
     */
    public function saveTracking(ShipmentInterface $shipment, array $tracking): void
    {
        $track = $this->trackFactory->create()->addData($tracking);

        $shipment->addTrack($track);
        try {
            $this->shipmentRepository->save($shipment);
        } catch (\Exception $exception) { return; }
    }
}
