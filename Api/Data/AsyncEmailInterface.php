<?php
/**
 * Copyright (c) 2020. Volodymyr Hryvinskyi.  All rights reserved.
 * @author: <mailto:volodymyr@hryvinskyi.com>
 * @github: <https://github.com/hryvinskyi>
 */

declare(strict_types=1);

namespace Hryvinskyi\AsynchronousEmailSending\Api\Data;

/**
 * Interface AsyncEmail
 *
 * @package Hryvinskyi\AsynchronousEmailSending\Api\Data
 */
interface AsyncEmailInterface
{
    /**
     * Statuses
     */
    const STATUS_PENDING = 0;
    const STATUS_SENT = 1;
    const STATUS_ERROR = 2;

    /**#@+
     * Constants for keys of data array.
     */
    const ENTITY_ID = 'entity_id';
    const STATUS = 'status';
    const SUBJECT = 'subject';
    const RAW_MESSAGE = 'raw_message';
    const CREATED_AT = 'created_at';
    const SENT_AT = 'sent_at';
    /**#@-*/

    /**
     * Get EntityId value
     *
     * @return int
     */
    public function getEntId(): int;

    /**
     * Set EntityId value
     *
     * @param int $entity_id
     *
     * @return $this
     */
    public function setEntId(int $entity_id): AsyncEmailInterface;

    /**
     * Get Status value
     *
     * @return int
     */
    public function getStatus(): int;

    /**
     * Set Status value
     *
     * @param int $status
     *
     * @return $this
     */
    public function setStatus(int $status): AsyncEmailInterface;

    /**
     * Get Subject value
     *
     * @return string
     */
    public function getSubject(): string;

    /**
     * Set Subject value
     *
     * @param string $subject
     *
     * @return $this
     */
    public function setSubject(string $subject): AsyncEmailInterface;

    /**
     * Get RawMessage value
     *
     * @return string
     */
    public function getRawMessage(): string;

    /**
     * Set RawMessage value
     *
     * @param string $raw_message
     *
     * @return $this
     */
    public function setRawMessage(string $raw_message): AsyncEmailInterface;

    /**
     * Get CreatedAt value
     *
     * @return string
     */
    public function getCreatedAt(): string;

    /**
     * Set CreatedAt value. Mysql Date Time Format: Y-m-d h:i:s
     *
     * @param string $created_at
     *
     * @return $this
     */
    public function setCreatedAt(string $created_at): AsyncEmailInterface;

    /**
     * Get SentAt value
     *
     * @return string
     */
    public function getSentAt(): string;

    /**
     * Set SentAt value. Mysql Date Time Format: Y-m-d h:i:s
     *
     * @param string $sent_at
     *
     * @return $this
     */
    public function setSentAt(string $sent_at): AsyncEmailInterface;
}
