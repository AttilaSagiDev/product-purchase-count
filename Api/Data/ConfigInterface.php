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
     * Notification position config path
     */
    public const XML_PATH_NOTIFICATION_POSITION =
        'product_purchase_count/product_purchase_count_display/notification_position';

    /**
     * Order state config path
     */
    public const XML_PATH_STATE = 'product_purchase_count/product_purchase_count_orders_settings/state';

    /**
     * Order state config path
     */
    public const XML_PATH_MAXIMUM_ORDERS =
        'product_purchase_count/product_purchase_count_orders_settings/maximum_orders';

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

    /**
     * Get notification position
     *
     * @return string
     */
    public function getNotificationPosition(): string;

    /**
     * Get orders state
     *
     * @return string
     */
    public function getOrdersState(): string;

    /**
     * Get maximum orders
     *
     * @return int
     */
    public function getMaximumOrders(): int;
}
