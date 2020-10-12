<?php

namespace SamedayCourier\Shipping\Plugin\Block\Adminhtml\Order;

use Magento\Sales\Block\Adminhtml\Order\View\Info;
use SamedayCourier\Shipping\Block\Adminhtml\Order\SamedayModal;

class View
{
    private const SHIPPING_METHOD_PREFIX = 'samedaycourier';

    /**
     * @param Info $subject
     * @param $result
     *
     * @return string
     */
    public function afterToHtml(
        Info $subject,
        $result
    ) {
        if($this->isEligibleForAwbGeneration($subject) && $subject->getNameInLayout() == 'order_info'){
            return $this->createAwbModalHtml($subject, $result);
        }

        return $result;
    }

    /**
     * Check if shipping method is sameday courier and
     * there is no awb generated
     *
     * @param Info $subject
     *
     * @return bool
     */
    private function isEligibleForAwbGeneration(Info $subject)
    {
        $shippingMethod = $subject->getOrder()->getShippingMethod();

        /** @TODO: check if any awb is generated for this order */
        return strpos($shippingMethod, self::SHIPPING_METHOD_PREFIX) !== false;
    }

    /**
     * Create the awb modal
     *
     * @param Info $subject
     * @param $result
     *
     * @return string
     */
    private function createAwbModalHtml(Info $subject, $result)
    {
        $block = $subject
            ->getLayout()
            ->createBlock(
                SamedayModal::class,
                $subject->getNameInLayout().'_modal_box'
            );

        $awbModal = $block
            ->setOrder($subject->getOrder())
            ->setTemplate('SamedayCourier_Shipping::order/samedaymodal.phtml')
            ->toHtml();

        return $result . $awbModal;
    }
}
