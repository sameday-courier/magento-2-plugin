<?php

namespace SamedayCourier\Shipping\Helper;

use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Message\ManagerInterface;
use Psr\Log\LoggerInterface;
use Sameday\Requests\SamedayRequestInterface;
use Sameday\Responses\SamedayResponseInterface;
use Sameday\Sameday;
use Sameday\SamedayClient;

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
     * @var WriterInterface
     */
    protected $configWriter;

    /**
     * @var PersistenceDataHandler
     */
    protected $persistenceDataHandler;

    public const PRODUCTION_CODE = 0;
    public const DEMO_CODE = 1;
    public const PRODUCTION_URL_PARAM = "API_URL_PROD";
    public const DEMO_URL_PARAM = "API_URL_DEMO";
    public const ROMANIA_CODE = "ro";
    public const HUNGARY_CODE = "hu";

    public const SAMEDAY_ENVS = [
        self::ROMANIA_CODE => [
            self::PRODUCTION_URL_PARAM => 'https://api.sameday.ro',
            self::DEMO_URL_PARAM => 'https://sameday-api.demo.zitec.com',
        ],
    ];

    /**
     * @param Context $context
     * @param ProductMetadataInterface $productMetadata
     * @param EncryptorInterface $encryptor
     * @param LoggerInterface $logger
     * @param ManagerInterface $messageManager
     * @param WriterInterface $configWriter
     */
    public function __construct(
        Context $context,
        ProductMetadataInterface $productMetadata,
        EncryptorInterface $encryptor,
        LoggerInterface $logger,
        ManagerInterface $messageManager,
        WriterInterface $configWriter,
        PersistenceDataHandler $persistenceDataHandler
    )
    {
        parent::__construct($context);

        $this->productMetadata = $productMetadata;
        $this->encryptor = $encryptor;
        $this->logger = $logger;
        $this->messageManager = $messageManager;
        $this->configWriter = $configWriter;
        $this->persistenceDataHandler = $persistenceDataHandler;
    }

    /**
     * @param string|null $username
     * @param string|null $password
     * @param bool|null $testing
     *
     * @return SamedayClient
     *
     * @throws \Sameday\Exceptions\SamedaySDKException
     */
    public function initClient($username = null, $password = null, $testing = null, $url_env = null, $country = null): SamedayClient
    {
        if($testing === null){
            $testing = $this->scopeConfig->getValue('carriers/samedaycourier/testing');
        }

        if($country === null){
            $country = $this->scopeConfig->getValue('carriers/samedaycourier/country') ?? self::ROMANIA_CODE;
        }

        if ($username === null && $password === null) {
            $username = $this->scopeConfig->getValue('carriers/samedaycourier/username');
            $password = $this->encryptor->decrypt($this->scopeConfig->getValue('carriers/samedaycourier/password'));
        }

        if ($testing === self::PRODUCTION_CODE) {
            $url_env_param = self::PRODUCTION_URL_PARAM;
        } else {
            $url_env_param = self::DEMO_URL_PARAM;
        }

        if($url_env === null) {
            $url_env = self::SAMEDAY_ENVS[$country][$url_env_param];
        }

        return new SamedayClient(
            $username,
            $password,
            $url_env,
            "{$this->productMetadata->getName()} ({$this->productMetadata->getEdition()})",
            $this->productMetadata->getVersion(),
            'curl',
            $this->persistenceDataHandler,
        );
    }

    /**
     * @param SamedayRequestInterface $request
     * @param string $type
     * @param bool $showFlashMessage
     *
     * @return false|SamedayResponseInterface
     */
    public function doRequest(SamedayRequestInterface $request, string $type = '', $showFlashMessage = true)
    {
        try {
            $sameday = new Sameday($this->initClient());
            return $sameday->{$type}($request);
        } catch(\Exception $e) {
            if ($showFlashMessage) {
                $this->messageManager->addError(__("SamedayCourier communication error occured. Please try again later"));
            }
            $this->logger->error('Sameday communication error', ['error' => $e]);
        }

        return false;
    }
    /**
     * @param $form_values
     * @param $testing_mode
     * @param $country
     * @return bool
     * @throws SamedaySDKException
     */
    public function loginClient($form_values = null, $testing_mode = null, $country = null)
    {
        if($country === null) $country = self::ROMANIA_CODE;
        if($testing_mode === null) $testing_mode = self::DEMO_CODE;
        if(!in_array($testing_mode, [self::DEMO_CODE, self::PRODUCTION_CODE])) return false;
        if($form_values !== null && !is_array($form_values)) return false;
        if(!in_array($country, [self::ROMANIA_CODE, self::HUNGARY_CODE])) return false;

        if($testing_mode === self::PRODUCTION_CODE){
            $url = (!empty(self::SAMEDAY_ENVS)) ? self::SAMEDAY_ENVS[$country][self::PRODUCTION_URL_PARAM] : null;
        }else{
            $url =  (!empty(self::SAMEDAY_ENVS)) ? self::SAMEDAY_ENVS[$country][self::DEMO_URL_PARAM] : null;
        }

        $client = $this->initClient(
            $form_values['username'],
            $form_values['password'],
            $testing_mode,
            $url,
            $country
        );

        try{
            if($client->login()){
                $this->configWriter->save('carriers/samedaycourier/testing', $testing_mode);
                $this->configWriter->save('carriers/samedaycourier/country', $country);

                return true;
            }
        } catch (Exception $exception) {
            $this->addMessage('danger', $this->l($exception->getMessage()));
        }

        return false;
    }

    /**
     * @param null $form_values
     * @return bool
     * @throws SamedaySDKException
     */
    public function connectionLogin($username = null, $password = null)
    {
        $connected = false;

        $form_values = [
            'username' => ($username !== null) ? $username : $this->scopeConfig->getValue('carriers/samedaycourier/username'),
            'password' => ($password !== null) ? $password : $this->encryptor->decrypt($this->scopeConfig->getValue('carriers/samedaycourier/password'))
        ];

        $arr_envs = (!empty(self::SAMEDAY_ENVS)) ? self::SAMEDAY_ENVS : null;
        foreach($arr_envs as $index => $arr_env){
            if($this->loginClient($form_values, self::PRODUCTION_CODE, $index) === true) $connected = true;
            if($this->loginClient($form_values, self::DEMO_CODE, $index) === true) $connected = true;
        }
        return $connected;
    }
}
