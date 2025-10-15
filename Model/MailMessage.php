<?php
/**
 * Copyright (c) 2020-2025. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\AsynchronousEmailSending\Model;

use Magento\Framework\Mail\EmailMessage;
use Symfony\Component\Mime\Message as SymfonyMessage;

/**
 * Extended EmailMessage for asynchronous email sending
 *
 * Supports both standard construction and direct Symfony message injection
 * to avoid duplicate header issues when reconstructing emails from raw messages.
 */
class MailMessage extends EmailMessage
{
    private string $rawMessage = '';

    /**
     * Set raw email message content
     *
     * @param string $rawMessage Raw email message
     * @return MailMessage
     */
    public function setRawMessage(string $rawMessage): MailMessage
    {
        $this->rawMessage = $rawMessage;

        return $this;
    }

    /**
     * Get raw email message content
     *
     * @return string Raw email message
     */
    public function getRawMessage(): string
    {
        return $this->rawMessage;
    }

    /**
     * Set Symfony message directly, bypassing normal header construction
     *
     * This method allows injecting a pre-built Symfony message
     *
     * @param SymfonyMessage $message Symfony message
     * @return void
     */
    public function setSymfonyMessage(SymfonyMessage $message): void
    {
        $this->symfonyMessage = $message;
    }
}
