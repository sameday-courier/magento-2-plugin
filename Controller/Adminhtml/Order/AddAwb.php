<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Controller\Adminhtml\Order as AdminOrder;
use Psr\Log\LoggerInterface;
use Sameday\Objects\ParcelDimensionsObject;
use Sameday\Objects\PostAwb\Request\AwbRecipientEntityObject;
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
     */
    public function execute()
    {
        /** @var \Magento\Sales\Api\Data\OrderInterface $order */
        $order = $this->_initOrder();
        $values = $this->getRequest()->getParams();
        if (!$order) {
            throw new NotAnOrderMatchedException();
        }

        $packageWeight = $values['package_weight'] >= 1 ? $values['package_weight'] : 1;
        $apiRequest = new SamedayPostAwbRequest(
            $values['pickup_point'],
            null,
            (new PackageType(PackageType::PARCEL)),
            [(new ParcelDimensionsObject($packageWeight))],
            $values['service'],
            (new AwbPaymentType(AwbPaymentType::CLIENT)),
            (new AwbRecipientEntityObject(
                1,
                1,
                $order->getBillingAddress()->getStreet()[0],
                $order->getBillingAddress()->getFirstname() . ' ' . $order->getBillingAddress()->getLastname(),
                $order->getBillingAddress()->getTelephone(),
                $order->getCustomerEmail()
            )),
            $values['insured_value'],
            $values['repayment'],
            null,
            null,
            [],
            null,
            $values['client_reference'] ?? null,
            null,
            null,
            $values['observation'],
            $order->getData('samedaycourier_locker')
        );
        /** @var SamedayPostAwbResponse|false $response */
        $response = $this->apiHelper->doRequest($apiRequest, 'postAwb');
        if ($response) {
            if(!empty($response->getParcels()[0])) {
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
            }else{
                $this->manager->addErrorMessage(__("SamedayCourier communication error occured. Please try again later"));
            }
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('sales/order/view', ['order_id' => $values['order_id']]);

        return $resultRedirect;
    }
}
