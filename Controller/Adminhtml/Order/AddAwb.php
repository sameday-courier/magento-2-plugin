<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Directory\Model\Region;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ObjectManager;
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
use SamedayCourier\Shipping\Exception\NotAnOrderMatchedException;
use SamedayCourier\Shipping\Helper\ApiHelper;
use Sameday\Responses\SamedayPostAwbResponse;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;

class AddAwb extends AdminOrder implements HttpPostActionInterface
{
    /**
     * Changes ACL Resource Id
     */
    const ADMIN_RESOURCE = 'Magento_Sales::hold';

    private $awbRepository;
    private $awbFactory;
    private $apiHelper;
    private $manager;
    private $serializer;

    public function __construct(
        Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Translate\InlineInterface $translateInline,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        OrderManagementInterface $orderManagement,
        OrderRepositoryInterface $orderRepository,
        LoggerInterface $logger,
        AwbRepositoryInterface $awbRepository,
        AwbInterfaceFactory $awbFactory,
        ApiHelper $apiHelper,
        ManagerInterface $manager,
        SerializerInterface $serializer
    )
    {
        parent::__construct($context, $coreRegistry, $fileFactory, $translateInline, $resultPageFactory, $resultJsonFactory, $resultLayoutFactory, $resultRawFactory, $orderManagement, $orderRepository, $logger);

        $this->awbRepository = $awbRepository;
        $this->awbFactory = $awbFactory;
        $this->apiHelper = $apiHelper;
        $this->manager = $manager;
        $this->serializer = $serializer;
    }

    /**
     * @inheritDoc
     * @throws NotAnOrderMatchedException
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

        $lockerId = $values['lockerId'] ?? null;

        $objectManager = ObjectManager::getInstance();
        $region = $objectManager->create(Region::class);
        $billingAddress = $order->getBillingAddress();
        $regionName = null;
        $city = null;
        $address = null;
        $contactPerson = null;
        $phone = null;
        $postalCode = null;
        $company = null;
        if (null !== $billingAddress) {
            $regionName = $region->loadByCode($billingAddress->getRegionCode(), $billingAddress->getCountryId())->getName();
            $city = $billingAddress->getCity();
            $address = $billingAddress->getStreet()[0];
            $contactPerson = sprintf('%s %s', $billingAddress->getFirstname(), $billingAddress->getLastname());
            $phone = $billingAddress->getTelephone();
            $postalCode = $billingAddress->getPostcode();
            if ((null !== $billingAddress->getCompany()) || ('' !== trim($billingAddress->getCompany()))) {
                $company = new CompanyEntityObject(
                    $billingAddress->getCompany(),
                );
            }
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
            [],
            null,
            null,
            null,
            null,
            $values['observation'],
            $lockerId
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
