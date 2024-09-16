<?php
/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\ProductPurchaseCount\ViewModel\Catalog\View;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Space\ProductPurchaseCount\Api\Data\ConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Space\ProductPurchaseCount\Model\Config\Source\NotificationPosition;

class Count implements ArgumentInterface
{
    /**
     * @var Registry
     */
    private Registry $registry;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var ConfigInterface
     */
    private ConfigInterface $config;

    /**
     * @param Registry $registry
     * @param StoreManagerInterface $storeManager
     * @param ConfigInterface $config
     */
    public function __construct(
        Registry $registry,
        StoreManagerInterface $storeManager,
        ConfigInterface $config
    ) {
        $this->registry = $registry;
        $this->storeManager = $storeManager;
        $this->config = $config;
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
     * Get store code
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getStoreCode(): string
    {
        return $this->storeManager->getStore()->getCode();
    }

    /**
     * Get position to show
     *
     * @return string
     */
    public function getPositionToShow(): string
    {
        return $this->config->getNotificationPosition();
    }
}
