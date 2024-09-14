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
     * Interval config path
     */
    public const XML_PATH_INTERVAL = 'product_purchase_count/product_purchase_count_display/interval';

    /**
     * Base notification config path
     */
    public const XML_PATH_NOTIFICATION_TEXT = 'product_purchase_count/product_purchase_count_display/notification_text';

    /**
     * Extended notification config path
     */
    public const XML_PATH_EXTENDED_NOTIFICATION_TEXT =
        'product_purchase_count/product_purchase_count_display/extended_notification_text';

    /**
     * Check if product purchase count module is enabled
     *
     * @return bool
     */
    public function isEnabled(): bool;

    /**
     * Get interval
     *
     * @return int
     */
    public function getInterval(): int;

    /**
     * Get notification text
     *
     * @return string
     */
    public function getNotificationText(): string;
}
