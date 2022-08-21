<?php

declare(strict_types=1);

namespace App\Command;

use App\Machine\Firmware\WorkingPrototypeFirmware;
use App\Machine\SnackMachine;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ShowStockCommand extends Command
{
    protected static $defaultName = 'show-stock';
    protected static $defaultDescription = 'Shows product slots in the vending machine';

    protected  function configure(): void
    {
        //...
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $snackMachine = new SnackMachine(new WorkingPrototypeFirmware());

        try {
            $snackMachine->loadMachine(json_decode(file_get_contents('products.json'),true));
        } catch (\Exception $exception)
        {
            $output->writeln('Error: '.$exception->getMessage());
            return Command::FAILURE;
        }


        $table = new Table($output);


        $table->setHeaders(array_merge([''],$snackMachine->getFirmwareColumns()));

        foreach ($snackMachine->getFirmwareRows() as $rowIdentifier){
            $row = [$rowIdentifier];
            foreach ($snackMachine->getFirmwareColumns() as $columnIdentifier){
                $slot = $snackMachine->getSlot($rowIdentifier.$columnIdentifier);
                $product = $slot->getProduct();
                if (!empty($product)){
                    array_push($row,$product->getName().' ('.$slot->getStock().') @ $'.$product->getPrice());
                } else {
                    array_push($row,'N/A');
                }
            }
            $table->setRow($rowIdentifier,$row);
        }

        $table->render();

        return Command::SUCCESS;
    }
}
