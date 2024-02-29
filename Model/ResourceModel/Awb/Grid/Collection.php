<?php

namespace SamedayCourier\Shipping\Model\ResourceModel\Awb\Grid;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Psr\Log\LoggerInterface as Logger;

class Collection extends SearchResult
{
    /**
     * Collection constructor.
     *
     * @param ScopeConfigInterface $config
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param null $mainTable
     * @param null $resourceModel
     * @param null $identifierName
     * @param null $connectionName
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(ScopeConfigInterface $config, EntityFactory $entityFactory, Logger $logger, FetchStrategy $fetchStrategy, EventManager $eventManager, $mainTable = null, $resourceModel = null, $identifierName = null, $connectionName = null)
    {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, 'samedaycourier_shipping_awb', \SamedayCourier\Shipping\Model\ResourceModel\Awb::class, $identifierName, $connectionName);

        $this->config = $config;
    }

    /**
     * @return Select
     */
    public function getSelect()
    {
        return parent::getSelect();
    }
}
