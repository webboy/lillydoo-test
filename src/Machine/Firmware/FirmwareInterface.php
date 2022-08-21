<?php

declare(strict_types=1);

namespace App\Machine\Firmware;

interface FirmwareInterface
{
    /**
     * @return array
     */
    public function getSlots(): array;

    /**
     * @return array
     */
    public function getAcceptedBills(): array;

    /**
     * @return array
     */
    public function getReturnCoins(): array;

    /**
     * @return array
     */
    public function getColumns(): array;

    /**
     * @return array
     */
    public function getRows(): array;
}
