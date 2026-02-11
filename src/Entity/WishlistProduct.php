<?php

declare(strict_types=1);

namespace BitBag\SyliusWishlistPlugin\Entity;

use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;

/**
 * @psalm-api
 *
 * @psalm-suppress MissingConstructor
 */
final class WishlistProduct implements WishlistProductInterface
{
    // @phpstan-ignore-next-line
    private ?int $id;

    private WishlistInterface $wishlist;

    private ?ProductInterface $product = null;

    private ?ProductVariantInterface $variant = null;

    #[\Override]
    public function getId(): ?int
    {
        return $this->id;
    }

    #[\Override]
    public function getWishlist(): WishlistInterface
    {
        return $this->wishlist;
    }

    #[\Override]
    public function setWishlist(WishlistInterface $wishlist): void
    {
        $this->wishlist = $wishlist;
    }

    #[\Override]
    public function getProduct(): ?ProductInterface
    {
        return $this->product;
    }

    #[\Override]
    public function setProduct(?ProductInterface $product): void
    {
        $this->product = $product;
    }

    #[\Override]
    public function getVariant(): ?ProductVariantInterface
    {
        return $this->variant;
    }

    #[\Override]
    public function setVariant(?ProductVariantInterface $variant): void
    {
        $this->variant = $variant;
    }
}
