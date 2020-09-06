<?php
/**
 * Copyright (c) 2020. Volodymyr Hryvinskyi.  All rights reserved.
 * @author: <mailto:volodymyr@hryvinskyi.com>
 * @github: <https://github.com/hryvinskyi>
 */

declare(strict_types=1);

namespace Hryvinskyi\AsynchronousEmailSending\Model;

use Magento\Framework\Mail\Message;

/**
 * Class MailMessage
 */
class MailMessage extends Message
{
    /**
     * @var string
     */
    private $rawMessage = '';

    /**
     * @param string $rawMessage
     *
     * @return MailMessage
     */
    public function setRawMessage(string $rawMessage): MailMessage
    {
        $this->rawMessage = $rawMessage;

        return $this;
    }

    /**
     * @return string
     */
    public function getRawMessage() {
        return $this->rawMessage;
    }
}
