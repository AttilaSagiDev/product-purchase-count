<?php
/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\ProductPurchaseCount\Api;

use Space\ProductPurchaseCount\Api\Data\ProductPurchaseCountInterface;

interface PurchaseCalculationInterface
{
    /**
     * Get purchase count
     *
     * @param int $productId
     * @return Space\ProductPurchaseCount\Api\Data\ProductPurchaseCountInterface
     */
    public function getPurchaseCount(int $productId): ProductPurchaseCountInterface;
}
