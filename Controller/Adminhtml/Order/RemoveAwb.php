<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Controller\Adminhtml\Order;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\ObjectManager;
use Sameday\Requests\SamedayDeleteAwbRequest;
use SamedayCourier\Shipping\Api\AwbRepositoryInterface;
use SamedayCourier\Shipping\Api\OrderBulkAwbRepositoryInterface;
use SamedayCourier\Shipping\Helper\ApiHelper;
use SamedayCourier\Shipping\Helper\OrderStatusHelper;

class RemoveAwb extends Action
{
    private $resultJsonFactory;
    private $manager;
    private $awbRepository;
    private $orderBulkAwbRepository;
    private $apiHelper;
    private $formKeyValidator;
    private $orderStatusHelper;

    public function __construct(
        Context $context,
        ResultFactory $resultFactory,
        ManagerInterface $manager,
        AwbRepositoryInterface $awbRepository,
        OrderBulkAwbRepositoryInterface $orderBulkAwbRepository,
        ApiHelper $apiHelper,
        OrderStatusHelper $orderStatusHelper,
        ?Validator $formKeyValidator = null
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultFactory;
        $this->manager = $manager;
        $this->awbRepository = $awbRepository;
        $this->orderBulkAwbRepository = $orderBulkAwbRepository;
        $this->apiHelper = $apiHelper;
        $this->orderStatusHelper = $orderStatusHelper;
        $this->formKeyValidator = $formKeyValidator ?: ObjectManager::getInstance()->get(Validator::class);
    }

    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create(ResultFactory::TYPE_JSON);
        $params = $this->getRequest()->getParams();
        if (!$this->getRequest()->isPost() || empty($params['isAjax']) || !$this->formKeyValidator->validate($this->getRequest())) {
            throw new InvalidRequestException(new NotFoundException());
        }

        $awb = $this->awbRepository->getById((int) $params['awb_id']);
        $orderId = (int) $awb->getOrderId();
        $apiRequest = new SamedayDeleteAwbRequest($params['sameday_awb_number']);
        $response = $this->apiHelper->doRequest($apiRequest, 'deleteAwb');
        if ($response) {
            $this->orderStatusHelper->revertStatusFromAwb($awb);
            $this->awbRepository->deleteById($params['awb_id']);
            $this->orderBulkAwbRepository->deleteByOrderId($orderId);
            $this->manager->addSuccessMessage("Awb removed successfully!");

            return $resultJson->setData(array_merge(
                [
                    'success' => true,
                    'order_id' => $orderId,
                    'feedback' => '—',
                ],
                $this->orderStatusHelper->getOrderStatusPayload($orderId)
            ));
        }

        return $resultJson->setData([
            'success' => false,
            'order_id' => $orderId,
        ]);
    }
}
