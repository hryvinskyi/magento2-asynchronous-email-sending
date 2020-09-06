<?php
/**
 * Copyright (c) 2020. Volodymyr Hryvinskyi.  All rights reserved.
 * @author: <mailto:volodymyr@hryvinskyi.com>
 * @github: <https://github.com/hryvinskyi>
 */

declare(strict_types=1);

namespace Hryvinskyi\AsynchronousEmailSending\Model\ResourceModel\AsyncEmail;

use Hryvinskyi\AsynchronousEmailSending\Model\AsyncEmail;
use Hryvinskyi\AsynchronousEmailSending\Model\ResourceModel\AsyncEmail as AsyncEmailResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 *
 * @method AsyncEmailResource getResource()
 * @method AsyncEmail[] getItems()
 * @method AsyncEmail[] getItemsByColumnValue($column, $value)
 * @method AsyncEmail getFirstItem()
 * @method AsyncEmail getLastItem()
 * @method AsyncEmail getItemByColumnValue($column, $value)
 * @method AsyncEmail getItemById($idValue)
 * @method AsyncEmail getNewEmptyItem()
 * @method AsyncEmail fetchItem()
 * @property AsyncEmail[] _items
 * @property AsyncEmailResource _resource
 */
class Collection extends AbstractCollection
{
    /**
     * @inheritdoc
     */
    protected $_eventPrefix = 'hryvinskyi_asynchronous_email_sending_collection';

    /**
     * @inheritdoc
     */
    protected $_eventObject = 'object';

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(AsyncEmail::class, AsyncEmailResource::class);
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
