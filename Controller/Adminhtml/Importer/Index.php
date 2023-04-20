<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Controller\Adminhtml\Importer;

use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\ObjectManager;
use RuntimeException;
use SamedayCourier\Shipping\Helper\LocalDataImporter;
use SamedayCourier\Shipping\Helper\LocalDataImporterResponse;

class Index extends Action
{
    private $resultJsonFactory;
    private $formKeyValidator;

    /**
     * @var LocalDataImporter
     */
    private $localDataImporter;

    /**
     * @param Context $context
     * @param ResultFactory $resultFactory
     * @param Json $json
     * @param LocalDataImporter $localDataImporter
     * @param Validator|null $formKeyValidator
     */
    public function __construct(
        Context $context,
        ResultFactory $resultFactory,
        Json $json,
        LocalDataImporter $localDataImporter,
        Validator $formKeyValidator = null
    ) {
        parent::__construct($context);

        $this->resultJsonFactory = $resultFactory;
        $this->json = $json;
        $this->formKeyValidator = $formKeyValidator ?: ObjectManager::getInstance()->get(Validator::class);
        $this->localDataImporter = $localDataImporter;
    }

    /**
     * @throws InvalidRequestException
     * @throws Exception
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();

        if (!$params['isAjax'] || !$this->formKeyValidator->validate($this->getRequest())) {
            throw new RuntimeException('Invalid request!');
        }

        /** @var LocalDataImporterResponse $result */
        $result = $this->localDataImporter->{$params['action']}();

        return $this->resultJsonFactory->create(ResultFactory::TYPE_JSON)->setData($result->getMessage());
    }
}
