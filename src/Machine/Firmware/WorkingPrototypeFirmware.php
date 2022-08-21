<?php

declare(strict_types=1);

namespace App\Machine\Firmware;

class WorkingPrototypeFirmware implements FirmwareInterface
{
    /**
     * @var array
     */
    protected array $rows           = [1,2];

    /**
     * @var array
     */
    protected array $columns        = ['a','b'];

    /**
     * @var array
     */
    protected array $acceptedBills  = [50,20,10,5];

    /**
     * @var array
     */
    protected array $returnCoins    = [2,1,0.5,0.2,0.1,0.05,0.02,0.01];

    /**
     * @return array
     */
    public function getSlots(): array
    {
        $slots = [];
        foreach ($this->rows as $row){
            foreach ($this->columns as $column){
                $slots[] = $row.$column;
            }
        }

        return $slots;
    }

    /**
     * @return array
     */
    public function getRows(): array
    {
        return $this->rows;
    }

    /**
     * @return array
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @return array
     */
    public function getAcceptedBills(): array
    {
        return $this->acceptedBills;
    }

    /**
     * @return array
     */
    public function getReturnCoins(): array
    {
        return $this->returnCoins;
    }
}
