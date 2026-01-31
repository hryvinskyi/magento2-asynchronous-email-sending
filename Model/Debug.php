<?php
/**
 * Copyright (c) 2020-2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\AsynchronousEmailSending\Model;

use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Logger\Handler\Base;
use Monolog\LogRecord;

/**
 * Debug logger handler for asynchronous email sending
 */
class Debug extends Base
{
    /**
     * @param DriverInterface $filesystem
     * @param Config $config
     * @param string|null $filePath
     * @param string $fileName
     *
     * @throws \Exception
     */
    public function __construct(
        DriverInterface $filesystem,
        private readonly Config $config,
        ?string $filePath = null,
        string $fileName = '/var/log/asynchronous_email_sending.log'
    ) {
        parent::__construct($filesystem, $filePath, $fileName);
    }

    /**
     * @inheritDoc
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function write(LogRecord $record): void
    {
        if ($this->config->isDebug() === false) {
            return;
        }

        $logDir = $this->filesystem->getParentDirectory($this->url);
        if (!$this->filesystem->isDirectory($logDir)) {
            $this->filesystem->createDirectory($logDir);
        }

        parent::write($record);
    }
}
