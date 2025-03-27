<?php

namespace SamedayCourier\Shipping\Block\Adminhtml\Order;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Block\Adminhtml\Order\View\Info;
use SamedayCourier\Shipping\Api\Data\AwbInterface;
use SamedayCourier\Shipping\Api\AwbRepositoryInterface;

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
     * @var
     */
    protected $awbRepository;

    /**
     * @param Context $context
     * @param OrderRepositoryInterface $orderRepository
     * @param AwbRepositoryInterface $awbRepository
     */
    public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepository,
        AwbRepositoryInterface $awbRepository
    ) {
        parent::__construct($context);

        $this->context = $context;
        $this->orderRepository = $orderRepository;
        $this->awbRepository = $awbRepository;
    }

    /**
     * @return string
     */
    public function goToAwbFormPage(): string
    {
        return $this->getUrl('samedaycourier_shipping/awb/form',
            [
                'order_id' => $this->getOrder()->getId(),
            ]
        );
    }

    /**
     * @return string
     */
    public function goToAwbHistoryPage(): string
    {
        return $this->getUrl('samedaycourier_shipping/awb/history',
            [
                'order_id' => $this->getOrder()->getId(),
            ]
        );
    }

    public function goToAddNewParcelFormPage(): string
    {
        return $this->getUrl('samedaycourier_shipping/awb_parcel/form',
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

    /**
     * @return string
     */
    public function getRouteAwbAsPdf(): string
    {
        return $this->getUrl('samedaycourier_shipping/order/showpdf', [
            'order_id' => $this->getOrder()->getId()
        ]);
    }

    /**
     * @return string
     */
    public function getRouteAddAwb(): string
    {
        return $this->getUrl('samedaycourier_shipping/order/addawb', [
            'order_id' => $this->getOrder()->getId()
        ]);
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

    /**
     * @return string
     */
    public function getRouteRemoveAwb(): string
    {
        return $this->getUrl('samedaycourier_shipping/order/removeawb');
    }

    /**
     * @return AwbInterface|null
     */
    public function getAwb(): ?AwbInterface
    {
        try {
            return $this->awbRepository->getByOrderId($this->getOrder()->getId());
        } catch (\Exception $exception) {
            return null;
        }
    }

    /**
     * @return bool
     */
    public function orderHasAwb(): bool
    {
        return $this->getAwb() !== null;
    }
}
