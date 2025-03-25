<?php

namespace SamedayCourier\Shipping\Block\Adminhtml\Awb;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use SamedayCourier\Shipping\Api\ServiceRepositoryInterface;
use SamedayCourier\Shipping\Block\Adminhtml\Service\Edit\GenericButton;

class BackButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @var Context $context
     */
    protected $context;

    public function __construct(Context $context, ServiceRepositoryInterface $serviceRepository)
    {
        parent::__construct($context, $serviceRepository);

        $this->context = $context;
    }

    /**
     * @return array
     */
    public function getButtonData(): array
    {
        return [
            'label' => __('Back'),
            'class' => 'back',
            'on_click' => sprintf("location.href = '%s';", $this->getBackUrl()),
        ];
    }

    /**
     * @return string
     */
    public function getBackUrl(): string
    {
        return $this->getUrl('sales/order/view', [
                'order_id' => $this->context->getRequest()->getParam('order_id')
            ]
        );
    }
}

