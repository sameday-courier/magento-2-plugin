<?php

namespace SamedayCourier\Shipping\Model\ResourceModel;

use Magento\Framework\Serialize\Serializer\Json;
use SamedayCourier\Shipping\Api\Data\OrderBulkAwbInterface;
use SamedayCourier\Shipping\Api\OrderBulkAwbRepositoryInterface;
use SamedayCourier\Shipping\Model\OrderBulkAwbFactory;
use SamedayCourier\Shipping\Model\ResourceModel\OrderBulkAwb as OrderBulkAwbResource;
use SamedayCourier\Shipping\Model\ResourceModel\OrderBulkAwb\CollectionFactory;

class OrderBulkAwbRepository implements OrderBulkAwbRepositoryInterface
{
    /**
     * @var OrderBulkAwbFactory
     */
    private $orderBulkAwbFactory;

    /**
     * @var OrderBulkAwbResource
     */
    private $resource;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Json
     */
    private $json;

    public function __construct(
        OrderBulkAwbFactory $orderBulkAwbFactory,
        OrderBulkAwbResource $resource,
        CollectionFactory $collectionFactory,
        Json $json
    ) {
        $this->orderBulkAwbFactory = $orderBulkAwbFactory;
        $this->resource = $resource;
        $this->collectionFactory = $collectionFactory;
        $this->json = $json;
    }

    public function getByOrderId(int $orderId)
    {
        $model = $this->orderBulkAwbFactory->create();
        $this->resource->load($model, $orderId, OrderBulkAwbInterface::ORDER_ID);

        if (!$model->getId()) {
            return null;
        }

        return $model;
    }

    public function getByOrderIds(array $orderIds): array
    {
        $orderIds = array_values(array_unique(array_filter(array_map('intval', $orderIds))));
        if ($orderIds === []) {
            return [];
        }

        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(OrderBulkAwbInterface::ORDER_ID, ['in' => $orderIds]);

        $result = [];
        foreach ($collection as $item) {
            $result[(int) $item->getOrderId()] = $item;
        }

        return $result;
    }

    public function updateFeedback(int $orderId, int $status, array $payload): void
    {
        $model = $this->orderBulkAwbFactory->create();
        $this->resource->load($model, $orderId, OrderBulkAwbInterface::ORDER_ID);

        $model->setOrderId($orderId);
        $model->setStatus($status);
        $model->setFeedback($this->json->serialize($payload));
        $this->resource->save($model);
    }

    public function deleteByOrderId(int $orderId): void
    {
        $model = $this->getByOrderId($orderId);
        if ($model) {
            $this->resource->delete($model);
        }
    }

    public function clearWithoutGeneratedAwb(): array
    {
        $connection = $this->resource->getConnection();
        $bulkTable = $this->resource->getMainTable();
        $awbTable = $connection->getTableName('samedaycourier_shipping_awb');

        $select = $connection->select()
            ->from(['b' => $bulkTable], ['order_id'])
            ->joinLeft(
                ['a' => $awbTable],
                'a.order_id = b.order_id AND TRIM(a.awb_number) <> \'\'',
                []
            )
            ->where('a.id IS NULL');

        $orderIds = array_map('intval', $connection->fetchCol($select));
        if ($orderIds === []) {
            return [];
        }

        $connection->delete($bulkTable, ['order_id IN (?)' => $orderIds]);

        return $orderIds;
    }
}
