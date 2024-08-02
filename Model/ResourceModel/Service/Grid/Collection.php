<?php

namespace SamedayCourier\Shipping\Model\ResourceModel\Service\Grid;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Psr\Log\LoggerInterface as Logger;
use SamedayCourier\Shipping\Helper\GeneralHelper;
use SamedayCourier\Shipping\Model\ResourceModel\Service;

class Collection extends SearchResult
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
     * @throws LocalizedException
     */
    public function __construct(
        ScopeConfigInterface $config,
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = null,
        $resourceModel = null,
        $identifierName = null,
        $connectionName = null
    )
    {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            'samedaycourier_shipping_service',
            Service::class,
            $identifierName,
            $connectionName
        );

        $this->config = $config;
    }

    /**
     * @return Select|null
     */
    public function getSelect(): ?Select
    {
        $select = parent::getSelect();

        $inUseServices = [];
        foreach ((new GeneralHelper())->getInUseServices() as $service) {
            $inUseServices[] = sprintf('"%s"', $service);
        }

        $inUseServices = sprintf('( %s )', implode(', ', $inUseServices));

        if ($select !== null && $this->config !== null) {
            $select->where('is_testing=?', (int) $this->config->getValue('carriers/samedaycourier/testing'));
            $select->where("code IN $inUseServices");
        }

        return $select;
    }
}
