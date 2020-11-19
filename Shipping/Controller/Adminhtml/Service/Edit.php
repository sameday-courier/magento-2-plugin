<?php

namespace SamedayCourier\Shipping\Controller\Adminhtml\Service;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use SamedayCourier\Shipping\Api\ServiceRepositoryInterface;

class Edit extends \Magento\Backend\App\Action
{
    /**
     * @var ServiceRepositoryInterface
     */
    private $repository;

    /**
     * Edit constructor.
     *
     * @param Action\Context $context
     * @param ServiceRepositoryInterface $repository
     */
    public function __construct(Action\Context $context, ServiceRepositoryInterface $repository)
    {
        parent::__construct($context);

        $this->repository = $repository;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        if (!$data) {
            return $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        }

        try {
            $service = $this->repository->get($data['service']['id']);
        } catch (NoSuchEntityException $e) {
            return $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        }

        $service
            ->setName($data['service']['name'])
            ->setIsPriceFree((bool) $data['service']['is_price_free'])
            ->setUseEstimatedCost((bool) $data['service']['use_estimated_cost'])
            ->setPriceFree($data['service']['price_free'])
            ->setPrice($data['service']['price'])
            ->setStatus($data['service']['status']);

        $this->repository->save($service);

        $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $redirect->setPath('samedaycourier_shipping/service/index');
    }
}
