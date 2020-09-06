<?php
/**
 * Copyright (c) 2020. Volodymyr Hryvinskyi.  All rights reserved.
 * @author: <mailto:volodymyr@hryvinskyi.com>
 * @github: <https://github.com/hryvinskyi>
 */

declare(strict_types=1);

namespace Hryvinskyi\AsynchronousEmailSending\Model;

interface EmailSenderHandlerInterface
{
    /**
     * Handles asynchronous email sending
     *
     * @return void
     */
    public function sendEmails(): void;
}
