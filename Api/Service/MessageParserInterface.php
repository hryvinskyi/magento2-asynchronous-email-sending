<?php
/**
 * Copyright (c) 2025. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\AsynchronousEmailSending\Api\Service;

use Hryvinskyi\AsynchronousEmailSending\Api\Message\ParsedMessageInterface;

/**
 * Interface for parsing raw email messages
 *
 * Responsibility: Parse raw MIME messages, decode content, and create ParsedMessage objects
 */
interface MessageParserInterface
{
    /**
     * Parse raw message string into structured data
     *
     * @param string $rawMessage Raw email message string
     * @param string $EOL End of line character (default: \n)
     * @return array{headers: string, body: string, headersEOL: string}|null Parsed message data or null on failure
     */
    public function parseRawMessage(string $rawMessage, string $EOL = "\n"): ?array;

    /**
     * Parse raw message string and create ParsedMessage object
     *
     * Parses headers, decodes MIME parts, and returns a fully parsed message
     * ready for conversion to Symfony Message format.
     *
     * @param string $rawMessage Raw message string to parse
     * @return ParsedMessageInterface Parsed message with headers and decoded parts
     * @throws \ParseError If message parsing fails
     */
    public function parse(string $rawMessage): ParsedMessageInterface;
}