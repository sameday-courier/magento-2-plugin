<?php

namespace SamedayCourier\Shipping\Helper;

use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Sameday\PersistentData\SamedayPersistentDataInterface;

class PersistenceDataHandler extends AbstractHelper implements SamedayPersistentDataInterface
{
    protected const PATH = 'carriers/samedaycourier/';

    /**
     * @var WriterInterface
     */
    private $writer;

    public function __construct(Context $context, WriterInterface $writer)
    {
        parent::__construct($context);

        $this->writer = $writer;
    }

    public function get($key)
    {
        return $this->scopeConfig->getValue(self::PATH . $key);
    }

    public function set($key, $value): void
    {
        $this->writer->save(self::PATH . $key, $value);
    }
}
