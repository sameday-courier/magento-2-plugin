<?php

namespace SamedayCourier\Shipping\Controller\Adminhtml\PickupPoint;

use Magento\Backend\App\Action;
use Magento\Framework\Exception\NoSuchEntityException;
use Sameday\Requests\SamedayDeletePickupPointRequest;
use Sameday\Responses\SamedayDeletePickupPointResponse;
use SamedayCourier\Shipping\Api\PickupPointRepositoryInterface;
use SamedayCourier\Shipping\Helper\ApiHelper;

class Delete extends Action
{
    /**
     * @var ApiHelper $apiHelper
     */
    protected $apiHelper;

    /**
     * @var PickupPointRepositoryInterface $pickupPointRepository
     */
    protected $pickupPointRepository;

    /**
     * @param Action\Context $context
     * @param ApiHelper $apiHelper
     * @param PickupPointRepositoryInterface $pickupPointRepository
     */
    public function __construct(
        Action\Context $context,
        ApiHelper $apiHelper,
        PickupPointRepositoryInterface $pickupPointRepository
    )
    {
        parent::__construct($context);

        $this->apiHelper = $apiHelper;
        $this->pickupPointRepository = $pickupPointRepository;
    }

    public function execute()
    {
        $redirect = $this->resultRedirectFactory->create();

        if (null === $pickupPointId = $this->getRequest()->getParams()['id'] ?? null) {
            $this->messageManager->addErrorMessage(__('PickupPoint ID is required.'));

            return $redirect->setPath('samedaycourier_shipping/pickuppoint/index');
        }

        try {
            $pickupPoint = $this->pickupPointRepository->get($pickupPointId);
        } catch (NoSuchEntityException $exception) {
            $this->messageManager->addErrorMessage(__('PickupPoint ID is not found.'));

            return $redirect->setPath('samedaycourier_shipping/pickuppoint/index');
        }

        /** @var SamedayDeletePickupPointResponse|false $response */
        $response = $this->apiHelper->doRequest(
            new SamedayDeletePickupPointRequest($pickupPoint->getSamedayId()),
            'deletePickupPoint'
        );

        if (false === $response) {
            $this->messageManager->addErrorMessage(__('Sameday was unable to process this request.'));
        }

        $this->pickupPointRepository->deleteById($pickupPointId);
        $this->messageManager->addSuccessMessage(__('PickupPoint has been deleted!'));

        return $redirect->setPath('samedaycourier_shipping/pickuppoint/index');
    }
}
