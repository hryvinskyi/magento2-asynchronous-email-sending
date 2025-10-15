<?php
/**
 * Copyright (c) 2020-2025. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\AsynchronousEmailSending\Cron;

use Hryvinskyi\AsynchronousEmailSending\Model\EmailSenderHandlerInterface;

/**
 * Cron job to send queued emails
 */
class SendEmails
{
    /**
     * @param EmailSenderHandlerInterface $emailSenderHandler Email sender handler
     */
    public function __construct(
        private readonly EmailSenderHandlerInterface $emailSenderHandler
    ) {
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
