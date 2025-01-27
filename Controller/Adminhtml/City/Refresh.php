<?php

namespace SamedayCourier\Shipping\Controller\Adminhtml\City;

use Magento\Framework\Controller\Result\Json;
use Magento\Backend\App\Action;
use Magento\Framework\Data\Form\FormKey\Validator;
use RuntimeException;
use SamedayCourier\Shipping\Helper\SamedayCitiesHelper;

class Refresh extends Action
{
    /**
     * @var Validator $validator
     */
    private $validator;

    /**
     * @var SamedayCitiesHelper $samedayCitiesHelper
     */
    private $samedayCitiesHelper;

    /**
     * @param Action\Context $context
     * @param Validator $validator
     * @param SamedayCitiesHelper $samedayCitiesHelper
     */
    public function __construct(
        Action\Context $context,
        Validator $validator,
        SamedayCitiesHelper $samedayCitiesHelper
    )
    {
        parent::__construct($context);

        $this->validator = $validator;
        $this->samedayCitiesHelper = $samedayCitiesHelper;
    }

    /**
     * @return Json
     */
    public function execute(): Json
    {
        $params = $this->getRequest()->getParams();
        if (!$params['isAjax'] || !$this->validator->validate($this->getRequest())) {
            throw new RuntimeException('Invalid request!');
        }

        return $this->samedayCitiesHelper->renderCities($params['countyId'] ?? null);
    }
}
