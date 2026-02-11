<?php

declare(strict_types=1);

namespace BitBag\SyliusWishlistPlugin\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Model\ShopUserInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

/**
 * @psalm-api
 */
interface WishlistInterface extends ResourceInterface
{
    /**
     * @return ArrayCollection<array-key, ProductInterface>
     */
    public function getProducts(): ArrayCollection;

    /**
     * @return ArrayCollection<array-key, ProductVariantInterface>
     */
    public function getProductVariants(): ArrayCollection;

    public function hasProductVariant(ProductVariantInterface $productVariant): bool;

    /**
     * @return Collection<array-key, WishlistProductInterface>
     */
    public function getWishlistProducts(): Collection;

    /**
     * @param Collection<array-key, WishlistProductInterface> $wishlistProducts
     */
    public function setWishlistProducts(Collection $wishlistProducts): void;

    public function hasProduct(ProductInterface $product): bool;

    public function hasWishlistProduct(WishlistProductInterface $wishlistProduct): bool;

    public function addWishlistProduct(WishlistProductInterface $wishlistProduct): void;

    public function getShopUser(): ?ShopUserInterface;

    public function setShopUser(ShopUserInterface $shopUser): void;

    public function getToken(): string;

    public function setToken(string $token): void;

    public function removeProduct(WishlistProductInterface $product): void;

    public function removeProductVariant(ProductVariantInterface $variant): void;
}
