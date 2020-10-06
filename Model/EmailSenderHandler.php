<?php
/**
 * Copyright (c) 2020. Volodymyr Hryvinskyi.  All rights reserved.
 * @author: <mailto:volodymyr@hryvinskyi.com>
 * @github: <https://github.com/hryvinskyi>
 */

declare(strict_types=1);

namespace Hryvinskyi\AsynchronousEmailSending\Model;

use Hryvinskyi\AsynchronousEmailSending\Api\AsyncEmailRepositoryInterface;
use Hryvinskyi\AsynchronousEmailSending\Service\SendFlag;
use Magento\Framework\App\ProductMetadataInterface;
use Psr\Log\LoggerInterface;
use Zend\Mail\Message;
use Zend\Mime\Mime;

/**
 * Class EmailSenderHandler
 */
class EmailSenderHandler implements EmailSenderHandlerInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var AsyncEmailRepositoryInterface
     */
    private $asyncEmailRepository;

    /**
     * @var TransportFactory
     */
    private $transportFactory;

    /**
     * @var MailMessageFactory
     */
    private $mailMessageFactory;

    /**
     * @var SendFlag
     */
    private $sendFlag;

    /**
     * @var ProductMetadataInterface
     */
    private $magentoProductMetaData;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * EmailSenderHandler constructor.
     *
     * @param Config $config
     * @param AsyncEmailRepositoryInterface $asyncEmailRepository
     * @param TransportFactory $transportFactory
     * @param MailMessageFactory $mailMessageFactory
     * @param SendFlag $sendFlag
     * @param LoggerInterface $logger
     */
    public function __construct(
        Config $config,
        AsyncEmailRepositoryInterface $asyncEmailRepository,
        TransportFactory $transportFactory,
        MailMessageFactory $mailMessageFactory,
        SendFlag $sendFlag,
        ProductMetadataInterface $magentoProductMetaData,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->asyncEmailRepository = $asyncEmailRepository;
        $this->transportFactory = $transportFactory;
        $this->mailMessageFactory = $mailMessageFactory;
        $this->sendFlag = $sendFlag;
        $this->magentoProductMetaData = $magentoProductMetaData;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function sendEmails(): void
    {
        if ($this->config->isEnabled() === false) {
            return;
        }

        $items = $this->asyncEmailRepository->getPendingItems($this->config->getSendingLimit());
        $this->sendFlag->setIsSending(true);
        foreach ($items->getItems() as $item) {
            try {
                $message = Message::fromString($item->getRawMessage())->setEncoding('utf-8');
                /** @var MailMessage $mailMessage */
                $mailMessage = $this->mailMessageFactory->create();

                $body = $message->getBody();

                if (version_compare($this->magentoProductMetaData->getVersion(), "2.3.3", ">=")) {
                    $body = quoted_printable_decode($body);
                }

                $mailMessage
                    ->setSubject($message->getSubject())
                    ->setFrom($message->getFrom())
                    ->setReplyTo($message->getReplyTo())
                    ->addBcc($message->getBcc())
                    ->addCc($message->getBcc())
                    ->addTo($message->getTo())
                    ->setMessageType($body != strip_tags($body) ? Mime::TYPE_HTML : Mime::TYPE_TEXT)
                    ->setBody($body)
                    ->setRawMessage($item->getRawMessage());

                $transport = $this->transportFactory->create($mailMessage);
                $transport->sendMessage();

                $item->setStatus(1)->setSentAt(date('Y-m-d h:i:s'));
                $this->asyncEmailRepository->save($item);
            } catch (\Throwable $exception) {
                $item->setStatus(2);
                $this->logger->critical(
                    'Error with ID: ' . $item->getEntId() . ' Message: ' . $exception->getMessage(),
                    $exception->getTrace()
                );
            }

            $this->asyncEmailRepository->save($item);
        }
    }
}
