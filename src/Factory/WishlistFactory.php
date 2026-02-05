<?php

declare(strict_types=1);

namespace BitBag\SyliusWishlistPlugin\Factory;

use BitBag\SyliusWishlistPlugin\Entity\WishlistInterface;
use Sylius\Component\Core\Model\ShopUserInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

final readonly class WishlistFactory implements WishlistFactoryInterface
{
    public function __construct(private FactoryInterface $wishlistFactory)
    {
    }

    #[\Override]
    public function createNew(): WishlistInterface
    {
        /** @var WishlistInterface $wishlist */
        $wishlist = $this->wishlistFactory->createNew();

        return $wishlist;
    }

    #[\Override]
    public function createForUser(ShopUserInterface $shopUser): WishlistInterface
    {
        $wishlist = $this->createNew();

        $wishlist->setShopUser($shopUser);

        return $wishlist;
    }
}
