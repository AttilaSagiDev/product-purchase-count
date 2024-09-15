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
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory as OrderItemCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Model\ResourceModel\Order\Item as ResourceItem;
use Magento\Sales\Model\ResourceModel\Order as ResourceOrder;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime as StdlibDateTime;
use Space\ProductPurchaseCount\Api\Data\ConfigInterface;
use Magento\Framework\Escaper;
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
     * @var OrderCollectionFactory
     */
    private OrderCollectionFactory $orderCollectionFactory;

    /**
     * @var OrderItemCollectionFactory
     */
    private OrderItemCollectionFactory $orderItemCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var ResourceItem
     */
    private ResourceItem $resourceItem;

    /**
     * @var ResourceOrder
     */
    private ResourceOrder $resourceOrder;

    /**
     * @var DateTime
     */
    private DateTime $dateTime;

    /**
     * @var ConfigInterface
     */
    private ConfigInterface $config;

    /**
     * @var Escaper
     */
    private Escaper $escaper;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Construct
     *
     * @param ProductPurchaseCountInterfaceFactory $productPurchaseCountFactory
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param OrderItemCollectionFactory $orderItemCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param ResourceItem $resourceItem
     * @param ResourceOrder $resourceOrder
     * @param DateTime $dateTime
     * @param ConfigInterface $config
     * @param Escaper $escaper
     * @param LoggerInterface $logger
     */
    public function __construct(
        ProductPurchaseCountInterfaceFactory $productPurchaseCountFactory,
        OrderCollectionFactory $orderCollectionFactory,
        OrderItemCollectionFactory $orderItemCollectionFactory,
        StoreManagerInterface $storeManager,
        ResourceItem $resourceItem,
        ResourceOrder $resourceOrder,
        DateTime $dateTime,
        ConfigInterface $config,
        Escaper $escaper,
        LoggerInterface $logger
    ) {
        $this->productPurchaseCountFactory = $productPurchaseCountFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->orderItemCollectionFactory = $orderItemCollectionFactory;
        $this->storeManager = $storeManager;
        $this->resourceItem = $resourceItem;
        $this->resourceOrder = $resourceOrder;
        $this->dateTime = $dateTime;
        $this->config = $config;
        $this->escaper = $escaper;
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
        $productPurchaseCountOriginal = $this->productPurchaseCountFactory->create();
        try {
            //-------------------

            $storeId = $this->storeManager->getStore()->getId();
            $endDate = $this->dateTime->date(StdlibDateTime::DATE_PHP_FORMAT . ' 23:59:59');
            $startDate = $this->dateTime->date(
                StdlibDateTime::DATE_PHP_FORMAT,
                strtotime($endDate . ' -' . $this->config->getInterval() . ' days')
            );
            $startDate .= ' 00:00:00';

            //-------------------

            $timeStart = microtime(true);
            $this->logger->debug('Space getOrdersCount Collection');

            $orderIds = $this->getOrderIdsByProductIdCollection($productId, (int)$storeId, $startDate, $endDate);

            if (!empty($orderIds)) {
                $orderCollection = $this->orderCollectionFactory->create();
                $orderCollection->addAttributeToSelect(OrderInterface::CUSTOMER_EMAIL)
                    ->addFieldToFilter(OrderInterface::ENTITY_ID, ['in' => $orderIds])
                    ->addFieldToFilter(OrderInterface::STORE_ID, ['eq' => $storeId]);
                $orderCollection->getSelect()
                    ->distinct()
                    ->group(OrderInterface::CUSTOMER_EMAIL);

                $this->logger->debug((string)$orderCollection->getSelect());

                $productPurchaseCountOriginal->setCount($orderCollection->getSize());
            } else {
                $productPurchaseCountOriginal->setCount(0);
            }

            $timeEnd = microtime(true);
            $executionTime = $timeEnd - $timeStart;

            $this->logger->debug('Collection count: ' . $productPurchaseCountOriginal->getCount());
            $this->logger->debug('From Date: ' . $startDate);
            $this->logger->debug('To Date: ' . $endDate);
            $this->logger->debug('Store Id: ' . $storeId);
            $this->logger->debug('Time: ' . $executionTime);
            $this->logger->debug('-----------');

            //-------------------

            $timeStart = microtime(true);

            $this->logger->debug('Space getOrdersCount API Direct');

            $productPurchaseCount = $this->productPurchaseCountFactory->create();
            $orderIds = $this->fetchOrderIdsByProductId($productId, (int)$storeId, $startDate, $endDate);

            if (!empty($orderIds)) {
                $orderCount = $this->fetchOrdersCountByOrderIds(array_unique($orderIds), (int)$storeId);
                $productPurchaseCount->setCount($orderCount);
                $productPurchaseCount->setNotificationText($this->convertNotificationText($orderCount));
            } else {
                $productPurchaseCount->setCount(0);
                $productPurchaseCount->setNotificationText('');
            }

            $timeEnd = microtime(true);
            $executionTime = $timeEnd - $timeStart;

            $this->logger->debug('Direct count: ' . $productPurchaseCount->getCount());
            $this->logger->debug('From Date: ' . $startDate);
            $this->logger->debug('To Date: ' . $endDate);
            $this->logger->debug('Store Id: ' . $storeId);
            $this->logger->debug('Time: ' . $executionTime);
            $this->logger->debug('-----------');

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
     * @param int $storeId
     * @return int
     * @throws LocalizedException
     */
    private function fetchOrdersCountByOrderIds(array $orderIds, int $storeId): int
    {
        $connection = $this->resourceOrder->getConnection();
        $select = $connection->select()
            ->from(
                $this->resourceOrder->getMainTable(),
                new \Zend_Db_Expr('COUNT(DISTINCT customer_email)')
            )
            ->where(OrderInterface::ENTITY_ID . ' IN (?)', $orderIds)
            ->where('store_id = ?', $storeId);

        $this->logger->debug((string)$select);

        return (int)$connection->fetchOne($select);
    }

    /**
     * Fetch order Ids by product ID
     *
     * @param int $productId
     * @param int $storeId
     * @param string $startDate
     * @param string $endDate
     * @return array
     * @throws LocalizedException
     */
    private function fetchOrderIdsByProductId(
        int $productId,
        int $storeId,
        string $startDate,
        string $endDate
    ): array {
        $connection = $this->resourceItem->getConnection();
        $select = $connection->select()->distinct()
            ->from($this->resourceItem->getMainTable(), OrderItemInterface::ORDER_ID)
            ->where(OrderItemInterface::PRODUCT_ID . ' = ?', $productId)
            ->where(OrderItemInterface::STORE_ID . ' = ?', $storeId)
            ->where(OrderItemInterface::CREATED_AT . ' >= ?', $startDate)
            ->where(OrderItemInterface::CREATED_AT . ' <= ?', $endDate);

        $this->logger->debug((string)$select);

        return $connection->fetchCol($select);
    }

    /**
     * Get notification text
     *
     * @param int $orderCount
     * @return string
     */
    private function convertNotificationText(int $orderCount): string
    {
        $notificationText = str_replace('%c', (string)$orderCount, $this->config->getNotificationText());
        if ($orderCount === 1) {
            $notificationText = str_replace('customers', 'customer', $notificationText);
        }

        return $this->escaper->escapeHtml($notificationText, ['strong']);
    }

    /**
     * Get order Ids by product ID collection
     *
     * @param int $productId
     * @param int $storeId
     * string $startDate,
     * string $endDate
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    private function getOrderIdsByProductIdCollection(
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

        $this->logger->debug((string)$orderItemCollection->getSelect());

        return $orderIds;
    }
}
