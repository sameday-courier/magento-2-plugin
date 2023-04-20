<?php

namespace SamedayCourier\Shipping\Helper;

use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Sameday\Requests\SamedayGetLockersRequest;
use Sameday\Requests\SamedayGetPickupPointsRequest;
use Sameday\Requests\SamedayGetServicesRequest;
use Sameday\Sameday;
use SamedayCourier\Shipping\Api\Data\LockerInterfaceFactory;
use SamedayCourier\Shipping\Api\Data\PickupPointInterface;
use SamedayCourier\Shipping\Api\Data\PickupPointInterfaceFactory;
use SamedayCourier\Shipping\Api\Data\ServiceInterface;
use SamedayCourier\Shipping\Api\Data\ServiceInterfaceFactory;
use SamedayCourier\Shipping\Api\LockerRepositoryInterface;
use SamedayCourier\Shipping\Api\PickupPointRepositoryInterface;
use SamedayCourier\Shipping\Api\ServiceRepositoryInterface;
use Sameday\Exceptions\SamedaySDKException;

class LocalDataImporter extends AbstractHelper
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
     * @var PickupPointInterfaceFactory
     */
    private $pickupPointFactory;

    /**
     * @var PickupPointRepositoryInterface
     */
    private $pickupPointRepository;

    /**
     * @var LockerInterfaceFactory
     */
    private $lockerFactory;

    /**
     * @var LockerRepositoryInterface
     */
    private $lockerRepository;

    /**
     * @var StoredDataHelper
     */
    private $storeDataHelper;

    /**
     * @param Context $context
     * @param ApiHelper $apiHelper
     * @param ScopeConfigInterface $config
     * @param ServiceInterfaceFactory $serviceFactory
     * @param ServiceRepositoryInterface $serviceRepository
     * @param StoredDataHelper $storedDataHelper
     * @param PickupPointInterfaceFactory $pickupPointFactory
     * @param PickupPointRepositoryInterface $pickupPointRepository
     * @param LockerRepositoryInterface $lockerRepository
     * @param LockerInterfaceFactory $lockerFactory
     */
    public function __construct(
        Context $context,
        ApiHelper $apiHelper,
        ScopeConfigInterface $config,
        ServiceInterfaceFactory $serviceFactory,
        ServiceRepositoryInterface $serviceRepository,
        StoredDataHelper $storedDataHelper,
        PickupPointInterfaceFactory $pickupPointFactory,
        PickupPointRepositoryInterface $pickupPointRepository,
        LockerRepositoryInterface $lockerRepository,
        LockerInterfaceFactory $lockerFactory
    ) {
        parent::__construct($context);

        $this->apiHelper = $apiHelper;
        $this->config = $config;
        $this->serviceFactory = $serviceFactory;
        $this->serviceRepository = $serviceRepository;
        $this->storeDataHelper = $storedDataHelper;
        $this->pickupPointFactory = $pickupPointFactory;
        $this->pickupPointRepository = $pickupPointRepository;
        $this->lockerRepository = $lockerRepository;
        $this->lockerFactory = $lockerFactory;
    }

    /**
     * @throws SamedaySDKException
     */
    public function importServices(): LocalDataImporterResponse
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
            } catch (Exception $e) {
                return (new LocalDataImporterResponse())
                    ->setSucceed(false)
                    ->setMessage(__($e->getMessage())
                );
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
                        ->setStatus(ServiceInterface::STATUS_DISABLED)
                        ->setServiceOptionalTaxes(
                            $this->storeDataHelper->serializeServiceOptionalTaxes($serviceObject->getOptionalTaxes())
                        )
                    ;
                }

                $service
                    ->setSamedayId($serviceObject->getId())
                    ->setSamedayName($serviceObject->getName())
                    ->setIsTesting($isTesting)
                    ->setServiceOptionalTaxes(
                        $this->storeDataHelper->serializeServiceOptionalTaxes($serviceObject->getOptionalTaxes())
                    )
                ;

                $this->serviceRepository->save($service);

                // Save as current services.
                $remoteServices[] = $serviceObject->getId();
            }
        } while ($page <= $services->getPages());


        // Build array of local services.
        $localServices = array_map(
            static function (ServiceInterface $service) {
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

        return (new LocalDataImporterResponse())
            ->setSucceed(true)
            ->setMessage(__('Services was imported with success !')
        );
    }

    /**
     * @throws SamedaySDKException
     */
    public function importPickupPoints(): LocalDataImporterResponse
    {
        $sameday = new Sameday($this->apiHelper->initClient());
        $isTesting = (bool) $this->config->getValue('carriers/samedaycourier/testing');

        $remotePickupPoints = [];
        $page = 1;
        do {
            $request = new SamedayGetPickupPointsRequest();
            $request->setPage($page++);
            try {
                $pickUpPoints = $sameday->getPickupPoints($request);
            } catch (Exception $e) {
                return (new LocalDataImporterResponse())
                    ->setSucceed(false)
                    ->setMessage(__($e->getMessage())
                );
            }

            foreach ($pickUpPoints->getPickupPoints() as $pickupPointObject) {
                try {
                    $pickupPoint = $this->pickupPointRepository->getBySamedayId($pickupPointObject->getId(), $isTesting);
                } catch (NoSuchEntityException $e) {
                    $pickupPoint = null;
                }

                if (!$pickupPoint) {
                    // Pickup point not found, add it.
                    $pickupPoint = $this->pickupPointFactory->create();
                }

                $pickupPoint
                    ->setSamedayId($pickupPointObject->getId())
                    ->setSamedayAlias($pickupPointObject->getAlias())
                    ->setIsTesting($isTesting)
                    ->setCity($pickupPointObject->getCity()->getName())
                    ->setCounty($pickupPointObject->getCounty()->getName())
                    ->setAddress($pickupPointObject->getAddress())
                    ->setContactPersons($pickupPointObject->getContactPersons())
                    ->setIsDefault($pickupPointObject->isDefault());

                $this->pickupPointRepository->save($pickupPoint);

                // Save as current pickup points.
                $remotePickupPoints[] = $pickupPointObject->getId();
            }
        } while ($page <= $pickUpPoints->getPages());


        // Build array of local pickup points.
        $localPickupPoints = array_map(
            static function (PickupPointInterface $pickupPoint) {
                return array(
                    'id' => $pickupPoint->getId(),
                    'sameday_id' => $pickupPoint->getSamedayId()
                );
            },
            $this->pickupPointRepository->getListByTesting($isTesting)->getItems()
        );

        // Delete local pickup points that aren't present in remote pickup points anymore.
        foreach ($localPickupPoints as $localPickupPoint) {
            if (!in_array($localPickupPoint['sameday_id'], $remotePickupPoints, false)) {
                $this->pickupPointRepository->deleteById($localPickupPoint['id']);
            }
        }

        return (new LocalDataImporterResponse())
            ->setSucceed(true)
            ->setMessage(__('Pickup-point list was imported with success!'))
        ;
    }

    /**
     * @throws SamedaySDKException
     */
    public function importLockers(): LocalDataImporterResponse
    {
        $sameday = new Sameday($this->apiHelper->initClient());
        $isTesting = (bool) $this->config->getValue('carriers/samedaycourier/testing');

        $request = new SamedayGetLockersRequest();
        try {
            $lockers = $sameday->getLockers($request);
        } catch (Exception $e) {
            return (new LocalDataImporterResponse())
                ->setSucceed(false)
                ->setMessage(__($e->getMessage()))
            ;
        }

        $remoteLockers = [];
        foreach ($lockers->getLockers() as $lockerObject) {
            $locker = null;
            try {
                $locker = $this->lockerRepository->getLockerBySamedayId($lockerObject->getId());
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

        return (new LocalDataImporterResponse())
            ->setSucceed(true)
            ->setMessage(__('Lockers was imported with success!')
        );
    }
}
