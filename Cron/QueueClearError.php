<?php
/**
 * Copyright (c) 2020-2025. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\AsynchronousEmailSending\Cron;

use Hryvinskyi\AsynchronousEmailSending\Api\AsyncEmailRepositoryInterface;
use Hryvinskyi\AsynchronousEmailSending\Api\Data\AsyncEmailInterface;
use Hryvinskyi\AsynchronousEmailSending\Exception\InvalidStatusException;
use Hryvinskyi\AsynchronousEmailSending\Model\Config;
use Psr\Log\LoggerInterface;

/**
 * Cron job to clear old error emails from queue
 */
class QueueClearError
{
    /**
     * @param Config $config
     * @param AsyncEmailRepositoryInterface $asyncEmailRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly Config $config,
        private readonly AsyncEmailRepositoryInterface $asyncEmailRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Clear old success emails
     */
    public function execute(): void
    {
        if ($this->config->isEnabled() === false || ($days = $this->config->getClearErrorAfterDays()) <= 0) {
            return;
        }

        try {
            $this->asyncEmailRepository->clear($days, AsyncEmailInterface::STATUS_ERROR);
        } catch (InvalidStatusException $e) {
            $this->logger->critical($e->getMessage());
        }
    }
}
