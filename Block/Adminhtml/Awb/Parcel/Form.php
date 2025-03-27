<?php

namespace SamedayCourier\Shipping\Block\Adminhtml\Awb\Parcel;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class Form extends Template
{
    /**
     * @var Context $context
     */
    protected $context;

    /**
     * @var OrderRepositoryInterface $orderRepository
     */
    protected $orderRepository;

    public function __construct(Context $context, OrderRepositoryInterface $orderRepository)
    {
        parent::__construct($context);

        $this->context = $context;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @return OrderInterface
     */
    public function getOrder(): OrderInterface
    {
        return $this->orderRepository->get($this->context->getRequest()->getParam('order_id'));
    }

    /**
     * @return string
     */
    public function getRouteAddParcel(): string
    {
        return $this->getUrl('samedaycourier_shipping/order/addparcel', [
            'order_id' => $this->getOrder()->getId()
        ]);
    }
}

