<?php

declare(strict_types=1);

namespace BitBag\SyliusWishlistPlugin\Entity;

use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

/**
 * @psalm-api
 */
interface WishlistProductInterface extends ResourceInterface
{
    public function getWishlist(): WishlistInterface;

    public function setWishlist(WishlistInterface $wishlist): void;

    public function getProduct(): ?ProductInterface;

    public function setProduct(?ProductInterface $product): void;

    public function getVariant(): ?ProductVariantInterface;

    public function setVariant(?ProductVariantInterface $variant): void;
}
