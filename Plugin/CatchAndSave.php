<?php
/**
 * Copyright (c) 2020-2025. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\AsynchronousEmailSending\Plugin;

use Hryvinskyi\AsynchronousEmailSending\Api\AsyncEmailRepositoryInterface;
use Hryvinskyi\AsynchronousEmailSending\Model\Config;
use Hryvinskyi\AsynchronousEmailSending\Service\SendFlag;
use Magento\Framework\Mail\TransportInterface;
use Psr\Log\LoggerInterface;

/**
 * Plugin to intercept and queue outgoing emails
 */
class CatchAndSave
{
    /**
     * @param Config $config
     * @param AsyncEmailRepositoryInterface $asyncEmailRepository
     * @param SendFlag $sendFlag
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly Config $config,
        private readonly AsyncEmailRepositoryInterface $asyncEmailRepository,
        private readonly SendFlag $sendFlag,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param TransportInterface $subject
     * @param \Closure $proceed
     * @return void
     */
    public function aroundSendMessage(
        TransportInterface $subject,
        \Closure $proceed
    ) {
        if ($this->config->isEnabled() === false || $this->sendFlag->isSending()) {
            return $proceed();
        }

        try {
            $this->asyncEmailRepository->saveTransport($subject);
        } catch (\Throwable $e) {
            $this->logger->critical($e->getMessage(), $e->getTrace());
            return $proceed();
        }
    }
}
