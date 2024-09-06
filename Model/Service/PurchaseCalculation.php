<?php
/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\ProductPurchaseCount\Model\Service;

use Space\ProductPurchaseCount\Api\Data\ProductPurchaseCountInterface;
use Space\ProductPurchaseCount\Api\PurchaseCalculationInterface;
use Space\ProductPurchaseCount\Api\Data\ProductPurchaseCountInterfaceFactory;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Model\ResourceModel\Order\Item as ResourceItem;
use Magento\Sales\Model\ResourceModel\Order as ResourceOrder;
use Psr\Log\LoggerInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Framework\Exception\LocalizedException;

class PurchaseCalculation implements PurchaseCalculationInterface
{
    /**
     * @var ProductPurchaseCountInterfaceFactory
     */
    private ProductPurchaseCountInterfaceFactory $productPurchaseCountFactory;

    /**
     * @var OrderItemRepositoryInterface
     */
    private OrderItemRepositoryInterface $orderItemRepository;

    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var ResourceItem
     */
    private ResourceItem $resourceItem;

    /**
     * @var ResourceOrder
     */
    private ResourceOrder $resourceOrder;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Construct
     *
     * @param ProductPurchaseCountInterfaceFactory $productPurchaseCountFactory
     * @param OrderItemRepositoryInterface $orderItemRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ResourceItem $resourceItem
     * @param ResourceOrder $resourceOrder
     * @param LoggerInterface $logger
     */
    public function __construct(
        ProductPurchaseCountInterfaceFactory $productPurchaseCountFactory,
        OrderItemRepositoryInterface $orderItemRepository,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ResourceItem $resourceItem,
        ResourceOrder $resourceOrder,
        LoggerInterface $logger
    ) {
        $this->productPurchaseCountFactory = $productPurchaseCountFactory;
        $this->orderItemRepository = $orderItemRepository;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->resourceItem = $resourceItem;
        $this->resourceOrder = $resourceOrder;
        $this->logger = $logger;
    }

    /**
     * Get purchase count
     *
     * @param int $productId
     * @return ProductPurchaseCountInterface
     */
    public function getPurchaseCount(int $productId): ProductPurchaseCountInterface
    {
        $timeStart = microtime(true);

        $productPurchaseCountOriginal = $this->productPurchaseCountFactory->create();
        $orderIds = $this->getOrderIdsByProductId($productId);
        if (!empty($orderIds)) {
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter(OrderInterface::ENTITY_ID, $orderIds, 'in')->create();
            $orderCount = $this->orderRepository->getList($searchCriteria)->getTotalCount();
            $productPurchaseCountOriginal->setCount($orderCount);
        }

        $timeEnd = microtime(true);
        $executionTime = $timeEnd - $timeStart;

        $this->logger->debug('Space getOrdersCount API');
        $this->logger->debug('Time: ' . $executionTime);

        try {
            $timeStart = microtime(true);

            $productPurchaseCount = $this->productPurchaseCountFactory->create();
            $orderIds = $this->fetchOrderIdsByProductId($productId);
            if (!empty($orderIds)) {
                $orderCount = $this->fetchOrdersCountByOrderIds($orderIds);
                $productPurchaseCount->setCount($orderCount);
            }

            $timeEnd = microtime(true);
            $executionTime = $timeEnd - $timeStart;

            $this->logger->debug('Space getOrdersCount API Direct');
            $this->logger->debug('Time: ' . $executionTime);

            return $productPurchaseCount;
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }

        return $productPurchaseCountOriginal;
    }

    /**
     * Fetch orders count by order IDs
     *
     * @param array $orderIds
     * @return int
     * @throws LocalizedException
     */
    private function fetchOrdersCountByOrderIds(array $orderIds): int
    {
        $connection = $this->resourceOrder->getConnection();
        $select = $connection->select()
            ->from(
                $this->resourceOrder->getMainTable(),
                new \Zend_Db_Expr('COUNT(*)')
            )
            ->where(OrderInterface::ENTITY_ID . ' IN (?)', $orderIds);

        return (int)$connection->fetchOne($select);
    }

    /**
     * Fetch order Ids by product ID
     *
     * @param int $productId
     * @return array
     * @throws LocalizedException
     */
    private function fetchOrderIdsByProductId(int $productId): array
    {
        $connection = $this->resourceItem->getConnection();
        $select = $connection->select()
            ->from($this->resourceItem->getMainTable(), OrderItemInterface::ORDER_ID)
            ->where(OrderItemInterface::PRODUCT_ID . ' = ?', $productId);

        return $connection->fetchCol($select);
    }

    /**
     * Get order Ids by product ID
     *
     * @param int $productId
     * @return array
     */
    private function getOrderIdsByProductId(int $productId): array
    {
        $orderIds = [];

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(OrderItemInterface::PRODUCT_ID, $productId)
            ->create();
        $orderItems = $this->orderItemRepository->getList($searchCriteria)->getItems();

        if (!empty($orderItems)) {
            foreach ($orderItems as $orderItem) {
                $orderIds[] = $orderItem->getOrderId();
            }
        }

        return $orderIds;
    }
}
