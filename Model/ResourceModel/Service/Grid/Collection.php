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
     * @var GeneralHelper $generalHelper
     */
    private $generalHelper;

    /**
     * @param ScopeConfigInterface $config
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param GeneralHelper $generalHelper
     * @throws LocalizedException
     */
    public function __construct(
        ScopeConfigInterface $config,
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        GeneralHelper $generalHelper
    )
    {
        $this->config = $config;
        $this->generalHelper = $generalHelper;

        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            'samedaycourier_shipping_service',
            Service::class,
        );
    }

    /**
     * @return Select|null
     */
    public function getSelect(): ?Select
    {
        $select = parent::getSelect();

        $inUseServices = [];
        foreach ($this->generalHelper->getInUseServices() as $service) {
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
