<?php
/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\ProductPurchaseCount\Test\Unit\Model\Config\Source;

use PHPUnit\Framework\TestCase;
use Space\ProductPurchaseCount\Model\Config\Source\Interval;

class IntervalTest extends TestCase
{
    /**
     * @var Interval
     */
    private Interval $model;

    protected function setUp(): void
    {
        $this->model = new Interval();
    }

    public function testToOptionArray(): void
    {
        $result = $this->model->toOptionArray();

        $this->assertIsArray($result);
        $this->assertCount(2, $result);

        $this->assertEquals(Interval::LAST_THREE_DAYS, $result[0]['value']);
        $this->assertEquals('Last 3 days', (string)$result[0]['label']);

        $this->assertEquals(Interval::LAST_WEEK, $result[1]['value']);
        $this->assertEquals('Last week (7 days)', (string)$result[1]['label']);
    }
}
