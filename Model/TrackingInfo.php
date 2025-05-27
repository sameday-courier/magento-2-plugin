<?php

namespace SamedayCourier\Shipping\Model;

use Sameday\Responses\SamedayGetAwbStatusHistoryResponse;

class TrackingInfo
{

    /**
     * @var string
     */
    public $carrierTitle;
    /**
     * @var string
     */
    public $trackingNumber;
    /**
     * @var string
     */
    public $trackingUrl;
    /**
     * @var string
     */
    public $trackSummary;

    private $awbHistory;

    public function setCarrierTitle($title): TrackingInfo
    {
        $this->carrierTitle = $title;
        return $this;

    }
    public function getCarrierTitle(): string
    {
        return $this->carrierTitle;
    }
    public function setTracking($trackingNumber): TrackingInfo
    {
        $this->trackingNumber = $trackingNumber;
        return $this;
    }
    public function getTracking(): string
    {
        return $this->trackingNumber;

    }
    public function setUrl($trackingUrl): TrackingInfo
    {
        $this->trackingUrl = $trackingUrl;
        return $this;
    }
    public function getUrl(): string
    {
        return $this->trackingUrl;

    }
    public function setTrackSummary(SamedayGetAwbStatusHistoryResponse $awbHistory): TrackingInfo
    {
        $this->awbHistory = $awbHistory;
        $this->trackSummary = $awbHistory->getExpeditionStatus()->getState();
        return $this;
    }

    public function getTrackSummary()
    {
        return null; // intentionally null so that magento will show tracking link
    }
    public function getErrorMessage()
    {
     return null; //intentionally null
    }
    public function getProgressdetail()
    {
        if($this->awbHistory) {
            return array_map(
                static function ($history) {
                    return [
                        'deliverydate'     => $history->getDate()->format('Y-m-d'),
                        'deliverytime'     => $history->getDate()->format('H:i:s'),
                        'deliverylocation' => ($history->getTransitLocation() !== "") ? $history->getTransitLocation() : "N/A",
                        'activity'         => $history->getState(),
                    ];
                },
                $this->awbHistory->getHistory()
            );
        }

        return [];
    }

}