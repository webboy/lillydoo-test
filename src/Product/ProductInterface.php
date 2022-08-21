<?php


namespace App\Product;


interface ProductInterface
{
    public function getName(): string;
    public function getPrice(): float;
}
