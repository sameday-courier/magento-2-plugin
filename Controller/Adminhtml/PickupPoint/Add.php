<?php

namespace SamedayCourier\Shipping\Controller\Adminhtml\PickupPoint;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;
use Sameday\Objects\CountryObject;
use Sameday\Objects\CountyObject;
use Sameday\Objects\PickupPoint\CityObject;
use Sameday\Objects\PickupPoint\ContactPersonObject;
use Sameday\Objects\PickupPoint\PickupPointContactPersonObject;
use Sameday\Objects\PickupPoint\PickupPointObject;
use Sameday\Requests\SamedayPostPickupPointRequest;
use SamedayCourier\Shipping\Helper\ApiHelper;

class Add extends Action
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var ApiHelper $apiHelper
     */
    private $apiHelper;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ApiHelper $apiHelper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ApiHelper $apiHelper
    )
    {
        parent::__construct($context);

        $this->resultPageFactory = $resultPageFactory;
        $this->apiHelper = $apiHelper;
    }

    /**
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        if (null === $pickupPoint = $this->getRequest()->getParams()['pickuppoint'] ?? null) {
            /** @var Page $page */
            $page = $this->resultPageFactory->create();
            $page->setActiveMenu('SamedayCourier_Shipping::pickuppoint');
            $page->getConfig()->getTitle()->prepend(__('Create new Pickup Point'));

            return $page;
        }


        $redirect = $this->resultRedirectFactory->create();

        if (false === $this->apiHelper->doRequest(
                new SamedayPostPickupPointRequest(
                    $pickupPoint['countryId'],
                    $pickupPoint['countyId'],
                    $pickupPoint['cityId'],
                    $pickupPoint['address'],
                    $pickupPoint['postalCode'],
                    $pickupPoint['alias'],
                    [
                        new PickupPointContactPersonObject(
                            $pickupPoint['contact_person_name'],
                            $pickupPoint['contact_person_phone_number'],
                            true
                        ),
                    ],
                    (bool) $pickupPoint['is_default']
                ),
                'postPickupPoint',
            )
        ) {
            return $redirect->setPath('samedaycourier_shipping/pickuppoint/add');
        }

        return $redirect->setPath('samedaycourier_shipping/pickuppoint/index');
    }
}
