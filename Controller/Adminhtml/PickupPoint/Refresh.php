<?php

namespace SamedayCourier\Shipping\Controller\Adminhtml\PickupPoint;

use Magento\Backend\App\Action;
use Sameday\Exceptions\SamedaySDKException;
use SamedayCourier\Shipping\Helper\LocalDataImporter;

class Refresh extends Action
{
    /**
     * @var LocalDataImporter
     */
    private $localDataImporter;

    /**
     * @param Action\Context $context
     * @param LocalDataImporter $localDataImporter
     */
    public function __construct(
        Action\Context $context,
        LocalDataImporter $localDataImporter
    ) {
        parent::__construct($context);

        $this->localDataImporter = $localDataImporter;
    }

    /**
     * @throws SamedaySDKException
     */
    public function execute()
    {
        $import = $this->localDataImporter->importPickupPoints();
        if (false === $import->isSucceed()) {
            $this->messageManager->addErrorMessage(sprintf(__('Communication error: %s'), $import->getMessage()));
        }

        return $this->_redirect('samedaycourier_shipping/pickuppoint/index');
    }
}
