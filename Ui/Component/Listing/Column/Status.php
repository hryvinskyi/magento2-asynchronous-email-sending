<?php
/**
 * Copyright (c) 2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\AsynchronousEmailSending\Ui\Component\Listing\Column;

use Hryvinskyi\AsynchronousEmailSending\Api\Data\AsyncEmailInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Status column with colored badges for async email grid.
 */
class Status extends Column
{
    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array<string, mixed> $components
     * @param array<string, mixed> $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare data source with status styling.
     *
     * @param array<string, mixed> $dataSource
     * @return array<string, mixed>
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['status'])) {
                    $item['status_class'] = $this->getStatusClass((int)$item['status']);
                    $item['status_label'] = $this->getStatusLabel((int)$item['status']);
                }
            }
        }

        return $dataSource;
    }

    /**
     * Get CSS class for status styling.
     *
     * @param int $status
     * @return string
     */
    private function getStatusClass(int $status): string
    {
        return match ($status) {
            AsyncEmailInterface::STATUS_PENDING => 'grid-severity-minor',
            AsyncEmailInterface::STATUS_SENT => 'grid-severity-notice',
            AsyncEmailInterface::STATUS_ERROR => 'grid-severity-critical',
            default => 'grid-severity-minor'
        };
    }

    /**
     * Get label for status.
     *
     * @param int $status
     * @return string
     */
    private function getStatusLabel(int $status): string
    {
        return match ($status) {
            AsyncEmailInterface::STATUS_PENDING => (string)__('Pending'),
            AsyncEmailInterface::STATUS_SENT => (string)__('Sent'),
            AsyncEmailInterface::STATUS_ERROR => (string)__('Error'),
            default => (string)__('Unknown')
        };
    }
}
