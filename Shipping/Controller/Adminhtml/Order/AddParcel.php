<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Controller\Adminhtml\Order as AdminOrder;
use Psr\Log\LoggerInterface;
use Sameday\Objects\ParcelDimensionsObject;
use Sameday\Objects\PostAwb\ParcelObject;
use Sameday\Requests\SamedayPostParcelRequest;
use Sameday\Responses\SamedayPostParcelResponse;
use SamedayCourier\Shipping\Api\Data\AwbInterface;
use SamedayCourier\Shipping\Exception\NotAnOrderMatchedException;
use SamedayCourier\Shipping\Api\AwbRepositoryInterface;
use SamedayCourier\Shipping\Helper\ApiHelper;
use Magento\Framework\Message\ManagerInterface;

class AddParcel extends AdminOrder implements HttpPostActionInterface
{
    private $awbRepository;
    private $apiHelper;
    private $manager;

    public function __construct(Action\Context $context, \Magento\Framework\Registry $coreRegistry, \Magento\Framework\App\Response\Http\FileFactory $fileFactory, \Magento\Framework\Translate\InlineInterface $translateInline, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory, \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory, \Magento\Framework\Controller\Result\RawFactory $resultRawFactory, OrderManagementInterface $orderManagement, OrderRepositoryInterface $orderRepository, LoggerInterface $logger, AwbRepositoryInterface $awbRepository, ApiHelper $apiHelper, ManagerInterface $manager)
    {
        parent::__construct($context, $coreRegistry, $fileFactory, $translateInline, $resultPageFactory, $resultJsonFactory, $resultLayoutFactory, $resultRawFactory, $orderManagement, $orderRepository, $logger);
        $this->awbRepository = $awbRepository;
        $this->apiHelper = $apiHelper;
        $this->manager = $manager;
    }

    public function execute()
    {
        /** @var \Magento\Sales\Api\Data\OrderInterface $order */
        $order = $this->_initOrder();
        if (!$order) throw new NotAnOrderMatchedException();

        $values = $this->getRequest()->getParams();

        if (!$this->getRequest()->isPost()) {
            throw new InvalidRequestException(new NotFoundException());
        }

        /** @var AwbInterface $awb */
        $awb = $this->awbRepository->getByOrderId($order->getEntityId());
        $parcels = unserialize($awb->getParcels());

        $apiRequest = new SamedayPostParcelRequest(
            $awb->getAwbNumber(),
            new ParcelDimensionsObject(
                max(1, $values['weight']),
                $values['package_width'] ?: null,
                $values['package_length'] ?: null,
                $values['parcel_height'] ?: null
            ),
            count($parcels) + 1,
            null,
            null,
            true
        );

        /** @var SamedayPostParcelResponse $response */
        $response = $this->apiHelper->doRequest($apiRequest, 'postParcel');
        if ($response) {
            $parcel = new ParcelObject(count($parcels) + 1, $response->getParcelAwbNumber());
            $parcels[] = $parcel;

            $awb->setParcels(serialize($parcels));
            $this->awbRepository->save($awb);
            $this->manager->addSuccessMessage("Awb updated successfully! Added a new parcel!");
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('sales/order/view', ['order_id' => $order->getEntityId()]);
        return $resultRedirect;
    }
}
