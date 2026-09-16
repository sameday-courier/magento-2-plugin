<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Helper;

use Exception;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Config as OrderConfig;
use Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory as OrderStatusCollectionFactory;
use Psr\Log\LoggerInterface;
use SamedayCourier\Shipping\Api\Data\AwbInterface;

/**
 * Applies / reverts Magento order status around AWB create and remove.
 */
class OrderStatusHelper extends AbstractHelper
{
    public const CONFIG_PATH_AWB_ORDER_STATUS = 'carriers/samedaycourier/awb_order_status';

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var OrderStatusCollectionFactory
     */
    private $orderStatusCollectionFactory;

    /**
     * @var OrderConfig
     */
    private $orderConfig;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepository,
        OrderStatusCollectionFactory $orderStatusCollectionFactory,
        OrderConfig $orderConfig,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->orderRepository = $orderRepository;
        $this->orderStatusCollectionFactory = $orderStatusCollectionFactory;
        $this->orderConfig = $orderConfig;
        $this->logger = $logger;
    }

    public function getConfiguredAwbOrderStatus(): string
    {
        return trim((string) $this->scopeConfig->getValue(self::CONFIG_PATH_AWB_ORDER_STATUS));
    }

    public function getStatusLabel(string $status): string
    {
        if ($status === '') {
            return '';
        }

        $statuses = $this->orderConfig->getStatuses();

        return isset($statuses[$status]) ? (string) $statuses[$status] : $status;
    }

    /**
     * @return array{order_status:string,order_status_label:string}
     */
    public function getOrderStatusPayload(int $orderId): array
    {
        try {
            $status = (string) $this->orderRepository->get($orderId)->getStatus();
        } catch (Exception $e) {
            $status = '';
        }

        return [
            'order_status' => $status,
            'order_status_label' => $this->getStatusLabel($status),
        ];
    }

    /**
     * Store the current order status on the AWB before save.
     * Call applyConfiguredStatus() after a successful AWB save.
     */
    public function captureInitialStatus(OrderInterface $order, AwbInterface $awb): void
    {
        $status = (string) $order->getStatus();
        $awb->setInitialOrderStatus($status !== '' ? $status : null);
    }

    public function applyConfiguredStatus(OrderInterface $order): void
    {
        $targetStatus = $this->getConfiguredAwbOrderStatus();
        if ($targetStatus === '' || $targetStatus === (string) $order->getStatus()) {
            return;
        }

        $this->changeOrderStatus(
            $order,
            $targetStatus,
            (string) __('Order status updated after Sameday AWB generation.')
        );
    }

    /**
     * Restore the order status captured when the AWB was created.
     */
    public function revertStatusFromAwb(AwbInterface $awb): void
    {
        $initialStatus = (string) $awb->getInitialOrderStatus();
        $orderId = (int) $awb->getOrderId();
        if ($initialStatus === '' || $orderId <= 0) {
            return;
        }

        try {
            $order = $this->orderRepository->get($orderId);
        } catch (Exception $e) {
            $this->logger->error(
                'Sameday could not load order to revert status after AWB removal',
                ['order_id' => $orderId, 'error' => $e->getMessage()]
            );

            return;
        }

        if ((string) $order->getStatus() === $initialStatus) {
            return;
        }

        $this->changeOrderStatus(
            $order,
            $initialStatus,
            (string) __('Order status restored after Sameday AWB removal.')
        );
    }

    private function changeOrderStatus(OrderInterface $order, string $status, string $comment): void
    {
        $state = $this->resolveStateForStatus($status);
        if ($state === null) {
            $this->logger->warning(
                'Sameday could not resolve Magento order state for status',
                ['status' => $status, 'order_id' => $order->getEntityId()]
            );

            return;
        }

        $order->setState($state);
        $order->setStatus($status);
        $this->addStatusHistoryComment($order, $comment, $status);

        try {
            $this->orderRepository->save($order);
        } catch (Exception $e) {
            $this->logger->error(
                'Sameday failed to save order status change',
                [
                    'order_id' => $order->getEntityId(),
                    'status' => $status,
                    'error' => $e->getMessage(),
                ]
            );
        }
    }

    private function resolveStateForStatus(string $status): ?string
    {
        $collection = $this->orderStatusCollectionFactory->create();
        $collection->joinStates();
        $collection->addFieldToFilter('main_table.status', $status);
        $state = $collection->getFirstItem()->getData('state');

        return $state !== null && $state !== '' ? (string) $state : null;
    }

    /**
     * Magento 2.4+ prefers addCommentToStatusHistory; Magento 2.3 uses addStatusHistoryComment.
     */
    private function addStatusHistoryComment(OrderInterface $order, string $comment, string $status): void
    {
        if (!$order instanceof Order) {
            return;
        }

        if (method_exists($order, 'addCommentToStatusHistory')) {
            $order->addCommentToStatusHistory($comment, $status, false, false);

            return;
        }

        if (method_exists($order, 'addStatusHistoryComment')) {
            $history = $order->addStatusHistoryComment($comment, $status);
            if ($history && method_exists($history, 'setIsCustomerNotified')) {
                $history->setIsCustomerNotified(false);
            }
        }
    }
}
