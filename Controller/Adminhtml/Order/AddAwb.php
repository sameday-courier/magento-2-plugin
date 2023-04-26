<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\Translate\InlineInterface;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Controller\Adminhtml\Order as AdminOrder;
use Psr\Log\LoggerInterface;
use Sameday\Objects\ParcelDimensionsObject;
use Sameday\Objects\PostAwb\Request\AwbRecipientEntityObject;
use Sameday\Objects\PostAwb\Request\CompanyEntityObject;
use Sameday\Objects\Types\AwbPaymentType;
use Sameday\Objects\Types\PackageType;
use Sameday\Requests\SamedayPostAwbRequest;
use SamedayCourier\Shipping\Api\AwbRepositoryInterface;
use SamedayCourier\Shipping\Api\Data\AwbInterfaceFactory;
use SamedayCourier\Shipping\Api\ServiceRepositoryInterface;
use SamedayCourier\Shipping\Exception\NotAnOrderMatchedException;
use SamedayCourier\Shipping\Helper\ApiHelper;
use Sameday\Responses\SamedayPostAwbResponse;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use SamedayCourier\Shipping\Helper\StoredDataHelper;

class AddAwb extends AdminOrder implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Magento_Sales::hold';

    private $awbRepository;
    private $awbFactory;
    private $apiHelper;
    private $manager;
    private $serializer;
    private $serviceRepository;
    private $storedDataHelper;

    public function __construct(
        Action\Context $context,
        Registry $coreRegistry,
        FileFactory $fileFactory,
        InlineInterface $translateInline,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        LayoutFactory $resultLayoutFactory,
        RawFactory $resultRawFactory,
        OrderManagementInterface $orderManagement,
        OrderRepositoryInterface $orderRepository,
        LoggerInterface $logger,
        AwbRepositoryInterface $awbRepository,
        AwbInterfaceFactory $awbFactory,
        ApiHelper $apiHelper,
        ManagerInterface $manager,
        SerializerInterface $serializer,
        ServiceRepositoryInterface $serviceRepository,
        StoredDataHelper $storedDataHelper
    )
    {
        parent::__construct(
            $context,
            $coreRegistry,
            $fileFactory,
            $translateInline,
            $resultPageFactory,
            $resultJsonFactory,
            $resultLayoutFactory,
            $resultRawFactory,
            $orderManagement,
            $orderRepository,
            $logger
        );

        $this->awbRepository = $awbRepository;
        $this->awbFactory = $awbFactory;
        $this->apiHelper = $apiHelper;
        $this->manager = $manager;
        $this->serializer = $serializer;
        $this->serviceRepository = $serviceRepository;
    }

    /**
     * @inheritDoc
     * @throws NotAnOrderMatchedException
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        /** @var OrderInterface $order */
        $order = $this->_initOrder();
        if (!$order) {
            throw new NotAnOrderMatchedException();
        }

        if (null === $this->getRequest()) {
            throw new NotAnOrderMatchedException();
        }

        $values = $this->getRequest()->getParams();

        $packageWeight = max($values['package_weight'], 1);

        $lockerLastMile = $values['locker_last_mile'] ?? null;

        /** @var OrderAddressInterface $shippingAddress */
        $shippingAddress = $order->getShippingAddress();

        $regionName = $shippingAddress->getRegion();
        $city = $shippingAddress->getCity();
        $address = implode(' ', $shippingAddress->getStreet());
        $phone = $shippingAddress->getTelephone();

        $serviceCode = $this->serviceRepository->getBySamedayId(
            $values['service'],
            $this->apiHelper->getEnvMode()
        )->getCode();
        if (($serviceCode !== ApiHelper::LOCKER_NEXT_DAY_SERVICE)
            && null !== $order->getSamedaycourierDestinationAddressHd()
        ) {
            $hdAddress = $this->serializer->unserialize($order->getSamedaycourierDestinationAddressHd());
            $regionName = $hdAddress['region'] ?? null;
            $city = $hdAddress['city'] ?? null;
            if (isset($hdAddress['street'])) {
                $address = implode(' ', $hdAddress['street']);
            }
        }

        $contactPerson = sprintf('%s %s',
            $shippingAddress->getFirstname(),
            $shippingAddress->getLastname()
        );
        $postalCode = $shippingAddress->getPostcode();
        if ((null !== $company = $shippingAddress->getCompany()) && ('' !== trim($shippingAddress->getCompany()))) {
            $company = new CompanyEntityObject(
                $shippingAddress->getCompany()
            );
        }

        $serviceTaxIds = [];
        if (isset($values['locker_first_mile'])) {
            $serviceTaxIds[] = $values['locker_first_mile'];
        }

        $apiRequest = new SamedayPostAwbRequest(
            $values['pickup_point'],
            null,
            (new PackageType(PackageType::PARCEL)),
            [(new ParcelDimensionsObject($packageWeight))],
            $values['service'],
            (new AwbPaymentType(AwbPaymentType::CLIENT)),
            (new AwbRecipientEntityObject(
                $city,
                $regionName,
                $address,
                $contactPerson,
                $phone,
                $order->getCustomerEmail(),
                $company,
                $postalCode
            )),
            $values['insured_value'],
            $values['repayment'],
            null,
            null,
            $serviceTaxIds,
            null,
            null,
            null,
            null,
            $values['observation'],
            null,
            (int) $lockerLastMile
        );

        /** @var SamedayPostAwbResponse|false $response */
        $response = $this->apiHelper->doRequest($apiRequest, 'postAwb');
        if ($response && !empty($response->getParcels()[0])) {
            $parcelsResponse = $response->getParcels();
            $parcelsArr = [];
            foreach($parcelsResponse as $index => $parcelResponse){
                $parcelsArr[$index]['position'] = $parcelResponse->getPosition();
                $parcelsArr[$index]['awbNumber'] = $parcelResponse->getAwbNumber();
            }

            $parcels = $this->serializer->serialize($parcelsArr);
            $awb = $this->awbFactory->create()
                ->setOrderId($values['order_id'])
                ->setAwbNumber($response->getAwbNumber())
                ->setAwbCost($values['repayment'])
                ->setParcels($parcels);

            $this->awbRepository->save($awb);
            $this->manager->addSuccessMessage("Sameday awb successfully created!");
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('sales/order/view', ['order_id' => $values['order_id']]);

        return $resultRedirect;
    }
}
