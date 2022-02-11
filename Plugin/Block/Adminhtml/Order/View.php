<?php

namespace SamedayCourier\Shipping\Plugin\Block\Adminhtml\Order;

use Magento\Sales\Block\Adminhtml\Order\View\Info;
use Sameday\Requests\SamedayGetParcelStatusHistoryRequest;
use SamedayCourier\Shipping\Api\AwbRepositoryInterface;
use SamedayCourier\Shipping\Api\Data\AwbInterface;
use SamedayCourier\Shipping\Block\Adminhtml\Order\SamedayModal;
use SamedayCourier\Shipping\Helper\ApiHelper;


class View
{
    private $awbRepository;
    private $apiHelper;

    public function __construct(AwbRepositoryInterface $awbRepository, ApiHelper $apiHelper)
    {
        $this->awbRepository = $awbRepository;
        $this->apiHelper = $apiHelper;
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
        try {
            return $this->awbRepository->getByOrderId($subject->getOrder()->getId());
        } catch (\Exception $exception)
        {
            return false;
        }
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
            ->setTemplate('SamedayCourier_Shipping::order/sameday_create_awb_modal.phtml')
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
        $parcelsHistory = [];

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $serializer = $objectManager->create(\Magento\Framework\Serialize\SerializerInterface::class);
        $parcels = $serializer->unserialize($awb->getParcels());

        foreach ($parcels as $parcel) {
            $apiRequest = new SamedayGetParcelStatusHistoryRequest($parcel['awbNumber']);
            $parcelsHistory[] = $this->apiHelper->doRequest($apiRequest, 'getParcelStatusHistory');
        }

        $block = $this->createSamedayModalBlock($subject, ['awb' => $awb, 'parcelsHistory' => $parcelsHistory]);

        $awbModal = $block
            ->setOrder($subject->getOrder())
            ->setTemplate('SamedayCourier_Shipping::order/sameday_post_awb_handling_modal.phtml')
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
