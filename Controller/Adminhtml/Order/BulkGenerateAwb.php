<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use SamedayCourier\Shipping\Helper\BulkAwbService;

class BulkGenerateAwb extends Action
{
    public const ADMIN_RESOURCE = 'Magento_Sales::sales_order';

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var Validator
     */
    private $formKeyValidator;

    /**
     * @var BulkAwbService
     */
    private $bulkAwbService;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Validator $formKeyValidator,
        BulkAwbService $bulkAwbService
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->formKeyValidator = $formKeyValidator;
        $this->bulkAwbService = $bulkAwbService;
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        if (!$this->getRequest()->isPost() || !$this->formKeyValidator->validate($this->getRequest())) {
            return $result->setData([
                'success' => false,
                'error' => (string) __('Invalid request.'),
            ]);
        }

        $orderId = (int) $this->getRequest()->getParam('order_id');
        if ($orderId <= 0) {
            return $result->setData([
                'success' => false,
                'error' => (string) __('Order ID is required.'),
            ]);
        }

        try {
            return $result->setData($this->bulkAwbService->generateForOrder($orderId));
        } catch (\Exception $e) {
            return $result->setData([
                'success' => false,
                'order_id' => $orderId,
                'error' => $e->getMessage() ?: (string) __('AWB generation failed.'),
            ]);
        }
    }
}
