<?php
/**
 * Copyright (c) 2020. Volodymyr Hryvinskyi.  All rights reserved.
 * @author: <mailto:volodymyr@hryvinskyi.com>
 * @github: <https://github.com/hryvinskyi>
 */

declare(strict_types=1);

namespace Hryvinskyi\AsynchronousEmailSending\Model\ResourceModel;

use Hryvinskyi\AsynchronousEmailSending\Api\Data\AsyncEmailInterface;
use Hryvinskyi\AsynchronousEmailSending\Exception\InvalidStatusException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class AsyncEmail
 *
 * @package Hryvinskyi\AsynchronousEmailSending\Model\ResourceModel
 */
class AsyncEmail extends AbstractDb
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('hryvinskyi_asynchronous_email_sending', 'entity_id');
    }

    /**
     * @inheritDoc
     * @throws InvalidStatusException
     */
    public function clear(int $days, int $status): void
    {
        if (in_array($status, [AsyncEmailInterface::STATUS_ERROR, AsyncEmailInterface::STATUS_SENT]) === false) {
            throw new InvalidStatusException(__('Status invalid'));
        }

        $date = date('Y-m-d h:i:s', strtotime('-' . $days . 'days'));

        $this->getConnection()->delete(
            $this->getMainTable(),
            [
                AsyncEmailInterface::CREATED_AT . ' <= ?' => $date,
                AsyncEmailInterface::STATUS . ' = ?' => $status
            ]
        );
    }
}
