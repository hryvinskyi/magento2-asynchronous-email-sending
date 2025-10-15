<?php
/**
 * Copyright (c) 2025. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\AsynchronousEmailSending\Api\Service;

use Hryvinskyi\AsynchronousEmailSending\Api\Message\ParsedMessageInterface;
use Hryvinskyi\AsynchronousEmailSending\Model\MailMessage;

/**
 * Interface for creating MailMessage from parsed message data
 *
 * Responsibility: Convert ParsedMessage objects into Symfony Message format
 */
interface MessagePopulatorInterface
{
    /**
     * Create MailMessage from ParsedMessage
     *
     * Converts a parsed message with decoded parts into a Magento MailMessage
     * object with proper Symfony MIME structure.
     *
     * @param ParsedMessageInterface $parsedMessage Parsed message with headers and decoded parts
     * @return MailMessage Populated Magento mail message
     * @throws \InvalidArgumentException If required headers are missing
     */
    public function createMailMessage(ParsedMessageInterface $parsedMessage): MailMessage;
}