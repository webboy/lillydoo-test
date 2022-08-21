<?php

declare(strict_types=1);

namespace App\Machine\Purchase;

use App\Exceptions\TransactionException;
use App\Machine\Slot\SlotInterface;

interface TransactionInterface
{
    public function getPaidAmount(): float;

    /**
     * @return float
     */
    public function getChangeAmount(): float;

    /**
     * @return float
     */
    public function getTotalCost(): float;

    /**
     * @return SlotInterface
     */
    public function getSlot(): SlotInterface;

    /**
     * @return int
     */
    public function getQuantity(): int;

    /**
     * @return array
     */
    public function getReturnCoins(): array;


}
