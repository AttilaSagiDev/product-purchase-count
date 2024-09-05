<?php
/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\ProductPurchaseCount\ViewModel\Catalog\View;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\Registry;
use Space\ProductPurchaseCount\Api\PurchaseCalculationInterface;
use Psr\Log\LoggerInterface;

class Count implements ArgumentInterface
{
    /**
     * @var Registry
     */
    private Registry $registry;

    /**
     * @var PurchaseCalculationInterface
     */
    private PurchaseCalculationInterface $purchaseCalculation;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param Registry $registry
     * @param PurchaseCalculationInterface $purchaseCalculation
     * @param LoggerInterface $logger
     */
    public function __construct(
        Registry $registry,
        PurchaseCalculationInterface $purchaseCalculation,
        LoggerInterface $logger
    ) {
        $this->registry = $registry;
        $this->purchaseCalculation = $purchaseCalculation;
        $this->logger = $logger;
    }

    /**
     * Get product Id
     *
     * @return int
     */
    public function getProductId(): int
    {
        return (int)$this->registry->registry('product')->getId();
    }

    /**
     * Get orders count
     *
     * @return int|null
     */
    public function getOrdersCount(): ?int
    {
        $timeStart = microtime(true);

        $orderCount = $this->purchaseCalculation->getPurchaseCount($this->getProductId())->getCount();

        $timeEnd = microtime(true);
        $executionTime = $timeEnd - $timeStart;

        $this->logger->debug('Space getOrdersCount');
        $this->logger->debug('Time: '. $executionTime);

        return $orderCount;
    }
}
