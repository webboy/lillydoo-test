<?php

namespace App\Machine\Slot;

use App\Product\ProductInterface;

interface SlotInterface
{
    /**\
     * @return string
     */
    public function getIdentifier(): string;

    /**
     * @return int
     */
    public function getStock(): int;

    /**
     * @return ProductInterface|null
     */
    public function getProduct(): ProductInterface|null;

    /**
     * @param $quantity
     * @return int
     */
    public function decreaseStock($quantity): int;
}