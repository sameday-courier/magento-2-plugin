<?php

namespace SamedayCourier\Shipping\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ProductMetadataInterface;

class ApiHelper extends AbstractHelper
{
    /**
     * @var ProductMetadataInterface 
     */
    private $productMetadata;

    /**
     * ApiHelper constructor.
     *
     * @param Context $context
     * @param ProductMetadataInterface $productMetadata
     */
    public function __construct(Context $context, ProductMetadataInterface $productMetadata)
    {
        parent::__construct($context);

        $this->productMetadata = $productMetadata;
    }

    /**
     * @param string|null $username
     * @param string|null $password
     * @param bool|null $testing
     *
     * @return \Sameday\SamedayClient
     *
     * @throws \Sameday\Exceptions\SamedaySDKException
     */
    public function initClient($username = null, $password = null, $testing = null)
    {
        if ($username === null && $password === null && $testing === null) {
            $username = $this->scopeConfig->getValue('carriers/samedaycourier/username');
            $password = $this->scopeConfig->getValue('carriers/samedaycourier/password');
            $testing = $this->scopeConfig->getValue('carriers/samedaycourier/testing');
        }

        return new \Sameday\SamedayClient(
            $username,
            $password,
            $testing ? 'https://sameday-api.demo.zitec.com' : 'https://api.sameday.ro',
            "{$this->productMetadata->getName()} ({$this->productMetadata->getEdition()})",
            $this->productMetadata->getVersion()
        );
    }
}
