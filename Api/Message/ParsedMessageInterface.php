<?php
/**
 * Copyright (c) 2025. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\AsynchronousEmailSending\Api\Message;

use Symfony\Component\Mime\Header\Headers;

/**
 * Interface for a fully parsed email message
 *
 * Contains parsed headers and an array of decoded message parts.
 * This data structure is ready for conversion to Symfony Message format.
 */
interface ParsedMessageInterface
{
    /**
     * Get message headers
     *
     * @return Headers Symfony Headers object with all parsed headers
     */
    public function getHeaders(): Headers;

    /**
     * Set message headers
     *
     * @param Headers $headers
     * @return void
     */
    public function setHeaders(Headers $headers): void;

    /**
     * Get parsed message parts
     *
     * @return ParsedMessagePartInterface[] Array of decoded message parts
     */
    public function getParts(): array;

    /**
     * Set parsed message parts
     *
     * @param ParsedMessagePartInterface[] $parts
     * @return void
     */
    public function setParts(array $parts): void;

    /**
     * Check if message has multiple parts
     *
     * @return bool True if message contains more than one part
     */
    public function hasMultipleParts(): bool;

    /**
     * Check if message has any parts
     *
     * @return bool True if message contains at least one part
     */
    public function hasParts(): bool;
}