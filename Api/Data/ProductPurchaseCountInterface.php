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
    public const NOTIFICATION_TEXT = 'notification_text';

    /**
     * Get product purchase count
     *
     * @return int|null
     */
    public function getCount(): ?int;

    /**
     * Get notification text
     *
     * @return string
     */
    public function getNotificationText(): string;

    /**
     * Set product purchase count
     *
     * @param int $count
     * @return ProductPurchaseCountInterface
     */
    public function setCount(int $count): ProductPurchaseCountInterface;

    /**
     * Set notification text
     *
     * @param string $notificationText
     * @return ProductPurchaseCountInterface
     */
    public function setNotificationText(string $notificationText): ProductPurchaseCountInterface;
}
