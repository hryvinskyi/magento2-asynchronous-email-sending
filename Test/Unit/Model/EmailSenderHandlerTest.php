<?php
/**
 * Copyright (c) 2020-2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\AsynchronousEmailSending\Test\Unit\Model;

use Hryvinskyi\AsynchronousEmailSending\Api\AsyncEmailRepositoryInterface;
use Hryvinskyi\AsynchronousEmailSending\Api\Data\AsyncEmailInterface;
use Hryvinskyi\AsynchronousEmailSending\Api\Data\AsyncEmailSearchResultsInterface;
use Hryvinskyi\AsynchronousEmailSending\Api\Message\ParsedMessageInterface;
use Hryvinskyi\AsynchronousEmailSending\Api\Service\MessageParserInterface;
use Hryvinskyi\AsynchronousEmailSending\Api\Service\MessagePopulatorInterface;
use Hryvinskyi\AsynchronousEmailSending\Model\Config;
use Hryvinskyi\AsynchronousEmailSending\Model\EmailSenderHandler;
use Hryvinskyi\AsynchronousEmailSending\Model\MailMessage;
use Hryvinskyi\AsynchronousEmailSending\Model\TransportFactory;
use Hryvinskyi\AsynchronousEmailSending\Service\SendFlag;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Email\Model\Transport;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Unit tests for EmailSenderHandler
 */
class EmailSenderHandlerTest extends TestCase
{
    private Config|MockObject $configMock;
    private AsyncEmailRepositoryInterface|MockObject $repositoryMock;
    private TransportFactory|MockObject $transportFactoryMock;
    private MessageParserInterface|MockObject $messageParserMock;
    private MessagePopulatorInterface|MockObject $messagePopulatorMock;
    private SendFlag|MockObject $sendFlagMock;
    private LoggerInterface|MockObject $loggerMock;
    private EmailSenderHandler $emailSenderHandler;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->configMock = $this->createMock(Config::class);
        $this->repositoryMock = $this->createMock(AsyncEmailRepositoryInterface::class);
        $this->transportFactoryMock = $this->createMock(TransportFactory::class);
        $this->messageParserMock = $this->createMock(MessageParserInterface::class);
        $this->messagePopulatorMock = $this->createMock(MessagePopulatorInterface::class);
        $this->sendFlagMock = $this->createMock(SendFlag::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);

        $this->emailSenderHandler = new EmailSenderHandler(
            $this->configMock,
            $this->repositoryMock,
            $this->transportFactoryMock,
            $this->messageParserMock,
            $this->messagePopulatorMock,
            $this->sendFlagMock,
            $this->loggerMock
        );
    }

    /**
     * Test sendEmails does nothing when module is disabled
     */
    public function testSendEmailsDoesNothingWhenDisabled(): void
    {
        $this->configMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn(false);

        $this->repositoryMock->expects($this->never())
            ->method('getPendingItems');

        $this->emailSenderHandler->sendEmails();
    }

    /**
     * Test sendEmails processes pending emails successfully
     */
    public function testSendEmailsProcessesPendingEmailsSuccessfully(): void
    {
        $rawMessage = "Subject: Test\r\nFrom: sender@example.com\r\nTo: recipient@example.com\r\n\r\nBody";

        $this->configMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);
        $this->configMock->expects($this->once())
            ->method('getSendingLimit')
            ->willReturn(10);

        $emailItemMock = $this->createMock(AsyncEmailInterface::class);
        $emailItemMock->expects($this->once())
            ->method('getRawMessage')
            ->willReturn($rawMessage);
        $emailItemMock->expects($this->once())
            ->method('setStatus')
            ->with(1)
            ->willReturnSelf();
        $emailItemMock->expects($this->once())
            ->method('setSentAt')
            ->willReturnSelf();

        $searchResultsMock = $this->createMock(AsyncEmailSearchResultsInterface::class);
        $searchResultsMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$emailItemMock]);

        $this->repositoryMock->expects($this->once())
            ->method('getPendingItems')
            ->with(10)
            ->willReturn($searchResultsMock);

        $parsedMessageMock = $this->createMock(ParsedMessageInterface::class);
        $this->messageParserMock->expects($this->once())
            ->method('parse')
            ->with($rawMessage)
            ->willReturn($parsedMessageMock);

        $mailMessageMock = $this->createMock(MailMessage::class);
        $mailMessageMock->expects($this->once())
            ->method('setRawMessage')
            ->with($rawMessage)
            ->willReturnSelf();

        $this->messagePopulatorMock->expects($this->once())
            ->method('createMailMessage')
            ->with($parsedMessageMock)
            ->willReturn($mailMessageMock);

        $transportMock = $this->createMock(Transport::class);
        $transportMock->expects($this->once())
            ->method('sendMessage');

        $this->transportFactoryMock->expects($this->once())
            ->method('create')
            ->with($mailMessageMock)
            ->willReturn($transportMock);

        $this->repositoryMock->expects($this->once())
            ->method('save')
            ->with($emailItemMock);

        $this->sendFlagMock->expects($this->once())
            ->method('setIsSending')
            ->with(true);

        $this->emailSenderHandler->sendEmails();
    }

    /**
     * Test sendEmails handles transport exception
     */
    public function testSendEmailsHandlesTransportException(): void
    {
        $rawMessage = "Subject: Test\r\nTo: recipient@example.com\r\n\r\nBody";

        $this->configMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);
        $this->configMock->expects($this->once())
            ->method('getSendingLimit')
            ->willReturn(10);

        $emailItemMock = $this->createMock(AsyncEmailInterface::class);
        $emailItemMock->expects($this->once())
            ->method('getRawMessage')
            ->willReturn($rawMessage);
        $emailItemMock->expects($this->once())
            ->method('setStatus')
            ->with(2)
            ->willReturnSelf();
        $emailItemMock->expects($this->atLeastOnce())
            ->method('getEntId')
            ->willReturn(123);

        $searchResultsMock = $this->createMock(AsyncEmailSearchResultsInterface::class);
        $searchResultsMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$emailItemMock]);

        $this->repositoryMock->expects($this->once())
            ->method('getPendingItems')
            ->willReturn($searchResultsMock);

        $parsedMessageMock = $this->createMock(ParsedMessageInterface::class);
        $this->messageParserMock->expects($this->once())
            ->method('parse')
            ->willReturn($parsedMessageMock);

        $mailMessageMock = $this->createMock(MailMessage::class);
        $mailMessageMock->method('setRawMessage')->willReturnSelf();

        $this->messagePopulatorMock->expects($this->once())
            ->method('createMailMessage')
            ->willReturn($mailMessageMock);

        $transportMock = $this->createMock(Transport::class);
        $transportMock->expects($this->once())
            ->method('sendMessage')
            ->willThrowException(new \Exception('Transport error'));

        $this->transportFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($transportMock);

        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with($this->stringContains('Failed to send email ID: 123'));

        $this->repositoryMock->expects($this->once())
            ->method('save')
            ->with($emailItemMock);

        $this->emailSenderHandler->sendEmails();
    }

    /**
     * Test sendEmails handles repository save exception
     */
    public function testSendEmailsHandlesRepositorySaveException(): void
    {
        $rawMessage = "Subject: Test\r\nTo: recipient@example.com\r\n\r\nBody";

        $this->configMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);
        $this->configMock->expects($this->once())
            ->method('getSendingLimit')
            ->willReturn(10);

        $emailItemMock = $this->createMock(AsyncEmailInterface::class);
        $emailItemMock->expects($this->once())
            ->method('getRawMessage')
            ->willReturn($rawMessage);
        $emailItemMock->expects($this->once())
            ->method('setStatus')
            ->with(2)
            ->willReturnSelf();
        $emailItemMock->expects($this->atLeastOnce())
            ->method('getEntId')
            ->willReturn(123);

        $searchResultsMock = $this->createMock(AsyncEmailSearchResultsInterface::class);
        $searchResultsMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$emailItemMock]);

        $this->repositoryMock->expects($this->once())
            ->method('getPendingItems')
            ->willReturn($searchResultsMock);

        $this->messageParserMock->expects($this->once())
            ->method('parse')
            ->willThrowException(new \Exception('Parse error'));

        $this->repositoryMock->expects($this->once())
            ->method('save')
            ->willThrowException(new CouldNotSaveException(__('Could not save')));

        $this->loggerMock->expects($this->exactly(2))
            ->method('critical');

        $this->emailSenderHandler->sendEmails();
    }

    /**
     * Test sendEmails processes multiple emails
     */
    public function testSendEmailsProcessesMultipleEmails(): void
    {
        $this->configMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);
        $this->configMock->expects($this->once())
            ->method('getSendingLimit')
            ->willReturn(10);

        $emailItem1 = $this->createMock(AsyncEmailInterface::class);
        $emailItem1->method('getRawMessage')->willReturn('Message 1');
        $emailItem1->method('setStatus')->willReturnSelf();
        $emailItem1->method('setSentAt')->willReturnSelf();

        $emailItem2 = $this->createMock(AsyncEmailInterface::class);
        $emailItem2->method('getRawMessage')->willReturn('Message 2');
        $emailItem2->method('setStatus')->willReturnSelf();
        $emailItem2->method('setSentAt')->willReturnSelf();

        $searchResultsMock = $this->createMock(AsyncEmailSearchResultsInterface::class);
        $searchResultsMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$emailItem1, $emailItem2]);

        $this->repositoryMock->expects($this->once())
            ->method('getPendingItems')
            ->willReturn($searchResultsMock);

        $parsedMessageMock = $this->createMock(ParsedMessageInterface::class);
        $this->messageParserMock->method('parse')->willReturn($parsedMessageMock);

        $mailMessageMock = $this->createMock(MailMessage::class);
        $mailMessageMock->method('setRawMessage')->willReturnSelf();
        $this->messagePopulatorMock->method('createMailMessage')->willReturn($mailMessageMock);

        $transportMock = $this->createMock(Transport::class);
        $this->transportFactoryMock->method('create')->willReturn($transportMock);

        $this->repositoryMock->expects($this->exactly(2))
            ->method('save');

        $this->emailSenderHandler->sendEmails();
    }

    /**
     * Test sendEmails with empty queue
     */
    public function testSendEmailsWithEmptyQueue(): void
    {
        $this->configMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);
        $this->configMock->expects($this->once())
            ->method('getSendingLimit')
            ->willReturn(10);

        $searchResultsMock = $this->createMock(AsyncEmailSearchResultsInterface::class);
        $searchResultsMock->expects($this->once())
            ->method('getItems')
            ->willReturn([]);

        $this->repositoryMock->expects($this->once())
            ->method('getPendingItems')
            ->willReturn($searchResultsMock);

        $this->transportFactoryMock->expects($this->never())
            ->method('create');

        $this->emailSenderHandler->sendEmails();
    }
}
