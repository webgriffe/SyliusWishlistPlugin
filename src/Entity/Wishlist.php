<?php

declare(strict_types=1);

namespace BitBag\SyliusWishlistPlugin\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Model\ShopUserInterface;

/**
 * @psalm-api
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class Wishlist implements WishlistInterface
{
    private ?int $id = null;

    /** @var Collection<array-key, WishlistProductInterface> */
    private Collection $wishlistProducts;

    private ?ShopUserInterface $shopUser = null;

    private WishlistTokenInterface|string $token;

    public function __construct()
    {
        $this->wishlistProducts = new ArrayCollection();
        $this->token = new WishlistToken();
    }

    #[\Override]
    public function getId(): ?int
    {
        return $this->id;
    }

    #[\Override]
    public function getProducts(): ArrayCollection
    {
        $products = [];

        foreach ($this->wishlistProducts as $wishlistProduct) {
            $products[] = $wishlistProduct->getProduct();
        }

        /** @var ArrayCollection<array-key, ProductInterface> $arrayCollection */
        $arrayCollection = new ArrayCollection($products);

        return $arrayCollection;
    }

    #[\Override]
    public function getProductVariants(): ArrayCollection
    {
        $variants = [];

        foreach ($this->wishlistProducts as $wishlistProduct) {
            $variants[] = $wishlistProduct->getVariant();
        }

        /** @var ArrayCollection<array-key, ProductVariantInterface> $arrayCollection */
        $arrayCollection = new ArrayCollection($variants);

        return $arrayCollection;
    }

    #[\Override]
    public function hasProductVariant(ProductVariantInterface $productVariant): bool
    {
        foreach ($this->wishlistProducts as $wishlistProduct) {
            if ($productVariant === $wishlistProduct->getVariant()) {
                return true;
            }
        }

        return false;
    }

    #[\Override]
    public function getWishlistProducts(): Collection
    {
        return $this->wishlistProducts;
    }

    #[\Override]
    public function hasProduct(ProductInterface $product): bool
    {
        foreach ($this->wishlistProducts as $wishlistProduct) {
            if ($product === $wishlistProduct->getProduct()) {
                return true;
            }
        }

        return false;
    }

    #[\Override]
    public function setWishlistProducts(Collection $wishlistProducts): void
    {
        $this->wishlistProducts = $wishlistProducts;
    }

    #[\Override]
    public function hasWishlistProduct(WishlistProductInterface $wishlistProduct): bool
    {
        return $this->wishlistProducts->contains($wishlistProduct);
    }

    #[\Override]
    public function addWishlistProduct(WishlistProductInterface $wishlistProduct): void
    {
        $productVariant = $wishlistProduct->getVariant();
        if ($productVariant !== null && !$this->hasProductVariant($productVariant)) {
            $wishlistProduct->setWishlist($this);
            $this->wishlistProducts->add($wishlistProduct);
        }
    }

    #[\Override]
    public function getShopUser(): ?ShopUserInterface
    {
        return $this->shopUser;
    }

    #[\Override]
    public function setShopUser(ShopUserInterface $shopUser): void
    {
        $this->shopUser = $shopUser;
    }

    #[\Override]
    public function getToken(): string
    {
        return (string) $this->token;
    }

    #[\Override]
    public function setToken(string $token): void
    {
        $this->token = new WishlistToken($token);
    }

    #[\Override]
    public function removeProduct(WishlistProductInterface $product): void
    {
        if ($this->hasWishlistProduct($product)) {
            $this->wishlistProducts->removeElement($product);
        }
    }

    #[\Override]
    public function removeProductVariant(ProductVariantInterface $variant): void
    {
        foreach ($this->wishlistProducts as $wishlistProduct) {
            if ($wishlistProduct->getVariant() === $variant) {
                $this->wishlistProducts->removeElement($wishlistProduct);
            }
        }
    }
}
