<?php

declare(strict_types=1);

namespace BitBag\SyliusWishlistPlugin\EventListener;

use BitBag\SyliusWishlistPlugin\Repository\WishlistRepositoryInterface;
use Doctrine\Persistence\ObjectManager;
use Sylius\Component\Core\Model\ShopUserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

final class MergeUserWishlistItemsListener
{
    public function __construct(
        private WishlistRepositoryInterface $wishlistRepository,
        private ObjectManager $wishlistManager,
        private string $wishlistCookieToken,
    ) {
    }

    public function onInteractiveLogin(InteractiveLoginEvent $interactiveLoginEvent): void
    {
        $user = $interactiveLoginEvent->getAuthenticationToken()->getUser();

        if (!$user instanceof ShopUserInterface) {
            return;
        }

        $this->resolveWishlist($interactiveLoginEvent->getRequest(), $user);
    }

    private function resolveWishlist(Request $request, ShopUserInterface $shopUser): void
    {
        $cookieWishlistToken = $request->cookies->get($this->wishlistCookieToken, '');

        $cookieWishlist = $this->wishlistRepository->findByToken($cookieWishlistToken);

        if (null === $cookieWishlist) {
            return;
        }

        $userWishlist = $this->wishlistRepository->findOneByShopUser($shopUser);

        if (null !== $userWishlist) {
            foreach ($cookieWishlist->getWishlistProducts() as $wishlistProduct) {
                $userWishlist->addWishlistProduct($wishlistProduct);
            }
        }

        if (null === $userWishlist) {
            $cookieWishlist->setShopUser($shopUser);
        }

        $this->wishlistManager->flush();
    }
}
