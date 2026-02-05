<?php

declare(strict_types=1);

namespace BitBag\SyliusWishlistPlugin\EventListener;

use Sylius\Component\Core\Model\ShopUserInterface;
use Sylius\Component\Resource\Storage\StorageInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

final class ClearCookieWishlistItemsListener
{
    public function __construct(private StorageInterface $cookieStorage, private string $wishlistCookieToken)
    {
    }

    public function onInteractiveLogin(InteractiveLoginEvent $interactiveLoginEvent): void
    {
        $user = $interactiveLoginEvent->getAuthenticationToken()->getUser();

        if (!$user instanceof ShopUserInterface) {
            return;
        }

        $this->cookieStorage->set($this->wishlistCookieToken, null);
    }
}
