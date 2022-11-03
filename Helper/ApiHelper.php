<?php

namespace SamedayCourier\Shipping\Helper;

use Exception;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Message\ManagerInterface;
use Psr\Log\LoggerInterface;
use Sameday\Exceptions\SamedayBadRequestException;
use Sameday\Exceptions\SamedaySDKException;
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
    public const ROMANIA_CODE = "ro";
    public const HUNGARY_CODE = "hu";

    public const SAMEDAY_ENVS = [
        self::ROMANIA_CODE => [
            self::PRODUCTION_CODE => 'https://api.sameday.ro',
            self::DEMO_CODE => 'https://sameday-api.demo.zitec.com',
        ],
        self::HUNGARY_CODE => [
            self::PRODUCTION_CODE => 'https://api.sameday.hu',
            self::DEMO_CODE => 'https://sameday-api-hu.demo.zitec.com',
        ]
    ];

    /**
     * @param Context $context
     * @param ProductMetadataInterface $productMetadata
     * @param EncryptorInterface $encryptor
     * @param LoggerInterface $logger
     * @param ManagerInterface $messageManager
     * @param WriterInterface $configWriter
     * @param PersistenceDataHandler $persistenceDataHandler
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
     * @param null $url_env
     * @return SamedayClient
     *
     * @throws SamedaySDKException
     */
    public function initClient(string $username = null, string $password = null, $url_env = null): SamedayClient
    {
        $country = $this->getHostCountry();
        $testing = (int) $this->scopeConfig->getValue('carriers/samedaycourier/testing');

        if ($username === null && $password === null) {
            $username = $this->scopeConfig->getValue('carriers/samedaycourier/username');
            $password = $this->encryptor->decrypt($this->scopeConfig->getValue('carriers/samedaycourier/password'));
        }

        $url_env_param = (int) ($testing === self::DEMO_CODE);

        if ($url_env === null) {
            $url_env = self::SAMEDAY_ENVS[$country][$url_env_param];
        }

        return new SamedayClient(
            $username,
            $password,
            $url_env,
            "{$this->productMetadata->getName()} ({$this->productMetadata->getEdition()})",
            $this->productMetadata->getVersion(),
            'curl',
            $this->persistenceDataHandler
        );
    }

    public function getHostCountry()
    {
        return $this->scopeConfig->getValue('carriers/samedaycourier/country') ?? self::ROMANIA_CODE;
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
            return (new Sameday($this->initClient()))->{$type}($request);
        } catch (SamedayBadRequestException $e) {
            $errors = $e->getErrors();

            $allErrors = array();
            foreach ($errors as $error) {
                foreach ($error['errors'] as $message) {
                    $allErrors[] = implode('.', $error['key']) . ': ' . $message;
                }
            }

            $message = implode(' ', $allErrors);

            if ($showFlashMessage) {
                $this->messageManager->addErrorMessage(__($message));
            }
        } catch(Exception $e) {
            if ($showFlashMessage) {
                $this->messageManager->addErrorMessage(__($e->getMessage()));
            }

            $this->logger->error('Sameday communication error', ['error' => $e->getCode() . ' : ' . $e->getMessage()]);
        }

        return false;
    }

    /**
     * @param $form_values
     * @return bool
     *
     * @throws SamedaySDKException
     */
    public function loginClient($form_values): bool
    {
        $isLogged = false;
        $envModes = self::SAMEDAY_ENVS;
        foreach ($envModes as $hostCountry => $envModesByHosts) {
            if ($isLogged === true) {
                break;
            }

            foreach ($envModesByHosts as $key => $apiUrl) {
                $sameday = $this->initClient(
                    $form_values['username'],
                    $form_values['password'],
                    $apiUrl
                );

                try {
                    if ($sameday->login()) {
                        $isTesting = (int) (self::DEMO_CODE === $key);
                        $this->configWriter->save('carriers/samedaycourier/testing', $isTesting);
                        $this->configWriter->save('carriers/samedaycourier/country', $hostCountry);
                        // Remove old persisted SamedayToken
                        $this->configWriter->delete('carriers/samedaycourier/token');
                        $this->configWriter->delete('carriers/samedaycourier/expires_at');

                        $isLogged = true;

                        break;
                    }
                } catch (Exception $exception) {
                    continue;
                }
            }
        }

        if ($isLogged) {
            return true;
        }

        return false;
    }

    /**
     * @param null $username
     * @param null $password
     * @return bool
     *
     * @throws SamedaySDKException
     */
    public function connectionLogin($username = null, $password = null): bool
    {
        $form_values = [
            'username' => $username ?? $this->scopeConfig->getValue('carriers/samedaycourier/username'),
            'password' => $password ?? $this->encryptor->decrypt($this->scopeConfig->getValue('carriers/samedaycourier/password')),
        ];

        return $this->loginClient($form_values);
    }
}
