<?php

namespace SamedayCourier\Shipping\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\NoSuchEntityException;
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

class AddAwb extends AdminOrder implements HttpPostActionInterface
{
    /**
     * Changes ACL Resource Id
     */
    const ADMIN_RESOURCE = 'Magento_Sales::hold';

    private $awbRepository;
    private $awbFactory;
    private $apiHelper;

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
        ApiHelper $apiHelper
    )
    {
        parent::__construct($context, $coreRegistry, $fileFactory, $translateInline, $resultPageFactory, $resultJsonFactory, $resultLayoutFactory, $resultRawFactory, $orderManagement, $orderRepository, $logger);

        $this->awbRepository = $awbRepository;
        $this->awbFactory = $awbFactory;
        $this->apiHelper = $apiHelper;
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
        
        $apiRequest = new SamedayPostAwbRequest(
            $values['pickup_point'],
            null,
            (new PackageType(PackageType::PARCEL)),
            [(new ParcelDimensionsObject($values['package_weight']))], // @todo min package weight = 1
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
            $values['awb_payment']
        );

        $response = $this->apiHelper->doRequest($apiRequest, 'postAwb');
        var_dump($response);
        exit;
        try {
            $awb = $this->awbRepository->getByOrderId((int) $values['order_id']);
        } catch (NoSuchEntityException $e) {
            $awb = $this->awbFactory->create();
        }

        $awb
            ->setOrderId($values['order_id'])
            ->setAwbNumber("testawbNumber")
            ->setAwbCost($values['repayment'])
            ->setParcels("nothinghere");

        $this->awbRepository->save($awb);

        echo "<pre>";
        print_r($values);
        echo "</pre>";


        echo "<pre>";
            print_r($awb->getAwbNumber());
        echo "</pre>";
         exit;
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('sales/order/view', ['order_id' => $order->getId()]);

        return $resultRedirect;
    }
}
