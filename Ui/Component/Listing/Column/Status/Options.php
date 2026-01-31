<?php
/**
 * Copyright (c) 2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\AsynchronousEmailSending\Ui\Component\Listing\Column\Status;

use Hryvinskyi\AsynchronousEmailSending\Api\Data\AsyncEmailInterface;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Status options for async email grid filter and column.
 */
class Options implements OptionSourceInterface
{
    /**
     * @var array<int, array{value: int, label: string}>|null
     */
    private ?array $options = null;

    /**
     * {@inheritDoc}
     *
     * @return array<int, array{value: int, label: string}>
     */
    public function toOptionArray(): array
    {
        if ($this->options === null) {
            $this->options = [
                [
                    'value' => AsyncEmailInterface::STATUS_PENDING,
                    'label' => __('Pending')
                ],
                [
                    'value' => AsyncEmailInterface::STATUS_SENT,
                    'label' => __('Sent')
                ],
                [
                    'value' => AsyncEmailInterface::STATUS_ERROR,
                    'label' => __('Error')
                ]
            ];
        }

        return $this->options;
    }
}
