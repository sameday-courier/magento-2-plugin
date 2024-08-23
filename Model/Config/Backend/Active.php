<?php

namespace SamedayCourier\Shipping\Model\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Sameday\Exceptions\SamedaySDKException;
use SamedayCourier\Shipping\Helper\ApiHelper;
use SamedayCourier\Shipping\Validators\SamedayValidator;

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

    public function __construct(
        ApiHelper $apiHelper,
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        EncryptorInterface $encryptor,
        TypeListInterface $cacheTypeList,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);

        $this->apiHelper = $apiHelper;
        $this->encryptor = $encryptor;
    }

    /**
     * @return SamedayValidator
     *
     * @throws SamedaySDKException
     */
    protected function _getValidationRulesBeforeSave(): SamedayValidator
    {
        $authenticationValidator = new SamedayValidator();

        $authenticationValidator->setValidation($this->authenticationValidator($this));
        $authenticationValidator->setMessage(
            __('Sameday Courier API authentication failed. Please check your credentials!')
        );

        return $authenticationValidator;
    }

    /**
     * @param Value $field
     * @return bool
     * @throws SamedaySDKException
     */
    public function authenticationValidator(Value $field): bool
    {
        $username = $field->getFieldsetDataValue('username');
        $password = $this->_config->getValue('carriers/samedaycourier/password');
        if ($field->getFieldsetDataValue('password') !== '******') {
            // Password updated.
            $password = $field->getFieldsetDataValue('password');
        } else {
            $password = $this->encryptor->decrypt($password);
        }

        // Check if login is valid.
        if (!$this->apiHelper->connectionLogin($username, $password)) {
            return false;
        }

        return true;
    }
}
