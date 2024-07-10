<?php

namespace SamedayCourier\Shipping\Setup;

use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '0.0.6', '<')) {
            $connection = $setup->getConnection();
            $table = $connection->getTableName('samedaycourier_shipping_service');

            if ($connection->isTableExists($table)) {
                if ($connection->tableColumnExists($table, 'locker_max_items')) {
                    $connection->dropColumn($table, 'locker_max_items');
                }
            }
        }

        $setup->endSetup();
    }
}
