<?php
/**
 * Copyright (c) 2020. Volodymyr Hryvinskyi.  All rights reserved.
 * @author: <mailto:volodymyr@hryvinskyi.com>
 * @github: <https://github.com/hryvinskyi>
 */

declare(strict_types=1);

namespace Hryvinskyi\AsynchronousEmailSending\Model;

use Magento\Email\Model\Transport;
use Magento\Framework\Mail\MailMessageInterface;
use Magento\Framework\ObjectManagerInterface;
use Zend\Mail\Message;

/**
 * Class TransportFactory
 */
class TransportFactory
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Instance name to create
     *
     * @var string
     */
    private $instanceName;

    /**
     * Factory constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        $instanceName = Transport::class
    ) {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param string $message
     *
     * @return Transport
     */
    public function create(MailMessageInterface $message): Transport
    {
        return $this->objectManager->create($this->instanceName, ['message' => $message]);
    }
}
