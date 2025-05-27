<?php

namespace SamedayCourier\Shipping\Model\Carrier;

use Magento\Directory\Model\Region;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Checkout\Model\Session;
use Psr\Log\LoggerInterface;
use Sameday\Objects\ParcelDimensionsObject;
use Sameday\Objects\PostAwb\Request\AwbRecipientEntityObject;
use Sameday\Objects\Types\AwbPaymentType;
use Sameday\Objects\Types\PackageType;
use Sameday\Requests\SamedayPostAwbEstimationRequest;
use SamedayCourier\Shipping\Api\PickupPointRepositoryInterface;
use SamedayCourier\Shipping\Api\ServiceRepositoryInterface;
use SamedayCourier\Shipping\Helper\ApiHelper as SamedayApiHelper;
use SamedayCourier\Shipping\Helper\ShippingService;
use SamedayCourier\Shipping\Helper\StoredDataHelper;
use SamedayCourier\Shipping\Model\Data\Service;
use SamedayCourier\Shipping\Model\TrackingInfo;

class Shipping extends AbstractCarrier implements CarrierInterface
{
    protected $_code = ShippingService::SHIPPING_METHOD_CODE;
    protected $_isFixed = true;
    protected $_rateResultFactory;
    protected $_rateMethodFactory;
    private $samedayApiHelper;
    private $storedDataHelper;
    private $cartSession;
    private $serviceRepository;
    private $pickupPointRepository;
    private $scopeConfig;

    public function __construct(
        SamedayApiHelper               $samedayApiHelper,
        StoredDataHelper               $storedDataHelper,
        Session                        $cartSession,
        ScopeConfigInterface           $scopeConfig,
        ErrorFactory                   $rateErrorFactory,
        LoggerInterface                $logger,
        ResultFactory                  $rateResultFactory,
        MethodFactory                  $rateMethodFactory,
        ServiceRepositoryInterface     $serviceRepository,
        PickupPointRepositoryInterface $pickupPointRepository,
        array                          $data = []
    )
    {
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);

        $this->samedayApiHelper = $samedayApiHelper;
        $this->storedDataHelper = $storedDataHelper;
        $this->cartSession = $cartSession;
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->serviceRepository = $serviceRepository;
        $this->pickupPointRepository = $pickupPointRepository;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return bool
     */
    public function isTrackingAvailable(): bool
    {
        return true;
    }

    /**
     * @param string $awbNumber
     *
     */
    public function getTrackingInfo(string $awbNumber)
    {

        $trackingInfo = new TrackingInfo();
        $trackingInfo->setCarrierTitle("Sameday Courier");
        $trackingInfo->setTracking($awbNumber);
        $trackingInfo->setUrl("https://www.sameday.ro/#awb=" . $awbNumber);

        $awbHistory = $this->samedayApiHelper->getAwbHistory($awbNumber);
        if (false === $awbHistory) {
            return $trackingInfo;
        }else{
            $trackingInfo->setTrackSummary($awbHistory);

            return $trackingInfo;
        }

    }

    /**
     * @inheritdoc
     */
    public function checkAvailableShipCountries(DataObject $request)
    {
        $destCountry = strtolower($request->getData('dest_country_id'));
        if (in_array($destCountry, $this->samedayApiHelper::AVAILABLE_SHIP_COUNTRIES, true)) {
            // Ship only to Available Countries.
            return $this;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function isStateProvinceRequired(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isCityRequired(): bool
    {
        return true;
    }

    public function getAllowedMethods(): array
    {
        return [$this->getCarrierCode() => __($this->getConfigData('name'))];
    }

    public function collectRates(RateRequest $request)
    {
        if (!$this->isActive()) {
            return false;
        }

        $result = $this->_rateResultFactory->create();
        $isTesting = (bool)$this->scopeConfig->getValue(StoredDataHelper::SAMEDAYCOURIER_ENV_MODE);

        $hostCountry = $this->samedayApiHelper->getHostCountry();
        $destCountry = strtolower($request->getData('dest_country_id'));
        $destCity = $request->getData('dest_city');
        $eligibleServices = $hostCountry === $destCountry
            ? $this->samedayApiHelper::ELIGIBLE_SAMEDAY_SERVICES
            : $this->samedayApiHelper::ELIGIBLE_SAMEDAY_SERVICES_CROSSBORDER;

        $services = array_filter(
            $this->serviceRepository->getAllActive($isTesting),
            static function (Service $service) use ($eligibleServices) {
                return in_array($service->getCode(), $eligibleServices, true);
            }
        );

        foreach ($services as $service) {
            if ($this->samedayApiHelper->isEligibleToLocker($service->getCode())) {
                $lockerMaxItems = $this->scopeConfig->getValue(StoredDataHelper::SAMEDAYCOURIER_LOCKER_MAX_ITEMS);
                if (null === $lockerMaxItems) {
                    $lockerMaxItems = StoredDataHelper::DEFAULT_VALUE_LOCKER_MAX_ITEMS;
                }

                if (sizeof($request->getAllItems()) > $lockerMaxItems) {
                    continue;
                }
            }

            $method = $this->_rateMethodFactory->create();

            $method->setCarrier($this->getCarrierCode());
            $method->setCarrierTitle($this->getConfigData('title'));
            $method->setMethod($service->getCode());
            $method->setMethodTitle($service->getName());
            $method->setCountryCode($destCountry);
            $method->setDestCity($destCity);
            $method->setShowLockersMap((bool)$this->scopeConfig->getValue('carriers/samedaycourier/show_lockers_map'));
            $method->setApiUsername($this->getConfigData('username'));

            $shippingCost = $service->getPrice();
            if ($service->getIsPriceFree() && $request->getPackageValueWithDiscount() >= $service->getPriceFree()) {
                $shippingCost = 0;
            } elseif ($service->getUseEstimatedCost()) {
                $shippingCostEstimation = $this->shippingEstimateCost($request, $service->getSamedayId());
                $shippingCost = $shippingCostEstimation ? $shippingCostEstimation->getCost() : $service->getPrice();
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

        $objectManager = ObjectManager::getInstance();

        $cart = $this->cartSession;
        $paymentMethodCode = null;
        if (null !== $cart) {
            try {
                $payment = $cart->getQuote()->getPayment();
                $paymentMethodCode = $payment->getMethod();
            } catch (\Exception $exception) {
            }
        }
        $repayment = 0;
        if (null === $paymentMethodCode || in_array($paymentMethodCode, $this->storedDataHelper::COD_OPTIONS, true)) {
            $repayment = $request->getData('package_value_with_discount');
        }

        $region = $objectManager->create(Region::class);
        $regionName = $region->loadByCode(
            $request->getData('dest_region_code'),
            $request->getData('dest_country_id')
        )->getName();

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
                $request->getData('firstname') . ' ' . $request->getData('lastname'),
                $request->getData('telephone'),
                null,
                null,
                $request->getDestPostcode()
            )),
            0,
            $repayment,
            null,
            [],
            $this->storedDataHelper->buildDestCurrency($request->getData('dest_country_id'))
        );

        return $this->samedayApiHelper->doRequest($apiRequest, 'postAwbEstimation', false);
    }
}
