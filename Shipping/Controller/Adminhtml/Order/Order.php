<?php

namespace SamedayCourier\Shipping\Controller\Adminhtml\Order;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Sales\Controller\Adminhtml\Order as AdminOrder;

class Order extends AdminOrder implements HttpPostActionInterface
{
    /**
     * Changes ACL Resource Id
     */
    const ADMIN_RESOURCE = 'Magento_Sales::hold';

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        $order = $this->_initOrder();
        if ($order) {
            $post = $this->getRequest()->getPostValue();

            echo "<pre>";
            print_r($post);
            exit;
            $resultRedirect->setPath('sales/order/view', ['order_id' => $order->getId()]);
            return $resultRedirect;
        }

        $resultRedirect->setPath('sales/*/');

        return $resultRedirect;
    }
}
