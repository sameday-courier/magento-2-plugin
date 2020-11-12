<?php

namespace SamedayCourier\Shipping\Model\Carrier;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Config;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Psr\Log\LoggerInterface;
use Sameday\Objects\ParcelDimensionsObject;
use Sameday\Objects\PostAwb\Request\AwbRecipientEntityObject;
use Sameday\Objects\Types\AwbPaymentType;
use Sameday\Objects\Types\PackageType;
use Sameday\Requests\SamedayPostAwbEstimationRequest;
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

        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * @inheritdoc
     */
    public function checkAvailableShipCountries(DataObject $request)
    {
        if ($request->getData('dest_country_id') === 'RO') {
            // Ship only to Romania.
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
        $isTesting = (bool) $this->getConfigData('carriers/samedaycourier/testing');

        $services = $this->serviceRepository->getAllActive($isTesting)->getItems();
        foreach ($services as $service) {
            $method = $this->_rateMethodFactory->create();

            $method->setCarrier($this->getCarrierCode());
            $method->setCarrierTitle($this->getConfigData('title'));

            $method->setMethod($service->getName());
            $method->setMethodTitle('*' . $service->getName());

            $shippingCostEstimation = $this->shippingEstimateCost($request, $service->getSamedayId());
            $method
                ->setPrice($shippingCostEstimation->getCost())
                ->setCost($shippingCostEstimation->getCost());

            $result->append($method);
        }

        return $result;
    }

    private function shippingEstimateCost(RateRequest $request, int $serviceId)
    {
        $defaultPickupPoint = $this->pickupPointRepository->getDefaultPickupPoint();
        $packageWeight = $request->getData('package_weight') ?? 1;
        $apiRequest = new SamedayPostAwbEstimationRequest(
            $defaultPickupPoint->getSamedayId(),
            null,
            (new PackageType(PackageType::PARCEL)),
            [(new ParcelDimensionsObject($packageWeight))],
            $serviceId,
            (new AwbPaymentType(AwbPaymentType::CLIENT)),
            (new AwbRecipientEntityObject(
                1, //@todo $request->getData('dest_city'),
                1, //@todo $request->getData('dest_region_code'),
                $request->getData('dest_street'),
                $request->getData('firstname') . ' ' .  $request->getData('lastname'),
                $request->getData('telephone'),
                ''
            )),
            0,
            $request->getData('package_value_with_discount')
        );

        return $this->samedayApiHelper->doRequest($apiRequest, 'postAwbEstimation');
    }
}
