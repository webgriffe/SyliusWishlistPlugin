<?php

declare(strict_types=1);

namespace Tests\BitBag\SyliusWishlistPlugin\Behat\Service;

use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ShopUserInterface;

interface WishlistCreatorInterface
{
    public function createWishlistWithProductAndUser(ShopUserInterface $shopUser, ProductInterface $product): void;
}
