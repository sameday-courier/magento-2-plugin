<?php

namespace SamedayCourier\Shipping\Controller\Adminhtml\City;

use Magento\Framework\Controller\Result\Json;
use Magento\Backend\App\Action;
use Magento\Framework\Data\Form\FormKey\Validator;
use RuntimeException;
use Sameday\Objects\CityObject;
use Sameday\Requests\SamedayGetCitiesRequest;
use Sameday\Responses\SamedayGetCitiesResponse;
use SamedayCourier\Shipping\Helper\ApiHelper;

class Refresh extends Action
{
    /**
     * @var ApiHelper $apiHelper
     */
    private $apiHelper;

    /**
     * @var Json $resultJson
     */
    private $resultJson;

    /**
     * @var Validator $validator
     */
    private $validator;

    /**
     * @param Action\Context $context
     * @param ApiHelper $apiHelper
     * @param Json $resultJson
     * @param Validator $validator
     */
    public function __construct(
        Action\Context $context,
        ApiHelper $apiHelper,
        Json $resultJson,
        Validator $validator
    )
    {
        parent::__construct($context);

        $this->apiHelper = $apiHelper;
        $this->resultJson = $resultJson;
        $this->validator = $validator;
    }

    public function execute()
    {
        $params = $this->getRequest()->getParams();
        if (!$params['isAjax'] || !$this->validator->validate($this->getRequest())) {
            throw new RuntimeException('Invalid request!');
        }

        if (null === $countyId = $params['countyId'] ?? null) {
            return $this->resultJson->setData([]);
        }

        $result = [];
        $page = 1;
        do {
            $samedayGetCityRequest = new SamedayGetCitiesRequest($countyId);
            $samedayGetCityRequest->setCountPerPage(1000);
            $samedayGetCityRequest->setPage($page++);

            /** @var SamedayGetCitiesResponse|false $samedayGetCityResponse */
            $samedayGetCityResponse = $this->apiHelper->doRequest(
                $samedayGetCityRequest,
                'getCities',
                false
            );

            if (false === $samedayGetCityResponse) {
                break;
            }

            foreach ($samedayGetCityResponse->getCities() as $city) {
                $result[] = [
                    'value' => $city->getId(),
                    'label' => $city->getName(),
                ];
            }
        } while ($page <= $samedayGetCityResponse->getPages());

        return $this->resultJson->setData($result);
    }
}
