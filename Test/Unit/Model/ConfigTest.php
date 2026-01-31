<?php
/**
 * Copyright (c) 2025-2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\AsynchronousEmailSending\Test\Unit\Model;

use Hryvinskyi\AsynchronousEmailSending\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Config class
 *
 * Tests configuration retrieval and validation
 */
class ConfigTest extends TestCase
{
    /**
     * Scope config mock
     *
     * @var ScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $scopeConfigMock;

    /**
     * Config instance
     *
     * @var Config
     */
    private Config $config;

    /**
     * Set up test dependencies
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->config = new Config($this->scopeConfigMock);
    }

    /**
     * Test isEnabled returns true when enabled
     *
     * @return void
     */
    public function testIsEnabledReturnsTrue(): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(Config::XML_CONF_GENERAL_ENABLED)
            ->willReturn(true);

        $this->assertTrue($this->config->isEnabled());
    }

    /**
     * Test isEnabled returns false when disabled
     *
     * @return void
     */
    public function testIsEnabledReturnsFalse(): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(Config::XML_CONF_GENERAL_ENABLED)
            ->willReturn(false);

        $this->assertFalse($this->config->isEnabled());
    }

    /**
     * Test getSendingLimit returns configured value
     *
     * @return void
     */
    public function testGetSendingLimitReturnsConfiguredValue(): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_CONF_GENERAL_SENDING_LIMIT)
            ->willReturn('50');

        $this->assertEquals(50, $this->config->getSendingLimit());
    }

    /**
     * Test getSendingLimit returns default when not configured
     *
     * @return void
     */
    public function testGetSendingLimitReturnsDefaultValue(): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_CONF_GENERAL_SENDING_LIMIT)
            ->willReturn(null);

        $result = $this->config->getSendingLimit();
        $this->assertIsInt($result);
        $this->assertEquals(0, $result);
    }
}
