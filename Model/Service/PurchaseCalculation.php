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
use Space\ProductPurchaseCount\Model\Config\Source\OrderSates;
use Magento\Sales\Model\Order;

class PurchaseCalculation implements PurchaseCalculationInterface
{
    /**
     * @var ProductPurchaseCountInterfaceFactory
     */
    private ProductPurchaseCountInterfaceFactory $productPurchaseCountFactory;

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
        StoreManagerInterface $storeManager,
        ResourceItem $resourceItem,
        ResourceOrder $resourceOrder,
        DateTime $dateTime,
        ConfigInterface $config,
        Escaper $escaper,
        LoggerInterface $logger
    ) {
        $this->productPurchaseCountFactory = $productPurchaseCountFactory;
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
        $productPurchaseCount = $this->productPurchaseCountFactory->create();
        try {
            $timeStart = microtime(true);

            $storeId = (int)$this->storeManager->getStore()->getId();
            $endDate = $this->dateTime->date(StdlibDateTime::DATE_PHP_FORMAT . ' 23:59:59');
            $startDate = $this->dateTime->date(
                StdlibDateTime::DATE_PHP_FORMAT,
                strtotime($endDate . ' -' . $this->config->getInterval() . ' days')
            );
            $startDate .= ' 00:00:00';

            $orderIds = $this->fetchOrderIdsByProductId($productId, $storeId, $startDate, $endDate);
            if (!empty($orderIds)) {
                $orderCount = $this->fetchOrdersCountByOrderIds(array_unique($orderIds), $storeId);
                $productPurchaseCount->setCount($orderCount);
                $productPurchaseCount->setNotificationText($this->convertNotificationText($orderCount));
            } else {
                $productPurchaseCount->setCount(0);
                $productPurchaseCount->setNotificationText('');
            }
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }

        return $productPurchaseCount;
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

        if ($this->config->getOrdersState() !== OrderSates::ALL) {
            $select->where(OrderInterface::STATE . ' = ?', Order::STATE_COMPLETE);
        }

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
            ->where(OrderItemInterface::CREATED_AT . ' <= ?', $endDate)
            ->limit($this->config->getMaximumOrders());

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
}
