<?php
/**
 * Copyright (c) 2025-2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\AsynchronousEmailSending\Test\Unit\Model\Service;

use Hryvinskyi\AsynchronousEmailSending\Api\Message\ParsedMessageInterface;
use Hryvinskyi\AsynchronousEmailSending\Api\Message\ParsedMessageInterfaceFactory;
use Hryvinskyi\AsynchronousEmailSending\Api\Message\ParsedMessagePartInterface;
use Hryvinskyi\AsynchronousEmailSending\Api\Message\ParsedMessagePartInterfaceFactory;
use Hryvinskyi\AsynchronousEmailSending\Model\Service\MessageParser;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Unit tests for MessageParser service
 */
class MessageParserTest extends TestCase
{
    private ParsedMessageInterfaceFactory|MockObject $parsedMessageFactoryMock;
    private ParsedMessagePartInterfaceFactory|MockObject $parsedMessagePartFactoryMock;
    private LoggerInterface|MockObject $loggerMock;
    private MessageParser $messageParser;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->parsedMessageFactoryMock = $this->createMock(ParsedMessageInterfaceFactory::class);
        $this->parsedMessagePartFactoryMock = $this->createMock(ParsedMessagePartInterfaceFactory::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);

        $this->messageParser = new MessageParser(
            $this->parsedMessageFactoryMock,
            $this->parsedMessagePartFactoryMock,
            $this->loggerMock
        );
    }

    /**
     * Test parsing simple raw message with headers and body
     */
    public function testParseRawMessageWithHeadersAndBody(): void
    {
        $rawMessage = "Subject: Test Email\r\nFrom: sender@example.com\r\n\r\nThis is the body";

        $result = $this->messageParser->parseRawMessage($rawMessage, "\r\n");

        $this->assertIsArray($result);
        $this->assertArrayHasKey('headers', $result);
        $this->assertArrayHasKey('body', $result);
        $this->assertArrayHasKey('headersEOL', $result);
        $this->assertEquals("Subject: Test Email\r\nFrom: sender@example.com", $result['headers']);
        $this->assertEquals('This is the body', $result['body']);
        $this->assertEquals("\r\n", $result['headersEOL']);
    }

    /**
     * Test parsing message with no headers (body only)
     */
    public function testParseRawMessageWithNoHeaders(): void
    {
        $rawMessage = "This is just a plain text message without headers";

        $result = $this->messageParser->parseRawMessage($rawMessage);

        $this->assertIsArray($result);
        $this->assertEquals('', $result['headers']);
        $this->assertNotEmpty($result['body']);
    }

    /**
     * Test parsing message with different EOL formats
     *
     * @dataProvider eolFormatProvider
     */
    public function testParseRawMessageWithDifferentEOLFormats(string $eol, string $rawMessage): void
    {
        $result = $this->messageParser->parseRawMessage($rawMessage, $eol);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('headersEOL', $result);
    }

    /**
     * @return array<string, array{eol: string, message: string}>
     */
    public static function eolFormatProvider(): array
    {
        return [
            'CRLF format' => [
                'eol' => "\r\n",
                'message' => "Subject: Test\r\n\r\nBody"
            ],
            'LF format' => [
                'eol' => "\n",
                'message' => "Subject: Test\n\nBody"
            ],
            'Mixed format' => [
                'eol' => "\n",
                'message' => "Subject: Test\r\n\r\nBody"
            ]
        ];
    }

    /**
     * Test parsing message with multi-line headers (continuations)
     */
    public function testParseMessageWithMultiLineHeaders(): void
    {
        $rawMessage = "Content-Type: text/html;\r\n charset=UTF-8;\r\n boundary=\"test\"\r\n\r\nBody";

        $result = $this->messageParser->parseRawMessage($rawMessage, "\r\n");

        $this->assertIsArray($result);
        $this->assertStringContainsString('Content-Type', $result['headers']);
    }

    /**
     * Test parse method returns ParsedMessageInterface
     */
    public function testParseReturnsParsedMessage(): void
    {
        $rawMessage = "Subject: Test Email\r\nFrom: sender@example.com\r\nTo: recipient@example.com\r\n\r\nTest body";

        $parsedMessageMock = $this->createMock(ParsedMessageInterface::class);
        $parsedMessageMock->method('setHeaders')->willReturnSelf();
        $parsedMessageMock->method('setParts')->willReturnSelf();

        $this->parsedMessageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($parsedMessageMock);

        $parsedPartMock = $this->createMock(ParsedMessagePartInterface::class);
        $parsedPartMock->method('setContent')->willReturnSelf();
        $parsedPartMock->method('setContentType')->willReturnSelf();
        $parsedPartMock->method('setCharset')->willReturnSelf();

        $this->parsedMessagePartFactoryMock->method('create')
            ->willReturn($parsedPartMock);

        $result = $this->messageParser->parse($rawMessage);

        $this->assertInstanceOf(ParsedMessageInterface::class, $result);
    }

    /**
     * Test parse method with empty raw message throws exception
     */
    public function testParseWithEmptyMessageThrowsException(): void
    {
        $this->loggerMock->expects($this->never())
            ->method('error');

        $parsedMessageMock = $this->createMock(ParsedMessageInterface::class);
        $parsedMessageMock->method('setHeaders')->willReturnSelf();
        $parsedMessageMock->method('setParts')->willReturnSelf();

        $this->parsedMessageFactoryMock->method('create')
            ->willReturn($parsedMessageMock);

        // Empty message should still be parseable (body only, no headers)
        $result = $this->messageParser->parse('');

        $this->assertInstanceOf(ParsedMessageInterface::class, $result);
    }

    /**
     * Test parsing message with malformed headers logs warning
     */
    public function testParseMessageWithMalformedHeadersLogsWarning(): void
    {
        $rawMessage = "Valid-Header: value\r\n\r\n\r\n\r\nAnother-Header: value\r\n\r\nBody";

        $parsedMessageMock = $this->createMock(ParsedMessageInterface::class);
        $parsedMessageMock->method('setHeaders')->willReturnSelf();
        $parsedMessageMock->method('setParts')->willReturnSelf();

        $this->parsedMessageFactoryMock->method('create')
            ->willReturn($parsedMessageMock);

        $parsedPartMock = $this->createMock(ParsedMessagePartInterface::class);
        $parsedPartMock->method('setContent')->willReturnSelf();
        $parsedPartMock->method('setContentType')->willReturnSelf();
        $parsedPartMock->method('setCharset')->willReturnSelf();

        $this->parsedMessagePartFactoryMock->method('create')
            ->willReturn($parsedPartMock);

        $result = $this->messageParser->parse($rawMessage);

        $this->assertInstanceOf(ParsedMessageInterface::class, $result);
    }

    /**
     * Test parsing message with invalid header format logs warning
     */
    public function testParseMessageWithInvalidHeaderFormatLogsWarning(): void
    {
        $rawMessage = "Valid-Header: value\r\nInvalidHeaderWithoutColon\r\n\r\nBody";

        $this->loggerMock->expects($this->atLeastOnce())
            ->method('warning');

        $parsedMessageMock = $this->createMock(ParsedMessageInterface::class);
        $parsedMessageMock->method('setHeaders')->willReturnSelf();
        $parsedMessageMock->method('setParts')->willReturnSelf();

        $this->parsedMessageFactoryMock->method('create')
            ->willReturn($parsedMessageMock);

        $parsedPartMock = $this->createMock(ParsedMessagePartInterface::class);
        $parsedPartMock->method('setContent')->willReturnSelf();
        $parsedPartMock->method('setContentType')->willReturnSelf();
        $parsedPartMock->method('setCharset')->willReturnSelf();

        $this->parsedMessagePartFactoryMock->method('create')
            ->willReturn($parsedPartMock);

        $result = $this->messageParser->parse($rawMessage);

        $this->assertInstanceOf(ParsedMessageInterface::class, $result);
    }

    /**
     * Test parsing message with special characters in headers
     */
    public function testParseMessageWithSpecialCharactersInHeaders(): void
    {
        $rawMessage = "Subject: Test with special chars\r\nFrom: sender@example.com\r\nTo: recipient@example.com\r\n\r\nBody";

        $parsedMessageMock = $this->createMock(ParsedMessageInterface::class);
        $parsedMessageMock->method('setHeaders')->willReturnSelf();
        $parsedMessageMock->method('setParts')->willReturnSelf();

        $this->parsedMessageFactoryMock->method('create')
            ->willReturn($parsedMessageMock);

        $parsedPartMock = $this->createMock(ParsedMessagePartInterface::class);
        $parsedPartMock->method('setContent')->willReturnSelf();
        $parsedPartMock->method('setContentType')->willReturnSelf();
        $parsedPartMock->method('setCharset')->willReturnSelf();

        $this->parsedMessagePartFactoryMock->method('create')
            ->willReturn($parsedPartMock);

        $result = $this->messageParser->parse($rawMessage);

        $this->assertInstanceOf(ParsedMessageInterface::class, $result);
    }

    /**
     * Test parsing message with continuation lines
     */
    public function testParseMessageWithContinuationLines(): void
    {
        $rawMessage = "Content-Type: multipart/alternative;\r\n boundary=\"boundary-string\";\r\n type=\"text/plain\"\r\nTo: recipient@example.com\r\n\r\nBody";

        $parsedMessageMock = $this->createMock(ParsedMessageInterface::class);
        $parsedMessageMock->method('setHeaders')->willReturnSelf();
        $parsedMessageMock->method('setParts')->willReturnSelf();

        $this->parsedMessageFactoryMock->method('create')
            ->willReturn($parsedMessageMock);

        $parsedPartMock = $this->createMock(ParsedMessagePartInterface::class);
        $parsedPartMock->method('setContent')->willReturnSelf();
        $parsedPartMock->method('setContentType')->willReturnSelf();
        $parsedPartMock->method('setCharset')->willReturnSelf();

        $this->parsedMessagePartFactoryMock->method('create')
            ->willReturn($parsedPartMock);

        $result = $this->messageParser->parse($rawMessage);

        $this->assertInstanceOf(ParsedMessageInterface::class, $result);
    }

    /**
     * Test parsing message handles empty continuation lines
     */
    public function testParseMessageHandlesEmptyContinuationLines(): void
    {
        $rawMessage = "Subject: Test\r\n \r\nFrom: sender@example.com\r\nTo: recipient@example.com\r\n\r\nBody";

        $parsedMessageMock = $this->createMock(ParsedMessageInterface::class);
        $parsedMessageMock->method('setHeaders')->willReturnSelf();
        $parsedMessageMock->method('setParts')->willReturnSelf();

        $this->parsedMessageFactoryMock->method('create')
            ->willReturn($parsedMessageMock);

        $parsedPartMock = $this->createMock(ParsedMessagePartInterface::class);
        $parsedPartMock->method('setContent')->willReturnSelf();
        $parsedPartMock->method('setContentType')->willReturnSelf();
        $parsedPartMock->method('setCharset')->willReturnSelf();

        $this->parsedMessagePartFactoryMock->method('create')
            ->willReturn($parsedPartMock);

        $result = $this->messageParser->parse($rawMessage);

        $this->assertInstanceOf(ParsedMessageInterface::class, $result);
    }
}
