<?php
/**
 * Copyright (c) 2020-2025. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\AsynchronousEmailSending\Model\Service;

use Hryvinskyi\AsynchronousEmailSending\Api\Service\MessageParserInterface;
use Hryvinskyi\AsynchronousEmailSending\Api\Message\ParsedMessageInterface;
use Hryvinskyi\AsynchronousEmailSending\Api\Message\ParsedMessageInterfaceFactory;
use Hryvinskyi\AsynchronousEmailSending\Api\Message\ParsedMessagePartInterface;
use Hryvinskyi\AsynchronousEmailSending\Api\Message\ParsedMessagePartInterfaceFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mime\Header\Headers;

/**
 * Service for parsing raw email messages
 *
 * Responsibility: Parse raw MIME messages, decode transfer encodings,
 * and create ParsedMessage objects with decoded content.
 *
 * Based on Laminas\Mail\Headers implementations.
 */
class MessageParser implements MessageParserInterface
{
    public function __construct(
        private readonly ParsedMessageInterfaceFactory $parsedMessageFactory,
        private readonly ParsedMessagePartInterfaceFactory $parsedMessagePartFactory,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @inheritDoc
     */
    public function parseRawMessage(string $rawMessage, string $EOL = "\n"): ?array
    {
        try {
            // Check for valid header at first line
            $firstLinePos = strpos($rawMessage, "\n");
            $firstLine = $firstLinePos === false ? $rawMessage : substr($rawMessage, 0, $firstLinePos);

            if (!preg_match('%^[^\s]+[^:]*:%', $firstLine)) {
                // No valid headers, entire message is body
                $body = str_replace(["\r", "\n"], ['', $EOL], $rawMessage);
                return [
                    'headers' => '',
                    'body' => $body,
                    'headersEOL' => $EOL
                ];
            }

            $headersEOL = $EOL;
            $body = '';

            // Find an empty line between headers and body
            if (str_contains($rawMessage, $EOL . $EOL)) {
                [$headers, $body] = explode($EOL . $EOL, $rawMessage, 2);
            } elseif ($EOL !== "\r\n" && str_contains($rawMessage, "\r\n\r\n")) {
                [$headers, $body] = explode("\r\n\r\n", $rawMessage, 2);
                $headersEOL = "\r\n";
            } elseif ($EOL !== "\n" && str_contains($rawMessage, "\n\n")) {
                [$headers, $body] = explode("\n\n", $rawMessage, 2);
                $headersEOL = "\n";
            } else {
                // At last resort find anything that looks like a new line
                $parts = preg_split("%([\r\n]+)\\1%U", $rawMessage, 2);
                if (is_array($parts) && count($parts) === 2) {
                    [$headers, $body] = $parts;
                } else {
                    // No body separator found, treat everything as headers
                    $headers = $rawMessage;
                }
            }

            return [
                'headers' => $headers,
                'body' => $body,
                'headersEOL' => $headersEOL
            ];
        } catch (\Throwable $e) {
            $this->logger->error('Failed to parse raw message: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * @inheritDoc
     */
    public function parse(string $rawMessage): ParsedMessageInterface
    {
        $parsed = $this->parseRawMessage($rawMessage);

        if ($parsed === null) {
            throw new \ParseError("Failed to parse raw message");
        }

        // Parse headers
        $headers = $this->parseHeaders($parsed['headers'], $parsed['headersEOL']);

        // Normalize line endings
        $body = $this->normalizeLineEndings($parsed['body'], true);

        // Parse multipart body if boundary is present
        $parsedParts = [];
        if ($headers->has('Content-Type')) {
            $contentType = $headers->get('Content-Type')->getBodyAsString();
            if (preg_match('/boundary="?([^\";\\r\\n]+)"?/', $contentType, $matches)) {
                $boundary = $matches[1];
                $parsedParts = $this->parseMultipartBodyIntoParts($body, $boundary);
            }
        }

        // If no multipart, create single part from body
        if (empty($parsedParts) && !empty($body)) {
            $encoding = '';
            if ($headers->has('Content-Transfer-Encoding')) {
                $encoding = $headers->get('Content-Transfer-Encoding')->getBodyAsString();
            }

            $decodedBody = $this->decodeContent($body, $encoding);
            $contentType = 'text/html';
            $charset = 'utf-8';

            if ($headers->has('Content-Type')) {
                $ct = $headers->get('Content-Type')->getBodyAsString();
                if (preg_match('/^([^;\s]+)/i', $ct, $matches)) {
                    $contentType = trim($matches[1]);
                }
                if (preg_match('/charset="?([^";\s]+)"?/i', $ct, $matches)) {
                    $charset = trim($matches[1]);
                }
            }
            /** @var ParsedMessagePartInterface $part */
            $part = $this->parsedMessagePartFactory->create();
            $part->setContent($decodedBody);
            $part->setContentType($contentType);
            $part->setCharset($charset);
            $parsedParts[] = $part;
        }

        /** @var ParsedMessageInterface $parsedMessage */
        $parsedMessage = $this->parsedMessageFactory->create();
        $parsedMessage->setHeaders($headers);
        $parsedMessage->setParts($parsedParts);
        return $parsedMessage;
    }

    /**
     * Decode content based on transfer encoding
     *
     * Supports quoted-printable, base64, 7bit, 8bit, binary
     *
     * @param string $content Encoded content
     * @param string $encoding Transfer encoding type
     * @return string Decoded content
     */
    private function decodeContent(string $content, string $encoding): string
    {
        return match ($encoding) {
            'quoted-printable' => quoted_printable_decode($content),
            'base64' => base64_decode($content, true) ?: $content,
            default => $content,
        };
    }

    /**
     * Parse header string into Headers object
     *
     * Based on Laminas\Mail\Headers::fromString() implementation
     * with proper handling of continuations and malformed headers
     *
     * @param string $headersString Raw headers string
     * @param string $EOL End of line character
     * @return Headers Symfony Headers object
     */
    private function parseHeaders(string $headersString, string $EOL): Headers
    {
        $headers = new Headers();

        if (empty($headersString)) {
            return $headers;
        }

        $currentLine = '';
        $emptyLine = 0;

        // Iterate the header lines, some might be continuations
        $lines = explode($EOL, $headersString);
        $total = count($lines);

        for ($i = 0; $i < $total; $i++) {
            $line = $lines[$i];

            if ($line === '') {
                // Empty line indicates end of headers
                // EXCEPT if there are more lines, in which case, there's a possible error condition
                $emptyLine++;
                if ($emptyLine > 2) {
                    $this->logger->warning('Malformed header detected: too many empty lines');
                    break;
                }
                continue;
            }

            if (preg_match('/^\s*$/', $line)) {
                // Skip empty continuation line
                continue;
            }

            if ($emptyLine > 1) {
                $this->logger->warning('Malformed header detected: empty lines in headers');
                break;
            }

            // Check if a header name is present (RFC 5322 field-name pattern)
            if (preg_match('/^[\x21-\x39\x3B-\x7E]+:.*$/', $line)) {
                if ($currentLine !== '') {
                    // A header name was present, then store the current complete line
                    $this->addHeaderLine($headers, $currentLine);
                }
                $currentLine = trim($line);
                continue;
            }

            // Continuation: append to current line
            // Recover the whitespace that breaks the line (unfolding, RFC 2822 section 2.2.3)
            if (preg_match('/^\s+.*$/', $line)) {
                $currentLine .= ' ' . trim($line);
                continue;
            }

            // Line does not match header format!
            $this->logger->warning(
                sprintf('Line "%s" does not match header format, skipping', $line)
            );
        }

        // Add the last header if exists
        if ($currentLine !== '') {
            $this->addHeaderLine($headers, $currentLine);
        }

        return $headers;
    }

    /**
     * Add a complete header line to Headers object
     *
     * Parses "Header-Name: value" format and adds to headers
     * Based on Laminas\Mail\Headers::addHeaderLine() implementation
     *
     * @param Headers $headers Symfony Headers object
     * @param string $headerLine Complete header line (e.g., "Subject: Test Email")
     * @return void
     */
    private function addHeaderLine(Headers $headers, string $headerLine): void
    {
        // Load and parse the header line
        $parsedHeader = $this->loadHeader($headerLine);

        if ($parsedHeader === null) {
            return;
        }

        // Add the parsed header to the Headers collection using appropriate type
        try {
            $this->addHeaderByType($headers, $parsedHeader['name'], $parsedHeader['value']);
        } catch (\Throwable $e) {
            $this->logger->warning(
                sprintf('Failed to add header "%s": %s', $parsedHeader['name'], $e->getMessage())
            );
        }
    }

    /**
     * Add header using appropriate type based on header name
     *
     * Symfony Mime component requires specific header types:
     * - MailboxListHeader for: To, From, Cc, Bcc, Reply-To, Sender
     * - DateHeader for: Date
     * - IdentificationHeader for: Message-ID, References, In-Reply-To, Content-ID
     * - UnstructuredHeader for: Subject and other text headers
     *
     * @param Headers $headers Symfony Headers object
     * @param string $name Header name
     * @param string $value Header value
     * @return void
     * @throws \Throwable
     */
    private function addHeaderByType(Headers $headers, string $name, string $value): void
    {
        $normalizedName = strtolower($name);

        // Mailbox list headers
        $mailboxHeaders = ['to', 'from', 'cc', 'bcc', 'reply-to', 'sender'];
        if (in_array($normalizedName, $mailboxHeaders, true)) {
            $headers->addMailboxListHeader($name, $this->parseMailboxes($value));
            return;
        }

        // Date header
        if ($normalizedName === 'date') {
            $headers->addDateHeader($name, new \DateTimeImmutable($value));
            return;
        }

        // Identification headers
        $identificationHeaders = ['message-id', 'references', 'in-reply-to', 'content-id'];
        if (in_array($normalizedName, $identificationHeaders, true)) {
            $headers->addIdHeader($name, $this->parseMessageIds($value));
            return;
        }

        // Default to text header for all other headers
        $headers->addTextHeader($name, $value);
    }

    /**
     * Parse mailbox list from header value
     *
     * Parses email addresses from header values like:
     * - "user@example.com"
     * - "Name <user@example.com>"
     * - "user1@example.com, user2@example.com"
     * - "Name1 <user1@example.com>, Name2 <user2@example.com>"
     *
     * @param string $value Header value containing email addresses
     * @return array<string> Array of email addresses
     */
    private function parseMailboxes(string $value): array
    {
        $mailboxes = [];

        // Split by comma for multiple addresses
        $addresses = array_map('trim', explode(',', $value));

        foreach ($addresses as $address) {
            // Extract email from "Name <email>" format or use plain email
            if (preg_match('/<([^>]+)>/', $address, $matches)) {
                $mailboxes[] = trim($matches[1]);
            } elseif (!empty($address)) {
                $mailboxes[] = $address;
            }
        }

        return array_filter($mailboxes);
    }

    /**
     * Parse message IDs from header value
     *
     * Parses message IDs from headers like Message-ID, References, In-Reply-To
     * Format: "<id@domain>" or multiple "<id1@domain> <id2@domain>"
     *
     * @param string $value Header value containing message IDs
     * @return string|array<string> Single ID or array of IDs
     */
    private function parseMessageIds(string $value): string|array
    {
        // Extract all message IDs in <...> format
        if (preg_match_all('/<([^>]+)>/', $value, $matches)) {
            $ids = $matches[1];
            return count($ids) === 1 ? $ids[0] : $ids;
        }

        // No angle brackets found, return trimmed value
        $trimmed = trim($value, ' <>');
        return !empty($trimmed) ? $trimmed : $value;
    }

    /**
     * Load and parse a header line
     *
     * Parses a complete header line in "Header-Name: value" format
     * Based on Laminas\Mail\Headers::loadHeader() pattern
     *
     * @param string $headerLine Complete header line
     * @return array{name: string, value: string}|null Parsed header or null if invalid
     */
    private function loadHeader(string $headerLine): ?array
    {
        if (!str_contains($headerLine, ':')) {
            $this->logger->warning(
                sprintf('Invalid header line format (missing colon): "%s"', $headerLine)
            );
            return null;
        }

        // Split on first colon only
        [$name, $value] = explode(':', $headerLine, 2);
        $name = trim($name);
        $value = trim($value);

        // Validate header name
        if ($name === '') {
            $this->logger->warning('Header name cannot be empty');
            return null;
        }

        // Validate header name contains only valid characters (RFC 5322)
        if (!preg_match('/^[\x21-\x39\x3B-\x7E]+$/', $name)) {
            $this->logger->warning(
                sprintf('Invalid header name "%s": contains invalid characters', $name)
            );
            return null;
        }

        return [
            'name' => $name,
            'value' => $value
        ];
    }

    /**
     * Parse multipart MIME body into ParsedMessagePart objects
     *
     * @param string $body Raw multipart body with boundaries
     * @param string $boundary MIME boundary string
     * @return array<ParsedMessagePartInterface> Array of ParsedMessagePart objects
     */
    private function parseMultipartBodyIntoParts(string $body, string $boundary): array
    {
        $parts = [];

        // Split body into parts using boundary
        $rawParts = preg_split("/--" . preg_quote($boundary) . "(?:--|(?:\r\n|$))/", $body);

        // Process each part
        foreach ($rawParts as $rawPart) {
            if (empty(trim($rawPart))) {
                continue;
            }

            try {
                // Split part into headers and content
                if (!str_contains($rawPart, "\r\n\r\n")) {
                    continue;
                }

                [$partHeadersString, $partContent] = explode("\r\n\r\n", $rawPart, 2);

                // Parse part headers into array
                $partHeaders = $this->parsePartHeaders($partHeadersString);

                // Get content type
                $contentType = $partHeaders['content-type'] ?? 'text/html';
                $contentDisposition = $partHeaders['content-disposition'] ?? '';
                $contentTransferEncoding = $partHeaders['content-transfer-encoding'] ?? '';

                // Decode content based on transfer encoding
                $decodedContent = $this->decodeContent(trim($partContent), $contentTransferEncoding);

                // Extract charset
                $charset = 'utf-8';
                if (preg_match('/charset="?([^";\s]+)"?/i', $contentType, $matches)) {
                    $charset = trim($matches[1]);
                }

                // Extract MIME type
                $mimeType = $contentType;
                if (preg_match('/^([^;\s]+)/i', $contentType, $matches)) {
                    $mimeType = trim($matches[1]);
                }

                // Determine if attachment
                $isAttachment = $contentDisposition && str_starts_with(strtolower($contentDisposition), 'attachment');

                // Extract filename if attachment
                $filename = null;
                if ($isAttachment) {
                    if (preg_match('/filename=([^;]+)/', $contentDisposition, $matches)) {
                        $filename = trim($matches[1], '"');
                    } elseif (preg_match('/name=([^;]+)/', $contentType, $matches)) {
                        $filename = trim($matches[1], '"');
                    }
                }

                /** @var ParsedMessagePartInterface $part */
                $part = $this->parsedMessagePartFactory->create();
                $part->setContent($decodedContent);
                $part->setContentType($mimeType);
                $part->setCharset($charset);
                $part->setFilename($filename);
                $part->setIsAttachment($isAttachment);

                $parts[] = $part;
            } catch (\Throwable $e) {
                $this->logger->warning(
                    sprintf('Failed to parse MIME part: %s', $e->getMessage())
                );
            }
        }

        return $parts;
    }

    /**
     * Parse part headers into associative array
     *
     * @param string $headersString Raw headers string
     * @return array<string, string> Associative array of headers
     */
    private function parsePartHeaders(string $headersString): array
    {
        $lines = explode("\r\n", $headersString);
        $currentHeader = null;
        $headers = [];

        foreach ($lines as $line) {
            // If line starts with whitespace, it's a continuation of the previous header
            if (preg_match('/^[ \t]/', $line)) {
                if ($currentHeader) {
                    $headers[$currentHeader] .= ' ' . trim($line);
                }
                continue;
            }

            // New header
            if (preg_match('/^([\w-]+):\s*(.*)$/', $line, $matches)) {
                $currentHeader = strtolower($matches[1]);
                $headers[$currentHeader] = $matches[2];
            }
        }

        return $headers;
    }

    /**
     * Normalize line endings
     *
     * @param string $content Content to normalize
     * @param bool $useCrLfEndings Whether to use CRLF endings
     * @return string Normalized content
     */
    private function normalizeLineEndings(string $content, bool $useCrLfEndings = false): string
    {
        $content = str_replace("\r\n", "\n", $content);

        if ($useCrLfEndings) {
            $content = str_replace("\n", "\r\n", $content);
        }

        return $content;
    }
}