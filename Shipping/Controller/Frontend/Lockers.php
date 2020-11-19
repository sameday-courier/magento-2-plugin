<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Controller\Frontend;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\ResultFactory;
use SamedayCourier\Shipping\Api\LockerRepositoryInterface;

class Lockers extends Action
{
    /** @var LockerRepositoryInterface $lockerRepository */
    private $lockerRepository;

    private $config;

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
        $isTesting = (bool) $this->config->getValue('carriers/samedaycourier/testing');
        $page = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $lockers = $this->lockerRepository->getListByTesting($isTesting);

        $dump = [];
        /** @var \SamedayCourier\Shipping\Model\Data\Locker $locker */
        foreach ($lockers->getItems() as $locker) {
            $dump[$locker['city']][] = [
                'id' => (int) $locker['id'],
                'name' => $locker['name'],
                'city' => $locker['city'],
                'county' => $locker['county'],
            ];
        }
        ksort($dump);

        $block = $page->getLayout()->getBlock('samedaycourier_shipping.template.lockers');
        $block->setData('cities', $dump);

        return $this->getResponse()->setBody($block->toHtml());
    }
}
