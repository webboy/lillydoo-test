<?php

declare(strict_types=1);

namespace App\Machine;

use App\Exceptions\SlotException;
use App\Exceptions\SnackMachineException;
use App\Exceptions\TransactionException;
use App\Machine\Purchase\Transaction;
use App\Machine\Purchase\TransactionInterface;
use App\Machine\Slot\SlotInterface;

interface MachineInterface
{
    /**
     * @param TransactionInterface $purchaseTransaction
     * @return TransactionInterface
     */
    public function execute(TransactionInterface $purchaseTransaction): TransactionInterface;

    /**
     * @param array $data
     * @return void
     */
    public function loadMachine(array $data): void;

    /**
     * @param string $slotIdentifier
     * @return SlotInterface
     */
    public function getSlot(string $slotIdentifier): SlotInterface;

    /**
     * @throws SlotException|TransactionException|SnackMachineException
     */
    public function makeTransaction($slotIdentifier, $quantity, array $inputBills): TransactionInterface;
}
