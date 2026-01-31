<?php
/**
 * Copyright (c) 2025-2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\AsynchronousEmailSending\Block\Adminhtml\Email;

use Hryvinskyi\AsynchronousEmailSending\Api\AsyncEmailRepositoryInterface;
use Hryvinskyi\AsynchronousEmailSending\Api\Data\AsyncEmailInterface;
use Hryvinskyi\AsynchronousEmailSending\Api\Service\MessageParserInterface;
use Magento\Backend\Block\Widget\Container;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Block for displaying email details in admin.
 */
class View extends Container
{
    /**
     * @var AsyncEmailInterface|null
     */
    private ?AsyncEmailInterface $email = null;

    /**
     * @param Context $context
     * @param AsyncEmailRepositoryInterface $asyncEmailRepository
     * @param MessageParserInterface $messageParser
     * @param array<string, mixed> $data
     */
    public function __construct(
        Context $context,
        private readonly AsyncEmailRepositoryInterface $asyncEmailRepository,
        private readonly MessageParserInterface $messageParser,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * {@inheritDoc}
     */
    protected function _construct(): void
    {
        parent::_construct();
        $this->_controller = 'adminhtml_email';
        $this->_blockGroup = 'Hryvinskyi_AsynchronousEmailSending';
    }

    /**
     * {@inheritDoc}
     */
    protected function _prepareLayout(): self
    {
        $this->buttonList->add(
            'back',
            [
                'label' => __('Back to List'),
                'onclick' => sprintf("setLocation('%s')", $this->getBackUrl()),
                'class' => 'back'
            ]
        );

        $this->buttonList->add(
            'resend',
            [
                'label' => __('Resend'),
                'onclick' => sprintf(
                    "confirmSetLocation('%s', '%s')",
                    __('Are you sure you want to resend this email?'),
                    $this->getResendUrl()
                ),
                'class' => 'primary'
            ]
        );

        $this->buttonList->add(
            'delete',
            [
                'label' => __('Delete'),
                'onclick' => sprintf(
                    "confirmSetLocation('%s', '%s')",
                    __('Are you sure you want to delete this email?'),
                    $this->getDeleteUrl()
                ),
                'class' => 'delete'
            ]
        );

        return parent::_prepareLayout();
    }

    /**
     * Get email entity.
     *
     * @return AsyncEmailInterface|null
     */
    public function getEmail(): ?AsyncEmailInterface
    {
        if ($this->email === null) {
            $entityId = (int)$this->getRequest()->getParam('entity_id');
            if ($entityId) {
                try {
                    $this->email = $this->asyncEmailRepository->getById($entityId);
                } catch (NoSuchEntityException) {
                    $this->email = null;
                }
            }
        }

        return $this->email;
    }

    /**
     * Get status label.
     *
     * @return string
     */
    public function getStatusLabel(): string
    {
        $email = $this->getEmail();
        if ($email === null) {
            return '';
        }

        return match ($email->getStatus()) {
            AsyncEmailInterface::STATUS_PENDING => (string)__('Pending'),
            AsyncEmailInterface::STATUS_SENT => (string)__('Sent'),
            AsyncEmailInterface::STATUS_ERROR => (string)__('Error'),
            default => (string)__('Unknown')
        };
    }

    /**
     * Get status CSS class.
     *
     * @return string
     */
    public function getStatusClass(): string
    {
        $email = $this->getEmail();
        if ($email === null) {
            return '';
        }

        return match ($email->getStatus()) {
            AsyncEmailInterface::STATUS_PENDING => 'grid-severity-minor',
            AsyncEmailInterface::STATUS_SENT => 'grid-severity-notice',
            AsyncEmailInterface::STATUS_ERROR => 'grid-severity-critical',
            default => 'grid-severity-minor'
        };
    }

    /**
     * Get parsed email headers.
     *
     * @return array<string, string>
     */
    public function getParsedHeaders(): array
    {
        $email = $this->getEmail();
        if ($email === null) {
            return [];
        }

        try {
            $parsed = $this->messageParser->parseRawMessage($email->getRawMessage(), "\r\n");
            $headers = [];
            $headerLines = explode("\r\n", $parsed['headers'] ?? '');

            foreach ($headerLines as $line) {
                if (str_contains($line, ':')) {
                    [$name, $value] = explode(':', $line, 2);
                    $headers[trim($name)] = trim($value);
                }
            }

            return $headers;
        } catch (\Exception) {
            return [];
        }
    }

    /**
     * Get back URL.
     *
     * @return string
     */
    public function getBackUrl(): string
    {
        return $this->getUrl('*/*/');
    }

    /**
     * Get resend URL.
     *
     * @return string
     */
    public function getResendUrl(): string
    {
        return $this->getUrl('*/*/resend', ['entity_id' => $this->getEmail()?->getEntId()]);
    }

    /**
     * Get delete URL.
     *
     * @return string
     */
    public function getDeleteUrl(): string
    {
        return $this->getUrl('*/*/delete', ['entity_id' => $this->getEmail()?->getEntId()]);
    }
}
