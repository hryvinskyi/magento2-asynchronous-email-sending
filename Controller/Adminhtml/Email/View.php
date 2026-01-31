<?php
/**
 * Copyright (c) 2025-2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\AsynchronousEmailSending\Controller\Adminhtml\Email;

use Hryvinskyi\AsynchronousEmailSending\Api\AsyncEmailRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

/**
 * View action controller for async email details.
 */
class View extends Action implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session.
     *
     * @see _isAllowed()
     */
    public const string ADMIN_RESOURCE = 'Hryvinskyi_AsynchronousEmailSending::email_queue_view';

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param AsyncEmailRepositoryInterface $asyncEmailRepository
     */
    public function __construct(
        Context $context,
        private readonly PageFactory $resultPageFactory,
        private readonly AsyncEmailRepositoryInterface $asyncEmailRepository
    ) {
        parent::__construct($context);
    }

    /**
     * Execute view action.
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $entityId = (int)$this->getRequest()->getParam('entity_id');

        if (!$entityId) {
            $this->messageManager->addErrorMessage(__('Email ID is required.'));
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath('*/*/');
        }

        try {
            $email = $this->asyncEmailRepository->getById($entityId);
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('This email no longer exists.'));
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath('*/*/');
        }

        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Hryvinskyi_AsynchronousEmailSending::email_queue');
        $resultPage->getConfig()->getTitle()->prepend(__('Email Details'));
        $resultPage->getConfig()->getTitle()->prepend($email->getSubject());

        return $resultPage;
    }
}
