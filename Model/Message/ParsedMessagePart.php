<?php
/**
 * Copyright (c) 2025. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\AsynchronousEmailSending\Model\Message;

use Hryvinskyi\AsynchronousEmailSending\Api\Message\ParsedMessagePartInterface;

/**
 * Value object representing a single parsed MIME part
 *
 * Immutable data structure containing decoded content from a MIME message part.
 * Used for transferring parsed data from MessageParser to MessagePopulator.
 */
class ParsedMessagePart implements ParsedMessagePartInterface
{
    private string $content;
    private string $contentType;
    private string $charset;
    private ?string $filename;
    private bool $isAttachment;

    /**
     * @param string $content Decoded content (HTML, text, or binary data)
     * @param string $contentType Content type (e.g., 'text/html', 'text/plain', 'image/png')
     * @param string $charset Character encoding (e.g., 'utf-8', 'iso-8859-1')
     * @param string|null $filename Filename for attachments, null for text parts
     * @param bool $isAttachment Whether this part is an attachment
     */
    public function __construct(
        string $content,
        string $contentType,
        string $charset = 'utf-8',
        ?string $filename = null,
        bool $isAttachment = false
    ) {
        $this->content = $content;
        $this->contentType = $contentType;
        $this->charset = $charset;
        $this->filename = $filename;
        $this->isAttachment = $isAttachment;
    }

    /**
     * @inheritDoc
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @inheritDoc
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    /**
     * @inheritDoc
     */
    public function getContentType(): string
    {
        return $this->contentType;
    }

    /**
     * @inheritDoc
     */
    public function setContentType(string $contentType): void
    {
        $this->contentType = $contentType;
    }

    /**
     * @inheritDoc
     */
    public function getCharset(): string
    {
        return $this->charset;
    }

    /**
     * @inheritDoc
     */
    public function setCharset(string $charset): void
    {
        $this->charset = $charset;
    }

    /**
     * @inheritDoc
     */
    public function getFilename(): ?string
    {
        return $this->filename;
    }

    /**
     * @inheritDoc
     */
    public function setFilename(?string $filename): void
    {
        $this->filename = $filename;
    }

    /**
     * @inheritDoc
     */
    public function isAttachment(): bool
    {
        return $this->isAttachment;
    }

    /**
     * @inheritDoc
     */
    public function setIsAttachment(bool $isAttachment): void
    {
        $this->isAttachment = $isAttachment;
    }

    /**
     * @inheritDoc
     */
    public function isHtml(): bool
    {
        return str_starts_with(strtolower($this->contentType), 'text/html');
    }

    /**
     * @inheritDoc
     */
    public function isText(): bool
    {
        return str_starts_with(strtolower($this->contentType), 'text/plain');
    }
}