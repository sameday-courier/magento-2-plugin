<?php

namespace SamedayCourier\Shipping\Controller\Adminhtml\Locker;

use Magento\Backend\App\Action;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Sameday\Requests\SamedayGetLockersRequest;
use Sameday\Sameday;
use SamedayCourier\Shipping\Api\Data\LockerInterfaceFactory;
use SamedayCourier\Shipping\Api\LockerRepositoryInterface;
use SamedayCourier\Shipping\Helper\ApiHelper;

class Refresh extends Action
{
    /**
     * @var ApiHelper
     */
    private $apiHelper;

    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @var LockerInterfaceFactory
     */
    private $lockerFactory;

    /**
     * @var LockerRepositoryInterface
     */
    private $lockerRepository;

    /**
     * Refresh constructor.
     *
     * @param ApiHelper $apiHelper
     * @param ScopeConfigInterface $config
     * @param LockerInterfaceFactory $lockerFactory
     * @param LockerRepositoryInterface $lockerRepository
     * @param Action\Context $context
     */
    public function __construct(
        ApiHelper $apiHelper,
        ScopeConfigInterface $config,
        LockerInterfaceFactory $lockerFactory,
        LockerRepositoryInterface $lockerRepository,
        Action\Context $context
    ) {
        parent::__construct($context);

        $this->apiHelper = $apiHelper;
        $this->config = $config;
        $this->lockerFactory = $lockerFactory;
        $this->lockerRepository = $lockerRepository;
    }

    public function execute()
    {
        $sameday = new Sameday($this->apiHelper->initClient());
        $isTesting = (bool) $this->config->getValue('carriers/samedaycourier/testing');

        $request = new SamedayGetLockersRequest();
        try {
            $lockers = $sameday->getLockers($request);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(sprintf(__('Communication error: %s'), $e->getMessage()));
            return $this->_redirect('samedaycourier_shipping/locker/index');
        }

        $remoteLockers = [];
        foreach ($lockers->getLockers() as $lockerObject) {
            $locker = null;
            try {
                $locker = $this->lockerRepository->getByLockerId($lockerObject->getId());
            } catch (NoSuchEntityException $exception) {
                $locker = $this->lockerFactory->create();
            }

            $locker
                ->setLockerId($lockerObject->getId())
                ->setName($lockerObject->getName())
                ->setCounty($lockerObject->getCounty())
                ->setCity($lockerObject->getCity())
                ->setAddress($lockerObject->getAddress())
                ->setPostalCode($lockerObject->getPostalCode())
                ->setLat($lockerObject->getLat())
                ->setLng($lockerObject->getLong())
                ->setIsTesting($isTesting);

            $this->lockerRepository->save($locker);
            $remoteLockers[] = $lockerObject->getId();
        }

        $localLockers = $this->lockerRepository->getListByTesting($isTesting);
        foreach ($localLockers->getItems() as $locker) {
            if (!in_array($locker['locker_id'], $remoteLockers, false)) {
                $this->lockerRepository->deleteById($locker['id']);
            }
        }

        return $this->_redirect('samedaycourier_shipping/locker/index');
    }
}
