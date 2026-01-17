<?php
/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\ProductPurchaseCount\Test\Unit\Model\ResourceModel;

use Magento\Framework\DB\Select;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Item\Collection as OrderItemCollection;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory as OrderItemCollectionFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Space\ProductPurchaseCount\Model\ResourceModel\CustomerOrders;

class CustomerOrdersTest extends TestCase
{
    /**
     * @var OrderCollectionFactory|MockObject
     */
    private OrderCollectionFactory|MockObject $orderCollectionFactoryMock;

    /**
     * @var OrderItemCollectionFactory|MockObject
     */
    private OrderItemCollectionFactory|MockObject $orderItemCollectionFactoryMock;

    /**
     * @var CustomerOrders
     */
    private CustomerOrders $model;

    protected function setUp(): void
    {
        $this->orderCollectionFactoryMock = $this->createMock(OrderCollectionFactory::class);
        $this->orderItemCollectionFactoryMock = $this->createMock(OrderItemCollectionFactory::class);

        $this->model = new CustomerOrders(
            $this->orderCollectionFactoryMock,
            $this->orderItemCollectionFactoryMock
        );
    }

    public function testGetCustomerOrders(): void
    {
        $productId = 1;
        $storeId = 1;
        $startDate = '2023-01-01';
        $endDate = '2023-01-31';
        $orderIds = [10, 11];

        $orderItemCollectionMock = $this->createMock(OrderItemCollection::class);
        $this->orderItemCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($orderItemCollectionMock);

        $orderItemCollectionMock->expects($this->once())
            ->method('addAttributeToSelect')
            ->with(OrderItemInterface::ORDER_ID)
            ->willReturnSelf();

        $orderItemCollectionMock->expects($this->exactly(3))
            ->method('addFieldToFilter')
            ->willReturnMap([
                [OrderItemInterface::STORE_ID, ['eq' => $storeId], $orderItemCollectionMock],
                [OrderItemInterface::PRODUCT_ID, ['eq' => $productId], $orderItemCollectionMock],
                [OrderInterface::CREATED_AT, ['from' => $startDate, 'to' => $endDate], $orderItemCollectionMock]
            ]);

        $orderItemCollectionMock->expects($this->once())
            ->method('distinct')
            ->with(true)
            ->willReturnSelf();

        $orderItemCollectionMock->expects($this->once())
            ->method('getSize')
            ->willReturn(2);

        $item1 = $this->createMock(\Magento\Sales\Model\Order\Item::class);
        $item1->expects($this->once())->method('getOrderId')->willReturn($orderIds[0]);
        $item2 = $this->createMock(\Magento\Sales\Model\Order\Item::class);
        $item2->expects($this->once())->method('getOrderId')->willReturn($orderIds[1]);

        $orderItemCollectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$item1, $item2]));

        $orderCollectionMock = $this->createMock(OrderCollection::class);
        $this->orderCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($orderCollectionMock);

        $orderCollectionMock->expects($this->once())
            ->method('addAttributeToSelect')
            ->with(OrderInterface::CUSTOMER_EMAIL)
            ->willReturnSelf();

        $orderCollectionMock->expects($this->exactly(2))
            ->method('addFieldToFilter')
            ->willReturnMap([
                [OrderInterface::ENTITY_ID, ['in' => $orderIds], $orderCollectionMock],
                [OrderInterface::STORE_ID, ['eq' => $storeId], $orderCollectionMock]
            ]);

        $selectMock = $this->createMock(Select::class);
        $orderCollectionMock->expects($this->once())
            ->method('getSelect')
            ->willReturn($selectMock);

        $selectMock->expects($this->once())
            ->method('distinct')
            ->willReturnSelf();

        $selectMock->expects($this->once())
            ->method('group')
            ->with(OrderInterface::CUSTOMER_EMAIL)
            ->willReturnSelf();

        $orderCollectionMock->expects($this->once())
            ->method('getSize')
            ->willReturn(2);

        $this->assertEquals(2, $this->model->getCustomerOrders($productId, $storeId, $startDate, $endDate));
    }

    public function testGetCustomerOrdersWithNoOrders(): void
    {
        $productId = 1;
        $storeId = 1;
        $startDate = '2023-01-01';
        $endDate = '2023-01-31';

        $orderItemCollectionMock = $this->createMock(OrderItemCollection::class);
        $this->orderItemCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($orderItemCollectionMock);

        $orderItemCollectionMock->expects($this->once())
            ->method('addAttributeToSelect')
            ->with(OrderItemInterface::ORDER_ID)
            ->willReturnSelf();

        $orderItemCollectionMock->expects($this->exactly(3))
            ->method('addFieldToFilter')
            ->willReturnMap([
                [OrderItemInterface::STORE_ID, ['eq' => $storeId], $orderItemCollectionMock],
                [OrderItemInterface::PRODUCT_ID, ['eq' => $productId], $orderItemCollectionMock],
                [OrderInterface::CREATED_AT, ['from' => $startDate, 'to' => $endDate], $orderItemCollectionMock]
            ]);

        $orderItemCollectionMock->expects($this->once())
            ->method('distinct')
            ->with(true)
            ->willReturnSelf();

        $orderItemCollectionMock->expects($this->once())
            ->method('getSize')
            ->willReturn(0);

        $this->orderCollectionFactoryMock->expects($this->never())
            ->method('create');

        $this->assertEquals(0, $this->model->getCustomerOrders($productId, $storeId, $startDate, $endDate));
    }
}
