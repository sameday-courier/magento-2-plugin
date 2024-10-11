<?php

namespace SamedayCourier\Shipping\Block\Adminhtml\PickupPoint\Add;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use SamedayCourier\Shipping\Block\Adminhtml\Service\Edit\GenericButton;

class BackButton extends GenericButton implements ButtonProviderInterface
{
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
        return $this->getUrl('*/*/');
    }
}
