<?php

namespace SamedayCourier\Shipping\Model\ResourceModel;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\NoSuchEntityException;
use SamedayCourier\Shipping\Api\Data\AwbInterface;
use SamedayCourier\Shipping\Api\AwbRepositoryInterface;
use SamedayCourier\Shipping\Model\AwbFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AwbRepository implements AwbRepositoryInterface
{
    /**
     * @var AwbFactory
     */
    private $awbFactory;

    /**
     * @var Awb
     */
    private $awbResourceModel;

    /**
     * AwbRepository constructor.
     *
     * @param AwbFactory $awbFactory
     * @param Awb $awbResourceModel
     */
    public function __construct(
        AwbFactory $awbFactory,
        Awb $awbResourceModel
    ) {
        $this->awbFactory = $awbFactory;
        $this->awbResourceModel = $awbResourceModel;
    }

    /**
     * @inheritdoc
     */
    public function getByOrderId($id)
    {
        $awbModel = $this->awbFactory->create();
        $this->awbResourceModel->load($awbModel, $id, AwbInterface::ORDER_ID);

        if (!$awbModel->getId()) {
            throw NoSuchEntityException::singleField(AwbInterface::ORDER_ID, $id);
        }

        return $awbModel->getDataModel();
    }

    /**
     * @throws AlreadyExistsException
     */
    public function save(AwbInterface $awb): void
    {
        $awbModel = $this->awbFactory->create();
        if ($awb->getId()) {
            $this->awbResourceModel->load($awbModel, $awb->getId());
        }

        $awbModel->updateData($awb);

        $this->awbResourceModel->save($awbModel);
    }

    /**
     * @throws \Exception
     */
    public function delete(AwbInterface $awb)
    {
        return $this->deleteById($awb->getId());
    }

    /**
     * @throws \Exception
     */
    public function deleteById($id)
    {
        $awbModel = $this->awbFactory->create();
        $this->awbResourceModel->load($awbModel, $id);

        if (!$awbModel->getId()) {
            return false;
        }

        $this->awbResourceModel->delete($awbModel);

        return true;
    }
}
