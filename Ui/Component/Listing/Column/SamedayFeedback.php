<?php

namespace SamedayCourier\Shipping\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use SamedayCourier\Shipping\Helper\BulkAwbService;

class SamedayFeedback extends Column
{
    /**
     * @var BulkAwbService
     */
    private $bulkAwbService;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        BulkAwbService $bulkAwbService,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->bulkAwbService = $bulkAwbService;
    }

    public function prepare()
    {
        parent::prepare();
        $config = (array) $this->getData('config');
        $fieldClass = isset($config['fieldClass']) && is_array($config['fieldClass'])
            ? $config['fieldClass']
            : [];
        $fieldClass['col-sameday_feedback'] = true;
        $config['fieldClass'] = $fieldClass;
        // Prevent Magento's default row "view order" fieldAction on this cell.
        $config['fieldAction'] = false;
        $this->setData('config', $config);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$item) {
            $orderId = (int) ($item['entity_id'] ?? 0);
            try {
                $item[$this->getData('name')] = $orderId
                    ? $this->bulkAwbService->formatFeedbackHtml($orderId)
                    : '—';
            } catch (\Exception $e) {
                $item[$this->getData('name')] = '—';
            }
        }

        return $dataSource;
    }
}
