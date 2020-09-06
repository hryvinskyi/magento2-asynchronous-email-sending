<?php
/**
 * Copyright (c) 2020. Volodymyr Hryvinskyi.  All rights reserved.
 * @author: <mailto:volodymyr@hryvinskyi.com>
 * @github: <https://github.com/hryvinskyi>
 */

declare(strict_types=1);

namespace Hryvinskyi\AsynchronousEmailSending\Cron;

use Hryvinskyi\AsynchronousEmailSending\Model\EmailSenderHandlerInterface;

/**
 * Queue emails sending.
 */
class SendEmails
{
    /**
     * Global configuration storage.
     *
     * @var EmailSenderHandlerInterface
     */
    private $emailSenderHandler;

    /**
     * @param EmailSenderHandlerInterface $emailSenderHandler
     */
    public function __construct(EmailSenderHandlerInterface $emailSenderHandler)
    {
        $this->emailSenderHandler = $emailSenderHandler;
    }

    /**
     * Handles asynchronous email sending during corresponding cron job.
     *
     * @return void
     */
    public function execute(): void
    {
        $this->emailSenderHandler->sendEmails();
    }
}
