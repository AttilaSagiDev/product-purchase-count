<?php
/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\ProductPurchaseCount\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Sales\Model\Order;

class OrderSates implements OptionSourceInterface
{
    /**
     * All order states
     */
    public const ALL = 'all';

    /**
     * Return array of options as value-label pairs
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            [
                'label' => __('All order states'),
                'value' => self::ALL,
            ],
            [
                'label' => __('Completed only'),
                'value' => Order::STATE_COMPLETE,
            ]
        ];
    }
}
