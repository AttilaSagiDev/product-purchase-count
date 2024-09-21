<?php
/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\ProductPurchaseCount\Model\ResourceModel;

use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory as OrderItemCollectionFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;

/**
 * Customer orders class. Used to manage count orders ut it's slower
 *
 * @deprecated since version 0.9.0
 * @see \Space\ProductPurchaseCount\Model\Service\PurchaseCalculation
 */
class CustomerOrders
{
    /**
     * @var OrderCollectionFactory
     */
    private OrderCollectionFactory $orderCollectionFactory;

    /**
     * @var OrderItemCollectionFactory
     */
    private OrderItemCollectionFactory $orderItemCollectionFactory;

    /**
     * Constructor
     *
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param OrderItemCollectionFactory $orderItemCollectionFactory
     */
    public function __construct(
        OrderCollectionFactory $orderCollectionFactory,
        OrderItemCollectionFactory $orderItemCollectionFactory
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->orderItemCollectionFactory = $orderItemCollectionFactory;
    }

    /**
     * Get customer orders
     *
     * @param int $productId
     * @param int $storeId
     * @param string $startDate
     * @param string $endDate
     * @return int
     * @deprecated since version 0.9.0
     * @see \Space\ProductPurchaseCount\Model\Service\PurchaseCalculation::getPurchaseCount()
     */
    public function getCustomerOrders(
        int $productId,
        int $storeId,
        string $startDate,
        string $endDate
    ): int {
        return $this->getCustomerOrdersCount($productId, $storeId, $startDate, $endDate);
    }

    /**
     * Get customer orders
     *
     * @param int $productId
     * @param int $storeId
     * @param string $startDate
     * @param string $endDate
     * @return int
     */
    private function getCustomerOrdersCount(
        int $productId,
        int $storeId,
        string $startDate,
        string $endDate
    ): int {
        $count = 0;

        $orderIds = $this->getOrderIdsByProductId($productId, $storeId, $startDate, $endDate);
        if (!empty($orderIds)) {
            $orderCollection = $this->orderCollectionFactory->create();
            $orderCollection->addAttributeToSelect(OrderInterface::CUSTOMER_EMAIL)
                ->addFieldToFilter(OrderInterface::ENTITY_ID, ['in' => $orderIds])
                ->addFieldToFilter(OrderInterface::STORE_ID, ['eq' => $storeId]);
            $orderCollection->getSelect()
                ->distinct()
                ->group(OrderInterface::CUSTOMER_EMAIL);

            $count = $orderCollection->getSize();
        }

        return $count;
    }

    /**
     * Get order Ids by product ID collection
     *
     * @param int $productId
     * @param int $storeId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    private function getOrderIdsByProductId(
        int $productId,
        int $storeId,
        string $startDate,
        string $endDate
    ): array {
        $orderIds = [];

        $orderItemCollection = $this->orderItemCollectionFactory->create();
        $orderItemCollection->addAttributeToSelect(OrderItemInterface::ORDER_ID)
            ->addFieldToFilter(OrderItemInterface::STORE_ID, ['eq' => $storeId])
            ->addFieldToFilter(OrderItemInterface::PRODUCT_ID, ['eq' => $productId])
            ->addFieldToFilter(
                OrderInterface::CREATED_AT,
                ['from' => $startDate, 'to' => $endDate]
            )->distinct(true);
        if ($orderItemCollection->getSize()) {
            foreach ($orderItemCollection as $orderItem) {
                $orderIds[] = $orderItem->getOrderId();
            }
        }

        return $orderIds;
    }
}
