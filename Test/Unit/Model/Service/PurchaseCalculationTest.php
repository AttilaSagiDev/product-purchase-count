<?php
/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\ProductPurchaseCount\Test\Unit\Model\Service;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Escaper;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime as StdlibDateTime;
use Magento\Sales\Model\ResourceModel\Order\Item as ResourceItem;
use Magento\Sales\Model\ResourceModel\Order as ResourceOrder;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Space\ProductPurchaseCount\Api\Data\ConfigInterface;
use Space\ProductPurchaseCount\Api\Data\ProductPurchaseCountInterface;
use Space\ProductPurchaseCount\Api\Data\ProductPurchaseCountInterfaceFactory;
use Space\ProductPurchaseCount\Model\Config\Source\OrderSates;
use Space\ProductPurchaseCount\Model\Service\PurchaseCalculation;

class PurchaseCalculationTest extends TestCase
{
    /**
     * @var ProductPurchaseCountInterfaceFactory|MockObject
     */
    private ProductPurchaseCountInterfaceFactory|MockObject $productPurchaseCountFactoryMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private StoreManagerInterface|MockObject $storeManagerMock;

    /**
     * @var ResourceItem|MockObject
     */
    private ResourceItem|MockObject $resourceItemMock;

    /**
     * @var ResourceOrder|MockObject
     */
    private ResourceOrder|MockObject $resourceOrderMock;

    /**
     * @var DateTime|MockObject
     */
    private DateTime|MockObject $dateTimeMock;

    /**
     * @var ConfigInterface|MockObject
     */
    private ConfigInterface|MockObject $configMock;

    /**
     * @var Escaper|MockObject
     */
    private Escaper|MockObject $escaperMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private LoggerInterface|MockObject $loggerMock;

    /**
     * @var PurchaseCalculation
     */
    private PurchaseCalculation $model;

    protected function setUp(): void
    {
        $this->productPurchaseCountFactoryMock = $this->createMock(ProductPurchaseCountInterfaceFactory::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->resourceItemMock = $this->createMock(ResourceItem::class);
        $this->resourceOrderMock = $this->createMock(ResourceOrder::class);
        $this->dateTimeMock = $this->createMock(DateTime::class);
        $this->configMock = $this->createMock(ConfigInterface::class);
        $this->escaperMock = $this->createMock(Escaper::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);

        $this->model = new PurchaseCalculation(
            $this->productPurchaseCountFactoryMock,
            $this->storeManagerMock,
            $this->resourceItemMock,
            $this->resourceOrderMock,
            $this->dateTimeMock,
            $this->configMock,
            $this->escaperMock,
            $this->loggerMock
        );
    }

    public function testGetPurchaseCount(): void
    {
        $productId = 1;
        $storeId = 1;
        $interval = 3;
        $orderIds = [10, 11];
        $orderCount = 2;
        $notificationText = '2 customers bought this product';

        $productPurchaseCountMock = $this->createMock(ProductPurchaseCountInterface::class);
        $this->productPurchaseCountFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($productPurchaseCountMock);

        $storeMock = $this->createMock(StoreInterface::class);
        $storeMock->expects($this->once())->method('getId')->willReturn($storeId);
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($storeMock);

        $this->dateTimeMock->expects($this->exactly(2))
            ->method('date')
            ->willReturnOnConsecutiveCalls('2023-10-27 23:59:59', '2023-10-24');

        $this->configMock->expects($this->once())->method('getInterval')->willReturn($interval);
        $this->configMock->expects($this->once())->method('getMaximumOrders')->willReturn(10);
        $this->configMock->expects($this->once())->method('getOrdersState')->willReturn(OrderSates::ALL);
        $this->configMock->expects($this->once())
            ->method('getNotificationText')->willReturn('%c customers bought this product');

        $connectionMock = $this->createMock(AdapterInterface::class);
        $selectMock = $this->createMock(Select::class);

        $this->resourceItemMock->expects($this->once())->method('getConnection')->willReturn($connectionMock);
        $this->resourceItemMock->expects($this->once())->method('getMainTable')->willReturn('sales_order_item');

        $connectionMock->expects($this->atLeastOnce())->method('select')->willReturn($selectMock);
        $selectMock->expects($this->any())->method('from')->willReturnSelf();
        $selectMock->expects($this->any())->method('where')->willReturnSelf();
        $selectMock->expects($this->any())->method('order')->willReturnSelf();
        $selectMock->expects($this->any())->method('limit')->willReturnSelf();
        $selectMock->expects($this->any())->method('distinct')->willReturnSelf();

        $connectionMock->expects($this->once())->method('fetchCol')->willReturn($orderIds);

        $this->resourceOrderMock->expects($this->once())->method('getConnection')->willReturn($connectionMock);
        $this->resourceOrderMock->expects($this->once())->method('getMainTable')->willReturn('sales_order');

        $connectionMock->expects($this->once())->method('fetchOne')->willReturn($orderCount);

        $this->escaperMock->expects($this->once())
            ->method('escapeHtml')
            ->with($notificationText, ['strong'])
            ->willReturn($notificationText);

        $productPurchaseCountMock->expects($this->once())->method('setCount')->with($orderCount);
        $productPurchaseCountMock->expects($this->once())->method('setNotificationText')->with($notificationText);

        $result = $this->model->getPurchaseCount($productId);
        $this->assertSame($productPurchaseCountMock, $result);
    }

    public function testGetPurchaseCountWithNoOrders(): void
    {
        $productId = 1;
        $storeId = 1;
        $interval = 3;

        $productPurchaseCountMock = $this->createMock(ProductPurchaseCountInterface::class);
        $this->productPurchaseCountFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($productPurchaseCountMock);

        $storeMock = $this->createMock(StoreInterface::class);
        $storeMock->expects($this->once())->method('getId')->willReturn($storeId);
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($storeMock);

        $this->dateTimeMock->expects($this->exactly(2))
            ->method('date')
            ->willReturnOnConsecutiveCalls('2023-10-27 23:59:59', '2023-10-24');

        $this->configMock->expects($this->once())->method('getInterval')->willReturn($interval);
        $this->configMock->expects($this->once())->method('getMaximumOrders')->willReturn(10);

        $connectionMock = $this->createMock(AdapterInterface::class);
        $selectMock = $this->createMock(Select::class);

        $this->resourceItemMock->expects($this->once())->method('getConnection')->willReturn($connectionMock);
        $this->resourceItemMock->expects($this->once())->method('getMainTable')->willReturn('sales_order_item');

        $connectionMock->expects($this->once())->method('select')->willReturn($selectMock);
        $selectMock->expects($this->any())->method('from')->willReturnSelf();
        $selectMock->expects($this->any())->method('where')->willReturnSelf();
        $selectMock->expects($this->any())->method('order')->willReturnSelf();
        $selectMock->expects($this->any())->method('limit')->willReturnSelf();
        $selectMock->expects($this->any())->method('distinct')->willReturnSelf();

        $connectionMock->expects($this->once())->method('fetchCol')->willReturn([]);

        $productPurchaseCountMock->expects($this->once())->method('setCount')->with(0);
        $productPurchaseCountMock->expects($this->once())->method('setNotificationText')->with('');

        $result = $this->model->getPurchaseCount($productId);
        $this->assertSame($productPurchaseCountMock, $result);
    }
}
