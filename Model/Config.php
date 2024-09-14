<?php
/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\ProductPurchaseCount\Model;

use Magento\Store\Model\ScopeInterface;
use Space\ProductPurchaseCount\Api\Data\ConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Space\ProductPurchaseCount\Model\Config\Source\Interval;

class Config implements ConfigInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * Constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Check if product purchase count module is enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            ConfigInterface::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Get interval
     *
     * @return int
     */
    public function getInterval(): int
    {
        return (int)$this->scopeConfig->getValue(
            ConfigInterface::XML_PATH_INTERVAL,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Get notification text
     *
     * @return string
     */
    public function getNotificationText(): string
    {
        if ($this->getInterval() === Interval::LAST_THREE_DAYS) {
            return $this->scopeConfig->getValue(
                ConfigInterface::XML_PATH_NOTIFICATION_TEXT,
                ScopeInterface::SCOPE_WEBSITE
            );
        }

        return $this->scopeConfig->getValue(
            ConfigInterface::XML_PATH_EXTENDED_NOTIFICATION_TEXT,
            ScopeInterface::SCOPE_WEBSITE
        );
    }
}
