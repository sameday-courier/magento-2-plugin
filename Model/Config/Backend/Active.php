<?php

namespace SamedayCourier\Shipping\Model\Config\Backend;

use Magento\Framework\App\Config\Value;
use Magento\Framework\Encryption\EncryptorInterface;
use SamedayCourier\Shipping\Helper\ApiHelper;

class Active extends Value
{
    /**
     * @var EncryptorInterface
     */
    private $encryptor;
    /**
     * @var ApiHelper
     */
    private $apiHelper;

    public function __construct(ApiHelper $apiHelper, \Magento\Framework\Model\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\App\Config\ScopeConfigInterface $config, EncryptorInterface $encryptor, \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList, \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null, \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null, array $data = [])
    {
        $this->apiHelper = $apiHelper;
        $this->encryptor = $encryptor;

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
        }else{
            $password = $this->encryptor->decrypt($password);
        }


        // Check if login is valid.
        if(!$this->apiHelper->connectionLogin($username, $password)){
            return false;
        }

        return true;
    }
}
