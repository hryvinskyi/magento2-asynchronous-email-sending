<?php
/**
 * Copyright (c) 2020-2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\AsynchronousEmailSending\Test\Unit\Model\Service;

use Hryvinskyi\AsynchronousEmailSending\Api\Message\ParsedMessageInterface;
use Hryvinskyi\AsynchronousEmailSending\Api\Message\ParsedMessagePartInterface;
use Hryvinskyi\AsynchronousEmailSending\Model\MailMessage;
use Hryvinskyi\AsynchronousEmailSending\Model\MailMessageFactory;
use Hryvinskyi\AsynchronousEmailSending\Model\Service\MessagePopulator;
use Magento\Framework\Mail\MimeMessageInterface;
use Magento\Framework\Mail\MimeMessageInterfaceFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\Header\Headers;

/**
 * Unit tests for MessagePopulator service
 */
class MessagePopulatorTest extends TestCase
{
    private MailMessageFactory|MockObject $mailMessageFactoryMock;
    private MimeMessageInterfaceFactory|MockObject $mimeMessageFactoryMock;
    private MessagePopulator $messagePopulator;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->mailMessageFactoryMock = $this->createMock(MailMessageFactory::class);
        $this->mimeMessageFactoryMock = $this->createMock(MimeMessageInterfaceFactory::class);

        $this->messagePopulator = new MessagePopulator(
            $this->mailMessageFactoryMock,
            $this->mimeMessageFactoryMock
        );
    }

    /**
     * Create a mock ParsedMessageInterface with headers containing To address
     */
    private function createParsedMessageMock(bool $hasTo = true, array $parts = []): ParsedMessageInterface|MockObject
    {
        $headers = new Headers();
        $headers->addTextHeader('Subject', 'Test Subject');
        $headers->addMailboxListHeader('From', ['sender@example.com']);

        if ($hasTo) {
            $headers->addMailboxListHeader('To', ['recipient@example.com']);
        }

        $parsedMessageMock = $this->createMock(ParsedMessageInterface::class);
        $parsedMessageMock->method('getHeaders')->willReturn($headers);
        $parsedMessageMock->method('getParts')->willReturn($parts);

        return $parsedMessageMock;
    }

    /**
     * Create a mock ParsedMessagePartInterface
     */
    private function createPartMock(
        string $content = 'Test content',
        string $contentType = 'text/html',
        string $charset = 'utf-8',
        bool $isAttachment = false,
        bool $isHtml = true,
        bool $isText = false,
        ?string $filename = null
    ): ParsedMessagePartInterface|MockObject {
        $partMock = $this->createMock(ParsedMessagePartInterface::class);
        $partMock->method('getContent')->willReturn($content);
        $partMock->method('getContentType')->willReturn($contentType);
        $partMock->method('getCharset')->willReturn($charset);
        $partMock->method('isAttachment')->willReturn($isAttachment);
        $partMock->method('isHtml')->willReturn($isHtml);
        $partMock->method('isText')->willReturn($isText);
        $partMock->method('getFilename')->willReturn($filename);

        return $partMock;
    }

    /**
     * Set up MailMessageFactory to return a mock
     */
    private function setupMailMessageFactoryMock(): MailMessage|MockObject
    {
        $mimeMessageMock = $this->createMock(MimeMessageInterface::class);
        $this->mimeMessageFactoryMock->method('create')->willReturn($mimeMessageMock);

        $mailMessageMock = $this->createMock(MailMessage::class);
        $mailMessageMock->method('setSymfonyMessage')->willReturnSelf();

        $this->mailMessageFactoryMock->method('create')->willReturn($mailMessageMock);

        return $mailMessageMock;
    }

    /**
     * Test createMailMessage with basic headers
     */
    public function testCreateMailMessageWithBasicHeaders(): void
    {
        $this->setupMailMessageFactoryMock();
        $parsedMessage = $this->createParsedMessageMock(true, [$this->createPartMock()]);

        $result = $this->messagePopulator->createMailMessage($parsedMessage);

        $this->assertInstanceOf(MailMessage::class, $result);
    }

    /**
     * Test createMailMessage with multiple recipients
     */
    public function testCreateMailMessageWithMultipleRecipients(): void
    {
        $headers = new Headers();
        $headers->addTextHeader('Subject', 'Test');
        $headers->addMailboxListHeader('From', ['sender@example.com']);
        $headers->addMailboxListHeader('To', ['recipient1@example.com', 'recipient2@example.com']);
        $headers->addMailboxListHeader('Cc', ['cc@example.com']);
        $headers->addMailboxListHeader('Bcc', ['bcc@example.com']);

        $parsedMessageMock = $this->createMock(ParsedMessageInterface::class);
        $parsedMessageMock->method('getHeaders')->willReturn($headers);
        $parsedMessageMock->method('getParts')->willReturn([$this->createPartMock()]);

        $this->setupMailMessageFactoryMock();

        $result = $this->messagePopulator->createMailMessage($parsedMessageMock);

        $this->assertInstanceOf(MailMessage::class, $result);
    }

    /**
     * Test createMailMessage with Reply-To header
     */
    public function testCreateMailMessageWithReplyTo(): void
    {
        $headers = new Headers();
        $headers->addTextHeader('Subject', 'Test');
        $headers->addMailboxListHeader('From', ['sender@example.com']);
        $headers->addMailboxListHeader('To', ['recipient@example.com']);
        $headers->addMailboxListHeader('Reply-To', ['replyto@example.com']);

        $parsedMessageMock = $this->createMock(ParsedMessageInterface::class);
        $parsedMessageMock->method('getHeaders')->willReturn($headers);
        $parsedMessageMock->method('getParts')->willReturn([$this->createPartMock()]);

        $this->setupMailMessageFactoryMock();

        $result = $this->messagePopulator->createMailMessage($parsedMessageMock);

        $this->assertInstanceOf(MailMessage::class, $result);
    }

    /**
     * Test createMailMessage with Sender header
     */
    public function testCreateMailMessageWithSender(): void
    {
        $headers = new Headers();
        $headers->addTextHeader('Subject', 'Test');
        $headers->addMailboxListHeader('From', ['sender@example.com']);
        $headers->addMailboxListHeader('To', ['recipient@example.com']);
        $headers->addMailboxHeader('Sender', 'actual-sender@example.com');

        $parsedMessageMock = $this->createMock(ParsedMessageInterface::class);
        $parsedMessageMock->method('getHeaders')->willReturn($headers);
        $parsedMessageMock->method('getParts')->willReturn([$this->createPartMock()]);

        $this->setupMailMessageFactoryMock();

        $result = $this->messagePopulator->createMailMessage($parsedMessageMock);

        $this->assertInstanceOf(MailMessage::class, $result);
    }

    /**
     * Test createMailMessage without subject
     */
    public function testCreateMailMessageWithoutSubject(): void
    {
        $headers = new Headers();
        $headers->addMailboxListHeader('From', ['sender@example.com']);
        $headers->addMailboxListHeader('To', ['recipient@example.com']);

        $parsedMessageMock = $this->createMock(ParsedMessageInterface::class);
        $parsedMessageMock->method('getHeaders')->willReturn($headers);
        $parsedMessageMock->method('getParts')->willReturn([$this->createPartMock()]);

        $this->setupMailMessageFactoryMock();

        $result = $this->messagePopulator->createMailMessage($parsedMessageMock);

        $this->assertInstanceOf(MailMessage::class, $result);
    }

    /**
     * Test createMailMessage with minimal headers
     */
    public function testCreateMailMessageWithMinimalHeaders(): void
    {
        $headers = new Headers();
        $headers->addMailboxListHeader('To', ['recipient@example.com']);

        $parsedMessageMock = $this->createMock(ParsedMessageInterface::class);
        $parsedMessageMock->method('getHeaders')->willReturn($headers);
        $parsedMessageMock->method('getParts')->willReturn([$this->createPartMock()]);

        $this->setupMailMessageFactoryMock();

        $result = $this->messagePopulator->createMailMessage($parsedMessageMock);

        $this->assertInstanceOf(MailMessage::class, $result);
    }

    /**
     * Test createMailMessage with empty headers throws exception
     */
    public function testCreateMailMessageWithEmptyHeadersThrowsException(): void
    {
        $headers = new Headers();

        $parsedMessageMock = $this->createMock(ParsedMessageInterface::class);
        $parsedMessageMock->method('getHeaders')->willReturn($headers);
        $parsedMessageMock->method('getParts')->willReturn([]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Email message must have at least one "To" addressee');

        $this->messagePopulator->createMailMessage($parsedMessageMock);
    }

    /**
     * Test createMailMessage with empty body parts
     */
    public function testCreateMailMessageWithEmptyBodyParts(): void
    {
        $this->setupMailMessageFactoryMock();
        $parsedMessage = $this->createParsedMessageMock(true, []);

        $result = $this->messagePopulator->createMailMessage($parsedMessage);

        $this->assertInstanceOf(MailMessage::class, $result);
    }

    /**
     * Test createMailMessage with text/plain content
     */
    public function testCreateMailMessageWithPlainTextContent(): void
    {
        $textPart = $this->createPartMock(
            'Plain text content',
            'text/plain',
            'utf-8',
            false,
            false,
            true
        );

        $this->setupMailMessageFactoryMock();
        $parsedMessage = $this->createParsedMessageMock(true, [$textPart]);

        $result = $this->messagePopulator->createMailMessage($parsedMessage);

        $this->assertInstanceOf(MailMessage::class, $result);
    }

    /**
     * Test createMailMessage with attachment
     */
    public function testCreateMailMessageWithAttachment(): void
    {
        $htmlPart = $this->createPartMock('<p>HTML content</p>', 'text/html', 'utf-8', false, true, false);
        $attachmentPart = $this->createPartMock(
            'attachment content',
            'application/pdf',
            'utf-8',
            true,
            false,
            false,
            'document.pdf'
        );

        $this->setupMailMessageFactoryMock();
        $parsedMessage = $this->createParsedMessageMock(true, [$htmlPart, $attachmentPart]);

        $result = $this->messagePopulator->createMailMessage($parsedMessage);

        $this->assertInstanceOf(MailMessage::class, $result);
    }

    /**
     * Test address parsing with various formats
     *
     * @dataProvider addressFormatProvider
     */
    public function testAddressParsingWithVariousFormats(string $addressString): void
    {
        $headers = new Headers();
        $headers->addTextHeader('Subject', 'Test');
        $headers->addMailboxListHeader('From', [$addressString]);
        $headers->addMailboxListHeader('To', ['recipient@example.com']);

        $parsedMessageMock = $this->createMock(ParsedMessageInterface::class);
        $parsedMessageMock->method('getHeaders')->willReturn($headers);
        $parsedMessageMock->method('getParts')->willReturn([$this->createPartMock()]);

        $this->setupMailMessageFactoryMock();

        $result = $this->messagePopulator->createMailMessage($parsedMessageMock);

        $this->assertInstanceOf(MailMessage::class, $result);
    }

    /**
     * @return array<int, array<int, string>>
     */
    public static function addressFormatProvider(): array
    {
        return [
            ['simple@example.com'],
            ['name@subdomain.example.com'],
            ['name+tag@example.com'],
        ];
    }

    /**
     * Test createMailMessage with multiple From addresses
     */
    public function testCreateMailMessageWithMultipleFromAddresses(): void
    {
        $headers = new Headers();
        $headers->addTextHeader('Subject', 'Test');
        $headers->addMailboxListHeader('From', ['sender1@example.com', 'sender2@example.com']);
        $headers->addMailboxListHeader('To', ['recipient@example.com']);

        $parsedMessageMock = $this->createMock(ParsedMessageInterface::class);
        $parsedMessageMock->method('getHeaders')->willReturn($headers);
        $parsedMessageMock->method('getParts')->willReturn([$this->createPartMock()]);

        $this->setupMailMessageFactoryMock();

        $result = $this->messagePopulator->createMailMessage($parsedMessageMock);

        $this->assertInstanceOf(MailMessage::class, $result);
    }
}
