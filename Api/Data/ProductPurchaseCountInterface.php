<?php
/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\ProductPurchaseCount\Api\Data;

interface ProductPurchaseCountInterface
{
    /**
     * Constants for keys of data array
     */
    public const COUNT = 'count';

    /**
     * Get product purchase count
     *
     * @return int|null
     */
    public function getCount(): ?int;

    /**
     * Set product purchase count
     *
     * @param int $count
     * @return ProductPurchaseCountInterface
     */
    public function setCount(int $count): ProductPurchaseCountInterface;
}
