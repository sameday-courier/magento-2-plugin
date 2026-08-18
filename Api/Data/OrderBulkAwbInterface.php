<?php

namespace SamedayCourier\Shipping\Api\Data;

interface OrderBulkAwbInterface
{
    public const ID = 'id';
    public const ORDER_ID = 'order_id';
    public const STATUS = 'status';
    public const FEEDBACK = 'feedback';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';

    public const STATUS_PENDING = 0;
    public const STATUS_SUCCESS = 1;
    public const STATUS_ERROR = 2;

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getOrderId();

    /**
     * @param int $orderId
     * @return $this
     */
    public function setOrderId($orderId);

    /**
     * @return int
     */
    public function getStatus();

    /**
     * @param int $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * @return string|null
     */
    public function getFeedback();

    /**
     * @param string|null $feedback
     * @return $this
     */
    public function setFeedback($feedback);
}
