<?php
/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\ProductPurchaseCount\Test\Unit\Model\Config\Source;

use Magento\Sales\Model\Order;
use PHPUnit\Framework\TestCase;
use Space\ProductPurchaseCount\Model\Config\Source\OrderSates;

class OrderSatesTest extends TestCase
{
    /**
     * @var OrderSates
     */
    private OrderSates $model;

    protected function setUp(): void
    {
        $this->model = new OrderSates();
    }

    public function testToOptionArray(): void
    {
        $result = $this->model->toOptionArray();

        $this->assertIsArray($result);
        $this->assertCount(2, $result);

        $this->assertEquals(OrderSates::ALL, $result[0]['value']);
        $this->assertEquals('All order states', (string)$result[0]['label']);

        $this->assertEquals(Order::STATE_COMPLETE, $result[1]['value']);
        $this->assertEquals('Completed only', (string)$result[1]['label']);
    }
}
