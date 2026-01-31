<?php
/**
 * Copyright (c) 2020-2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
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
    public function setEntId(int $entityId): AsyncEmailInterface
    {
        $this->setData(self::ENTITY_ID, $entityId);

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
    public function setRawMessage(string $rawMessage): AsyncEmailInterface
    {
        $this->setData(self::RAW_MESSAGE, $rawMessage);

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
    public function setCreatedAt(string $createdAt): AsyncEmailInterface
    {
        $this->setData(self::CREATED_AT, $createdAt);

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
    public function setSentAt(string $sentAt): AsyncEmailInterface
    {
        $this->setData(self::SENT_AT, $sentAt);

        return $this;
    }
}
