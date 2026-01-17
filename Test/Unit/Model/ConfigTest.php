<?php
declare(strict_types=1);

namespace Space\ProductPurchaseCount\Test\Unit\Model;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Space\ProductPurchaseCount\Model\Config;
use Space\ProductPurchaseCount\Api\Data\ConfigInterface;
use Space\ProductPurchaseCount\Model\Config\Source\Interval;

class ConfigTest extends TestCase
{
    /**
     * @var ScopeConfigInterface|MockObject
     */
    private MockObject|ScopeConfigInterface $scopeConfigMock;

    /**
     * @var Config
     */
    private Config $model;

    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->model = new Config($this->scopeConfigMock);
    }

    public function testIsEnabledReturnsTrue()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(ConfigInterface::XML_PATH_ENABLED, ScopeInterface::SCOPE_WEBSITE)
            ->willReturn(true);

        $this->assertTrue($this->model->isEnabled());
    }

    public function testGetIntervalReturnsInt()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(ConfigInterface::XML_PATH_INTERVAL, ScopeInterface::SCOPE_WEBSITE)
            ->willReturn("7");

        $this->assertEquals(7, $this->model->getInterval());
    }

    /**
     * Fixed using willReturnCallback to handle multiple calls without withConsecutive
     */
    public function testGetNotificationTextReturnsStandardTextWhenIntervalIsThreeDays()
    {
        $this->scopeConfigMock->expects($this->exactly(2))
            ->method('getValue')
            ->willReturnCallback(function ($path, $scope) {
                if ($path === ConfigInterface::XML_PATH_INTERVAL) {
                    return Interval::LAST_THREE_DAYS;
                }
                if ($path === ConfigInterface::XML_PATH_NOTIFICATION_TEXT) {
                    return "Ordered in the last 3 days";
                }
                return null;
            });

        $this->assertEquals("Ordered in the last 3 days", $this->model->getNotificationText());
    }

    public function testGetNotificationTextReturnsExtendedTextWhenIntervalIsNotThreeDays()
    {
        $this->scopeConfigMock->expects($this->exactly(2))
            ->method('getValue')
            ->willReturnCallback(function ($path, $scope) {
                if ($path === ConfigInterface::XML_PATH_INTERVAL) {
                    return 99; // Not 3 days
                }
                if ($path === ConfigInterface::XML_PATH_EXTENDED_NOTIFICATION_TEXT) {
                    return "Ordered recently";
                }
                return null;
            });

        $this->assertEquals("Ordered recently", $this->model->getNotificationText());
    }

    public function testGetNotificationPosition()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(ConfigInterface::XML_PATH_NOTIFICATION_POSITION, ScopeInterface::SCOPE_WEBSITE)
            ->willReturn('top-right');

        $this->assertEquals('top-right', $this->model->getNotificationPosition());
    }

    public function testGetOrdersState()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(ConfigInterface::XML_PATH_STATE, ScopeInterface::SCOPE_WEBSITE)
            ->willReturn('processing');

        $this->assertEquals('processing', $this->model->getOrdersState());
    }

    public function testGetMaximumOrders()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(ConfigInterface::XML_PATH_MAXIMUM_ORDERS, ScopeInterface::SCOPE_WEBSITE)
            ->willReturn("50");

        $this->assertEquals(50, $this->model->getMaximumOrders());
    }
}
