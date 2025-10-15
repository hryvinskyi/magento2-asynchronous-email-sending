<?php
/**
 * Copyright (c) 2020-2025. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\AsynchronousEmailSending\Model;

use Magento\Email\Model\Transport;
use Magento\Framework\Mail\EmailMessageInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Factory for creating Transport instances
 */
class TransportFactory
{
    /**
     * @param ObjectManagerInterface $objectManager Object Manager instance
     * @param string $instanceName Instance name to create
     */
    public function __construct(
        private readonly ObjectManagerInterface $objectManager,
        private readonly string $instanceName = Transport::class
    ) {
    }

    /**
     * Create class instance with specified parameters
     *
     * @param EmailMessageInterface $message
     *
     * @return Transport
     */
    public function create(EmailMessageInterface $message): Transport
    {
        return $this->objectManager->create($this->instanceName, ['message' => $message]);
    }
}
