<?php

declare(strict_types=1);

namespace BitBag\SyliusWishlistPlugin\Factory;

use BitBag\SyliusWishlistPlugin\Entity\WishlistInterface;
use BitBag\SyliusWishlistPlugin\Entity\WishlistProductInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

interface WishlistProductFactoryInterface extends FactoryInterface
{
    public function createForWishlistAndProduct(WishlistInterface $wishlist, ProductInterface $product): WishlistProductInterface;

    public function createForWishlistAndVariant(WishlistInterface $wishlist, ProductVariantInterface $variant): WishlistProductInterface;
}
