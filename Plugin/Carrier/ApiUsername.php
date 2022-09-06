<?php

namespace SamedayCourier\Shipping\Plugin\Carrier;

use Magento\Quote\Api\Data\ShippingMethodInterfaceFactory;

class ApiUsername
{
    /**
     * @var ShippingMethodInterfaceFactory
     */
    protected $extensionFactory;

    /**
     * Description constructor.
     * @param ShippingMethodInterfaceFactory $extensionFactory
     */
    public function __construct(
        ShippingMethodInterfaceFactory $extensionFactory
    )
    {
        $this->extensionFactory = $extensionFactory;
    }

    /**
     * @param $subject
     * @param $result
     * @param $rateModel
     *
     * @return mixed
     */
    public function afterModelToDataObject($subject, $result, $rateModel)
    {
        $extensionAttribute = $result->getExtensionAttributes() ?: $this->extensionFactory->create();

        $extensionAttribute->setApiUsername($rateModel->getApiUsername());
        $result->setExtensionAttributes($extensionAttribute);

        return $result;
    }
}
