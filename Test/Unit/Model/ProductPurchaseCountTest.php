<?php
/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\ProductPurchaseCount\Test\Unit\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use PHPUnit\Framework\TestCase;
use Space\ProductPurchaseCount\Model\ProductPurchaseCount;

class ProductPurchaseCountTest extends TestCase
{
    /**
     * @var ProductPurchaseCount
     */
    private ProductPurchaseCount $model;

    /**
     * @throws LocalizedException
     */
    protected function setUp(): void
    {
        $this->model = new ProductPurchaseCount(
            $this->createMock(Context::class),
            $this->createMock(Registry::class)
        );
    }

    public function testGetCount(): void
    {
        $this->model->setData('count', 5);
        $this->assertEquals(5, $this->model->getCount());
    }

    public function testGetNotificationText(): void
    {
        $this->model->setData('notification_text', 'Test Notification');
        $this->assertEquals('Test Notification', $this->model->getNotificationText());
    }

    public function testSetCount(): void
    {
        $this->model->setCount(10);
        $this->assertEquals(10, $this->model->getData('count'));
    }

    public function testSetNotificationText(): void
    {
        $this->model->setNotificationText('New Notification');
        $this->assertEquals('New Notification', $this->model->getData('notification_text'));
    }
}
