<?php

namespace SamedayCourier\Shipping\Helper;

use Magento\Framework\App\Cache\Manager;
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

    /**
     * @var Manager
     */
    private $cacheManager;

    public function __construct(Context $context, WriterInterface $writer, Manager $cacheManager)
    {
        parent::__construct($context);

        $this->writer = $writer;
        $this->cacheManager = $cacheManager;
    }

    public function get($key)
    {
        return $this->scopeConfig->getValue(self::PATH . $key);
    }

    public function set($key, $value): void
    {
        $this->writer->save(self::PATH . $key, $value);

        // Flush Cache
        $this->cacheManager->flush($this->cacheManager->getAvailableTypes());
    }
}
