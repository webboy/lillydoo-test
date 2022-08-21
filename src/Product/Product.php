<?php

namespace App\Product;

class Product implements ProductInterface
{
    private string $name;

    private float $price;

    public function __construct($name,$price)
    {
        $this->name = $name;
        $this->price = $price;
    }


    public function getName(): string
    {
        return $this->name;
    }

    public function getPrice(): float
    {
        return $this->price;
    }
}