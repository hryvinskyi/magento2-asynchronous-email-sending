<?php
/**
 * Copyright (c) 2020-2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
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
    public const int STATUS_PENDING = 0;
    public const int STATUS_SENT = 1;
    public const int STATUS_ERROR = 2;

    /**#@+
     * Constants for keys of data array.
     */
    public const string ENTITY_ID = 'entity_id';
    public const string STATUS = 'status';
    public const string SUBJECT = 'subject';
    public const string RAW_MESSAGE = 'raw_message';
    public const string CREATED_AT = 'created_at';
    public const string SENT_AT = 'sent_at';
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
     * @param int $entityId
     *
     * @return $this
     */
    public function setEntId(int $entityId): AsyncEmailInterface;

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
     * @param string $rawMessage
     *
     * @return $this
     */
    public function setRawMessage(string $rawMessage): AsyncEmailInterface;

    /**
     * Get CreatedAt value
     *
     * @return string
     */
    public function getCreatedAt(): string;

    /**
     * Set CreatedAt value. Mysql Date Time Format: Y-m-d H:i:s
     *
     * @param string $createdAt
     *
     * @return $this
     */
    public function setCreatedAt(string $createdAt): AsyncEmailInterface;

    /**
     * Get SentAt value
     *
     * @return string
     */
    public function getSentAt(): string;

    /**
     * Set SentAt value. Mysql Date Time Format: Y-m-d H:i:s
     *
     * @param string $sentAt
     *
     * @return $this
     */
    public function setSentAt(string $sentAt): AsyncEmailInterface;
}
