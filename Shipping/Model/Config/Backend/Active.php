<?php

namespace SamedayCourier\Shipping\Model\Config\Backend;

use Magento\Framework\App\Config\Value;
use SamedayCourier\Shipping\Helper\ApiHelper;

class Active extends Value
{
    /**
     * @var ApiHelper
     */
    private $apiHelper;

    public function __construct(ApiHelper $apiHelper, \Magento\Framework\Model\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\App\Config\ScopeConfigInterface $config, \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList, \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null, \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null, array $data = [])
    {
        $this->apiHelper = $apiHelper;

        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * @inheritdoc
     *
     * @throws \Zend_Validate_Exception
     */
    protected function _getValidationRulesBeforeSave()
    {
        $authenticationValidator = new \Zend_Validate_Callback([$this, 'authenticationValidator']);
        $authenticationValidator->setMessage(__('Sameday Courier API authentication failed. Please check your credentials.'));

        $validatorChain = new \Zend_Validate();
        $validatorChain->addValidator($authenticationValidator, true);

        return $validatorChain;
    }

    /**
     * @param Value $field
     *
     * @return bool
     *
     * @throws \Sameday\Exceptions\SamedayAuthenticationException
     * @throws \Sameday\Exceptions\SamedaySDKException
     * @throws \Sameday\Exceptions\SamedayServerException
     */
    public function authenticationValidator(Value $field)
    {
        if (!$field->getValue()) {
            return true;
        }

        $username = $field->getFieldsetDataValue('username');
        $password = $this->_config->getValue('carriers/samedaycourier/password');
        if ($field->getFieldsetDataValue('password') !== '******') {
            // Password updated.
            $password = $field->getFieldsetDataValue('password');
        }

        $testing = (bool) $field->getFieldsetDataValue('testing');

        // Check if login is valid.
        $client = $this->apiHelper->initClient($username, $password, $testing);
        if (!$client->login()) {
            return false;
        }

        return true;
    }
}
