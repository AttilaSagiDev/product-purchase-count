<?php
/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\ProductPurchaseCount\Test\Unit\Model\Config\Source;

use PHPUnit\Framework\TestCase;
use Space\ProductPurchaseCount\Model\Config\Source\NotificationPosition;

class NotificationPositionTest extends TestCase
{
    /**
     * @var NotificationPosition
     */
    private NotificationPosition $model;

    protected function setUp(): void
    {
        $this->model = new NotificationPosition();
    }

    public function testToOptionArray(): void
    {
        $result = $this->model->toOptionArray();

        $this->assertIsArray($result);
        $this->assertCount(2, $result);

        $this->assertEquals(NotificationPosition::AFTER_PRODUCT_INFO, $result[0]['value']);
        $this->assertEquals('After Product Info', (string)$result[0]['label']);

        $this->assertEquals(NotificationPosition::AFTER_MEDIA_GALLERY, $result[1]['value']);
        $this->assertEquals('After Media Gallery', (string)$result[1]['label']);
    }
}
