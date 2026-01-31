<?php
/**
 * Copyright (c) 2025-2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\AsynchronousEmailSending\Controller\Adminhtml\Email;

use Hryvinskyi\AsynchronousEmailSending\Api\AsyncEmailRepositoryInterface;
use Hryvinskyi\AsynchronousEmailSending\Api\Data\AsyncEmailInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

/**
 * Resend action controller for async email.
 */
class Resend extends Action implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session.
     *
     * @see _isAllowed()
     */
    public const string ADMIN_RESOURCE = 'Hryvinskyi_AsynchronousEmailSending::email_queue_resend';

    /**
     * @param Context $context
     * @param AsyncEmailRepositoryInterface $asyncEmailRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        private readonly AsyncEmailRepositoryInterface $asyncEmailRepository,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct($context);
    }

    /**
     * Execute resend action.
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('*/*/');

        $entityId = (int)$this->getRequest()->getParam('entity_id');

        if (!$entityId) {
            $this->messageManager->addErrorMessage(__('Email ID is required.'));
            return $resultRedirect;
        }

        try {
            $email = $this->asyncEmailRepository->getById($entityId);
            $email->setStatus(AsyncEmailInterface::STATUS_PENDING);
            $this->asyncEmailRepository->save($email);
            $this->messageManager->addSuccessMessage(__('The email has been queued for resending.'));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->error('Resend email error: ' . $e->getMessage());
            $this->messageManager->addErrorMessage(
                __('An error occurred while queueing the email for resend. Please try again.')
            );
        }

        return $resultRedirect;
    }
}
