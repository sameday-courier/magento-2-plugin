<?php

namespace SamedayCourier\Shipping\Controller\Adminhtml\Order;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Sales\Controller\Adminhtml\Order as AdminOrder;
use SamedayCourier\Shipping\Exception\NotAnOrderMatchedException;

class AddParcel extends AdminOrder implements HttpPostActionInterface
{
    public function execute()
    {
        /** @var \Magento\Sales\Api\Data\OrderInterface $order */
        $order = $this->_initOrder();
        $values = $this->getRequest()->getParams();
        if (!$order) {
            throw new NotAnOrderMatchedException();
        }

        var_dump($values);
    }
}