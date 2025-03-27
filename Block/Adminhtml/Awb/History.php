<?php

namespace SamedayCourier\Shipping\Block\Adminhtml\Awb;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Sameday\Requests\SamedayGetParcelStatusHistoryRequest;
use SamedayCourier\Shipping\Api\AwbRepositoryInterface;
use SamedayCourier\Shipping\Api\Data\AwbInterface;
use SamedayCourier\Shipping\Helper\ApiHelper;

class History extends Template
{
    /**
     * @var Context $context
     */
    protected $context;

    /**
     * @var AwbRepositoryInterface $awbRepository
     */
    protected $awbRepository;

    /**
     * @var OrderRepositoryInterface $orderRepository
     */
    protected $orderRepository;

    /**
     * @var ApiHelper $apiHelper
     */
    protected $apiHelper;

    public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepository,
        AwbRepositoryInterface $awbRepository,
        ApiHelper $apiHelper
    )
    {
        parent::__construct($context);

        $this->context = $context;
        $this->orderRepository = $orderRepository;
        $this->awbRepository = $awbRepository;
        $this->apiHelper = $apiHelper;
    }

    /**
     * @return OrderInterface
     */
    public function getOrder(): OrderInterface
    {
        return $this->orderRepository->get($this->context->getRequest()->getParam('order_id'));
    }

    /**
     * @return AwbInterface|null
     */
    public function getAwb(): ?AwbInterface
    {
        try {
            return $this->awbRepository->getByOrderId($this->getOrder()->getId());
        } catch (NoSuchEntityException $exception) {
            return null;
        }
    }

    /**
     * @return array
     */
    public function getParcelsHistory(): array
    {
        $parcelsHistory = [];
        if (null === $awb = $this->getAwb()) {
            return $parcelsHistory;
        }

        $objectManager = ObjectManager::getInstance();
        $serializer = $objectManager->create(SerializerInterface::class);
        $parcels = $serializer->unserialize($awb->getParcels());

        foreach ($parcels as $parcel) {
            $apiRequest = new SamedayGetParcelStatusHistoryRequest($parcel['awbNumber']);
            $parcelsHistory[] = $this->apiHelper->doRequest($apiRequest, 'getParcelStatusHistory');
        }

        return $parcelsHistory;
    }
}
