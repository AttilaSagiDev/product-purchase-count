<?php
/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\ProductPurchaseCount\Api\Data;

interface ConfigInterface
{
    /**
     * Enabled config path
     */
    public const XML_PATH_ENABLED = 'product_purchase_count/product_purchase_count_config/enabled';

    /**
     * Check if product purchase count module is enabled
     *
     * @return bool
     */
    public function isEnabled(): bool;
}
