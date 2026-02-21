<?php
/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\ProductPurchaseCount\Test\Unit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Space\ProductPurchaseCount\Api\Data\ConfigInterface;
use Space\ProductPurchaseCount\Model\Config;
use Space\ProductPurchaseCount\Model\Config\Source\Interval;

class ConfigTest extends TestCase
{
    /**
     * @var ScopeConfigInterface|MockObject
     */
    private ScopeConfigInterface|MockObject $scopeConfigMock;

    /**
     * @var Config
     */
    private Config $model;

    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMockForAbstractClass();

        $this->model = new Config($this->scopeConfigMock);
    }

    public function testIsEnabled(): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(
                ConfigInterface::XML_PATH_ENABLED,
                ScopeInterface::SCOPE_WEBSITE
            )
            ->willReturn(true);

        $this->assertTrue($this->model->isEnabled());
    }

    public function testGetInterval(): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                ConfigInterface::XML_PATH_INTERVAL,
                ScopeInterface::SCOPE_WEBSITE
            )
            ->willReturn(Interval::LAST_THREE_DAYS);

        $this->assertEquals(Interval::LAST_THREE_DAYS, $this->model->getInterval());
    }

    public function testGetNotificationTextForLastThreeDays(): void
    {
        $this->scopeConfigMock->expects($this->exactly(2))
            ->method('getValue')
            ->willReturnMap([
                [
                    ConfigInterface::XML_PATH_INTERVAL,
                    ScopeInterface::SCOPE_WEBSITE,
                    null,
                    Interval::LAST_THREE_DAYS
                ],
                [
                    ConfigInterface::XML_PATH_NOTIFICATION_TEXT,
                    ScopeInterface::SCOPE_WEBSITE,
                    null,
                    'Notification Text'
                ]
            ]);

        $this->assertEquals('Notification Text', $this->model->getNotificationText());
    }

    public function testGetNotificationTextForExtendedInterval(): void
    {
        $this->scopeConfigMock->expects($this->exactly(2))
            ->method('getValue')
            ->willReturnMap([
                [
                    ConfigInterface::XML_PATH_INTERVAL,
                    ScopeInterface::SCOPE_WEBSITE,
                    null,
                    Interval::LAST_WEEK
                ],
                [
                    ConfigInterface::XML_PATH_EXTENDED_NOTIFICATION_TEXT,
                    ScopeInterface::SCOPE_WEBSITE,
                    null,
                    'Extended Notification Text'
                ]
            ]);

        $this->assertEquals('Extended Notification Text', $this->model->getNotificationText());
    }

    public function testGetNotificationPosition(): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                ConfigInterface::XML_PATH_NOTIFICATION_POSITION,
                ScopeInterface::SCOPE_WEBSITE
            )
            ->willReturn('top');

        $this->assertEquals('top', $this->model->getNotificationPosition());
    }

    public function testGetOrdersState(): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                ConfigInterface::XML_PATH_STATE,
                ScopeInterface::SCOPE_WEBSITE
            )
            ->willReturn('processing');

        $this->assertEquals('processing', $this->model->getOrdersState());
    }

    public function testGetMaximumOrders(): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                ConfigInterface::XML_PATH_MAXIMUM_ORDERS,
                ScopeInterface::SCOPE_WEBSITE
            )
            ->willReturn(10);

        $this->assertEquals(10, $this->model->getMaximumOrders());
    }
}
