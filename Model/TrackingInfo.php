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
        $implodedHistory = implode(' | ', array_map(
            static function ($history) {
                return $history->date->format('Y-m-d H:i:s') . ": " . $history->label;
            },
            $awbHistory->history
        ));
        $this->trackSummary = $awbHistory->expeditionStatus->label .PHP_EOL . $implodedHistory;
        return $this;
    }

    public function getTrackSummary(): string
    {
        return $this->trackSummary;
    }

}