<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Controller\Frontend;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\ResultFactory;
use SamedayCourier\Shipping\Api\LockerRepositoryInterface;
use SamedayCourier\Shipping\Model\Data\Locker;

class Lockers extends Action
{
    /** @var LockerRepositoryInterface $lockerRepository */
    private $lockerRepository;

    private $config;

    /** @var ResultFactory $resultFactory */
    protected $resultFactory;

    public function __construct(Context $context, LockerRepositoryInterface $lockerRepository, ScopeConfigInterface $config, ResultFactory $resultFactory)
    {
        parent::__construct($context);

        $this->lockerRepository = $lockerRepository;
        $this->config = $config;

        $this->resultFactory = $resultFactory;
    }

    public function execute()
    {
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $isShowLockersMap = (bool) $this->config->getValue('carriers/samedaycourier/show_lockers_map');

        // If the client choose to use Sameday Locker map, is not need the local list of lockers and return empty data
        if ($isShowLockersMap === true) {
            $resultJson->setData([]);

            return $resultJson;
        }

        $isTesting = (bool) $this->config->getValue('carriers/samedaycourier/testing');
        $lockers = $this->lockerRepository->getListByTesting($isTesting);
        $dump = [];
        /** @var Locker $locker */
        foreach ($lockers->getItems() as $locker) {
            $dump[$locker['city']]['label'] = $locker['city'] . ' (' . $locker['county'] . ')';
            $dump[$locker['city']]['lockers'][] = [
                'id' => $locker['id'],
                'label' => $locker['name'] . ' - ' . $locker['address'],
            ];
        }
        ksort($dump);

        $resultJson->setData(array_values($dump));

        return $resultJson;
    }
}
