<?php

namespace App\Machine\Slot;

use App\Product\ProductInterface;

class Slot implements SlotInterface
{

    protected string $slotIdentifier;

    protected ProductInterface|null $product;

    protected int $stock;

    /**
     * @param string $slotIdentifier
     * @param ProductInterface|null $product
     * @param int $stock
     */
    public function __construct(string $slotIdentifier, ProductInterface $product = null, int $stock=10)
    {
        $this->slotIdentifier = $slotIdentifier;
        $this->product = $product;

        //Set stock if product is not null
        if (is_null($product)){
            $this->stock = 0;
        } else {
            $this->stock = $stock;
        }
    }

    /**
     * @inheritDoc
     */
    public function getIdentifier(): string
    {
        return $this->slotIdentifier;
    }

    /**
     * @inheritDoc
     */
    public function getStock(): int
    {
        return $this->stock;
    }

    /**
     * @param $quantity
     * @return int
     */
    public function decreaseStock($quantity): int
    {
        return $this->stock = $this->stock - $quantity;
    }

    /**
     * @inheritDoc
     */
    public function getProduct(): ProductInterface|null
    {
        return $this->product;
    }
}