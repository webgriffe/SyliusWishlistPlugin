<?php

declare(strict_types=1);

namespace Tests\BitBag\SyliusWishlistPlugin\Behat\Page\Shop;

use Sylius\Behat\Page\Shop\Product\IndexPageInterface;

interface ProductIndexPageInterface extends IndexPageInterface
{
    public function addProductToWishlist(string $productName): void;
}
