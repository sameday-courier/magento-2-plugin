<?php

namespace SamedayCourier\Shipping\Block\Adminhtml\Order;

use Magento\Backend\Block\Template;

class BulkAwbToolbar extends Template
{
    public function getGenerateUrl(): string
    {
        return $this->getUrl('samedaycourier_shipping/order/bulkGenerateAwb');
    }

    public function getGeneratePreviewUrl(): string
    {
        return $this->getUrl('samedaycourier_shipping/order/bulkGeneratePreview');
    }

    public function getRemoveUrl(): string
    {
        return $this->getUrl('samedaycourier_shipping/order/bulkRemoveAwb');
    }

    public function getClearErrorsUrl(): string
    {
        return $this->getUrl('samedaycourier_shipping/order/bulkClearErrors');
    }

    public function getConfigJson(): string
    {
        return json_encode([
            'generateUrl' => $this->getGenerateUrl(),
            'generatePreviewUrl' => $this->getGeneratePreviewUrl(),
            'removeUrl' => $this->getRemoveUrl(),
            'clearErrorsUrl' => $this->getClearErrorsUrl(),
            'formKey' => $this->getFormKey(),
            'labels' => [
                'csvOrderId' => (string) __('Order ID'),
                'csvStatus' => (string) __('Status'),
                'csvMessage' => (string) __('Message'),
                'csvAwb' => (string) __('AWB Number'),
                'statusSuccess' => (string) __('Success'),
                'statusFailed' => (string) __('Failed'),
                'statusSkipped' => (string) __('Skipped'),
                'clearConfirm' => (string) __('Clear Sameday feedback errors for orders without a generated AWB?'),
                'noSelection' => (string) __('Please select at least one order.'),
                'previewFailed' => (string) __('Could not prepare bulk AWB preview.'),
                'crossborderConfirmRequired' => (string) __('Please confirm the cross-border currency disclaimer.'),
            ],
        ]);
    }
}
