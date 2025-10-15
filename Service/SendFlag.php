<?php
/**
 * Copyright (c) 2020-2025. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\AsynchronousEmailSending\Service;

/**
 * Service to track if emails are currently being sent (prevents recursion)
 */
class SendFlag
{
    /**
     * @var bool Flag indicating if email sending is in progress
     */
    private bool $isSending = false;

    /**
     * @return bool
     */
    public function isSending(): bool
    {
        return $this->isSending;
    }

    /**
     * @param bool $isSending
     */
    public function setIsSending(bool $isSending): void
    {
        $this->isSending = $isSending;
    }
}
