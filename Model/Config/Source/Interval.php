<?php
/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\ProductPurchaseCount\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Interval implements OptionSourceInterface
{
    /**
     * Last three days
     */
    public const LAST_THREE_DAYS = 3;

    /**
     * Last week
     */
    public const LAST_WEEK = 7;

    /**
     * Return array of options as value-label pairs
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            [
                'label' => __('Last 3 days'),
                'value' => self::LAST_THREE_DAYS,
            ],
            [
                'label' => __('Last week (7 days)'),
                'value' => self::LAST_WEEK,
            ]
        ];
    }
}
