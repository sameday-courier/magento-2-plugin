<?php

namespace SamedayCourier\Shipping\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use SamedayCourier\Shipping\Api\Data\OrderInfoInterfaceFactory;
use SamedayCourier\Shipping\Api\OrderInfoRepositoryInterface;
use Magento\Framework\App\ResourceConnection;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var OrderInfoRepositoryInterface $orderInfoRepository
     */
    private $orderInfoRepository;

    /**
     * @var OrderInfoInterfaceFactory $orderInfoFactory
     */
    private $orderInfoFactory;

    /**
     * @var AdapterInterface $connection
     */
    private $connection;

    /**
     * @param OrderInfoRepositoryInterface $orderInfoRepository
     * @param OrderInfoInterfaceFactory $orderInfoFactory
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        OrderInfoRepositoryInterface $orderInfoRepository,
        OrderInfoInterfaceFactory $orderInfoFactory,
        ResourceConnection $resourceConnection
    )
    {
        $this->orderInfoRepository = $orderInfoRepository;
        $this->orderInfoFactory = $orderInfoFactory;
        $this->connection = $resourceConnection->getConnection();
    }

    /**
     * @param ModuleDataSetupInterface $setup
     *
     * @param ModuleContextInterface $context
     *
     * @return void
     */
    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ): void
    {
        $setup->startSetup();

        $tableName = $this->connection->getTableName('sales_order');
        $sql = sprintf(
            '
            SELECT
                entity_id,
                samedaycourier_locker,
                samedaycourier_fee,
                samedaycourier_destination_address_hd
            FROM %s
            WHERE
                samedaycourier_locker IS NOT NULL
                OR samedaycourier_fee IS NOT NULL
                OR samedaycourier_destination_address_hd IS NOT NULL
            ',
            $tableName
        );
        $orders = $this->connection->fetchAll($sql);

        if (!empty($orders)) {
            foreach ($orders as $order) {
                try {
                    $orderInfoModel = $this->orderInfoRepository->getByOrderId($order['entity_id']);
                } catch (NoSuchEntityException $exception) {
                    $orderInfoModel = $this->orderInfoFactory->create();
                    $orderInfoModel->setOrderId($order['entity_id']);
                }

                $orderInfoModel
                    ->setSamedaycourierLocker($order['samedaycourier_locker'])
                    ->setSamedaycourierFee($order['samedaycourier_fee'])
                    ->setSamedaycourierDestinationAddressHd($order['samedaycourier_destination_address_hd'])
                ;

                try {
                    $this->orderInfoRepository->save($orderInfoModel);
                    $orderInfoModel = null;
                } catch (\Exception $exception) {
                    return;
                }
            }
        }

        $setup->endSetup();
    }
}
