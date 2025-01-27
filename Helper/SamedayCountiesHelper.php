<?php

namespace SamedayCourier\Shipping\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Sameday\Requests\SamedayGetCountiesRequest;
use Sameday\Responses\SamedayGetCountiesResponse;

class SamedayCountiesHelper extends AbstractHelper
{
    /**
     * @var ApiHelper $apiHelper
     */
    private $apiHelper;

    public function __construct(
        Context $context,
        ApiHelper $apiHelper
    )
    {
        parent::__construct($context);

        $this->apiHelper = $apiHelper;
    }

    /**
     * @return array
     */
    public function getCounties(): array
    {
        /** @var SamedayGetCountiesResponse|false $counties */
        $counties = $this->apiHelper->doRequest(
            new SamedayGetCountiesRequest(null),
            'getCounties',
            false
        );

        if (false !== $counties) {
            return $counties->getCounties();
        }

        return [];
    }
}
