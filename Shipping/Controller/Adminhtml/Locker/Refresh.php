<?php

namespace SamedayCourier\Shipping\Controller\Adminhtml\Locker;

use Magento\Backend\App\Action;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Sameday\Requests\SamedayGetLockersRequest;
use Sameday\Sameday;
use SamedayCourier\Shipping\Api\Data\ServiceInterfaceFactory;
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
     * @var ServiceInterfaceFactory
     */
    private $serviceFactory;

    /**
     * @var LockerRepositoryInterface
     */
    private $lockerRepository;

    /**
     * Refresh constructor.
     *
     * @param ApiHelper $apiHelper
     * @param ScopeConfigInterface $config
     * @param ServiceInterfaceFactory $serviceFactory
     * @param LockerRepositoryInterface $lockerRepository
     * @param Action\Context $context
     */
    public function __construct(
        ApiHelper $apiHelper,
        ScopeConfigInterface $config,
        ServiceInterfaceFactory $serviceFactory,
        LockerRepositoryInterface $lockerRepository,
        Action\Context $context
    ) {
        parent::__construct($context);

        $this->apiHelper = $apiHelper;
        $this->config = $config;
        $this->serviceFactory = $serviceFactory;
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


        foreach ($lockers->getLockers() as $lockerObject) {
            $locker = $this->lockerRepository->get($lockerObject->getId());
            var_dump($locker);
        }
        exit;
        /*

        $remoteServices = [];
        $page = 1;
        do {
            $request = new SamedayGetServicesRequest();
            $request->setPage($page++);
            try {
                $services = $sameday->getServices($request);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(sprintf(__('Communication error: %s'), $e->getMessage()));

                return $this->_redirect('samedaycourier_shipping/service/index');
            }

            foreach ($services->getServices() as $serviceObject) {
                try {
                    $service = $this->serviceRepository->getBySamedayId($serviceObject->getId(), $isTesting);
                } catch (NoSuchEntityException $e) {
                    $service = null;
                }

                if (!$service) {
                    // Service not found, add it.
                    $service = $this->serviceFactory->create()
                        ->setName($serviceObject->getName())
                        ->setIsPriceFree(false)
                        ->setStatus(ServiceInterface::STATUS_DISABLED);
                }

                $service
                    ->setSamedayId($serviceObject->getId())
                    ->setSamedayName($serviceObject->getName())
                    ->setIsTesting($isTesting);

                $this->serviceRepository->save($service);

                // Save as current services.
                $remoteServices[] = $serviceObject->getId();
            }
        } while ($page <= $services->getPages());


        // Build array of local services.
        $localServices = array_map(
            function (ServiceInterface $service) {
                return array(
                    'id' => $service->getId(),
                    'sameday_id' => $service->getSamedayId()
                );
            },
            $this->serviceRepository->getListByTesting($isTesting)->getItems()
        );

        // Delete local services that aren't present in remote services anymore.
        foreach ($localServices as $localService) {
            if (!in_array($localService['sameday_id'], $remoteServices, false)) {
                $this->serviceRepository->deleteById($localService['id']);
            }
        }*/
        var_dump('lala'); exit;
        return $this->_redirect('samedaycourier_shipping/locker/index');
    }
}
