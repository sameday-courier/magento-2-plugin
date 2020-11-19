<?php

namespace SamedayCourier\Shipping\Controller\Adminhtml\Service;

use Magento\Backend\App\Action;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Sameday\Requests\SamedayGetServicesRequest;
use Sameday\Sameday;
use SamedayCourier\Shipping\Api\Data\ServiceInterface;
use SamedayCourier\Shipping\Api\Data\ServiceInterfaceFactory;
use SamedayCourier\Shipping\Api\ServiceRepositoryInterface;
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
     * @var ServiceRepositoryInterface
     */
    private $serviceRepository;

    /**
     * Refresh constructor.
     *
     * @param ApiHelper $apiHelper
     * @param ScopeConfigInterface $config
     * @param ServiceInterfaceFactory $serviceFactory
     * @param ServiceRepositoryInterface $serviceRepository
     * @param Action\Context $context
     */
    public function __construct(
        ApiHelper $apiHelper,
        ScopeConfigInterface $config,
        ServiceInterfaceFactory $serviceFactory,
        ServiceRepositoryInterface $serviceRepository,
        Action\Context $context
    ) {
        parent::__construct($context);

        $this->apiHelper = $apiHelper;
        $this->config = $config;
        $this->serviceFactory = $serviceFactory;
        $this->serviceRepository = $serviceRepository;
    }

    public function execute()
    {
        $sameday = new Sameday($this->apiHelper->initClient());
        $isTesting = (bool) $this->config->getValue('carriers/samedaycourier/testing');

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
                        ->setCode($serviceObject->getCode())
                        ->setPrice(0)
                        ->setIsPriceFree(false)
                        ->setUseEstimatedCost(false)
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
        }

        return $this->_redirect('samedaycourier_shipping/service/index');
    }
}
