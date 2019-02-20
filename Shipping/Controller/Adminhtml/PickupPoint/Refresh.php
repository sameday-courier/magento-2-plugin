<?php

namespace SamedayCourier\Shipping\Controller\Adminhtml\Pickuppoint;

use Magento\Backend\App\Action;
use Sameday\Requests\SamedayGetPickupPointsRequest;
use Sameday\Sameday;
use SamedayCourier\Shipping\Helper\ApiHelper;

class Refresh extends \Magento\Backend\App\Action
{
    /**
     * @var ApiHelper
     */
    private $apiHelper;

    /**
     * Refresh constructor.
     *
     * @param ApiHelper $apiHelper
     * @param Action\Context $context
     */
    public function __construct(ApiHelper $apiHelper, Action\Context $context)
    {
        parent::__construct($context);

        $this->apiHelper = $apiHelper;
    }

    public function execute()
    {
        $sameday = new Sameday($this->apiHelper->initClient());

        $remotePickupPoints = [];
        $page = 1;
        do {
            $request = new SamedayGetPickupPointsRequest();
            $request->setPage($page++);
            try {
                $pickUpPoints = $sameday->getPickupPoints($request);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(sprintf(__('Communication error: %s'), 'AAA'));

                return $this->_redirect('samedaycourier_shipping/pickuppoint/index');
            }

//            foreach ($pickUpPoints->getPickupPoints() as $pickupPointObject) {
//                $pickupPoint = $this->model_extension_shipping_sameday->getPickupPointSameday($pickupPointObject->getId(), $testing);
//                if (!$pickupPoint) {
//                    // Pickup point not found, add it.
//                    $this->model_extension_shipping_sameday->addPickupPoint($pickupPointObject, $testing);
//                } else {
//                    $this->model_extension_shipping_sameday->updatePickupPoint($pickupPointObject, $testing);
//                }
//
//                // Save as current pickup points.
//                $remotePickupPoints[] = $pickupPointObject->getId();
//            }
        } while ($page <= $pickUpPoints->getPages());


        return $this->_redirect('samedaycourier_shipping/pickuppoint/index');
    }
}
