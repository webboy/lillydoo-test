<?php

declare(strict_types=1);

namespace App\Machine;

use App\Exceptions\SlotException;
use App\Exceptions\SnackMachineException;
use App\Exceptions\TransactionException;
use App\Machine\Firmware\FirmwareInterface;
use App\Machine\Purchase\Transaction;
use App\Machine\Purchase\TransactionInterface;
use App\Machine\Slot\Slot;
use App\Machine\Slot\SlotInterface;
use App\Product\Product;
use App\Product\ProductInterface;

class SnackMachine implements MachineInterface
{
    protected FirmwareInterface $firmware;

    /**
     * @var array|SlotInterface[]
     */
    protected array $slots;

    public function __construct(FirmwareInterface $firmware)
    {
        $this->firmware = $firmware;

        //Load the machine with empty slots first
        foreach ($this->firmware->getSlots() as $slotIdentifier)
        {
            $this->slots[$slotIdentifier] = new Slot($slotIdentifier);
        }
    }

    /**
     * @param array $data
     * @throws SnackMachineException
     */
    public function loadMachine(array $data):void
    {
        foreach ($data as $key => $value){
            $product = new Product($value['name'],$value['price']);
            $this->loadProductToSlot(strval($key),$product,$value['stock']);
        }
    }

    /**
     * @throws SlotException|TransactionException|SnackMachineException
     */
    public function makeTransaction($slotIdentifier, $quantity,array $inputBills): TransactionInterface
    {
        $slot       = $this->getSlot($slotIdentifier);

        //Create transaction
        return new Transaction(
            $slot,
            $quantity,
            $inputBills,
            $this->firmware->getAcceptedBills(),
            $this->firmware->getReturnCoins()
        );
    }

    /**
     * @throws TransactionException
     * @throws SlotException
     */
    public function execute(TransactionInterface $purchaseTransaction): TransactionInterface
    {
        $slot       = $purchaseTransaction->getSlot();
        $quantity   = $purchaseTransaction->getQuantity();
        $product    = $slot->getProduct();
        $stock      = $slot->getStock();

        //Check if the slot is not empty
        if (is_null($product)){
            throw new SlotException('Slot has no product');
        }
        //Check for stock
        if ($stock === 0){
            throw new SlotException('Selected product '.$product->getName().' is out of stock');
        }
        //Check if there is enough of the product
        if ($stock < $quantity){
            throw new SlotException('There is not enough of '.$product->getName().' in stock');
        }

        //Check Money
        $cost       = $product->getPrice() * $quantity;
        $paid       = $purchaseTransaction->getPaidAmount();

        if ($cost > $paid){
            throw new TransactionException('Not enough money. Total money added: $'.$paid.'. Total cost: $'.$cost);
        }
        //Calculate change
        $purchaseTransaction->calculateReturnCoins();

        //Reduce the number of slot products
        $this->slots[$slot->getIdentifier()]->decreaseStock($quantity);

        return $purchaseTransaction;
    }

    /**
     * @return array
     */
    public function getFirmwareRows(): array
    {
        return $this->firmware->getRows();
    }

    /**
     * @return array
     */
    public function getFirmwareColumns(): array
    {
        return $this->firmware->getColumns();
    }


    /**
     * @param string $slotIdentifier
     * @return SlotInterface
     * @throws SnackMachineException
     */
    public function getSlot(string $slotIdentifier): SlotInterface
    {
        if ($this->validateSlot($slotIdentifier) === false)
        {
            throw new SlotException('Invalid Slot Identifier');
        }

        return $this->slots[$slotIdentifier];
    }

    /**
     * @param string $slotIdentifier
     * @param ProductInterface $product
     * @param int $stock
     * @return void
     * @throws SnackMachineException
     */
    private function loadProductToSlot(string $slotIdentifier, ProductInterface $product, int $stock): void
    {
        if ($this->validateSlot($slotIdentifier) === false)
        {
            throw new SlotException('Invalid Slot Identifier');
        }

        $this->slots[$slotIdentifier] = new Slot($slotIdentifier, $product, $stock);
    }

    /**
     * @param string $slotIdentifier
     * @return bool
     */
    private function validateSlot(string $slotIdentifier): bool
    {
        return array_key_exists($slotIdentifier,$this->slots);
    }
}
