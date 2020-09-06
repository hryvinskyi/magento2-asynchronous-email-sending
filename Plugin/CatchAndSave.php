<?php
/**
 * Copyright (c) 2020. Volodymyr Hryvinskyi.  All rights reserved.
 * @author: <mailto:volodymyr@hryvinskyi.com>
 * @github: <https://github.com/hryvinskyi>
 */

declare(strict_types=1);

namespace Hryvinskyi\AsynchronousEmailSending\Plugin;

use Hryvinskyi\AsynchronousEmailSending\Api\AsyncEmailRepositoryInterface;
use Hryvinskyi\AsynchronousEmailSending\Model\Config;
use Hryvinskyi\AsynchronousEmailSending\Service\SendFlag;
use Magento\Framework\Mail\TransportInterface;
use Psr\Log\LoggerInterface;

/**
 * Class CatchAndSave
 */
class CatchAndSave
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
     * @var SendFlag
     */
    private $sendFlag;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * CatchAndSave constructor.
     *
     * @param Config $config
     * @param AsyncEmailRepositoryInterface $asyncEmailRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        Config $config,
        AsyncEmailRepositoryInterface $asyncEmailRepository,
        SendFlag $sendFlag,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->asyncEmailRepository = $asyncEmailRepository;
        $this->sendFlag = $sendFlag;
        $this->logger = $logger;
    }

    /**
     * @param TransportInterface $subject
     * @param \Closure $proceed
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
            $proceed();
        }
    }
}
