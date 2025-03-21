<?php

namespace SamedayCourier\Shipping\Block\Adminhtml\Order;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class SamedaySection extends Template
{
    /**
     * @var Context $context
     */
    protected $context;

    /**
     * @var OrderRepositoryInterface $orderRepository
     */
    protected $orderRepository;

    /**
     * @param Context $context
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepository
    ) {
        parent::__construct($context);

        $this->context = $context;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @return string
     */
    public function goToAwbFormUrl(): string
    {

        return $this->getUrl('samedaycourier_shipping/awb/form',
            [
                'order_id' => $this->getOrder()->getId(),
            ]
        );
    }

    /**
     * @return OrderInterface
     */
    private function getOrder(): OrderInterface
    {
        return $this->orderRepository->get($this->context->getRequest()->getParam('order_id'));
    }
}
