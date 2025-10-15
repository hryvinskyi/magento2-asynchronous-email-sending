<?php
/**
 * Copyright (c) 2025. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\AsynchronousEmailSending\Model\Message;

use Hryvinskyi\AsynchronousEmailSending\Api\Message\ParsedMessageInterface;
use Hryvinskyi\AsynchronousEmailSending\Api\Message\ParsedMessagePartInterface;
use Symfony\Component\Mime\Header\Headers;

/**
 * Data structure representing a fully parsed email message
 *
 * Contains parsed headers and decoded message parts ready for
 * conversion to Symfony Message format.
 */
class ParsedMessage implements ParsedMessageInterface
{
    private Headers $headers;

    /**
     * @var ParsedMessagePartInterface[]
     */
    private array $parts = [];

    /**
     * @param Headers $headers Parsed Symfony headers
     * @param ParsedMessagePartInterface[] $parts Decoded message parts
     */
    public function __construct(
        Headers $headers,
        array $parts = []
    ) {
        $this->headers = $headers;
        $this->parts = $parts;
    }

    /**
     * @inheritDoc
     */
    public function getHeaders(): Headers
    {
        return $this->headers;
    }

    /**
     * @inheritDoc
     */
    public function setHeaders(Headers $headers): void
    {
        $this->headers = $headers;
    }

    /**
     * @inheritDoc
     */
    public function getParts(): array
    {
        return $this->parts;
    }

    /**
     * @inheritDoc
     */
    public function setParts(array $parts): void
    {
        $this->parts = $parts;
    }

    /**
     * @inheritDoc
     */
    public function hasMultipleParts(): bool
    {
        return count($this->parts) > 1;
    }

    /**
     * @inheritDoc
     */
    public function hasParts(): bool
    {
        return !empty($this->parts);
    }
}