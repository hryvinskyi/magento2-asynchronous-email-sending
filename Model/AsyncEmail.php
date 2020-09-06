<?php
/**
 * Copyright (c) 2020. Volodymyr Hryvinskyi.  All rights reserved.
 * @author: <mailto:volodymyr@hryvinskyi.com>
 * @github: <https://github.com/hryvinskyi>
 */

declare(strict_types=1);

namespace Hryvinskyi\AsynchronousEmailSending\Model;

use Hryvinskyi\AsynchronousEmailSending\Api\Data\AsyncEmailInterface;
use Hryvinskyi\AsynchronousEmailSending\Model\ResourceModel\AsyncEmail as AsyncEmailResource;
use Hryvinskyi\AsynchronousEmailSending\Model\ResourceModel\AsyncEmail\Collection as CollectionCollection;
use Magento\Framework\Model\AbstractModel;

/**
 * Class AsyncEmail
 *
 * @method AsyncEmailResource getResource()
 * @method CollectionCollection getCollection()
 * @method CollectionCollection getResourceCollection()
 *
 * @package Hryvinskyi\AsynchronousEmailSending\Model
 */
class AsyncEmail extends AbstractModel implements AsyncEmailInterface
{
    /**
     * @inheritdoc
     */
    protected $_eventPrefix = 'hryvinskyi_asynchronous_email_sending_model';

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(AsyncEmailResource::class);
    }

    /**
     * @inheritdoc
     */
    public function getEntId(): int
    {
        return (int)$this->_getData(self::ENTITY_ID);
    }

    /**
     * @inheritdoc
     */
    public function setEntId(int $entity_id): AsyncEmailInterface
    {
        $this->setData(self::ENTITY_ID, $entity_id);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStatus(): int
    {
        return (int)$this->_getData(self::STATUS);
    }

    /**
     * @inheritdoc
     */
    public function setStatus(int $status): AsyncEmailInterface
    {
        $this->setData(self::STATUS, $status);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSubject(): string
    {
        return (string)$this->_getData(self::SUBJECT);
    }

    /**
     * @inheritdoc
     */
    public function setSubject(string $subject): AsyncEmailInterface
    {
        $this->setData(self::SUBJECT, $subject);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRawMessage(): string
    {
        return (string)$this->_getData(self::RAW_MESSAGE);
    }

    /**
     * @inheritdoc
     */
    public function setRawMessage(string $raw_message): AsyncEmailInterface
    {
        $this->setData(self::RAW_MESSAGE, $raw_message);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCreatedAt(): string
    {
        return (string)$this->_getData(self::CREATED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setCreatedAt(string $created_at): AsyncEmailInterface
    {
        $this->setData(self::CREATED_AT, $created_at);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSentAt(): string
    {
        return (string)$this->_getData(self::SENT_AT);
    }

    /**
     * @inheritdoc
     */
    public function setSentAt(string $sent_at): AsyncEmailInterface
    {
        $this->setData(self::SENT_AT, $sent_at);

        return $this;
    }
}
