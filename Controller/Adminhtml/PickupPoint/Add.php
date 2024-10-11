<?php

namespace SamedayCourier\Shipping\Controller\Adminhtml\PickupPoint;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;

class Add extends Action
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(Context $context, PageFactory $resultPageFactory)
    {
        parent::__construct($context);

        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        if (null === $pickupPoint = $this->getRequest()->getParams()['pickuppoint'] ?? null) {
            /** @var Page $page */
            $page = $this->resultPageFactory->create();
            $page->setActiveMenu('SamedayCourier_Shipping::pickuppoint');
            $page->getConfig()->getTitle()->prepend(__('Create new Pickup Point'));

            return $page;
        }

        // WIP - process form and request Sameday API

        $redirect = $this->resultRedirectFactory->create();

        return $redirect->setPath('samedaycourier_shipping/pickuppoint/index');
    }
}
