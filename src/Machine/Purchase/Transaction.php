<?php

namespace App\Machine\Purchase;

use App\Exceptions\TransactionException;
use App\Machine\Slot\SlotInterface;

class Transaction implements TransactionInterface
{
    protected array $acceptedInputBills = [];

    protected array $inputBills     = [];

    protected SlotInterface $slot;

    protected int $quantity = 0;

    protected array $availableReturnCoins   = [];

    protected array $returnedCoins  = [];

    /**
     * @param SlotInterface $slot
     * @param int $quantity
     * @param array $inputBills
     * @param array $acceptedInputBills
     * @param array $availableReturnCoins
     * @throws TransactionException
     */
    public function __construct(SlotInterface $slot,int $quantity, array $inputBills, array $acceptedInputBills, array $availableReturnCoins)
    {
        $this->slot                 = $slot;
        $this->quantity             = $quantity;
        $this->acceptedInputBills   = $acceptedInputBills;
        $this->availableReturnCoins = $availableReturnCoins;

        foreach ($inputBills as $inputBill)
        {
            $this->addInputBill($inputBill);
        }
    }

    /**
     * @return SlotInterface
     */
    public function getSlot(): SlotInterface
    {
        return $this->slot;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * @throws TransactionException
     */
    private function addInputBill(float $amount)
    {
        //Check if the money bill is accepted
        if(!in_array($amount,$this->acceptedInputBills))
        {
            throw new TransactionException('Money bill of '.$amount.' is not supported');
        }
        $this->inputBills[] = $amount;
    }

    /**
     * @return float
     */
    public function getPaidAmount(): float
    {
        return array_sum($this->inputBills);
    }

    /**
     * @return float
     */
    public function getTotalCost(): float
    {
        return $this->slot->getProduct()->getPrice() * $this->quantity;
    }

    /**
     * @return float
     */
    public function getChangeAmount(): float
    {
        return $this->getPaidAmount() - $this->getTotalCost();
    }

    /**
     * @throws TransactionException
     */
    public function calculateReturnCoins():void
    {
        $change = $this->getChangeAmount();

        $returnCoins = [];
        //We need descending order
        rsort($this->availableReturnCoins);
        $index = 0;
        do{
            $coins = floor($change/$this->availableReturnCoins[$index]);
            if ($coins > 0) {
                $returnCoins[] = [
                    'value' => $this->availableReturnCoins[$index],
                    'coins' => $coins,
                ];
            }
            $change = round(fmod($change,$this->availableReturnCoins[$index]),2);
            $index++;
        } while ($change > 0 && !empty($this->availableReturnCoins[$index]));

        if ($change > 0)
        {
            throw new TransactionException('Unable to return change of '.$this->getChangeAmount());
        }

        $this->returnedCoins = $returnCoins;
    }

    /**
     * @return array
     */
    public function getReturnCoins(): array
    {
        return $this->returnedCoins;
    }
}