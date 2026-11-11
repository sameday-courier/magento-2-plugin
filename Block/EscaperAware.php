<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Block;

use Magento\Framework\Escaper;

/**
 * Exposes Magento's Escaper for templates on Magento 2.3 (where $escaper is not injected).
 */
trait EscaperAware
{
    public function getEscaper(): Escaper
    {
        return $this->_escaper;
    }
}
