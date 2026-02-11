<?php

declare(strict_types=1);

namespace BitBag\SyliusWishlistPlugin\Factory;

use BitBag\SyliusWishlistPlugin\Entity\WishlistInterface;
use Sylius\Component\Core\Model\ShopUserInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

interface WishlistFactoryInterface extends FactoryInterface
{
    #[\Override]
    public function createNew(): WishlistInterface;

    public function createForUser(ShopUserInterface $shopUser): WishlistInterface;
}
