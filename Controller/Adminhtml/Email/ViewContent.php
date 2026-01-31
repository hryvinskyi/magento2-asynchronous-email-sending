<?php
/**
 * Copyright (c) 2025-2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\AsynchronousEmailSending\Controller\Adminhtml\Email;

use Hryvinskyi\AsynchronousEmailSending\Api\AsyncEmailRepositoryInterface;
use Hryvinskyi\AsynchronousEmailSending\Api\Service\MessageParserInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Controller to render raw email content in iframe.
 */
class ViewContent extends Action implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session.
     *
     * @see _isAllowed()
     */
    public const string ADMIN_RESOURCE = 'Hryvinskyi_AsynchronousEmailSending::email_queue_view';

    /**
     * @param Context $context
     * @param RawFactory $resultRawFactory
     * @param AsyncEmailRepositoryInterface $asyncEmailRepository
     * @param MessageParserInterface $messageParser
     */
    public function __construct(
        Context $context,
        private readonly RawFactory $resultRawFactory,
        private readonly AsyncEmailRepositoryInterface $asyncEmailRepository,
        private readonly MessageParserInterface $messageParser
    ) {
        parent::__construct($context);
    }

    /**
     * Execute action to render email content.
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        /** @var Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();

        $entityId = (int)$this->getRequest()->getParam('entity_id');

        if (!$entityId) {
            $resultRaw->setContents('<p>Email ID is required.</p>');
            return $resultRaw;
        }

        try {
            $email = $this->asyncEmailRepository->getById($entityId);
            $rawMessage = $email->getRawMessage();

            $content = $this->extractHtmlContent($rawMessage);

            $resultRaw->setHeader('Content-Type', 'text/html; charset=UTF-8');
            $resultRaw->setContents($content);
        } catch (NoSuchEntityException) {
            $resultRaw->setContents('<p>Email not found.</p>');
        } catch (\Exception $e) {
            $resultRaw->setContents('<p>Error loading email content.</p>');
        }

        return $resultRaw;
    }

    /**
     * Extract HTML content from raw email message.
     *
     * @param string $rawMessage
     * @return string
     */
    private function extractHtmlContent(string $rawMessage): string
    {
        try {
            $parsed = $this->messageParser->parse($rawMessage);
            $parts = $parsed->getParts();

            foreach ($parts as $part) {
                $contentType = $part->getContentType();
                if (str_contains($contentType, 'text/html')) {
                    return $part->getContent();
                }
            }

            foreach ($parts as $part) {
                $contentType = $part->getContentType();
                if (str_contains($contentType, 'text/plain')) {
                    return '<pre style="font-family: sans-serif; padding: 20px;">'
                        . htmlspecialchars($part->getContent(), ENT_QUOTES, 'UTF-8')
                        . '</pre>';
                }
            }

            $parsedRaw = $this->messageParser->parseRawMessage($rawMessage, "\r\n");
            $body = $parsedRaw['body'] ?? '';

            if (!empty($body)) {
                if (str_contains($body, '<html') || str_contains($body, '<body')) {
                    return $body;
                }
                return '<pre style="font-family: sans-serif; padding: 20px;">'
                    . htmlspecialchars($body, ENT_QUOTES, 'UTF-8')
                    . '</pre>';
            }

            return '<p>No content available.</p>';
        } catch (\Exception) {
            return '<p>Error parsing email content.</p>';
        }
    }
}
