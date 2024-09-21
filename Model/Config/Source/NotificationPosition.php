<?php
/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\ProductPurchaseCount\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class NotificationPosition implements OptionSourceInterface
{
    /**
     * After product info
     */
    public const AFTER_PRODUCT_INFO = 'product.info.main';

    /**
     * After media gallery
     */
    public const AFTER_MEDIA_GALLERY = 'product.info.media';

    /**
     * Return array of options as value-label pairs
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            [
                'label' => __('After Product Info'),
                'value' => self::AFTER_PRODUCT_INFO,
            ],
            [
                'label' => __('After Media Gallery'),
                'value' => self::AFTER_MEDIA_GALLERY,
            ]
        ];
    }
}
