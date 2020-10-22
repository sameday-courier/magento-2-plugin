<?php

namespace SamedayCourier\Shipping\Plugin\Block\Adminhtml\Order;

use Magento\Sales\Block\Adminhtml\Order\View\Info;
use SamedayCourier\Shipping\Api\AwbRepositoryInterface;
use SamedayCourier\Shipping\Api\Data\AwbInterface;
use SamedayCourier\Shipping\Block\Adminhtml\Order\SamedayModal;

class View
{
    private $awbRepository;

    public function __construct(AwbRepositoryInterface $awbRepository)
    {
        $this->awbRepository = $awbRepository;
    }

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
        if ($subject->getNameInLayout() == 'order_info')
        {
            $orderHasAwb = $this->orderHasAwb($subject);
            if(!$orderHasAwb){
                return $this->createAwbModalHtml($subject, $result);
            }

            return $this->createEditAwbModalHtml($subject, $result, $orderHasAwb);
        }


        return $result;
    }

    /**
     * Check if there is no awb generated
     *
     * @param Info $subject
     *
     * @return false|AwbInterface
     */
    private function orderHasAwb(Info $subject)
    {
        $awb = $this->awbRepository->getByOrderId($subject->getOrder()->getId());
        return isset($awb) ? $awb : false;
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
        $block = $this->createSamedayModalBlock($subject);

        $awbModal = $block
            ->setOrder($subject->getOrder())
            ->setTemplate('SamedayCourier_Shipping::order/samedaymodal.phtml')
            ->toHtml();

        return $result . $awbModal;
    }

    /**
     * @param Info $subject
     * @param $result
     * @param AwbInterface $awb
     *
     * @return string
     */
    private function createEditAwbModalHtml(Info $subject, $result, AwbInterface $awb)
    {
        $block = $this->createSamedayModalBlock($subject, ['awb' => $awb]);

        $awbModal = $block
            ->setOrder($subject->getOrder())
            ->setTemplate('SamedayCourier_Shipping::order/samedaypostawbmodal.phtml')
            ->toHtml();

        return $result . $awbModal;
    }

    /**
     * @param Info $subject
     * @param array $arguments
     *
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function createSamedayModalBlock(Info $subject, array $arguments = [])
    {
        return $subject
            ->getLayout()
            ->createBlock(
                SamedayModal::class,
                $subject->getNameInLayout().'_modal_box',
                [
                    'data' => $arguments
                ]
            );
    }
}
