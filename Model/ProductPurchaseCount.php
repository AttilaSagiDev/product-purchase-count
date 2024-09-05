<?php
/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\ProductPurchaseCount\Model;

use Magento\Framework\Model\AbstractModel;
use Space\ProductPurchaseCount\Api\Data\ProductPurchaseCountInterface;

class ProductPurchaseCount extends AbstractModel implements ProductPurchaseCountInterface
{
    /**
     * Get product purchase count
     *
     * @return int|null
     */
    public function getCount(): ?int
    {
        return $this->getData(self::COUNT);
    }

    /**
     * Set product purchase count
     *
     * @param int $count
     * @return ProductPurchaseCountInterface
     */
    public function setCount(int $count): ProductPurchaseCountInterface
    {
        return $this->setData(self::COUNT, $count);
    }
}
