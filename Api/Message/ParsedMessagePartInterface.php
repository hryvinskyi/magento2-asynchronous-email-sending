<?php
/**
 * Copyright (c) 2025. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\AsynchronousEmailSending\Api\Message;

/**
 * Interface for a parsed MIME message part
 *
 * Represents a single decoded part from a MIME message (HTML, text, or attachment).
 * This is an immutable value object for transferring parsed data.
 */
interface ParsedMessagePartInterface
{
    /**
     * Get decoded content
     *
     * @return string Decoded content (HTML, text, or binary data)
     */
    public function getContent(): string;

    /**
     * Set decoded content
     *
     * @param string $content Decoded content (HTML, text, or binary data)
     * @return void
     */
    public function setContent(string $content): void;

    /**
     * Get content type
     *
     * @return string Content type (e.g., 'text/html', 'text/plain', 'image/png')
     */
    public function getContentType(): string;

    /**
     * Set content type
     *
     * @param string $contentType Content type (e.g., 'text/html', 'text/plain', 'image/png')
     * @return void
     */
    public function setContentType(string $contentType): void;

    /**
     * Get charset
     *
     * @return string Character encoding (e.g., 'utf-8', 'iso-8859-1')
     */
    public function getCharset(): string;

    /**
     * Set charset
     *
     * @param string $charset Character encoding (e.g., 'utf-8', 'iso-8859-1')
     * @return void
     */
    public function setCharset(string $charset): void;

    /**
     * Get filename for attachments
     *
     * @return string|null Filename if this is an attachment, null otherwise
     */
    public function getFilename(): ?string;

    /**
     * Set filename for attachments
     *
     * @param string|null $filename Filename if this is an attachment, null otherwise
     * @return void
     */
    public function setFilename(?string $filename): void;

    /**
     * Check if this part is an attachment
     *
     * @return bool True if this part is an attachment
     */
    public function isAttachment(): bool;

    /**
     * Set whether this part is an attachment
     *
     * @param bool $isAttachment True if this part is an attachment
     * @return void
     */
    public function setIsAttachment(bool $isAttachment): void;

    /**
     * Check if this part contains HTML content
     *
     * @return bool True if content type is text/html
     */
    public function isHtml(): bool;

    /**
     * Check if this part contains plain text content
     *
     * @return bool True if content type is text/plain
     */
    public function isText(): bool;
}