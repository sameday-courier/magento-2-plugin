<?php

namespace SamedayCourier\Shipping\Model\Carrier;

use Magento\Directory\Model\Region;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Psr\Log\LoggerInterface;
use Sameday\Objects\ParcelDimensionsObject;
use Sameday\Objects\PostAwb\Request\AwbRecipientEntityObject;
use Sameday\Objects\Types\AwbPaymentType;
use Sameday\Objects\Types\PackageType;
use Sameday\Requests\SamedayPostAwbEstimationRequest;
use SamedayCourier\Shipping\Api\Data\ServiceInterface;
use SamedayCourier\Shipping\Api\PickupPointRepositoryInterface;
use SamedayCourier\Shipping\Api\ServiceRepositoryInterface;
use SamedayCourier\Shipping\Helper\ApiHelper as SamedayApiHelper;

class Shipping extends AbstractCarrier implements CarrierInterface
{
    protected $_code = 'samedaycourier';
    protected $_isFixed = true;
    protected $_rateResultFactory;
    protected $_rateMethodFactory;
    private $samedayApiHelper;
    private $serviceRepository;
    private $pickupPointRepository;
    private $scopeConfig;

    public function __construct(
        SamedayApiHelper $samedayApiHelper,
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        ServiceRepositoryInterface $serviceRepository,
        PickupPointRepositoryInterface $pickupPointRepository,
        array $data = []
    ) {
        $this->samedayApiHelper = $samedayApiHelper;
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->serviceRepository = $serviceRepository;
        $this->pickupPointRepository = $pickupPointRepository;
        $this->scopeConfig = $scopeConfig;

        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * @inheritdoc
     */
    public function checkAvailableShipCountries(DataObject $request)
    {
        if (strtolower($request->getData('dest_country_id')) === $this->samedayApiHelper->getHostCountry()) {
            // Ship only to Sameday API host country.
            return $this;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function isStateProvinceRequired()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isCityRequired()
    {
        return true;
    }

    public function getAllowedMethods()
    {
        return [$this->getCarrierCode() => __($this->getConfigData('name'))];
    }

    public function collectRates(RateRequest $request)
    {
        if (!$this->isActive()) {
            return false;
        }

        $result = $this->_rateResultFactory->create();
        $isTesting = (bool) $this->scopeConfig->getValue('carriers/samedaycourier/testing');

        $services = $this->serviceRepository->getAllActive($isTesting)->getItems();
        foreach ($services as $service) {
            $isLockerService = in_array($service->getCode(), ServiceInterface::SERVICES_WITH_LOCKERS);
            $lockerMaxItems = $service->getLockerMaxItems();
            if (sizeof($request->getAllItems()) > $lockerMaxItems && $isLockerService) {
                continue;
            }

            $method = $this->_rateMethodFactory->create();

            $method->setCarrier($this->getCarrierCode());
            $method->setCarrierTitle($this->getConfigData('title'));

            $method->setMethod($service->getSamedayId());
            $method->setMethodTitle($service->getName());

            $method->setShowLockersMap((bool) $this->scopeConfig->getValue('carriers/samedaycourier/show_lockers_map'));

            $shippingCost = $service->getPrice();
            if ($service->getIsPriceFree() && $request->getPackageValueWithDiscount() >= $service->getPriceFree()) {
                $shippingCost = 0;
            } elseif ($service->getUseEstimatedCost()) {
                $shippingCostEstimation = $this->shippingEstimateCost($request, $service->getSamedayId());
                $shippingCost = $shippingCostEstimation ?  $shippingCostEstimation->getCost() : $service->getPrice();
            }

            $method
                ->setPrice($shippingCost)
                ->setCost($shippingCost);

            $result->append($method);
        }

        return $result;
    }

    private function shippingEstimateCost(RateRequest $request, int $serviceId)
    {
        $defaultPickupPoint = $this->pickupPointRepository->getDefaultPickupPoint();
        $packageWeight = max(1, $request->getData('package_weight'));

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $region = $objectManager->create(Region::class);
        $regionName = $region->loadByCode($request->getData('dest_region_code'), $request->getData('dest_country_id'))->getName();
        $city = $request->getDestCity();
        if ($region->getCode() === 'B') {
            $city = 'Sectorul 1';
        }

        if (null === $city) {
            return false;
        }

        $apiRequest = new SamedayPostAwbEstimationRequest(
            $defaultPickupPoint->getSamedayId(),
            null,
            (new PackageType(PackageType::PARCEL)),
            [(new ParcelDimensionsObject($packageWeight))],
            $serviceId,
            (new AwbPaymentType(AwbPaymentType::CLIENT)),
            (new AwbRecipientEntityObject(
                $city,
                $regionName ?? $request->getDestRegionCode(),
                $request->getData('dest_street'),
                $request->getData('firstname') . ' ' .  $request->getData('lastname'),
                $request->getData('telephone'),
                null,
                null,
                $request->getDestPostcode(),
            )),
            0,
            $request->getData('package_value_with_discount')
        );

        return $this->samedayApiHelper->doRequest($apiRequest, 'postAwbEstimation', false);
    }
}
