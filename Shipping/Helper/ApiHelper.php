<?php

namespace SamedayCourier\Shipping\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Message\ManagerInterface;
use Psr\Log\LoggerInterface;
use Sameday\Requests\SamedayRequestInterface;
use Sameday\Responses\SamedayResponseInterface;
use Sameday\Sameday;

class ApiHelper extends AbstractHelper
{
    /**
     * @var ProductMetadataInterface 
     */
    private $productMetadata;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * ApiHelper constructor.
     *
     * @param Context $context
     * @param ProductMetadataInterface $productMetadata
     * @param EncryptorInterface $encryptor
     */
    public function __construct(Context $context, ProductMetadataInterface $productMetadata, EncryptorInterface $encryptor, LoggerInterface $logger, ManagerInterface $messageManager)
    {
        parent::__construct($context);

        $this->productMetadata = $productMetadata;
        $this->encryptor = $encryptor;
        $this->logger = $logger;
        $this->messageManager = $messageManager;
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
            $password = $this->encryptor->decrypt($this->scopeConfig->getValue('carriers/samedaycourier/password'));
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

    /**
     * @param SamedayRequestInterface $request
     * @param string $type
     *
     * @return false|SamedayResponseInterface
     */
    public function doRequest(SamedayRequestInterface $request, string $type = '')
    {
        try {
            $sameday = new Sameday($this->initClient());
            return $sameday->{$type}($request);
        } catch(\Exception $e) {
            $this->messageManager->addError(__("SamedayCourier communication error occured. Please try again later"));
            $this->logger->error('Sameday communication error', ['error' => $e]);
        }

        return false;
    }
}
