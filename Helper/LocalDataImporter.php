<?php

namespace SamedayCourier\Shipping\Helper;

use Exception;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Module\Dir;
use Magento\Framework\Serialize\Serializer\Json;
use Sameday\Requests\SamedayGetLockersRequest;
use Sameday\Requests\SamedayGetPickupPointsRequest;
use Sameday\Requests\SamedayGetServicesRequest;
use Sameday\Sameday;
use SamedayCourier\Shipping\Api\CityRepositoryInterface;
use SamedayCourier\Shipping\Api\Data\LockerInterfaceFactory;
use SamedayCourier\Shipping\Api\Data\CityInterfaceFactory;
use SamedayCourier\Shipping\Api\Data\PickupPointInterface;
use SamedayCourier\Shipping\Api\Data\PickupPointInterfaceFactory;
use SamedayCourier\Shipping\Api\Data\ServiceInterface;
use SamedayCourier\Shipping\Api\Data\ServiceInterfaceFactory;
use SamedayCourier\Shipping\Api\Data\RegionInterfaceFactory;
use SamedayCourier\Shipping\Api\LockerRepositoryInterface;
use SamedayCourier\Shipping\Api\PickupPointRepositoryInterface;
use SamedayCourier\Shipping\Api\RegionRepositoryInterface;
use SamedayCourier\Shipping\Api\ServiceRepositoryInterface;
use Sameday\Exceptions\SamedaySDKException;
use SamedayCourier\Shipping\Model\Region;
use SamedayCourier\Shipping\Model\ResourceModel\CityRepository;

class LocalDataImporter extends AbstractHelper
{
    /**
     * @var ApiHelper $apiHelper
     */
    private $apiHelper;

    /**
     * @var SamedayCitiesHelper $samedayCitiesHelper
     */
    private $samedayCitiesHelper;

    /**
     * @var Dir $moduleDirectory
     */
    private $moduleDirectory;

    /**
     * @var GeneralHelper $generalHelper
     */
    private $generalHelper;

    /**
     * @var ServiceInterfaceFactory
     */
    private $serviceFactory;

    /**
     * @var ServiceRepositoryInterface
     */
    private $serviceRepository;

    /**
     * @var RegionRepositoryInterface $regionRepository
     */
    private $regionRepository;

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
     * @var RegionInterfaceFactory
     */
    private $regionFactory;

    /**
     * @var LockerRepositoryInterface
     */
    private $lockerRepository;

    /**
     * @var CityInterfaceFactory
     */
    private $cityFactory;

    /**
     * @var CityRepository
     */
    private $cityRepository;

    /**
     * @var StoredDataHelper
     */
    private $storeDataHelper;

    /**
     * @var Json $jsonHelper
     */
    private $jsonHelper;

    /**
     * @param Context $context
     * @param ApiHelper $apiHelper
     * @param GeneralHelper $generalHelper
     * @param SamedayCountiesHelper $samedayCountiesHelper
     * @param SamedayCitiesHelper $samedayCitiesHelper
     * @param ServiceInterfaceFactory $serviceFactory
     * @param ServiceRepositoryInterface $serviceRepository
     * @param StoredDataHelper $storedDataHelper
     * @param PickupPointInterfaceFactory $pickupPointFactory
     * @param PickupPointRepositoryInterface $pickupPointRepository
     * @param LockerRepositoryInterface $lockerRepository
     * @param RegionRepositoryInterface $regionRepository
     * @param LockerInterfaceFactory $lockerFactory
     * @param RegionInterfaceFactory $regionFactory
     * @param CityRepositoryInterface $cityRepository
     * @param CityInterfaceFactory $cityFactory
     * @param Dir $moduleDirectory
     * @param Json $jsonHelper
     */
    public function __construct(
        Context $context,
        ApiHelper $apiHelper,
        GeneralHelper $generalHelper,
        SamedayCountiesHelper $samedayCountiesHelper,
        SamedayCitiesHelper $samedayCitiesHelper,
        ServiceInterfaceFactory $serviceFactory,
        ServiceRepositoryInterface $serviceRepository,
        StoredDataHelper $storedDataHelper,
        PickupPointInterfaceFactory $pickupPointFactory,
        PickupPointRepositoryInterface $pickupPointRepository,
        LockerRepositoryInterface $lockerRepository,
        RegionRepositoryInterface $regionRepository,
        LockerInterfaceFactory $lockerFactory,
        RegionInterfaceFactory $regionFactory,
        CityRepositoryInterface $cityRepository,
        CityInterfaceFactory $cityFactory,
        Dir $moduleDirectory,
        Json $jsonHelper
    ) {
        parent::__construct($context);

        $this->apiHelper = $apiHelper;
        $this->generalHelper = $generalHelper;
        $this->samedayCountiesHelper = $samedayCountiesHelper;
        $this->samedayCitiesHelper = $samedayCitiesHelper;
        $this->serviceFactory = $serviceFactory;
        $this->serviceRepository = $serviceRepository;
        $this->storeDataHelper = $storedDataHelper;
        $this->pickupPointFactory = $pickupPointFactory;
        $this->pickupPointRepository = $pickupPointRepository;
        $this->lockerRepository = $lockerRepository;
        $this->regionRepository = $regionRepository;
        $this->lockerFactory = $lockerFactory;
        $this->regionFactory = $regionFactory;
        $this->cityRepository = $cityRepository;
        $this->cityFactory = $cityFactory;
        $this->moduleDirectory = $moduleDirectory;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * @throws SamedaySDKException
     */
    public function importServices(): LocalDataImporterResponse
    {
        $sameday = new Sameday($this->apiHelper->initClient());
        $isTesting = $this->apiHelper->getEnvMode();

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

            $lockerService = null;
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

                if (false !== $this->generalHelper->isOoHService($serviceObject->getCode())) {
                    $service->setName($this->generalHelper::OOH_LABEL[$this->generalHelper->getHostCountry()]);
                }

                $this->serviceRepository->save($service);

                // Save as current services.
                $remoteServices[] = $serviceObject->getId();

                // Keep LockerService
                if ($service->getCode() === GeneralHelper::SAMEDAY_SERVICE_LOCKER_CODE) {
                    $lockerService = $service;
                }
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
            $this->serviceRepository->getListByTesting($isTesting)
        );

        // Delete local services that aren't present in remote services anymore.
        foreach ($localServices as $localService) {
            if (!in_array($localService['sameday_id'], $remoteServices)) {
                $this->serviceRepository->deleteById($localService['id']);
            }
        }

        // Update PUDO service status to be the same as LockerNextDay
        if (null !== $lockerService) {
            try{
                $pudoService = $this->serviceRepository->getBySamedayCode(
                    GeneralHelper::SAMEDAY_SERVICE_PUDO_CODE,
                    $lockerService->getIsTesting()
                );
            } catch (NoSuchEntityException $e) {
                $pudoService = null;
            }

            if (null !== $pudoService) {
                $pudoService->setStatus($lockerService->getStatus());
                $this->serviceRepository->save($pudoService);
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
        $isTesting = $this->apiHelper->getEnvMode();

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
            $this->pickupPointRepository->getListByTesting($isTesting)
        );

        // Delete local pickup points that aren't present in remote pickup points anymore.
        foreach ($localPickupPoints as $localPickupPoint) {
            if (!in_array((int) $localPickupPoint['sameday_id'], $remotePickupPoints, true)) {
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
        $isTesting = $this->apiHelper->getEnvMode();

        $remoteLockers = [];
        $page = 1;
        do {
            $request = new SamedayGetLockersRequest();
            $request->setPage($page++);
            try {
                $lockers = $sameday->getLockers($request);
            } catch (Exception $e) {
                return (new LocalDataImporterResponse())
                    ->setSucceed(false)
                    ->setMessage(__($e->getMessage()))
                ;
            }

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
            foreach ($localLockers as $locker) {
                if (!in_array((int) $locker['locker_id'], $remoteLockers, true)) {
                    try {
                        $this->lockerRepository->deleteById($locker['id']);
                    } catch (Exception $e) {}
                }
            }
        } while ($page <= $lockers->getPages());

        return (new LocalDataImporterResponse())
            ->setSucceed(true)
            ->setMessage(__('Lockers was imported with success!')
        );
    }

    public function importCounties(): LocalDataImporterResponse
    {
        $file = $this->moduleDirectory->getDir('SamedayCourier_Shipping') . '/utils/counties.json';
        if (!file_exists($file)) {

            return (new LocalDataImporterResponse())
                ->setSucceed(false)
                ->setMessage(__('Json file not found!'))
            ;
        }

        try {
            $counties = $this->jsonHelper->unserialize(file_get_contents($file));
        } catch (\RuntimeException $exception) {
            return (new LocalDataImporterResponse())
                ->setSucceed(false)
                ->setMessage(__($exception->getMessage()))
            ;
        }

        foreach ($counties as $county) {
            $region = $this->regionRepository->getByCodeAndCountryCode(
                $county['code'],
                $county['country_code']
            );

            if (null === $region) {
                $region = $this->regionFactory->create();
            }

            $region->setCountryId($county['country_code']);
            $region->setCode($county['code']);
            $region->setDefaultName($county['county']);

            $this->regionRepository->save($region);
        }

        return (new LocalDataImporterResponse())
            ->setSucceed(true)
            ->setMessage(__('Counties was imported with success!')
        );
    }

    /**
     * @return LocalDataImporterResponse
     */
    public function importCities(): LocalDataImporterResponse
    {
        $file = $this->moduleDirectory->getDir('SamedayCourier_Shipping') . '/utils/cities.json';
        if (!file_exists($file)) {

            return (new LocalDataImporterResponse())
                ->setSucceed(false)
                ->setMessage(__('Json file not found!'))
            ;
        }

        try {
            $cities = $this->jsonHelper->unserialize(file_get_contents($file));
        } catch (\RuntimeException $exception) {
            return (new LocalDataImporterResponse())
                ->setSucceed(false)
                ->setMessage(__($exception->getMessage()))
            ;
        }

        foreach ($cities as $city) {
            try {
                $region = $this->regionRepository->getByCodeAndCountryCode(
                    $city['county_code'],
                    $city['country_code']
                );
            } catch (NoSuchEntityException $exception) {
                $region = $this->regionFactory->create();
            }

            try {
                $samedayCity = $this->cityRepository->getBySamedayId((int) $city['city_id']);
            } catch (Exception $exception) {
                $samedayCity = $this->cityFactory->create();
            }

            $samedayCity->setSamedayId($city['city_id']);
            $samedayCity->setName($city['city_name']);
            $samedayCity->setRegionId($region->getRegionId());

            try {
                $this->cityRepository->save($samedayCity);
            } catch (Exception $e) {
                continue;
            }
        }

        return (new LocalDataImporterResponse())
            ->setSucceed(true)
            ->setMessage(__('Cities was imported with success!')
        );
    }
}
