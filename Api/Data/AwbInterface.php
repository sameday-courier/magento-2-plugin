<?php

namespace SamedayCourier\Shipping\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * @api
 */
interface AwbInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the getters in snake case
     */
    const ID = 'id';
    const ORDER_ID = 'order_id';
    const AWB_NUMBER = 'awb_number';
    const PARCELS = 'parcels';
    const AWB_COST = 'awb_cost';

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getOrderId();

    /**
     * @param int $orderId
     *
     * @return $this
     */
    public function setOrderId($orderId);

    /**
     * @return string
     */
    public function getAwbNumber();

    /**
     * @param string $awbNumber
     *
     * @return $this
     */
    public function setAwbNumber($awbNumber);

    /**
     * @return string
     */
    public function getParcels();

    /**
     * @param string $parcels
     *
     * @return $this
     */
    public function setParcels($parcels);

    /**
     * @return float
     */
    public function getAwbCost();

    /**
     * @param float $awbCost
     *
     * @return $this
     */
    public function setAwbCost($awbCost);
}