<?php

namespace SamedayCourier\Shipping\Controller\Adminhtml\PickupPoint;

use Magento\Backend\App\Action;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Sameday\Requests\SamedayGetPickupPointsRequest;
use Sameday\Sameday;
use SamedayCourier\Shipping\Api\Data\PickupPointInterface;
use SamedayCourier\Shipping\Api\Data\PickupPointInterfaceFactory;
use SamedayCourier\Shipping\Api\PickupPointRepositoryInterface;
use SamedayCourier\Shipping\Helper\ApiHelper;

class Refresh extends Action
{
    /**
     * @var ApiHelper
     */
    private $apiHelper;

    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @var PickupPointInterfaceFactory
     */
    private $pickupPointFactory;

    /**
     * @var PickupPointRepositoryInterface
     */
    private $pickupPointRepository;

    /**
     * Refresh constructor.
     *
     * @param ApiHelper $apiHelper
     * @param ScopeConfigInterface $config
     * @param PickupPointInterfaceFactory $pickupPointFactory
     * @param PickupPointRepositoryInterface $pickupPointRepository
     * @param Action\Context $context
     */
    public function __construct(
        ApiHelper $apiHelper,
        ScopeConfigInterface $config,
        PickupPointInterfaceFactory $pickupPointFactory,
        PickupPointRepositoryInterface $pickupPointRepository,
        Action\Context $context
    ) {
        parent::__construct($context);

        $this->apiHelper = $apiHelper;
        $this->config = $config;
        $this->pickupPointFactory = $pickupPointFactory;
        $this->pickupPointRepository = $pickupPointRepository;
    }

    public function execute()
    {
        $sameday = new Sameday($this->apiHelper->initClient());
        $isTesting = (bool) $this->config->getValue('carriers/samedaycourier/testing');

        $remotePickupPoints = [];
        $page = 1;
        do {
            $request = new SamedayGetPickupPointsRequest();
            $request->setPage($page++);
            try {
                $pickUpPoints = $sameday->getPickupPoints($request);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(sprintf(__('Communication error: %s'), $e->getMessage()));

                return $this->_redirect('samedaycourier_shipping/pickuppoint/index');
            }

            foreach ($pickUpPoints->getPickupPoints() as $pickupPointObject) {
                try {
                    $pickupPoint = $this->pickupPointRepository->getBySamedayId($pickupPointObject->getId(), $isTesting);
                } catch (NoSuchEntityException $e) {
                    $pickupPoint = null;
                }

                if (!$pickupPoint) {
                    // Pickup point not found, add it.
                    $pickupPoint = $this->pickupPointFactory->create();
                }

                $pickupPoint
                    ->setSamedayId($pickupPointObject->getId())
                    ->setSamedayAlias($pickupPointObject->getAlias())
                    ->setIsTesting($isTesting)
                    ->setCity($pickupPointObject->getCity()->getName())
                    ->setCounty($pickupPointObject->getCounty()->getName())
                    ->setAddress($pickupPointObject->getAddress())
                    ->setContactPersons($pickupPointObject->getContactPersons())
                    ->setIsDefault($pickupPointObject->isDefault());

                $this->pickupPointRepository->save($pickupPoint);

                // Save as current pickup points.
                $remotePickupPoints[] = $pickupPointObject->getId();
            }
        } while ($page <= $pickUpPoints->getPages());


        // Build array of local pickup points.
        $localPickupPoints = array_map(
            function (PickupPointInterface $pickupPoint) {
                return array(
                    'id' => $pickupPoint->getId(),
                    'sameday_id' => $pickupPoint->getSamedayId()
                );
            },
            $this->pickupPointRepository->getListByTesting($isTesting)->getItems()
        );

        // Delete local pickup points that aren't present in remote pickup points anymore.
        foreach ($localPickupPoints as $localPickupPoint) {
            if (!in_array($localPickupPoint['sameday_id'], $remotePickupPoints, false)) {
                $this->pickupPointRepository->deleteById($localPickupPoint['id']);
            }
        }

        return $this->_redirect('samedaycourier_shipping/pickuppoint/index');
    }
}
