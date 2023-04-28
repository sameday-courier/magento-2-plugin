<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Controller\Adminhtml\Order;

use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Api\OrderAddressRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use RuntimeException;

class ChangeLocker extends Action
{
    private $resultJsonFactory;
    private $json;
    private $formKeyValidator;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    public function __construct(
        Context $context,
        ResourceConnection $resourceConnection,
        ResultFactory $resultFactory,
        Json $json,
        OrderRepositoryInterface $orderRepository,
        Validator $formKeyValidator = null
    ) {
        parent::__construct($context);

        $this->resultJsonFactory = $resultFactory;
        $this->json = $json;
        $this->orderRepository = $orderRepository;
        $this->formKeyValidator = $formKeyValidator ?: ObjectManager::getInstance()->get(Validator::class);
    }

    /**
     * @throws InvalidRequestException
     * @throws Exception
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();

        if (!$params['isAjax'] || !$this->formKeyValidator->validate($this->getRequest())) {
            throw new RuntimeException('Invalid request!');
        }

        if (null === $locker = $params['locker'] ?? null) {
            throw new RuntimeException('No data found');
        }

        if (! isset($locker['name'])) {
            throw new RuntimeException('Invalid Locker data');
        }

        $orderId = (int) $params['order_id'];

        /** @var Order $order */
        $order = $this->orderRepository->get($orderId);

        $order->setSamedaycourierLocker($this->json->serialize($locker));
        $this->orderRepository->save($order);

        return $this->resultJsonFactory->create(ResultFactory::TYPE_JSON)->setData(['success' => true]);
    }
}
