<?php
/**
 * Copyright (c) 2020. Volodymyr Hryvinskyi.  All rights reserved.
 * @author: <mailto:volodymyr@hryvinskyi.com>
 * @github: <https://github.com/hryvinskyi>
 */

declare(strict_types=1);

namespace Hryvinskyi\AsynchronousEmailSending\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface AsyncEmailSearchResultsInterface
 *
 * @package Hryvinskyi\AsynchronousEmailSending\Api\Data
 */
interface AsyncEmailSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get AsyncEmail list.
     *
     * @return \Hryvinskyi\AsynchronousEmailSending\Api\Data\AsyncEmailInterface[]
     */
    public function getItems(): array;

    /**
     * Set AsyncEmail list.
     *
     * @param \Hryvinskyi\AsynchronousEmailSending\Api\Data\AsyncEmailInterface[] $items
     *
     * @return $this
     */
    public function setItems(array $items): self;
}
