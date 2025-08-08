<?php

namespace SamedayCourier\Shipping\Helper;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\Serialize\Serializer\Json;

class CacheHelper
{
    private const ONE_YEAR = 86400;

    private const SAMEDAY_COURIER_CACHE_TAG = 'SAMEDAY_COURIER_CACHE_TAG';

    /**
     * @var CacheInterface $cache
     */
    private $cache;

    /**
     * @var Json $json
     */
    private $json;

    public function __construct(
        CacheInterface $cache,
        Json $json
    )
    {
        $this->cache = $cache;
        $this->json = $json;
    }

    /**
     * @param string $key
     * @param array $data
     *
     * @param int $lifetime
     *
     * @return void
     */
    public function cacheData(string $key, array $data, int $lifetime = self::ONE_YEAR): void
    {
        $this->cache->clean([self::SAMEDAY_COURIER_CACHE_TAG]);

        $this->cache->save(
            $this->json->serialize($data),
            $key,
            [self::SAMEDAY_COURIER_CACHE_TAG],
            $lifetime
        );
    }

    /**
     * @param $key
     *
     * @return array
     */
    public function loadData($key): array
    {
        $data = $this->cache->load($key);
        if ($data) {
            return $this->json->unserialize($data);
        }

        return [];
    }
}
