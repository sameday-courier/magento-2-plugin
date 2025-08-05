<?php

namespace SamedayCourier\Shipping\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Exception\InputException;
use Sameday\Requests\SamedayGetCitiesRequest;
use Sameday\Responses\SamedayGetCitiesResponse;
use SamedayCourier\Shipping\Api\CityRepositoryInterface;
use SamedayCourier\Shipping\Model\ResourceModel\CityRepository;

class SamedayCitiesHelper extends AbstractHelper
{
    /**
     * @var ApiHelper $apiHelper
     */
    private $apiHelper;

    /**
     * @var Json $resultJson
     */
    private $resultJson;

    /**
     * @var CityRepositoryInterface $cityRepository
     */
    private $cityRepository;

    /**
     * @var CacheHelper $cacheHelper
     */
    private $cacheHelper;

    /**
     * @param Context $context
     * @param ApiHelper $apiHelper
     * @param Json $resultJson
     * @param CityRepositoryInterface $cityRepository
     * @param CacheHelper $cacheHelper
     */
    public function __construct(
        Context $context,
        ApiHelper $apiHelper,
        Json $resultJson,
        CityRepositoryInterface $cityRepository,
        CacheHelper $cacheHelper
    )
    {
        parent::__construct($context);

        $this->apiHelper = $apiHelper;
        $this->resultJson = $resultJson;
        $this->cityRepository = $cityRepository;
        $this->cacheHelper = $cacheHelper;
    }

    /**
     * @param int|null $countyId
     *
     * @return array
     */
    public function getCities(?int $countyId): array
    {
        $result = [];
        $page = 1;
        do {
            $samedayGetCityRequest = new SamedayGetCitiesRequest($countyId);
            $samedayGetCityRequest->setCountPerPage(1000);
            $samedayGetCityRequest->setPage($page++);

            /** @var SamedayGetCitiesResponse|false $samedayGetCityResponse */
            $samedayGetCityResponse = $this->apiHelper->doRequest(
                $samedayGetCityRequest,
                'getCities',
                false
            );

            if (false === $samedayGetCityResponse) {
                break;
            }

            foreach ($samedayGetCityResponse->getCities() as $city) {
                $result[] = [
                    'value' => $city->getId(),
                    'label' => $city->getName(),
                ];
            }
        } while ($page <= $samedayGetCityResponse->getPages());

        return $result;
    }

    /**
     * @param int|null $countyId
     *
     * @return Json
     */
    public function renderCities(?int $countyId): Json
    {
        if (null === $countyId) {
            return $this->resultJson->setData([]);
        }

        return $this->resultJson->setData($this->getCities($countyId));
    }

    /**
     * @return array
     *
     * @throws InputException
     */
    public function getCachedCities(): array
    {
        if (empty($this->cacheHelper->loadData(GeneralHelper::CACHE_CITIES_DATA_KEY))) {
                $this->cacheHelper->cacheData(
                GeneralHelper::CACHE_CITIES_DATA_KEY,
                    $this->cityRepository->getCitiesForShipCountries(),
                )
            ;
        }

        return $this->cacheHelper->loadData(GeneralHelper::CACHE_CITIES_DATA_KEY);
    }
}
