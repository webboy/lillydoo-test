<?php

declare(strict_types=1);

namespace App\Command;

use App\Machine\Firmware\WorkingPrototypeFirmware;
use App\Machine\SnackMachine;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class PurchaseCommand extends Command
{
    protected static $defaultName = 'purchase';
    protected static $defaultDescription = 'Make a purchase with the vending machine';

    protected  function configure(): void
    {
        $this->addArgument('slot',InputArgument::REQUIRED,'Slot identifier row+column');
        $this->addArgument('quantity',InputArgument::REQUIRED,'Quantity of the product you wish to purchase');
        $this->addArgument('money',InputArgument::REQUIRED,'Money provided');
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

        try {
            $transaction = $snackMachine->makeTransaction(
                $input->getArgument('slot'),
                intval($input->getArgument('quantity')),
                [$input->getArgument('money')]
            );
        } catch (\Exception $exception){
            $output->writeln('Error: '.$exception->getMessage());
            return Command::FAILURE;
        }

        try {
            $transaction = $snackMachine->execute($transaction);
        } catch (\Exception $exception){
            $output->writeln('Error: '.$exception->getMessage());
            return Command::FAILURE;
        }

        $product    = $transaction->getSlot()->getProduct();
        $qty        = $transaction->getQuantity();

        $output->writeLn("You bought ".$qty." of ".$product->getName()." for ".$transaction->getTotalCost()."€, each for ".$product->getPrice()."€");
        $output->writeLn("Your change is: ".$transaction->getChangeAmount()."€");

        $table = new Table($output);
        $table->setHeaders(['Coins', 'Count']);

        $returnCoins = $transaction->getReturnCoins();

        foreach ($transaction->getReturnCoins() as $key =>$returnCoin) {
            $table->setRow($key,[$returnCoin['value'],$returnCoin['coins']]);
        }

        $table->render();

        return Command::SUCCESS;
    }
}
