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
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;

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
     * Construct
     *
     * @param OrderItemRepositoryInterface $orderItemRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ProductPurchaseCountInterfaceFactory $productPurchaseCountFactory
     */
    public function __construct(
        OrderItemRepositoryInterface $orderItemRepository,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ProductPurchaseCountInterfaceFactory $productPurchaseCountFactory
    ) {
        $this->productPurchaseCountFactory = $productPurchaseCountFactory;
        $this->orderItemRepository = $orderItemRepository;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Get purchase count
     *
     * @param int $productId
     * @return ProductPurchaseCountInterface
     */
    public function getPurchaseCount(int $productId): ProductPurchaseCountInterface
    {
        $productPurchaseCount = $this->productPurchaseCountFactory->create();
        $orderIds = $this->getOrderIdsByProductId($productId);
        if (!empty($orderIds)) {
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter(OrderInterface::ENTITY_ID, $orderIds, 'in')->create();
            $orderCount = $this->orderRepository->getList($searchCriteria)->getTotalCount();
            $productPurchaseCount->setCount($orderCount);
        }

        return $productPurchaseCount;
    }

    /**
     * Get order order Ids by product Id
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
