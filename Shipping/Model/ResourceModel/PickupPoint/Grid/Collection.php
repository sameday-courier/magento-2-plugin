<?php

namespace SamedayCourier\Shipping\Model\ResourceModel\PickupPoint\Grid;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;

class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    /**
     * @var ScopeConfigInterface
     */
    private $config;

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
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, 'samedaycourier_shipping_pickuppoint', \SamedayCourier\Shipping\Model\ResourceModel\PickupPoint::class, $identifierName, $connectionName);

        $this->config = $config;
    }

    /**
     * @return \Magento\Framework\DB\Select
     */
    public function getSelect()
    {
        $select = parent::getSelect();

        if ($select !== null && $this->config !== null) {
            $select->where('is_testing=?', (int) $this->config->getValue('carriers/samedaycourier/testing'));
        }

        return $select;
    }
}
