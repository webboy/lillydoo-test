<?php

namespace Unit;

use App\Exceptions\SlotException;
use App\Exceptions\SnackMachineException;
use App\Exceptions\TransactionException;
use App\Machine\Firmware\WorkingPrototypeFirmware;
use App\Machine\SnackMachine;
use PHPUnit\Framework\TestCase;

class SnackMachineTest extends TestCase
{
    private array $loadData = [
        '1a'    => [
            'name'  => 'Mars',
            'price' => 2.49,
            'stock' => 12
        ],
        '1b'    => [
            'name'  => 'Sneakers',
            'price' => 2.29,
            'stock' => 10
        ],
        '2a'    => [
            'name'  => 'Coke',
            'price' => 1.19,
            'stock' => 17
        ],
    ];
    /**
     * @throws SnackMachineException
     */
    public function testGetSlot(){
        $snackMachine = new SnackMachine(new WorkingPrototypeFirmware());

        $slotIdentifier = '2a';

        $snackMachine->loadMachine($this->loadData);

        $slot = $snackMachine->getSlot($slotIdentifier);

        $this->assertEquals($this->loadData[$slotIdentifier]['name'],$slot->getProduct()->getName());
        $this->assertEquals($this->loadData[$slotIdentifier]['price'],$slot->getProduct()->getPrice());
        $this->assertEquals($this->loadData[$slotIdentifier]['stock'],$slot->getStock());

        $this->expectException(SnackMachineException::class);
        $slot = $snackMachine->getSlot('sasasasasa');

    }

    /**
     * @throws SnackMachineException
     */
    public function testGetSlotFails(){
        $snackMachine = new SnackMachine(new WorkingPrototypeFirmware());
        $snackMachine->loadMachine($this->loadData);
        $this->expectException(SnackMachineException::class);
        $slot = $snackMachine->getSlot('sasasasasa');
    }

    public function testEmptySlot(){
        $snackMachine = new SnackMachine(new WorkingPrototypeFirmware());
        $slotIdentifier = '2b';
        $snackMachine->loadMachine($this->loadData);
        $slot = $snackMachine->getSlot($slotIdentifier);

        $this->assertEquals(0,$slot->getStock());
        $this->assertNull($slot->getProduct());
    }

    /**
     * @throws SnackMachineException
     * @throws TransactionException
     * @throws SlotException
     */
    public function testExecute(){
        $snackMachine = new SnackMachine(new WorkingPrototypeFirmware());
        $snackMachine->loadMachine($this->loadData);

        $slotIdentifier = '1a';
        $qty = 2;
        $bills = [10];

        //Expect certain stock
        $slot = $snackMachine->getSlot($slotIdentifier);
        $this->assertEquals($this->loadData[$slotIdentifier]['stock'],$slot->getStock());

        $transaction = $snackMachine->makeTransaction($slotIdentifier,$qty,$bills);
        $transaction = $snackMachine->execute($transaction);

        $this->assertEquals($this->loadData[$slotIdentifier]['price']*$qty,$transaction->getTotalCost());
        $this->assertEquals(array_sum($bills)-$this->loadData[$slotIdentifier]['price']*$qty,$transaction->getChangeAmount());
        //We expect 2 coins of 2 and 1 coin of 1 and 1 coin of 0.02 in our change
        $changeCoins = $transaction->getReturnCoins();

        $this->assertIsArray($changeCoins);
        $this->assertArrayHasKey(0,$changeCoins);
        $this->assertArrayHasKey(1,$changeCoins);
        $this->assertArrayHasKey(2,$changeCoins);
        $this->assertEquals(2,$changeCoins[0]['coins']);

        //Expect stock to be lowered by $qty
        $slot = $snackMachine->getSlot($slotIdentifier);
        $this->assertEquals($this->loadData[$slotIdentifier]['stock'] - $qty,$slot->getStock());
    }

    /**
     * @throws SnackMachineException
     * @throws TransactionException
     * @throws SlotException
     */
    public function testExecuteNotEnoughMoney()
    {
        $snackMachine = new SnackMachine(new WorkingPrototypeFirmware());
        $snackMachine->loadMachine($this->loadData);

        $slotIdentifier = '1a';
        $qty = 3;
        $bills = [5];

        $transaction = $snackMachine->makeTransaction($slotIdentifier,$qty,$bills);

        $this->expectException(TransactionException::class);
        $snackMachine->execute($transaction);
    }
}


