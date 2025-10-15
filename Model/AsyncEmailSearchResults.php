<?php
/**
 * Copyright (c) 2020-2025. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\AsynchronousEmailSending\Model;

use Hryvinskyi\AsynchronousEmailSending\Api\Data\AsyncEmailSearchResultsInterface;
use Magento\Framework\Api\Search\SearchResult;

/**
 * Class AsyncEmailSearchResults
 */
class AsyncEmailSearchResults extends SearchResult implements AsyncEmailSearchResultsInterface
{
    /**
     * {@inheritdoc}
     */
    public function getItems(): array
    {
        return parent::getItems() ?? [];
    }

    /**
     * {@inheritdoc}
     */
    public function setItems(?array $items = null): AsyncEmailSearchResultsInterface
    {
        parent::setData(self::ITEMS, $items);

        return $this;
    }
}
