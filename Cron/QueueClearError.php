<?php
/**
 * Copyright (c) 2020. Volodymyr Hryvinskyi.  All rights reserved.
 * @author: <mailto:volodymyr@hryvinskyi.com>
 * @github: <https://github.com/hryvinskyi>
 */

declare(strict_types=1);

namespace Hryvinskyi\AsynchronousEmailSending\Cron;

use Hryvinskyi\AsynchronousEmailSending\Api\AsyncEmailRepositoryInterface;
use Hryvinskyi\AsynchronousEmailSending\Api\Data\AsyncEmailInterface;
use Hryvinskyi\AsynchronousEmailSending\Exception\InvalidStatusException;
use Hryvinskyi\AsynchronousEmailSending\Model\Config;
use Psr\Log\LoggerInterface;

class QueueClearError
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * QueueClearSuccess constructor.
     *
     * @param Config $config
     * @param AsyncEmailRepositoryInterface $asyncEmailRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        Config $config,
        AsyncEmailRepositoryInterface $asyncEmailRepository,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->asyncEmailRepository = $asyncEmailRepository;
        $this->logger = $logger;
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
