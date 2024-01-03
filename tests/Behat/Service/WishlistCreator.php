<?php

/*
 * This file was created by developers working at BitBag
 * Do you need more information about us and what we do? Visit our https://bitbag.io website!
 * We are hiring developers from all over the world. Join us and start your new, exciting adventure and become part of us: https://bitbag.io/career
*/

declare(strict_types=1);

namespace Tests\BitBag\SyliusWishlistPlugin\Behat\Service;

use BitBag\SyliusWishlistPlugin\Factory\WishlistFactoryInterface;
use BitBag\SyliusWishlistPlugin\Factory\WishlistProductFactoryInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ShopUserInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class WishlistCreator implements WishlistCreatorInterface
{
    private WishlistFactoryInterface $wishlistFactory;

    private WishlistProductFactoryInterface $wishlistProductFactory;

    private RepositoryInterface $wishlistRepository;

    public function __construct(
        WishlistFactoryInterface $wishlistFactory,
        WishlistProductFactoryInterface $wishlistProductFactory,
        RepositoryInterface $wishlistRepository,
    ) {
        $this->wishlistFactory = $wishlistFactory;
        $this->wishlistProductFactory = $wishlistProductFactory;
        $this->wishlistRepository = $wishlistRepository;
    }

    public function createWishlistWithProductAndUser(ShopUserInterface $shopUser, ProductInterface $product): void
    {
        $wishlist = $this->wishlistFactory->createForUser($shopUser);
        $wishlistProduct = $this->wishlistProductFactory->createForWishlistAndProduct($wishlist, $product);

        $wishlist->addWishlistProduct($wishlistProduct);

        $this->wishlistRepository->add($wishlist);
    }
}
