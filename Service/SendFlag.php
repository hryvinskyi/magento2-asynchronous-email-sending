<?php
/**
 * Copyright (c) 2020. Volodymyr Hryvinskyi.  All rights reserved.
 * @author: <mailto:volodymyr@hryvinskyi.com>
 * @github: <https://github.com/hryvinskyi>
 */

declare(strict_types=1);

namespace Hryvinskyi\AsynchronousEmailSending\Service;

/**
 * Class SendFlag
 */
class SendFlag
{
    /**
     * @var bool
     */
    private $isSending = false;

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
