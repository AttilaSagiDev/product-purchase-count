<?php
/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\ProductPurchaseCount\Test\Unit\ViewModel\Catalog\View;

use Magento\Catalog\Model\Product;
use Magento\Framework\Registry;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Space\ProductPurchaseCount\Api\Data\ConfigInterface;
use Space\ProductPurchaseCount\ViewModel\Catalog\View\Count;

class CountTest extends TestCase
{
    /**
     * @var Registry|MockObject
     */
    private $registryMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var ConfigInterface|MockObject
     */
    private $configMock;

    /**
     * @var Count
     */
    private Count $model;

    protected function setUp(): void
    {
        $this->registryMock = $this->createMock(Registry::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->configMock = $this->createMock(ConfigInterface::class);

        $this->model = new Count(
            $this->registryMock,
            $this->storeManagerMock,
            $this->configMock
        );
    }

    public function testGetProductId(): void
    {
        $productId = 123;
        $productMock = $this->createMock(Product::class);
        $productMock->expects($this->once())->method('getId')->willReturn($productId);

        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with('product')
            ->willReturn($productMock);

        $this->assertEquals($productId, $this->model->getProductId());
    }

    public function testGetStoreCode(): void
    {
        $storeCode = 'default';
        $storeMock = $this->createMock(StoreInterface::class);
        $storeMock->expects($this->once())->method('getCode')->willReturn($storeCode);

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);

        $this->assertEquals($storeCode, $this->model->getStoreCode());
    }

    public function testGetPositionToShow(): void
    {
        $position = 'top';
        $this->configMock->expects($this->once())
            ->method('getNotificationPosition')
            ->willReturn($position);

        $this->assertEquals($position, $this->model->getPositionToShow());
    }
}
