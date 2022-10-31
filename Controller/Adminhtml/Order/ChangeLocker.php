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
use RuntimeException;

class ChangeLocker extends Action
{
    private const SAMEDAY_COURIER_LOCKER = 'samedaycourier_locker';
    private const ORDERS_TABLE_NAME = 'sales_order';

    private $resourceConnection;
    private $resultJsonFactory;
    private $json;
    private $formKeyValidator;

    public function __construct(
        Context $context,
        ResourceConnection $resourceConnection,
        ResultFactory $resultFactory,
        Json $json,
        Validator $formKeyValidator = null
    ) {
        parent::__construct($context);

        $this->resourceConnection = $resourceConnection;
        $this->resultJsonFactory = $resultFactory;
        $this->json = $json;
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

        $locker = $this->json->serialize($locker);

        $connection = $this->resourceConnection->getConnection();
        $ordersTableName = $connection->getTableName(self::ORDERS_TABLE_NAME);
        $samedayCourierColumnName = self::SAMEDAY_COURIER_LOCKER;
        $orderId = (int) $params['order_id'];

        // Store new locker in DB:
        $queryString = "UPDATE $ordersTableName SET `$samedayCourierColumnName` = '$locker' WHERE `entity_id` = '$orderId'";
        $connection->query($queryString);

        return $this->resultJsonFactory->create(ResultFactory::TYPE_JSON)->setData(['success' => true]);
    }
}
