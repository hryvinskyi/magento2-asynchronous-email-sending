<?php
/**
 * Copyright (c) 2020. Volodymyr Hryvinskyi.  All rights reserved.
 * @author: <mailto:volodymyr@hryvinskyi.com>
 * @github: <https://github.com/hryvinskyi>
 */

declare(strict_types=1);

namespace Hryvinskyi\AsynchronousEmailSending\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class Config
 */
class Config
{
    /**
     * Configuration paths
     */
    const XML_CONF_SALES_EMAIL_ASYNC_SENDING = 'sales_email/general/async_sending';
    const XML_CONF_GENERAL_ENABLED = 'hryvinskyi_asynchronous_sending/general/enabled';
    const XML_CONF_GENERAL_SENDING_LIMIT = 'hryvinskyi_asynchronous_sending/general/sending_limit';
    const XML_CONF_GENERAL_CLEAR_SUCCESS_AFTER_DAYS = 'hryvinskyi_asynchronous_sending/general/clear_success_after_days';
    const XML_CONF_GENERAL_CLEAR_ERRORS_AFTER_DAYS = 'hryvinskyi_asynchronous_sending/general/clear_errors_after_days';
    const XML_CONF_GENERAL_DEBUG = 'hryvinskyi_asynchronous_sending/general/debug';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Config constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_CONF_GENERAL_ENABLED);
    }

    /**
     * @return int
     */
    public function getSendingLimit(): int
    {
        return (int)$this->scopeConfig->getValue(self::XML_CONF_GENERAL_SENDING_LIMIT);
    }

    /**
     * @return bool
     */
    public function isSalesAsyncSending(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_CONF_SALES_EMAIL_ASYNC_SENDING);
    }

    /**
     * @return int
     */
    public function getClearSuccessAfterDays(): int
    {
        return (int)$this->scopeConfig->getValue(self::XML_CONF_GENERAL_CLEAR_SUCCESS_AFTER_DAYS);
    }

    /**
     * @return int
     */
    public function getClearErrorAfterDays(): int
    {
        return (int)$this->scopeConfig->getValue(self::XML_CONF_GENERAL_CLEAR_ERRORS_AFTER_DAYS);
    }

    /**
     * @return bool
     */
    public function isDebug(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_CONF_GENERAL_DEBUG);
    }
}
