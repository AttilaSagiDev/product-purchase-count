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
     * @param Registry $registry
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Registry $registry,
        StoreManagerInterface $storeManager
    ) {
        $this->registry = $registry;
        $this->storeManager = $storeManager;
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
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreCode(): string
    {
        return $this->storeManager->getStore()->getCode();
    }
}
