<?php

namespace SamedayCourier\Shipping\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $this->setupPickupPoints($setup);
        $this->setupServices($setup);
        $this->setupLockers($setup);
        $this->setupAwbs($setup);
        $this->setupOrderLocker($setup);

        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     *
     * @throws \Zend_Db_Exception
     */
    private function setupPickupPoints(SchemaSetupInterface $setup)
    {
        if ($setup->tableExists('samedaycourier_shipping_pickuppoint')) {
            return;
        }

        $table = $setup->getConnection()
            ->newTable($setup->getTable('samedaycourier_shipping_pickuppoint'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true]
            )
            ->addColumn(
                'sameday_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER
            )
            ->addColumn(
                'sameday_alias',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255
            )
            ->addColumn(
                'is_testing',
                \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN
            )
            ->addColumn(
                'city',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255
            )
            ->addColumn(
                'county',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255
            )
            ->addColumn(
                'address',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255
            )
            ->addColumn(
                'contact_persons',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT
            )
            ->addColumn(
                'is_default',
                \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN
            );

        $setup->getConnection()->createTable($table);
    }

    /**
     * @param SchemaSetupInterface $setup
     *
     * @throws \Zend_Db_Exception
     */
    private function setupServices(SchemaSetupInterface $setup)
    {
        if ($setup->tableExists('samedaycourier_shipping_service')) {
            return;
        }

        $table = $setup->getConnection()
            ->newTable($setup->getTable('samedaycourier_shipping_service'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true]
            )
            ->addColumn(
                'sameday_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER
            )
            ->addColumn(
                'sameday_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255
            )
            ->addColumn(
                'is_testing',
                \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN
            )
            ->addColumn(
                'name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255
            )
            ->addColumn(
                'price',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                null,
                ['precision' => 10, 'scale' => 2]
            )
            ->addColumn(
                'is_price_free',
                \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN
            )
            ->addColumn(
                'price_free',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                null,
                ['precision' => 10, 'scale' => 2]
            )
            ->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT
            )
            ->addColumn(
                'working_days',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT
            );

        $setup->getConnection()->createTable($table);
    }

    /**
     * @param SchemaSetupInterface $setup
     *
     * @throws \Zend_Db_Exception
     */
    private function setupLockers(SchemaSetupInterface $setup)
    {
        if ($setup->tableExists('samedaycourier_shipping_locker')) {
            return;
        }

        $table = $setup->getConnection()
            ->newTable($setup->getTable('samedaycourier_shipping_locker'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true]
            )
            ->addColumn(
                'locker_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER
            )
            ->addColumn(
                'name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255
            )
            ->addColumn(
                'county',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255
            )
            ->addColumn(
                'city',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255
            )
            ->addColumn(
                'address',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255
            )
            ->addColumn(
                'postal_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255
            )
            ->addColumn(
                'lat',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255
            )
            ->addColumn(
                'lng',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255
            )
            ->addColumn(
                'is_testing',
                \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN
            )
            ;

        $setup->getConnection()->createTable($table);
    }

    /**
     * @param SchemaSetupInterface $setup
     *
     * @throws \Zend_Db_Exception
     */
    private function setupAwbs(SchemaSetupInterface $setup)
    {
        if ($setup->tableExists('samedaycourier_shipping_awb')) {
            return;
        }

        $table = $setup->getConnection()
            ->newTable($setup->getTable('samedaycourier_shipping_awb'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true]
            )
            ->addColumn(
                'order_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                'awb_number',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255
            )
            ->addColumn(
                'parcels',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT
            )
            ->addColumn(
                'awb_cost',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                [10, 2]
            );

        $setup->getConnection()->createTable($table);

        $setup->getConnection()->addForeignKey(
            $setup->getFkName('samedaycourier_shipping_awb', 'order_id', $setup->getTable('sales_order'), 'entity_id'),
            $setup->getTable('samedaycourier_shipping_awb'),
            'order_id',
            $setup->getTable('sales_order'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_NO_ACTION
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function setupOrderLocker($setup)
    {
        if ($setup->getConnection()->tableColumnExists('sales_order', 'samedaycourier_locker')) {
            return;
        }

        $setup->getConnection()->addColumn(
            $setup->getTable(
                'sales_order'),
            'samedaycourier_locker',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'unsigned' => true,
                'nullable' => true,
                'comment' => 'SamedayCourier Locker'
            ]
        );
    }
}
