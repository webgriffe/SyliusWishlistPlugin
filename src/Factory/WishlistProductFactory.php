<?php

declare(strict_types=1);

namespace BitBag\SyliusWishlistPlugin\Factory;

use BitBag\SyliusWishlistPlugin\Entity\WishlistInterface;
use BitBag\SyliusWishlistPlugin\Entity\WishlistProductInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

final readonly class WishlistProductFactory implements WishlistProductFactoryInterface
{
    public function __construct(private FactoryInterface $wishlistProductFactory)
    {
    }

    #[\Override]
    public function createNew(): WishlistProductInterface
    {
        /** @var WishlistProductInterface $wishlistProduct */
        $wishlistProduct = $this->wishlistProductFactory->createNew();

        return $wishlistProduct;
    }

    #[\Override]
    public function createForWishlistAndProduct(WishlistInterface $wishlist, ProductInterface $product): WishlistProductInterface
    {
        $wishlistProduct = $this->createNew();

        $wishlistProduct->setWishlist($wishlist);
        $wishlistProduct->setProduct($product);
        /** @var ProductVariantInterface $variant */
        $variant = $product->getVariants()->first();
        $wishlistProduct->setVariant($variant);

        return $wishlistProduct;
    }

    #[\Override]
    public function createForWishlistAndVariant(WishlistInterface $wishlist, ProductVariantInterface $variant): WishlistProductInterface
    {
        $wishlistProduct = $this->createNew();

        $wishlistProduct->setWishlist($wishlist);
        /** @var ProductInterface $product */
        $product = $variant->getProduct();
        $wishlistProduct->setProduct($product);
        $wishlistProduct->setVariant($variant);

        return $wishlistProduct;
    }
}
