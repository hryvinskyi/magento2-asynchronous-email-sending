<?php
/**
 * Copyright (c) 2020. Volodymyr Hryvinskyi.  All rights reserved.
 * @author: <mailto:volodymyr@hryvinskyi.com>
 * @github: <https://github.com/hryvinskyi>
 */

declare(strict_types=1);

namespace Hryvinskyi\AsynchronousEmailSending\Model;

use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Logger\Handler\Base;

/**
 * Class Debug
 */
class Debug extends Base
{
    /**
     * @var Config
     */
    private $config;

    /**
     * Debug constructor.
     *
     * @param DriverInterface $filesystem
     * @param Config $config
     * @param null $filePath
     * @param string $fileName
     *
     * @throws \Exception
     */
    public function __construct(
        DriverInterface $filesystem,
        Config $config,
        $filePath = null,
        $fileName = '/var/log/asynchronous_email_sending.log'
    ) {
        parent::__construct($filesystem, $filePath, $fileName);

        $this->config = $config;
    }

    /**
     * @inheritDoc
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function write(array $record)
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
