<?php

namespace abenevaut\Stripe\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\DataTransferObject\DataTransferObject;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class ProductEntity extends DataTransferObject
{
//    use HasFactory;

    public string $name;

//    protected static function newFactory(): UptimeEntityFactory
//    {
//        return UptimeEntityFactory::new();
//    }

    /**
     * @throws UnknownProperties
     */
    public static function make(array $product)
    {
        return new ProductEntity($product);
    }
}
