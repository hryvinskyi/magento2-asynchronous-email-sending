<?php
/**
 * Copyright (c) 2020-2025. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\AsynchronousEmailSending\Model;

use Hryvinskyi\AsynchronousEmailSending\Api\AsyncEmailRepositoryInterface;
use Hryvinskyi\AsynchronousEmailSending\Api\Service\MessageParserInterface;
use Hryvinskyi\AsynchronousEmailSending\Api\Service\MessagePopulatorInterface;
use Hryvinskyi\AsynchronousEmailSending\Service\SendFlag;
use Magento\Framework\Exception\CouldNotSaveException;
use Psr\Log\LoggerInterface;

/**
 * Email Sender Handler for Symfony Mailer (Magento 2.4.8+)
 */
class EmailSenderHandler implements EmailSenderHandlerInterface
{
    /**
     * Status code for successfully sent email
     */
    private const STATUS_SENT = 1;

    /**
     * Status code for failed email
     */
    private const STATUS_FAILED = 2;

    public function __construct(
        private readonly Config $config,
        private readonly AsyncEmailRepositoryInterface $asyncEmailRepository,
        private readonly TransportFactory $transportFactory,
        private readonly MessageParserInterface $messageParser,
        private readonly MessagePopulatorInterface $messagePopulator,
        private readonly SendFlag $sendFlag,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @inheritDoc
     */
    public function sendEmails(): void
    {
        if (!$this->config->isEnabled()) {
            return;
        }

        $items = $this->asyncEmailRepository->getPendingItems($this->config->getSendingLimit());
        $this->sendFlag->setIsSending(true);

        foreach ($items->getItems() as $item) {
            try {
                $this->processSingleEmail($item);
                $this->markEmailAsSent($item);
            } catch (\Throwable $exception) {
                $this->handleEmailFailure($item, $exception);
            }
        }
    }

    /**
     * Process and send a single email item
     *
     * Parses the raw MIME message, properly decoding multipart content
     * and transfer encodings (quoted-printable, base64), then converts
     * to Symfony Message format for sending.
     *
     * @param mixed $item Email queue item
     * @return void
     * @throws \Exception If email processing fails
     */
    private function processSingleEmail($item): void
    {
        $rawMessageString = $item->getRawMessage();

        // Parse raw message into ParsedMessage with headers and decoded parts
        // This properly handles multipart MIME and decodes quoted-printable/base64
        $parsedMessage = $this->messageParser->parse($rawMessageString);

        // Create MailMessage from parsed message
        $mailMessage = $this->messagePopulator->createMailMessage($parsedMessage);

        // Set the raw message for reference
        $mailMessage->setRawMessage($rawMessageString);

        // Send email via transport
        $transport = $this->transportFactory->create($mailMessage);
        $transport->sendMessage();
    }

    /**
     * Mark email as successfully sent
     *
     * @param mixed $item Email queue item
     * @return void
     * @throws CouldNotSaveException If save operation fails
     */
    private function markEmailAsSent($item): void
    {
        $item->setStatus(self::STATUS_SENT)
            ->setSentAt(date('Y-m-d H:i:s'));
        $this->asyncEmailRepository->save($item);
    }

    /**
     * Handle email sending failure
     *
     * @param mixed $item Email queue item
     * @param \Throwable $exception Exception that occurred
     * @return void
     */
    private function handleEmailFailure($item, \Throwable $exception): void
    {
        $item->setStatus(self::STATUS_FAILED);

        $this->logger->critical(
            sprintf('Failed to send email ID: %s. Error: %s', $item->getEntId(), $exception->getMessage()),
            ['exception' => $exception]
        );

        try {
            $this->asyncEmailRepository->save($item);
        } catch (CouldNotSaveException $e) {
            $this->logger->critical(
                sprintf('Failed to save error status for email ID: %s', $item->getEntId()),
                ['exception' => $e]
            );
        }
    }
}