<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Controller\Adminhtml\Order;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\App\ObjectManager;
use SamedayCourier\Shipping\Api\AwbRepositoryInterface;
use Sameday\Requests\SamedayDeleteAwbRequest;
use SamedayCourier\Shipping\Helper\ApiHelper;

class RemoveAwb extends Action
{
    private $resultJsonFactory;
    private $manager;
    private $awbRepository;
    private $apiHelper;
    private $formKeyValidator;

    public function __construct(Context $context, ResultFactory $resultFactory, ManagerInterface $manager, AwbRepositoryInterface $awbRepository, ApiHelper $apiHelper, Validator $formKeyValidator = null)
    {
        parent::__construct($context);
        $this->resultJsonFactory = $resultFactory;
        $this->manager = $manager;
        $this->awbRepository = $awbRepository;
        $this->apiHelper = $apiHelper;
        $this->formKeyValidator = $formKeyValidator ?: ObjectManager::getInstance()->get(Validator::class);
    }

    public function execute()
    {
        $params = $this->getRequest()->getParams();
        if (!$this->getRequest()->isPost() || !$params['isAjax'] || !$this->formKeyValidator->validate($this->getRequest())) {
            throw new InvalidRequestException(new NotFoundException());
        }

        $apiRequest = new SamedayDeleteAwbRequest($params['sameday_awb_number']);
        $response = $this->apiHelper->doRequest($apiRequest, 'deleteAwb');
        if ($response) {
            $this->awbRepository->deleteById($params['awb_id']);
            $this->manager->addSuccessMessage("Awb removed successfully!");
        }

        $resultJson = $this->resultJsonFactory->create(ResultFactory::TYPE_JSON);
        return $resultJson->setData(['success' => true]);
    }
}
