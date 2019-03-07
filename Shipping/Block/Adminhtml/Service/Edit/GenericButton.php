<?php

namespace SamedayCourier\Shipping\Block\Adminhtml\Service\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use SamedayCourier\Shipping\Api\ServiceRepositoryInterface;

class GenericButton
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var ServiceRepositoryInterface
     */
    protected $serviceRepository;

    /**
     * @param Context $context
     * @param ServiceRepositoryInterface $serviceRepository
     */
    public function __construct(
        Context $context,
        ServiceRepositoryInterface $serviceRepository
    ) {
        $this->context = $context;
        $this->serviceRepository = $serviceRepository;
    }

    /**
     * @return int|null
     */
    public function getServiceId()
    {
        try {
            return $this->serviceRepository->get($this->context->getRequest()->getParam('id'))->getId();
        } catch (NoSuchEntityException $e) {
        }

        return null;
    }

    /**
     * @param string $route
     * @param array $params
     *
     * @return string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
