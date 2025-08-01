<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Controller\Adminhtml\Order as AdminOrder;
use Psr\Log\LoggerInterface;
use Sameday\Objects\Types\AwbPdfType;
use Sameday\Requests\SamedayGetAwbPdfRequest;
use Sameday\Responses\SamedayGetAwbPdfResponse;
use SamedayCourier\Shipping\Api\AwbRepositoryInterface;
use SamedayCourier\Shipping\Helper\ApiHelper;
use SamedayCourier\Shipping\Helper\GeneralHelper;

class ShowPdf extends AdminOrder
{
    /**
     * @var ApiHelper $apiHelper
     */
    private $apiHelper;

    /**
     * @var AwbRepositoryInterface $awbRepository
     */
    private $awbRepository;

    /**
     * @var GeneralHelper $generalHelper
     */
    private $generalHelper;

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
        ApiHelper $apiHelper,
        GeneralHelper $generalHelper,
        AwbRepositoryInterface $awbRepository
    )
    {
        parent::__construct($context, $coreRegistry, $fileFactory, $translateInline, $resultPageFactory, $resultJsonFactory, $resultLayoutFactory, $resultRawFactory, $orderManagement, $orderRepository, $logger);

        $this->apiHelper = $apiHelper;
        $this->awbRepository = $awbRepository;
        $this->generalHelper = $generalHelper;
    }

    public function execute()
    {
        /** @var OrderInterface $order */
        $order = $this->_initOrder();

        $awb = $this->awbRepository->getByOrderId($order->getEntityId());

        $apiRequest = new SamedayGetAwbPdfRequest(
            $awb->getAwbNumber(),
            (new AwbPdfType($this->generalHelper->getAwbLabelFormat()))
        );

        /** @var SamedayGetAwbPdfResponse $response */
        $response = $this->apiHelper->doRequest($apiRequest, 'getAwbPdf');
        if (!$response) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('sales/order/view', ['order_id' => $order->getEntityId()]);

            return $resultRedirect;
        }

        $this->getResponse()->setHeader('Content-type', 'application/pdf; charset=UTF-8');
        $this->getResponse()->setBody($response->getPdf());

        return null;
    }
}
