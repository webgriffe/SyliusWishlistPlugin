<?php

declare(strict_types=1);

namespace BitBag\SyliusWishlistPlugin\Context;

use BitBag\SyliusWishlistPlugin\Entity\WishlistInterface;
use BitBag\SyliusWishlistPlugin\Factory\WishlistFactoryInterface;
use BitBag\SyliusWishlistPlugin\Repository\WishlistRepositoryInterface;
use Sylius\Component\Core\Model\ShopUserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final readonly class WishlistContext implements WishlistContextInterface
{
    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private WishlistRepositoryInterface $wishlistRepository,
        private WishlistFactoryInterface $wishlistFactory,
        private string $wishlistCookieToken,
    ) {
    }

    #[\Override]
    public function getWishlist(Request $request): WishlistInterface
    {
        $cookieWishlistToken = $request->cookies->get($this->wishlistCookieToken);

        $token = $this->tokenStorage->getToken();
        $user = $token instanceof TokenInterface ? $token->getUser() : null;

        if (null === $cookieWishlistToken && !$user instanceof UserInterface) {
            return $this->wishlistFactory->createNew();
        }

        if (null !== $cookieWishlistToken && !$user instanceof ShopUserInterface) {
            $byToken = $this->wishlistRepository->findByToken($cookieWishlistToken);
            if ($byToken instanceof WishlistInterface) {
                return $byToken;
            }

            return $this->wishlistFactory->createNew();
        }

        if ($user instanceof ShopUserInterface) {
            $oneByShopUser = $this->wishlistRepository->findOneByShopUser($user);
            if ($oneByShopUser instanceof WishlistInterface) {
                return $oneByShopUser;
            }

            return $this->wishlistFactory->createForUser($user);
        }

        return $this->wishlistFactory->createNew();
    }
}
